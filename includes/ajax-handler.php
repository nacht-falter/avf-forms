<?php

add_action('wp_ajax_avf_membership_action', 'avf_handle_ajax_membership_requests');
add_action('wp_ajax_avf_schnupperkurs_action', 'avf_handle_ajax_schnupperkurs_requests');
add_action('wp_ajax_avf_download_csv', 'avf_generate_csv_download');
add_action('wp_ajax_avf_fetch_memberships', 'avf_fetch_membership_data');
add_action('wp_ajax_avf_fetch_schnupperkurse', 'avf_fetch_schnupperkurs_data');
add_action('wp_ajax_avf_get_membership_stats', 'avf_get_membership_stats');
add_action('wp_ajax_avf_get_follow_ups', 'avf_get_follow_ups');

function avf_validate_user_and_nonce()
{
    if (!current_user_can('manage_memberships')) {
        wp_send_json_error('You do not have permission to perform this action.');
        wp_die();
    }
    if (!check_ajax_referer('avf_membership_action', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }
}

function avf_handle_insert_update($table_name, $data, $action_type, $id = null)
{
    if (str_contains($table_name, 'avf_memberships')) {
        $redirect_url = admin_url('admin.php?page=avf-membership-admin');
    } elseif (str_contains($table_name, 'avf_schnupperkurse')) {
        $redirect_url = admin_url('admin.php?page=avf-schnupperkurs-admin');
    }

    global $wpdb;
    if ($action_type === 'update' && $id) {
        $wpdb->update($table_name, $data, ['id' => $id]);
        return array('status' => 'success', 'message' => 'Die Einträge wurden erfolgreich aktualisiert');
    } else {
        $wpdb->insert($table_name, $data);
        return array('status' => 'success', 'message' => 'Die Einträge wurden erfolgreich hinzugefügt', 'redirect_url' => $redirect_url);
    }
}

function avf_handle_delete($table_name, $id)
{
    global $wpdb;
    $id = intval($id);

    $deleted = $wpdb->delete($table_name, ['id' => $id]);

    if ($deleted) {
        return array('status' => 'success', 'message' => 'Record successfully deleted');
    } else {
        return array('status' => 'error', 'message' => 'Error deleting record');
    }
}

function avf_handle_bulk_delete($table_name, $ids)
{
    global $wpdb;
    $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $query = "DELETE FROM $table_name WHERE id IN ($placeholders)";
    $deleted = $wpdb->query($wpdb->prepare($query, ...$ids));

    if ($deleted) {
        return array('status' => 'success', 'message' => "$deleted record(s) successfully deleted.");
    } else {
        return array('status' => 'error', 'message' => 'Error deleting records.');
    }
}

function avf_calculate_resignation_date($kuendigungseingang)
{
    $input_date = new DateTime($kuendigungseingang);

    $current_quarter = ceil($input_date->format('n') / 3);
    $quarter_end_month = $current_quarter * 3;
    $quarter_end = new DateTime($input_date->format('Y') . '-' . $quarter_end_month . '-01');
    $quarter_end->modify('last day of this month');

    // Period of notice is 6 weeks
    $threshold = clone $quarter_end;
    $threshold->modify('-6 weeks');

    if ($input_date > $threshold) {
        $next_quarter = $current_quarter + 1;
        $next_year = $input_date->format('Y');

        // Handle last quarter of the year
        if ($next_quarter > 4) {
            $next_quarter = 1;
            $next_year++;
        }

        $next_quarter_month = $next_quarter * 3;
        $next_quarter_end = new DateTime("$next_year-$next_quarter_month-01");
        $next_quarter_end->modify('last day of this month');

        return $next_quarter_end->format('Y-m-d');
    } else {
        return $quarter_end->format('Y-m-d');
    }
}

function avf_handle_ajax_membership_requests()
{
    avf_validate_user_and_nonce();

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $action_type = $_POST['action_type'];
    $response = array('status' => 'error', 'message' => 'Invalid action');

    if ($action_type === 'create' || $action_type === 'update') {
        $kuendigungseingang = sanitize_text_field($_POST['kuendigungseingang'] ?? '');
        $austrittsdatum = sanitize_text_field($_POST['austrittsdatum'] ?? '');
        $wiedervorlage = sanitize_text_field($_POST['wiedervorlage'] ?? '');
        $wiedervorlage_grund = sanitize_text_field($_POST['wiedervorlage-grund'] ?? '');

        // Autofill reminder fields on cancellation
        if (!empty($kuendigungseingang) && empty($austrittsdatum)) {
            $austrittsdatum = avf_calculate_resignation_date($kuendigungseingang);

            $wiedervorlage_date = date('Y-m-d', strtotime($austrittsdatum . ' -2 months + 15 days'));
            $delete_sepa_date = date('m/Y', strtotime($austrittsdatum . ' +1 day'));

            if (strpos($wiedervorlage_grund, 'SEPA löschen') === false) {
                $wiedervorlage_grund = empty($wiedervorlage_grund)
                ? "SEPA löschen ab {$delete_sepa_date}"
                : "SEPA löschen ab {$delete_sepa_date}, " . $wiedervorlage_grund;
            }

            $wiedervorlage = empty($wiedervorlage)
                ? $wiedervorlage_date
                : (strtotime($wiedervorlage) < strtotime($wiedervorlage_date)
                    ? $wiedervorlage
                    : $wiedervorlage_date);
        }

        $data = [
            'mitgliedschaft_art' => sanitize_text_field($_POST['mitgliedschaft_art']),
            'vorname' => sanitize_text_field($_POST['vorname']),
            'nachname' => sanitize_text_field($_POST['nachname']),
            'vorname_eltern' => sanitize_text_field($_POST['vorname_eltern']),
            'nachname_eltern' => sanitize_text_field($_POST['nachname_eltern']),
            'geschwisterkind' => isset($_POST['geschwisterkind']) ? 1 : 0,
            'email' => sanitize_email($_POST['email']),
            'telefon' => sanitize_text_field($_POST['telefon']),
            'geburtsdatum' => sanitize_text_field($_POST['geburtsdatum']),
            'strasse' => sanitize_text_field($_POST['strasse']),
            'hausnummer' => sanitize_text_field($_POST['hausnummer']),
            'plz' => sanitize_text_field($_POST['plz']),
            'ort' => sanitize_text_field($_POST['ort']),
            'beitrittsdatum' => sanitize_text_field($_POST['beitrittsdatum']),
            'kuendigungseingang' => !empty($kuendigungseingang) ? $kuendigungseingang : null,
            'austrittsdatum' => !empty($austrittsdatum) ? $austrittsdatum : null,
            'starterpaket' => isset($_POST['starterpaket']) ? 1 : 0,
            'spende' => isset($_POST['spende']) ? 1 : 0,
            'spende_monatlich' => isset($_POST['spende_monatlich']) ? floatval($_POST['spende_monatlich']) : null,
            'spende_einmalig' => isset($_POST['spende_einmalig']) ? floatval($_POST['spende_einmalig']) : null,
            'satzung_datenschutz' => isset($_POST['satzung_datenschutz']) ? 1 : 0,
            'hinweise' => isset($_POST['hinweise']) ? 1 : 0,
            'sepa' => isset($_POST['sepa']) ? 1 : 0,
            'kontoinhaber' => sanitize_text_field($_POST['kontoinhaber']),
            'iban' => sanitize_text_field($_POST['iban']),
            'bic' => sanitize_text_field($_POST['bic']),
            'bank' => sanitize_text_field($_POST['bank']),
            'beitrag' => isset($_POST['beitrag']) ? floatval($_POST['beitrag']) : null,
            'wiedervorlage' => !empty($wiedervorlage) ? $wiedervorlage : null,
            'wiedervorlage_grund' => $wiedervorlage_grund,
            'notizen' => sanitize_textarea_field($_POST['notizen'])
        ];

        if ($action_type === 'create') {
            $now = current_time('mysql');
            $data['submission_date'] = $now;
            $data['wiedervorlage'] = $now;
            $data['wiedervorlage_grund'] = "SEPA anlegen";
        }

        $response = avf_handle_insert_update($table_name, $data, $action_type, $_POST['id'] ?? null);

    } elseif ($action_type === 'delete') {
        $response = avf_handle_delete($table_name, $_POST['id']);
    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $response = avf_handle_bulk_delete($table_name, array_map('intval', $_POST['ids']));
    } elseif ($action_type === 'export_csv' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        if (empty($_POST['ids'])) {
            wp_send_json_error('No data selected for export');
        } else {
            $ids = implode(',', array_map('intval', $_POST['ids']));

            $download_url = add_query_arg(
                [
                'action' => 'avf_download_csv',
                'ids' => $ids,
                '_ajax_nonce' => $_POST['_ajax_nonce'],
                ],
                admin_url('admin-ajax.php')
            );
            wp_send_json_success(['download_url' => $download_url]);
        }
    } elseif ($action_type === 'update_fees') {
        if (empty($_POST['avf_beitraege']) || ! is_array($_POST['avf_beitraege']) ) {
            wp_send_json_error('No fee data received.');
        }

        $current_fees = get_option('avf_beitraege');
        $new_fees     = array_map('floatval', $_POST['avf_beitraege']);

        $fees_changed = update_option('avf_beitraege', $new_fees);

        if ($fees_changed
            && isset($_POST['update-existing-fees'])
            && '1' === $_POST['update-existing-fees']
        ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_memberships';

            foreach ($new_fees as $membership_type => $new_fee) {
                if (! isset($current_fees[ $membership_type ]) ) {
                    continue;
                }

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE $table_name SET beitrag = %f
                         WHERE beitrag = %f
                         AND mitgliedschaft_art = %s",
                        $new_fee,
                        $current_fees[ $membership_type ],
                        $membership_type
                    )
                );
            }
        }
        wp_send_json_success(array( 'message' => $fees_changed ? 'Beiträge aktualisiert' : 'Beiträge sind bereits aktuell'));
    } elseif ($action_type === 'send_email') {
        if (empty($_POST['ids'])) {
            wp_send_json_error('No data selected for export');
        } else {
            $ids = array_map('intval', $_POST['ids']);
            avf_send_email_to_members($ids);
        }
    }

    echo json_encode($response);
    wp_die();
}

