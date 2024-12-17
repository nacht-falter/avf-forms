<?php
function Avf_Handle_Ajax_membership_requests()
{
    if (!current_user_can('manage_memberships')) {
        wp_send_json_error('You do not have permission to perform this action.');
        wp_die();
    }

    if (!check_ajax_referer('avf_membership_action', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

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

        if ($action_type === 'update') {
            $id = intval($_POST['id']);
            $wpdb->update($table_name, $data, ['id' => $id]);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich aktualisiert');
        } else {
            $wpdb->insert($table_name, $data);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich angelegt', 'redirect_url' => admin_url('admin.php?page=avf-membership-admin'));
        }

    } elseif ($action_type === 'delete') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, ['id' => $id]);
        $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich entfernt');

    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM $table_name WHERE id IN ($placeholders)";

        $deleted = $wpdb->query($wpdb->prepare($query, ...$ids));

        if ($deleted) {
            $response = array('status' => 'success', 'message' => "$deleted Mitgliedschaft(en) erfolgreich gelöscht.");
        } else {
            $response = array('status' => 'error', 'message' => 'Fehler beim Löschen der Einträge.');
        }
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
                ], admin_url('admin-ajax.php')
            );
            wp_send_json_success(['download_url' => $download_url]);
        }
    }

    echo json_encode($response);
    wp_die(); // Important to close the AJAX call
}

add_action('wp_ajax_avf_membership_action', 'Avf_Handle_Ajax_membership_requests');

function Avf_Handle_Ajax_schnupperkurs_requests()
{
    if (!current_user_can('manage_memberships')) {
        wp_send_json_error('You do not have permission to perform this action.');
        wp_die();
    }

    if (!check_ajax_referer('avf_membership_action', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

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

        if ($action_type === 'update') {
            $id = intval($_POST['id']);
            $wpdb->update($table_name, $data, ['id' => $id]);
            $response = array('status' => 'success', 'message' => 'Schnupperkurs erfolgreich aktualisiert');
        } else {
            $wpdb->insert($table_name, $data);
            $response = array('status' => 'success', 'message' => 'Schnupperkurs erfolgreich angelegt', 'redirect_url' => admin_url('admin.php?page=avf-schnupperkurs-admin'));
        }

    } elseif ($action_type === 'delete') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, ['id' => $id]);
        $response = array('status' => 'success', 'message' => 'Schnupperkurs erfolgreich entfernt');

    } elseif ($action_type === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM $table_name WHERE id IN ($placeholders)";

        $deleted = $wpdb->query($wpdb->prepare($query, ...$ids));

        if ($deleted) {
            $response = array('status' => 'success', 'message' => "$deleted Schnupperkurs(e) erfolgreich gelöscht.");
        } else {
            $response = array('status' => 'error', 'message' => 'Fehler beim Löschen der Einträge.');
        }
    }

    echo json_encode($response);
    wp_die(); // Important to close the AJAX call
}

add_action('wp_ajax_avf_schnupperkurs_action', 'Avf_Handle_Ajax_schnupperkurs_requests');

