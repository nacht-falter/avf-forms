<?php
function avf_handle_ajax_requests()
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

    $action = $_POST['action_type'];
    $response = array('status' => 'error', 'message' => 'Invalid action');

    if ($action === 'create' || $action === 'update') {
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

        if ($action === 'update') {
            $id = intval($_POST['id']);
            $wpdb->update($table_name, $data, ['id' => $id]);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich aktualisiert');
        } else {
            $wpdb->insert($table_name, $data);
            $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich angelegt');
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, ['id' => $id]);
        $response = array('status' => 'success', 'message' => 'Mitgliedschaft erfolgreich entfernt');

    } elseif ($action === 'bulk_delete' && isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']); // Sanitize the IDs

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM $table_name WHERE id IN ($placeholders)";

        $deleted = $wpdb->query($wpdb->prepare($query, ...$ids));

        if ($deleted) {
            $response = array('status' => 'success', 'message' => "$deleted Mitgliedschaft(en) erfolgreich gelöscht.");
        } else {
            $response = array('status' => 'error', 'message' => 'Fehler beim Löschen der Einträge.');
        }
    }

    echo json_encode($response);
    wp_die(); // Important to close the AJAX call
}

add_action('wp_ajax_avf_membership_action', 'avf_handle_ajax_requests');
