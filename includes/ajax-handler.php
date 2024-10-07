<?php
function Avf_Handle_Ajax_requests()
{
    if (!current_user_can('manage_options')) {
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
            'starterpaket' => isset($_POST['starterpaket']) ? 1 : 0,
            'spende' => isset($_POST['spende']) ? 1 : 0,
            'spende_monatlich' => !empty($_POST['spende_monatlich']) ? floatval($_POST['spende_monatlich']) : null,
            'spende_einmalig' => !empty($_POST['spende_einmalig']) ? floatval($_POST['spende_einmalig']) : null,
            'satzung_datenschutz' => isset($_POST['satzung_datenschutz']) ? 1 : 0,
            'hinweise' => isset($_POST['hinweise']) ? 1 : 0,
            'sepa' => isset($_POST['sepa']) ? 1 : 0,
            'kontoinhaber' => sanitize_text_field($_POST['kontoinhaber']),
            'iban' => sanitize_text_field($_POST['iban']),
            'notizen' => sanitize_textarea_field($_POST['notizen']),
            'submission_date' => current_time('mysql'), // Capture the current timestamp
        ];

        if ($action_type === 'update') {
            $id = intval($_POST['id']);
            $wpdb->update($table_name, $data, ['id' => $id]);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich aktualisiert');
        } else {
            $wpdb->insert($table_name, $data);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich angelegt');
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

add_action('wp_ajax_avf_membership_action', 'Avf_Handle_Ajax_requests');

function Generate_Csv_download()
{
    check_ajax_referer('avf_membership_action', '_ajax_nonce');

    if (!current_user_can('manage_options')) {
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
                'Starterpaket',
                'Spende',
                'Spende monatlich',
                'Spende einmalig',
                'Satzung und Datenschutz',
                'Hinweise',
                'SEPA-Mandat',
                'Kontoinhaber',
                'IBAN',
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
                        formatField($row['geschwisterkind']),
                        $row['vorname_eltern'],
                        $row['nachname_eltern'],
                        $row['email'],
                        $row['telefon'],
                        date('d.m.Y', strtotime($row['geburtsdatum'])),
                        $row['strasse'],
                        $row['hausnummer'],
                        $row['plz'],
                        $row['ort'],
                        date('d.m.Y', strtotime($row['beitrittsdatum'])),
                        formatField($row['starterpaket']),
                        formatField($row['spende']),
                        $row['spende_monatlich'],
                        $row['spende_einmalig'],
                        $row['satzung_datenschutz'] ? 'Akzeptiert' : 'Nicht akzeptiert',
                        $row['hinweise'] ? 'Gelesen' : 'Nicht gelesen',
                        $row['sepa'] ? 'Erteilt' : 'Nicht erteilt',
                        $row['kontoinhaber'],
                        $row['iban'],
                        $row['notizen'],
                        date('d.m.Y', strtotime($row['submission_date']))
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

function formatField($value)
{
    if (is_null($value) || $value === '') {
        return '';
    }
    return $value ? 'Ja' : 'Nein';
}