function avf_handle_ajax_schnupperkurs_requests()
{
    avf_validate_user_and_nonce();

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_schnupperkurse';

    $action_type = $_POST['action_type'];
    $response = array('status' => 'error', 'message' => 'Invalid action');

    if ($action_type === 'create' || $action_type === 'update') {
        $beginn = sanitize_text_field($_POST['beginn']);
        $beginn_date = new DateTime($beginn);
        $ende_date = $beginn_date->modify('+2 months');

        $data = [
            'schnupperkurs_art' => sanitize_text_field($_POST['schnupperkurs_art']),
            'vorname' => sanitize_text_field($_POST['vorname']),
            'nachname' => sanitize_text_field($_POST['nachname']),
            'email' => sanitize_email($_POST['email']),
            'telefon' => sanitize_text_field($_POST['telefon']),
            'geburtsdatum' => sanitize_text_field($_POST['geburtsdatum']),
            'beginn' => $beginn,
            'ende' => $ende_date->format('Y-m-d'),
            'wie_erfahren' => sanitize_text_field($_POST['wie_erfahren']) === 'sonstiges'
                ? sanitize_text_field($_POST['wie_erfahren_sonstiges'])
                : sanitize_text_field($_POST['wie_erfahren']),
            'notizen' => sanitize_textarea_field($_POST['notizen']),
        ];

        if ($action_type === 'create') {
            $data['submission_date'] = current_time('mysql');
        }

        $response = avf_handle_insert_update($table_name, $data, $action_type, $_POST['id'] ?? null);

    } elseif ($action_type === 'delete') {
        $response = avf_handle_delete($table_name, $_POST['id']);
    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $response = avf_handle_bulk_delete($table_name, array_map('intval', $_POST['ids']));
    }

    echo json_encode($response);
    wp_die();
}

