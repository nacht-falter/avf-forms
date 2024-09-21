<?php
function avf_display_submissions() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'avf_memberships';
    
    $results = $wpdb->get_results("SELECT * FROM $table_name");

    echo '<div class="wrap">';
    echo '<h1>AVF-Mitgliedschaften</h1>';

    if (!empty($results)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>ID</th>';
        echo '<th>Art der Mitgliedschaft</th>';
        echo '<th>Vorname</th>';
        echo '<th>Nachname</th>';
        echo '<th>E-Mail</th>';
        echo '<th>Telefon</th>';
        echo '<th>Geburtsdatum</th>';
        echo '<th>Strasse</th>';
        echo '<th>Hausnummer</th>';
        echo '<th>PLZ</th>';
        echo '<th>Ort</th>';
        echo '<th>Beitrittsdatum</th>';
        echo '<th>Starterpaket</th>';
        echo '<th>Spende</th>';
        echo '<th>Spende monatlich</th>';
        echo '<th>Spende einmalig</th>';
        echo '<th>Satzung Datenschutz</th>';
        echo '<th>Hinweise</th>';
        echo '<th>SEPA</th>';
        echo '<th>Kontoinhaber</th>';
        echo '<th>IBAN</th>';
        echo '<th>Geschwisterkind</th>';
        echo '<th>Vorname Eltern</th>';
        echo '<th>Nachname Eltern</th>';
        echo '<th>Antragsdatum</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row->id) . '</td>';
            echo '<td>' . esc_html($row->mitgliedschaft_art) . '</td>';
            echo '<td>' . esc_html($row->vorname) . '</td>';
            echo '<td>' . esc_html($row->nachname) . '</td>';
            echo '<td>' . esc_html($row->email) . '</td>';
            echo '<td>' . esc_html($row->telefon) . '</td>';
            echo '<td>' . esc_html($row->geburtsdatum) . '</td>';
            echo '<td>' . esc_html($row->strasse) . '</td>';
            echo '<td>' . esc_html($row->hausnummer) . '</td>';
            echo '<td>' . esc_html($row->plz) . '</td>';
            echo '<td>' . esc_html($row->ort) . '</td>';
            echo '<td>' . esc_html($row->beitrittsdatum) . '</td>';
            echo '<td>' . ($row->starterpaket ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . ($row->spende ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html($row->spende_monatlich) . '</td>';
            echo '<td>' . esc_html($row->spende_einmalig) . '</td>';
            echo '<td>' . ($row->satzung_datenschutz ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . ($row->hinweise ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . ($row->sepa ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html($row->kontoinhaber) . '</td>';
            echo '<td>' . esc_html($row->iban) . '</td>';
            echo '<td>' . ($row->geschwisterkind ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html($row->vorname_eltern) . '</td>';
            echo '<td>' . esc_html($row->nachname_eltern) . '</td>';
            echo '<td>' . esc_html($row->submission_date) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>Keine Einträge gefunden.</p>';
    }

    echo '</div>';

}
