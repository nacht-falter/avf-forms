<?php

add_action('wp_ajax_avf_membership_action', 'Avf_Handle_Ajax_membership_requests');
add_action('wp_ajax_avf_schnupperkurs_action', 'Avf_Handle_Ajax_schnupperkurs_requests');
add_action('wp_ajax_avf_download_csv', 'Generate_Csv_download');
add_action('wp_ajax_avf_fetch_memberships', 'Fetch_Membership_data');
add_action('wp_ajax_avf_fetch_schnupperkurse', 'Fetch_Schnupperkurs_data');
add_action('wp_ajax_avf_get_total_membership_fees', 'Get_Total_Membership_fees');
add_action('wp_ajax_avf_get_membership_stats', 'Get_Membership_stats');

function validate_user_and_nonce()
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

function handle_insert_update($table_name, $data, $action_type, $id = null)
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

function handle_delete($table_name, $id)
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

function handle_bulk_delete($table_name, $ids)
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

function Avf_Handle_Ajax_membership_requests()
{
    validate_user_and_nonce();

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $action_type = $_POST['action_type'];
    $response = array('status' => 'error', 'message' => 'Invalid action');

    if ($action_type === 'create' || $action_type === 'update') {
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
            'austrittsdatum' => !empty($_POST['austrittsdatum']) ? sanitize_text_field($_POST['austrittsdatum']) : null,
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
            'wiedervorlage' => !empty($_POST['wiedervorlage']) ? sanitize_text_field($_POST['wiedervorlage']) : null,
            'wiedervorlage_grund' => !empty($_POST['wiedervorlage_grund']) ? sanitize_text_field($_POST['wiedervorlage_grund']) : null,
            'notizen' => sanitize_textarea_field($_POST['notizen']),
            'submission_date' => current_time('mysql'), // Capture the current timestamp
        ];

        $response = handle_insert_update($table_name, $data, $action_type, $_POST['id'] ?? null);

    } elseif ($action_type === 'delete') {
        $response = handle_delete($table_name, $_POST['id']);
    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $response = handle_bulk_delete($table_name, array_map('intval', $_POST['ids']));
    }

    echo json_encode($response);
    wp_die();
}

function Avf_Handle_Ajax_schnupperkurs_requests()
{
    validate_user_and_nonce();

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
            'submission_date' => current_time('mysql'),
        ];

        $response = handle_insert_update($table_name, $data, $action_type, $_POST['id'] ?? null);

    } elseif ($action_type === 'delete') {
        $response = handle_delete($table_name, $_POST['id']);
    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $response = handle_bulk_delete($table_name, array_map('intval', $_POST['ids']));
    }

    echo json_encode($response);
    wp_die();
}