function avf_generate_csv_download()
{
    avf_validate_user_and_nonce();

    $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

    if (!empty($ids) && is_array($ids)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_memberships';
        $ids_string = implode(',', array_map('intval', $ids));
        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE id IN ($ids_string)", ARRAY_A);

        if (!empty($data)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="memberships.csv"');

            $header = [
                'Mitgliedschaftsart',
                'Vorname',
                'Nachname',
                'Geschwisterkind',
                'Vorname Eltern',
                'Nachname Eltern',
                'E-Mail',
                'Telefon',
                'Geburtsdatum',
                'Strasse',
                'Hausnummer',
                'PLZ',
                'Ort',
                'Beitrittsdatum',
                'Kündigungseingang',
                'Austrittsdatum',
                'Starterpaket',
                'Spende',
                'Spende monatlich',
                'Spende einmalig',
                'Satzung und Datenschutz',
                'Hinweise',
                'SEPA-Mandat',
                'Kontoinhaber',
                'IBAN',
                'BIC',
                'Bank',
                'Beitrag',
                'Notizen',
                'Eingangsdatum'
            ];

            $output = fopen('php://output', 'w');

            fputcsv($output, $header);

            foreach ($data as $row) {
                fputcsv(
                    $output, [
                        MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']],
                        $row['vorname'],
                        $row['nachname'],
                        Avf_Forms_Utils::format_bool($row['geschwisterkind']),
                        $row['vorname_eltern'],
                        $row['nachname_eltern'],
                        $row['email'],
                        $row['telefon'],
                        Avf_Forms_Utils::format_date($row['geburtsdatum']),
                        $row['strasse'],
                        $row['hausnummer'],
                        $row['plz'],
                        $row['ort'],
                        Avf_Forms_Utils::format_date($row['beitrittsdatum']),
                        Avf_Forms_Utils::format_date($row['kuendigungseingang']),
                        Avf_Forms_Utils::format_date($row['austrittsdatum']),
                        Avf_Forms_Utils::format_bool($row['starterpaket']),
                        Avf_Forms_Utils::format_bool($row['spende']),
                        $row['spende_monatlich'],
                        $row['spende_einmalig'],
                        $row['satzung_datenschutz'] ? 'Akzeptiert' : 'Nicht akzeptiert',
                        $row['hinweise'] ? 'Gelesen' : 'Nicht gelesen',
                        $row['sepa'] ? 'Erteilt' : 'Nicht erteilt',
                        $row['kontoinhaber'],
                        $row['iban'],
                        $row['bic'],
                        $row['bank'],
                        $row['beitrag'],
                        $row['notizen'],
                        Avf_Forms_Utils::format_date($row['submission_date']),
                    ]
                );
            }

            fclose($output);
            exit();
        } else {
            wp_die('No data found for the selected IDs.');
        }
    } else {
        wp_die('Invalid request.');
    }
}

function validate_and_get_params($allowed_columns)
{
    avf_validate_user_and_nonce();

    $params = [
        'column' => isset($_POST['column']) ? sanitize_text_field($_POST['column']) : 'init',
        'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC'
    ];

    if ($params['column'] != 'init' && (!defined($allowed_columns) || !array_key_exists($params['column'], constant($allowed_columns)))) {
        wp_send_json_error('Invalid column: ' . esc_html($params['column']));
    }

    if (!in_array(strtoupper($params['order']), ['ASC', 'DESC'])) {
        wp_send_json_error('Invalid order: ' . esc_html($params['order']));
    }

    return $params;
}

function process_membership_filters($filters)
{
    $allowed_filters = ['aktiv', 'kind', 'sonder', 'passiv', 'foerder', 'beitragsbefreit', 'ausgetreten'];
    $related_filters = [
        'aktiv' => ['aktiv_ermaessigt', 'familie'],
        'kind' => ['jugend'],
    ];

    $active_filters = [];
    foreach ($filters as $filter) {
        if (in_array($filter, $allowed_filters)) {
            $active_filters[] = $filter;
            if (isset($related_filters[$filter])) {
                $active_filters = array_merge($active_filters, $related_filters[$filter]);
            }
        } else {
            wp_send_json_error('Invalid filter: ' . esc_html($filter));
        }
    }

    return array_unique($active_filters);
}