function Generate_Csv_download()
{
    check_ajax_referer('avf_membership_action', '_ajax_nonce');

    if (!current_user_can('manage_memberships')) {
        wp_die('You do not have permission to perform this action.');
    }

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

add_action('wp_ajax_avf_download_csv', 'Generate_Csv_download');

function Fetch_Membership_data()
{
    if (!current_user_can('manage_memberships')) {
        wp_send_json_error('You do not have permission to perform this action.');
        wp_die();
    }

    if (!check_ajax_referer('avf_membership_action', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $column = isset($_POST['column']) ? sanitize_text_field($_POST['column']) : 'init';
    $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC';
    $filters = isset($_POST['filters']) ? (array)$_POST['filters'] : [];
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

    if (is_array($filters)) {
        $filters = array_map('sanitize_text_field', $filters);
    } else {
        wp_send_json_error('Invalid filters');
    }

    $allowed_filters = [
        'aktiv',
        'kind',
        'sonder',
        'passiv',
        'foerder'
    ];

    $related_filters = [
        'aktiv' => ['aktiv_ermaessigt', 'familie'],
        'kind' => ['jugend'],
    ];

    if ($column != 'init' && (!defined('COLUMN_HEADERS_MEMBERSHIPS') || !array_key_exists($column, COLUMN_HEADERS_MEMBERSHIPS))) {
        wp_send_json_error('Invalid column: ' . esc_html($column));
    }

    if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
        wp_send_json_error('Invalid order: ' . esc_html($order));
    }

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

    $active_filters = array_unique($active_filters);

    $where_in_clause = '';
    if ($active_filters) {
        $where_in_clause = implode(', ', array_fill(0, count($active_filters), '%s'));
    }

    $search_clause = '';
    if ($search) {
        $search_columns = ['vorname', 'nachname', 'email', 'kontoinhaber', 'notizen'];
        $like_clauses = array_map(
            function ($column) {
                return "$column LIKE %s";
            }, $search_columns
        );
        $search_clause = $wpdb->prepare(
            ' AND (' . implode(' OR ', $like_clauses) . ')',
            ...array_fill(0, count($search_columns), '%' . $search . '%')
        );
    }

    $order_clause = '';
    if ($column === 'init') {
        $order_clause .= 'CASE WHEN wiedervorlage <= CURRENT_DATE THEN 1 ELSE 2 END, mitgliedschaft_art ASC';
    } else {
        $order_clause .= $column . ' ' . $order;
    }

    $query = "SELECT * FROM $table_name WHERE 1=1"; // Always true to allow additional conditions
    if ($where_in_clause) {
        $query .= " AND mitgliedschaft_art IN ($where_in_clause)";
    }
    $query .= " $search_clause ORDER BY $order_clause";

    $results = $wpdb->get_results($wpdb->prepare($query, ...$active_filters), ARRAY_A);

    $html = '';
    if (empty($results)) {
        $html .= '<td colspan="5" class="no-memberships-msg">Keine Mitgliedschaften gefunden.</td>';
    } else {
        $dateColumns = ['geburtsdatum', 'beitrittsdatum', 'austrittsdatum', 'wiedervorlage'];
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

            $classes = [];
            $titleParts = [];

            $age = date_diff(date_create($row['geburtsdatum']), date_create('now'))->y;
            $checkAge = false;
            $markInactive = false;
            $markWiedervorlage = false;
            $markCustomBeitrag = false;

            if (($age < 14 && $row['mitgliedschaft_art'] != 'kind')) {
                $checkAge = true;
                $classes[] = 'highlight-red';
                $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
            } elseif ($age >= 14 && $age < 18 && $row['mitgliedschaft_art'] != 'jugend') {
                $checkAge = true;
                $classes[] = 'highlight-red';
                $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
            } elseif ($age >= 18 && ($row['mitgliedschaft_art'] == 'kind' || $row['mitgliedschaft_art'] == 'jugend')) {
                $checkAge = true;
                $classes[] = 'highlight-red';
                $titleParts[] = 'Mitgliedschaftsart prüfen (Alter)';
            }

            if (!empty($row['austrittsdatum'])) {
                $classes[] = 'highlight-yellow';
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
            $html .= $checkAge ? '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Alter stimmt nicht mit Mitgliedschaftsart überein."></span>' : '';
            $html .= $markInactive ? '&nbsp;<span class="dashicons dashicons-warning" style="color: orange;" title="Austritt zum ' . esc_attr(date('d.m.Y', strtotime($row['austrittsdatum']))) . '"></span>' : '';
            $html .= $markWiedervorlage ? '&nbsp;<span class="dashicons dashicons-warning" style="color: #3498db;" title="Wiedervorlage: ' . $column_wiedervorlage_grund . '"></span>' : '';

            $html .= <<<HTML
                </td>
                <td>{$column_vorname}</td>
                <td>{$column_nachname}</td>
                <td>{$column_email}</td>
                <td>{$column_geburtsdatum}</td>
                <td>{$column_beitrittsdatum}</td>
                <td>{$column_austrittsdatum}</td>
                HTML;

            $html .= $column_starterpaket ? '<td>&#10003;</td>' : '<td></td>';
            $html .= $column_spende ? '<td>&#10003;</td>' : '<td></td>';
            $html .= $column_spende_monatlich ? '<td>' . $column_spende_monatlich . ' €</td>' : '<td></td>';
            $html .= $column_spende_einmalig ? '<td>' . $column_spende_einmalig . ' €</td>' : '<td></td>';
            $html .= $column_sepa ? '<td>&#10003</td>' : '<td></td>';

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
    }

    wp_send_json_success($html);
}

add_action('wp_ajax_avf_fetch_memberships', 'Fetch_Membership_data');

function Fetch_Schnupperkurs_data()
{
    if (!current_user_can('manage_memberships')) {
        wp_send_json_error('You do not have permission to perform this action.');
        wp_die();
    }

    if (!check_ajax_referer('avf_membership_action', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
        wp_die();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_schnupperkurse';

    $column = isset($_POST['column']) ? sanitize_text_field($_POST['column']) : 'init';
    $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC';

    if ($column != 'init' && (!defined('COLUMN_HEADERS_SCHNUPPERKURSE') || !array_key_exists($column, COLUMN_HEADERS_SCHNUPPERKURSE))) {
        wp_send_json_error('Invalid column: ' . esc_html($column));
        wp_die();
    }

    if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
        wp_send_json_error('Invalid order: ' . esc_html($order));
        wp_die();
    }

    if ($column === 'init') {
        $order_clause = 'beginn ASC';
    } else {
        $order_clause = $column . ' ' . $order;
    };

    $query = "SELECT * FROM $table_name ORDER BY $order_clause";

    $results = $wpdb->get_results($query, ARRAY_A);

    $html = '';
    if (empty($results)) {
        $html .= '<td colspan="5" class="no-memberships-msg">Keine Schnupperkurse gefunden.</td>';
    } else {
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
            $beginn = strtotime($row['beginn']);
            if ($beginn && $beginn < strtotime('-2 months')) {
                $rowClasses = 'highlight-red';
                $rowTitle = 'Schnupperkurs ist vorbei';
                $markOver = true;
            }

            $html .= '<tr class="table-row-link ' . esc_attr($rowClasses) . '" title="' . esc_attr($rowTitle) . '"';
            $html .= ' onclick="handleRowClick(event, ' . esc_attr($row['id']) . ')">';

            $schnupperkurs_art_display = SCHNUPPERKURSARTEN[$column_schnupperkurs_art] ?? $column_schnupperkurs_art;

            $html .= <<<HTML
                <th scope="row" class="check-column" style="cursor: initial;">
                <input type="checkbox" class="membership-checkbox" value="{$column_id_attr}">
                </th>
                <td>{$column_id}</td>
                <td>{$schnupperkurs_art_display}</td>
                <td>{$column_vorname}</td>
                <td>{$column_nachname}</td>
                <td>{$column_email}</td>
                <td>{$column_telefon}</td>
                <td>{$column_geburtsdatum}</td>
                <td>
                HTML;

            $html .= $column_beginn;
            $html .= $markOver ? '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Schnupperkurs ist vorbei."></span>' : '';

            $wie_erfahren_display = WIE_ERFAHREN[$column_wie_erfahren] ?? $column_wie_erfahren;

            $html .= <<<HTML
                </td>
                <td>{$column_ende}</td>
                <td>{$wie_erfahren_display}</td>
                <td class="notizen-col">{$column_notizen}</td>
                <td>{$column_submission_date}</td>
                </tr>
                HTML;
        }
    }

    wp_send_json_success($html);
}

add_action('wp_ajax_avf_fetch_schnupperkurse', 'Fetch_Schnupperkurs_data');