function Generate_Csv_download()
{
    validate_user_and_nonce();

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
    validate_user_and_nonce();

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

function format_date_columns($row, $date_columns)
{
    $formatted_row = [];
    foreach (array_keys($row) as $key) {
        $formatted_row['column_' . $key] = esc_html($row[$key]);
        if ($key === 'id') {
            $formatted_row['column_' . $key . '_attr'] = esc_attr($row[$key]);
        }
        if (in_array($key, $date_columns, true)) {
            $dateString = $row[$key] ?? '';
            $date = $dateString ? DateTime::createFromFormat('Y-m-d', $dateString) : null;
            if ($date) {
                $formatted_row['column_' . $key] = esc_html($date->format('d.m.Y'));
            }
        }
    }
    return $formatted_row;
}

function process_membership_filters($filters)
{
    $allowed_filters = ['aktiv', 'kind', 'sonder', 'passiv', 'foerder'];
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

    $where_in_clause = '';
    if ($active_filters) {
        $where_in_clause = implode(', ', array_fill(0, count($active_filters), '%s'));
        $query_parts['params'] = $active_filters;
    }

    $search_clause = '';
    if ($search) {
        $search_columns = ['vorname', 'nachname', 'email', 'kontoinhaber', 'notizen'];
        $like_clauses = array_map(
            function ($column) {
                return "$column LIKE %s";
            },
            $search_columns
        );
        $search_clause = ' AND (' . implode(' OR ', $like_clauses) . ')';
        $query_parts['params'] = array_merge(
            $query_parts['params'],
            array_fill(0, count($search_columns), '%' . $search . '%')
        );
    }

    $order_clause = $column === 'init'
        ? 'CASE WHEN wiedervorlage <= CURRENT_DATE THEN 1 ELSE 2 END, mitgliedschaft_art ASC'
        : $column . ' ' . $order;

    $query_parts['query'] = "SELECT * FROM $table_name WHERE 1=1";
    if ($where_in_clause) {
        $query_parts['query'] .= " AND mitgliedschaft_art IN ($where_in_clause)";
    }
    $query_parts['query'] .= "$search_clause ORDER BY $order_clause";

    return $query_parts;
}

function generate_membership_html($results)
{
    $html = '';
    $dateColumns = ['geburtsdatum', 'beitrittsdatum', 'austrittsdatum', 'wiedervorlage'];

    foreach ($results as $row) {
        foreach (array_keys($row) as $key) {
            ${'column_' . $key} = esc_html($row[$key]);
            if ($key === 'id') {
                ${'column_' . $key . '_attr'} = esc_attr($row[$key]);
            }
            if (in_array($key, $dateColumns, true)) {
                $dateString = $row[$key] ?? '';
                $date = $dateString ? DateTime::createFromFormat('Y-m-d', $dateString) : null;

                if ($date) {
                    ${'column_' . $key} = esc_html($date->format('d.m.Y'));
                }
            }
        }

        $classes = [];
        $titleParts = [];

        $age = date_diff(date_create($row['geburtsdatum']), date_create('now'))->y;
        $checkAge = false;
        $markInactive = false;
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
            $classes[] = 'highlight-red';
            $titleParts[] = 'Ausgetreten';
            $markInactive = true;
        }

        if (BEITRAEGE[$row['mitgliedschaft_art']] != $row['beitrag']) {
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
        $html .= ' onclick="handleRowClick(event, ' . $column_id_attr . ')">';

        $html .= <<<HTML
            <th scope="row" class="check-column" style="cursor: initial;">
            <input type="checkbox" class="membership-checkbox" value="{$column_id_attr}">
            </th>
            <td>{$column_id}</td>
            <td>
            HTML;

        $html .= esc_html(MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']] ?? 'Unbekannt');
        $html .= $checkAge ? '&nbsp;<span class="dashicons dashicons-warning" style="color: orange;" title="Alter stimmt nicht mit Mitgliedschaftsart überein."></span>' : '';
        $html .= $markInactive ? '&nbsp;<span class="dashicons dashicons-dismiss" style="color: red;" title="Austritt zum ' . esc_attr(date('d.m.Y', strtotime($row['austrittsdatum']))) . '"></span>' : '';
        $html .= $markWiedervorlage ? '&nbsp;<span class="dashicons dashicons-info" style="color: #3498db;" title="Wiedervorlage: ' . $column_wiedervorlage_grund . '"></span>' : '';

        $html .= <<<HTML
            </td>
            <td>{$column_vorname}</td>
            <td>{$column_nachname}</td>
            <td>{$column_email}</td>
            <td>{$column_geburtsdatum}</td>
            <td>{$column_beitrittsdatum}</td>
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
        $dateColumns = ['geburtsdatum', 'beginn', 'ende'];
    foreach ($results as $row) {
        foreach (array_keys($row) as $key) {
            ${'column_' . $key} = esc_html($row[$key]);
            if ($key === 'id') {
                ${'column_' . $key . '_attr'} = esc_attr($row[$key]);
            }
            if (in_array($key, $dateColumns, true)) {
                $date = DateTime::createFromFormat('Y-m-d', $row[$key]);
                if ($date) {
                    ${'column_' . $key} = esc_html($date->format('d.m.Y'));
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
        $html .= ' onclick="handleRowClick(event, ' . esc_attr($row['id']) . ')">';

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
        if (isset($result['vorname'], $result['nachname'], $result['email'])) {
            $query = $wpdb->prepare(
                "SELECT id, beitrittsdatum FROM $memberships_table
                WHERE LOWER(vorname) = LOWER(%s)
                AND LOWER(nachname) = LOWER(%s)
                AND LOWER(email) = LOWER(%s)",
                $result['vorname'],
                $result['nachname'],
                $result['email']
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

function Fetch_Membership_data()
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

function Fetch_Schnupperkurs_data()
{
    $params = validate_and_get_params('COLUMN_HEADERS_SCHNUPPERKURSE');

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_schnupperkurse';

    $order_clause = $params['column'] === 'init' ? 'beginn ASC' : $params['column'] . ' ' . $params['order'];
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

function Get_Total_Membership_fees()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $query = "SELECT SUM(beitrag) FROM $table_name WHERE austrittsdatum IS NULL OR austrittsdatum > CURRENT_DATE";
    $result = $wpdb->get_var($query);

    if ($result) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error('Error fetching total membership fees.');
    }
}

function Get_Membership_stats()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';
    $current_year = date('Y');
    $previous_year = $current_year - 1;

    function get_combined_stats_for_year($year, $table_name)
    {
        global $wpdb;

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
                $combined_stats[$mitgliedschaft_art] = ['beitritte' => 0];
            }
            $combined_stats[$mitgliedschaft_art]['austritte'] = intval($row['count']);
        }

        return $combined_stats;
    }

    function get_membership_stats_by_type($table_name)
    {
        global $wpdb;

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
        $html = '<table class="wp-list-table widefat striped">';
        $html .= '<thead><tr><th>Mitgliedschaftsart</th><th>Anzahl</th><th>Beiträge</th></tr></thead>';
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

    function format_combined_stats($stats, $year)
    {
        $html = '<h2>' . $year . '</h2>';
        $html .= '<table class="wp-list-table widefat striped">';
        $html .= '<thead><tr><th>Mitgliedschaftsart</th><th>Beitritte</th><th>Austritte</th></tr></thead>';
        $html .= '<tbody>';

        $total_beitritte = 0;
        $total_austritte = 0;

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

        $html .= sprintf(
            '<tr class="total"><td><strong>Gesamt:</strong></td><td><strong>%d</strong></td><td><strong>%d</strong></td></tr>',
            $total_beitritte,
            $total_austritte
        );

        $html .= '</tbody></table>';
        return $html;
    }

    $stats_by_type = get_membership_stats_by_type($table_name);
    $stats_current_year = get_combined_stats_for_year($current_year, $table_name);
    $stats_previous_year = get_combined_stats_for_year($previous_year, $table_name);

    if (!empty($stats_by_type) || !empty($stats_current_year) || !empty($stats_previous_year)) {
        $html .= '<h2>Aktuelle Mitgliederzahlen</h2>';
        $html .= format_membership_stats_by_type($stats_by_type);
        $html .= format_combined_stats($stats_current_year, $current_year);
        $html .= format_combined_stats($stats_previous_year, $previous_year);

        wp_send_json_success(['membership_stats' => $html]);
    } else {
        wp_send_json_error('Error fetching membership stats.');
    }
}