function build_membership_query($table_name, $active_filters, $search, $column, $order)
{
    $query_parts = ['params' => []];
    $where_clauses = [];

    // Special case for "ausgetreten" filter
    if (($index = array_search("ausgetreten", $active_filters, true)) !== false) {
        $where_clauses[] = "(austrittsdatum IS NULL OR austrittsdatum > CURRENT_DATE)";
        unset($active_filters[$index]);
    }

    // Special case for "beitragsbefreit" filter
    if (($index = array_search("beitragsbefreit", $active_filters, true)) !== false) {
        $where_clauses[] = "beitrag != 0";
        unset($active_filters[$index]);
    }

    // Return empty dataset if no active filters are selected
    if (empty($active_filters)) {
        $where_clauses[] = "mitgliedschaft_art IN (NULL)";
    } else {
        $placeholders = implode(', ', array_fill(0, count($active_filters), '%s'));
        $where_clauses[] = "mitgliedschaft_art IN ($placeholders)";
        $query_parts['params'] = array_merge($query_parts['params'], $active_filters);
    }

    if ($search) {
        $search_columns = ['vorname', 'nachname', 'email', 'kontoinhaber', 'notizen'];
        $like_clauses = array_map(fn($col) => "$col LIKE %s", $search_columns);
        $where_clauses[] = '(' . implode(' OR ', $like_clauses) . ')';
        $query_parts['params'] = array_merge(
            $query_parts['params'],
            array_fill(0, count($search_columns), '%' . $search . '%')
        );
    }

    $order_clause = $column === 'init'
        ? 'CASE WHEN wiedervorlage <= CURRENT_DATE THEN 1 ELSE 2 END, mitgliedschaft_art ASC'
        : "$column $order";

    $query_parts['query'] = "SELECT * FROM $table_name";
    if (!empty($where_clauses)) {
        $query_parts['query'] .= " WHERE " . implode(' AND ', $where_clauses);
    }
    $query_parts['query'] .= " ORDER BY $order_clause";

    return $query_parts;
}


function generate_membership_html($results)
{
    $html = '';
    $dateColumns = ['geburtsdatum', 'beitrittsdatum', 'kuendigungseingang', 'austrittsdatum', 'wiedervorlage', 'submission_date'];

    foreach ($results as $row) {
        foreach (array_keys($row) as $key) {
            if ($key === 'notizen') {
                ${'column_' . $key} = nl2br(esc_html($row[$key]));
            } else {
                ${'column_' . $key} = esc_html($row[$key]);
            }

            if ($key === 'id') {
                ${'column_' . $key . '_attr'} = esc_attr($row[$key]);
            }

            if (in_array($key, $dateColumns, true)) {
                $dateString = $row[$key] ?? '';

                if (empty($dateString) || $dateString === '0000-00-00') {
                    ${'column_' . $key} = '';
                } else {
                    try {
                        $date = new DateTime($dateString);
                        ${'column_' . $key} = esc_html($date->format('d.m.Y'));
                    } catch (Exception $e) {
                        // Handle invalid date format
                        ${'column_' . $key} = esc_html($dateString);
                    }
                }
            }
        }

        $classes = [];
        $titleParts = [];

        $age = date_diff(date_create($row['geburtsdatum']), date_create('now'))->y;
        $checkAge = false;
        $markResigned = false;
        $markCancelled = false;
        $markWiedervorlage = false;
        $markCustomBeitrag = false;

        if (($age < 14 && $row['mitgliedschaft_art'] != 'kind')) {
            $checkAge = true;
            $classes[] = 'highlight-yellow';
            $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
        } elseif ($age >= 14 && $age < 18 && $row['mitgliedschaft_art'] != 'jugend') {
            $checkAge = true;
            $classes[] = 'highlight-yellow';
            $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
        } elseif ($age >= 18 && ($row['mitgliedschaft_art'] == 'kind' || $row['mitgliedschaft_art'] == 'jugend')) {
            $checkAge = true;
            $classes[] = 'highlight-yellow';
            $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
        }

        if (!empty($row['austrittsdatum'])) {
            $austrittsdatum = strtotime($row['austrittsdatum']);
            $today = strtotime(date('Y-m-d'));

            $classes[] = 'highlight-red';

            if ($austrittsdatum > $today) {
                $titleParts[] = 'Gekündigt';
                $markCancelled = true;
            } else {
                $titleParts[] = 'Ausgetreten';
                $markResigned = true;
            }
        }

        $beitraege = get_option('avf_beitraege');
        if ($beitraege[$row['mitgliedschaft_art']] != $row['beitrag']) {
            $markCustomBeitrag = true;
        }

        if (!empty($row['wiedervorlage']) && strtotime($row['wiedervorlage']) <= strtotime('now')) {
            $classes[] = 'highlight-light-blue';
            $titleParts[] = 'Wiedervorlage: ' . $column_wiedervorlage_grund;
            $markWiedervorlage = true;
        }

        $rowClasses = implode(' ', $classes);
        $rowTitle = implode(' | ', $titleParts);

        $html .= '<tr class="table-row-link ' . esc_attr($rowClasses) . '" title="' . esc_attr($rowTitle) . '"';
        $html .= ' data-id="' . esc_attr($column_id_attr) . '"' . ' data-type="membership">';

        $html .= <<<HTML
            <th scope="row" class="check-column no-link" style="cursor: initial;">
            <input type="checkbox" class="membership-checkbox no-link" value="{$column_id_attr}">
            </th>
            <td>{$column_id}</td>
            <td>
            HTML;

        $html .= esc_html(MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']] ?? 'Unbekannt');
        $html .= $checkAge ? '&nbsp;<span class="dashicons dashicons-warning" style="color: orange;" title="Alter stimmt nicht mit Mitgliedschaftsart überein."></span>' : '';
        $html .= $markCancelled ? '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Gekündigt zum ' . esc_attr(date('d.m.Y', $austrittsdatum)) . '"></span>' : '';
        $html .= $markResigned ? '&nbsp;<span class="dashicons dashicons-dismiss" style="color: red;" title="Ausgetreten zum ' . esc_attr(date('d.m.Y', $austrittsdatum)) . '"></span>' : '';
        $html .= $markWiedervorlage ? '&nbsp;<span class="dashicons dashicons-info" style="color: #3498db;" title="Wiedervorlage: ' . $column_wiedervorlage_grund . '"></span>' : '';

        $html .= <<<HTML
            </td>
            <td>{$column_vorname}</td>
            <td>{$column_nachname}</td>
            <td>{$column_email}</td>
            <td>{$column_geburtsdatum}</td>
            <td>{$column_beitrittsdatum}</td>
            <td>{$column_kuendigungseingang}</td>
            <td>{$column_austrittsdatum}</td>
            HTML;

        $html .= $column_starterpaket ? '<td><span class="dashicons dashicons-yes"></span></td>' : '<td></td>';
        $html .= $column_spende ? '<td><span class="dashicons dashicons-yes"></span></td>' : '<td></td>';
        $html .= $column_spende_monatlich ? '<td>' . $column_spende_monatlich . ' €</td>' : '<td></td>';
        $html .= $column_spende_einmalig ? '<td>' . $column_spende_einmalig . ' €</td>' : '<td></td>';
        $html .= $column_sepa ? '<td><span class="dashicons dashicons-yes"></span></td>' : '<td></td>';

        $html .= <<<HTML
            <td>{$column_kontoinhaber}</td>
            <td>{$column_iban}</td>
            <td>{$column_bic}</td>
            <td>{$column_bank}</td>
            HTML;

        $html .= '<td class="' . ($markCustomBeitrag ? 'highlight-blue' : '') . '" title="' . ($markCustomBeitrag ? 'Beitrag angepasst' : '') . '">';
        $html .= $column_beitrag . ' €';
        $html .= $markCustomBeitrag ? '&nbsp;<span class="dashicons dashicons-edit" style="color: #3498db;"></span>' : '';
        $html .= '</td>';

        $html .= <<<HTML
            <td class="notizen-col">{$column_notizen}</td>
            <td>{$column_submission_date}</td>
            </tr>
            HTML;
    }

    return $html;
}

function generate_schnupperkurs_html($results)
{
    $html = '';
    $dateColumns = ['geburtsdatum', 'beginn', 'ende', 'submission_date'];

    foreach ($results as $row) {
        foreach (array_keys($row) as $key) {
            if ($key === 'notizen') {
                ${'column_' . $key} = nl2br(esc_html($row[$key]));
            } else {
                ${'column_' . $key} = esc_html($row[$key]);
            }

            if ($key === 'id') {
                ${'column_' . $key . '_attr'} = esc_attr($row[$key]);
            }
            if (in_array($key, $dateColumns, true)) {
                $dateString = $row[$key] ?? '';

                if ($dateString) {
                    try {
                        $date = new DateTime($dateString);
                        ${'column_' . $key} = esc_html($date->format('d.m.Y'));
                    } catch (Exception) {
                        ${'column_' . $key} = esc_html($dateString);
                    }
                }
            }
        }

        $rowClasses = '';
        $rowTitle = '';

        $markOver = false;
        $markMember = false;
        $beginn = strtotime($row['beginn']);
        if ($beginn && $beginn < strtotime('-2 months')) {
            $rowClasses = 'highlight-red';
            $rowTitle = 'Schnupperkurs ist vorbei';
            $markOver = true;
        }

        if ($row['is_member']) {
            $rowClasses .= ' highlight-green';
            $rowTitle .= $markOver ? ' | ' : '';
            $rowTitle .= 'Mitglied seit ' . date('d.m.Y', strtotime($row['member_since']));
            $markMember = true;
        }

        $html .= '<tr class="table-row-link ' . esc_attr($rowClasses) . '" title="' . esc_attr($rowTitle) . '"';
        $html .= ' data-id="' . esc_attr($column_id_attr) . '"' . ' data-type="schnupperkurs">';

        $html .= <<<HTML
            <th scope="row" class="check-column" style="cursor: initial;">
            <input type="checkbox" class="membership-checkbox" value="{$column_id_attr}">
            </th>
            <td>{$column_id}</td>
            <td>
            HTML;

        $schnupperkurs_art_display = SCHNUPPERKURSARTEN[$column_schnupperkurs_art] ?? $column_schnupperkurs_art;
        $html .= $schnupperkurs_art_display;
        $html .= $markMember ? '&nbsp;<span class="dashicons dashicons-yes-alt" style="color: green;" title="Mitglied seit ' . esc_attr(date('d.m.Y', strtotime($row['member_since']))) . '"></span>' : '';

        $html .= <<<HTML
            </td>
            <td>{$column_vorname}</td>
            <td>{$column_nachname}</td>
            <td>{$column_email}</td>
            <td>{$column_telefon}</td>
            <td>{$column_geburtsdatum}</td>
            <td>{$column_beginn}</td>
            <td>
            HTML;

        $html .= $column_ende;
        $html .= $markOver && !$markMember ? '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Schnupperkurs ist vorbei"></span>' : '';

        $wie_erfahren_display = WIE_ERFAHREN[$column_wie_erfahren] ?? $column_wie_erfahren;

        $html .= <<<HTML
            </td>
            <td>{$wie_erfahren_display}</td>
            <td class="notizen-col">{$column_notizen}</td>
            <td>{$column_submission_date}</td>
            </tr>
            HTML;
    }

    return $html;
}

function check_membership_status($schnupperkurs_results)
{
    global $wpdb;
    $memberships_table = $wpdb->prefix . 'avf_memberships';

    foreach ($schnupperkurs_results as &$result) {
        if (isset($result['vorname'], $result['nachname'], $result['geburtsdatum'])) {
            $query = $wpdb->prepare(
                "SELECT id, beitrittsdatum
                    FROM $memberships_table
                    WHERE LOWER(vorname) = LOWER(%s)
                    AND LOWER(nachname) = LOWER(%s)
                    AND geburtsdatum = DATE(%s);",
                $result['vorname'],
                $result['nachname'],
                $result['geburtsdatum']
            );

            $membership = $wpdb->get_row($query);
            $result['is_member'] = !empty($membership);
            if ($result['is_member']) {
                $result['member_since'] = $membership->beitrittsdatum;
                $result['member_id'] = $membership->id;
            }
        }
    }

    return $schnupperkurs_results;
}

function avf_fetch_membership_data()
{
    $params = validate_and_get_params('COLUMN_HEADERS_MEMBERSHIPS');

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $filters = isset($_POST['filters']) ? (array)$_POST['filters'] : [];
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if (is_array($filters)) {
        $filters = array_map('sanitize_text_field', $filters);
    } else {
        wp_send_json_error('Invalid filters');
    }

    $active_filters = process_membership_filters($filters);

    $query_parts = build_membership_query($table_name, $active_filters, $search, $params['column'], $params['order']);
    $results = $wpdb->get_results($wpdb->prepare($query_parts['query'], ...$query_parts['params']), ARRAY_A);

    if (empty($results)) {
        $html = '<td colspan="5" class="no-memberships-msg">Keine Mitgliedschaften gefunden.</td>';
    } else {
        $html = generate_membership_html($results);
    }

    wp_send_json_success(
        [
        'html' => $html,
        'count' => count($results),
        ]
    );
}

function avf_fetch_schnupperkurs_data()
{
    $params = validate_and_get_params('COLUMN_HEADERS_SCHNUPPERKURSE');

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_schnupperkurse';

    $order_clause = $params['column'] === 'init' ? 'beginn DESC' : $params['column'] . ' ' . $params['order'];
    $query = "SELECT * FROM $table_name ORDER BY $order_clause";

    $results = $wpdb->get_results($query, ARRAY_A);

    if (empty($results)) {
        $html = '<td colspan="5" class="no-memberships-msg">Keine Schnupperkurse gefunden.</td>';
    } else {
        $enriched_results = check_membership_status($results);
        $html = generate_schnupperkurs_html($enriched_results);
    }

    wp_send_json_success(
        [
        'html' => $html,
        'count' => count($results),
        ]
    );
}

function avf_get_follow_ups()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $results = $wpdb->get_results(
        "SELECT * FROM $table_name where wiedervorlage IS NOT NULL ORDER BY wiedervorlage ASC", ARRAY_A
    );
    function render_follow_up_table($results)
    {
        if (empty($results)) {
            return '<p>No follow-up records found.</p>';
        }

        $html = '<table class="widefat striped">';
        $html .= '<thead>
                    <tr>
                        <th>ID</th>
                        <th>Art der Mitgliedschaft</th>
                        <th>Vorname</th>
                        <th>Nachname</th>
                        <th>E-Mail</th>
                        <th>Wiedervorlage Datum</th>
                        <th>Wiedervorlage Grund</th>
                    </tr>
                </thead>
            <tbody>';


        foreach ($results as $result) {
            $edit_link = admin_url('admin.php?page=avf-membership-form-page&edit=' . $result['id']);

            $markCancelled = false;
            $markResigned = false;
            $markWiedervorlage = !empty($result['wiedervorlage']) && strtotime($result['wiedervorlage']) <= strtotime('now');

            if (!empty($result['austrittsdatum'])) {
                $austrittsdatum = strtotime($result['austrittsdatum']);
                $today = strtotime(date('Y-m-d'));

                if ($austrittsdatum > $today) {
                    $markCancelled = true;
                } else {
                    $markResigned = true;
                }
            }

            $formatted_date = !empty($result['wiedervorlage'])
            ? date('d.m.Y', strtotime($result['wiedervorlage']))
            : '';

            $html .= '<tr class="table-row-link';
            $html .= $markWiedervorlage ? ' highlight-light-blue"' : '';
            $html .= '" onclick="window.location.href=\'' . esc_js($edit_link) . '\'">';
            $html .= '<td>' . esc_html($result['id']) . '</td>';
            $html .= '<td>' . MITGLIEDSCHAFTSARTEN[esc_html($result['mitgliedschaft_art'])];
            $html .= $markCancelled ? '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Gekündigt zum ' . esc_attr(date('d.m.Y', $austrittsdatum)) . '"></span>' : '';
            $html .= $markResigned ? '&nbsp;<span class="dashicons dashicons-dismiss" style="color: red;" title="Ausgetreten zum ' . esc_attr(date('d.m.Y', $austrittsdatum)) . '"></span>' : '';
            $html .= $markWiedervorlage ? '&nbsp;<span class="dashicons dashicons-info" style="color: #3498db;" title="Wiedervorlage: ' . $result['wiedervorlage_grund'] . '"></span>' : '';
            $html .= '</td>';
            $html .= '<td>' . esc_html($result['vorname']) . '</td>';
            $html .= '<td>' . esc_html($result['nachname']) . '</td>';
            $html .= '<td>' . esc_html($result['email']) . '</td>';
            $html .= '<td>' . esc_html($formatted_date) . '</td>';
            $html .= '<td>' . esc_html($result['wiedervorlage_grund']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    if (!empty($results)) {
        $table_html = render_follow_up_table($results);
        wp_send_json_success(['follow_ups' => $table_html]);
    } else {
        wp_send_json_error('Error fetching follow-up data.');

    }
}

function avf_get_membership_stats()
{
    $current_year = date('Y');
    $previous_year = $current_year - 1;

    function get_combined_stats_for_year($year)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_memberships';

        $beitritte_query = $wpdb->prepare(
            "SELECT mitgliedschaft_art, COUNT(*) as count
            FROM $table_name
            WHERE YEAR(beitrittsdatum) = %d
            GROUP BY mitgliedschaft_art",
            $year
        );

        $austritte_query = $wpdb->prepare(
            "SELECT mitgliedschaft_art, COUNT(*) as count
            FROM $table_name
            WHERE YEAR(austrittsdatum) = %d
            GROUP BY mitgliedschaft_art",
            $year
        );

        $beitritte = $wpdb->get_results($beitritte_query, ARRAY_A);
        $austritte = $wpdb->get_results($austritte_query, ARRAY_A);

        $combined_stats = [];
        foreach ($beitritte as $row) {
            $mitgliedschaft_art = $row['mitgliedschaft_art'];
            $combined_stats[$mitgliedschaft_art]['beitritte'] = intval($row['count']);
            $combined_stats[$mitgliedschaft_art]['austritte'] = 0;
        }

        foreach ($austritte as $row) {
            $mitgliedschaft_art = $row['mitgliedschaft_art'];
            if (!isset($combined_stats[$mitgliedschaft_art])) {
                $combined_stats[$mitgliedschaft_art] = [
                'beitritte' => 0,
                'austritte' => 0,
                ];
            }
            $combined_stats[$mitgliedschaft_art]['austritte'] = intval($row['count']);
        }

        return $combined_stats;
    }

    function get_combined_schnupperkurse_for_year($year)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_schnupperkurse';
        $memberships_table = $wpdb->prefix . 'avf_memberships';

        $schnupperkurse_query = $wpdb->prepare(
            "SELECT schnupperkurs_art, COUNT(*) as count
            FROM $table_name
            WHERE YEAR(beginn) = %d
            GROUP BY schnupperkurs_art",
            $year
        );

        $schnupperkurs_conversion_query = $wpdb->prepare(
            "SELECT schnupperkurs_art, COUNT(*) as count
            FROM $table_name AS sk
            WHERE YEAR(sk.beginn) = %d AND EXISTS (
                SELECT 1
                FROM $memberships_table AS m
                WHERE LOWER(m.vorname) = LOWER(sk.vorname)
                AND LOWER(m.nachname) = LOWER(sk.nachname)
                AND m.geburtsdatum = DATE(sk.geburtsdatum)
                AND m.beitrittsdatum >= sk.beginn
            )
            GROUP BY schnupperkurs_art",
            $year
        );

        $schnupperkurse = $wpdb->get_results($schnupperkurse_query, ARRAY_A);
        $schnupperkurs_conversion = $wpdb->get_results($schnupperkurs_conversion_query, ARRAY_A);

        $combined_stats = [];
        foreach ($schnupperkurse as $row) {
            $schnupperkurs_art = $row['schnupperkurs_art'];
            $combined_stats[$schnupperkurs_art]['schnupperkurse'] = intval($row['count']);
            $combined_stats[$schnupperkurs_art]['schnupperkurs_conversion'] = 0;
        }

        foreach ($schnupperkurs_conversion as $row) {
            $schnupperkurs_art = $row['schnupperkurs_art'];
            if (!isset($combined_stats[$schnupperkurs_art])) {
                $combined_stats[$schnupperkurs_art] = [
                'schnupperkurse' => 0,
                'schnupperkurs_conversion' => 0
                ];
            }
            $combined_stats[$schnupperkurs_art]['schnupperkurs_conversion'] = intval($row['count']);
        }

        return $combined_stats;
    }

    function get_membership_stats_by_type()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_memberships';

        $query = $wpdb->prepare(
            "SELECT mitgliedschaft_art, COUNT(*) as count, SUM(beitrag) as fees
            FROM $table_name
            WHERE austrittsdatum IS NULL OR austrittsdatum > %s
            GROUP BY mitgliedschaft_art",
            date('Y-m-d')
        );

        return $wpdb->get_results($query, ARRAY_A);
    }

    function format_membership_stats_by_type($stats)
    {
        if (empty($stats)) {
            return '<p>Keine Mitgliedschaften gefunden.</p>';
        }

        $html = '<table class="wp-list-table widefat striped">';
        $html .= '<thead><tr><th>Mitgliedschaftsart</th><th>Anzahl</th><th>Beiträge pro Quartal</th></tr></thead>';
        $html .= '<tbody>';

        $total_count = 0;
        $total_fees = 0;
        foreach ($stats as $row) {
            $membership_name = esc_html(MITGLIEDSCHAFTSARTEN_PLURAL[$row['mitgliedschaft_art']] ?? $row['mitgliedschaft_art']);
            $html .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%d €</td></tr>',
                $membership_name,
                intval($row['count']),
                intval($row['fees'])
            );
            $total_count += intval($row['count']);
            $total_fees += intval($row['fees']);
        }

        $html .= sprintf(
            '<tr class="total"><td><strong>Gesamt:</strong></td><td><strong>%d</strong></td><td><strong>%d €</strong></td></tr>',
            $total_count,
            $total_fees
        );

        $html .= '</tbody></table>';
        return $html;
    }

    function format_combined_stats($stats, $data_type)
    {
        $columns = $data_type === 'schnupperkurse'
            ? ['Schnupperkursart', 'Anzahl', 'Beigetreten']
            : ['Mitgliedschaftsart', 'Beitritte', 'Austritte'];
        $html = $data_type === 'schnupperkurse' ? '<h3>Schnupperkurse</h3>' : '<h3>Beitritte/Austritte</h3>';

        if (empty($stats)) {
            $html .= '<p>Keine Daten gefunden.</p>';
            return $html;
        }

        $html .= '<table class="wp-list-table widefat striped">';
        $html .= '<thead><tr><th>' . implode('</th><th>', $columns) . '</th></tr></thead>';
        $html .= '<tbody>';

        $total_beitritte = 0;
        $total_austritte = 0;
        $total_schnupperkurse = 0;
        $total_schnupperkurs_conversion = 0;

        if ($data_type === 'schnupperkurse') {
            foreach ($stats as $schnupperkurs_art => $counts) {
                $schnupperkurs_art = esc_html(SCHNUPPERKURSARTEN[$schnupperkurs_art] ?? $schnupperkurs_art);
                $html .= sprintf(
                    '<tr><td>%s</td><td>%d</td><td>%d</td></tr>',
                    $schnupperkurs_art,
                    $counts['schnupperkurse'],
                    $counts['schnupperkurs_conversion']
                );
                $total_schnupperkurse += $counts['schnupperkurse'];
                $total_schnupperkurs_conversion += $counts['schnupperkurs_conversion'];
            }
        } else {
            foreach ($stats as $membership_type => $counts) {
                $membership_name = esc_html(MITGLIEDSCHAFTSARTEN_PLURAL[$membership_type] ?? $membership_type);
                $html .= sprintf(
                    '<tr><td>%s</td><td>%d</td><td>%d</td></tr>',
                    $membership_name,
                    $counts['beitritte'],
                    $counts['austritte']
                );
                $total_beitritte += $counts['beitritte'];
                $total_austritte += $counts['austritte'];
            }
        }

        $html .= sprintf(
            '<tr class="total"><td><strong>Gesamt:</strong></td><td><strong>%d</strong></td><td><strong>%d</strong></td></tr>',
            $data_type === 'schnupperkurse' ? $total_schnupperkurse : $total_beitritte,
            $data_type === 'schnupperkurse' ? $total_schnupperkurs_conversion : $total_austritte
        );

        $html .= '</tbody></table>';
        return $html;
    }

    $stats_by_type = get_membership_stats_by_type();
    $stats_current_year = get_combined_stats_for_year($current_year);
    $schnupperkurse_current_year = get_combined_schnupperkurse_for_year($current_year);
    $stats_previous_year = get_combined_stats_for_year($previous_year);
    $schnupperkurse_previous_year = get_combined_schnupperkurse_for_year($previous_year);

    if (!empty($stats_by_type)
        || !empty($stats_current_year)
        || !empty($stats_previous_year)
        || !empty($schnupperkurse_current_year)
        || !empty($schnupperkurse_previous_year)
    ) {
        $html = '<h2>Aktuelle Mitgliederzahlen</h2>';
        $html .= format_membership_stats_by_type($stats_by_type);

        $html .= '<h2>' . $current_year . '</h2>';
        $html .= format_combined_stats($stats_current_year, 'memberships');
        $html .= format_combined_stats($schnupperkurse_current_year, 'schnupperkurse');

        $html .= '<h2>' . $previous_year . '</h2>';
        $html .= format_combined_stats($stats_previous_year, 'memberships');
        $html .= format_combined_stats($schnupperkurse_previous_year, 'schnupperkurse');

        wp_send_json_success(['membership_stats' => $html]);
    } else {
        wp_send_json_error('Error fetching membership stats.');
    }
}

function avf_send_email_to_members($ids)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $sql = $wpdb->prepare("SELECT email FROM $table_name WHERE id IN ($placeholders)", ...$ids);
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (!empty($results)) {
        $emails = array_column($results, 'email');
        $valid_emails = array_filter(
            $emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            }
        );

        if (empty($valid_emails)) {
            wp_send_json_error(['message' => 'No valid email addresses found']);
        }

        $action = count($valid_emails) == 1 ? "to" : "bcc";
        $mailto_link = "mailto:?$action=" . urlencode(implode(',', $valid_emails));
        wp_send_json_success(['mailto' => $mailto_link]);
    } else {
        wp_send_json_error(['message' => 'No data found for the selected IDs.']);
    }
}
