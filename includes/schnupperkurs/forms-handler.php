<?php

class Avf_Forms_Schnupperkurs_Handler
{

    public static function register()
    {
        add_action('init', array( __CLASS__, 'handle_schnupperkurs_form_submission' ));
    }

    public static function handle_schnupperkurs_form_submission()
    {
        $is_adult = isset($_POST['schnupperkurs_erwachsene_submit']);
        $is_child = isset($_POST['schnupperkurs_kind_submit']);

        if (!$is_adult && !$is_child) {
            return;
        }

        if (!isset($_POST['schnupperkurs_nonce']) || !wp_verify_nonce($_POST['schnupperkurs_nonce'], 'schnupperkurs_form_submit')) {
            wp_die();
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_schnupperkurse';

        $errors = [];
        $transient_key = $is_adult ? 'schnupperkurs_form_errors' : 'schnupperkurs_kind_form_errors';

        $vorname = sanitize_text_field($_POST['vorname'] ?? '');
        $nachname = sanitize_text_field($_POST['nachname'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $telefon = sanitize_text_field($_POST['telefon'] ?? '');
        $geburtsdatum = sanitize_text_field($_POST['geburtsdatum'] ?? '');
        $beginn = sanitize_text_field($_POST['beginn'] ?? '');
        $wie_erfahren = sanitize_text_field($_POST['wie_erfahren'] ?? '');
        $wie_erfahren_sonstiges = sanitize_text_field($_POST['wie_erfahren_sonstiges'] ?? '');
        $haftungsausschluss = isset($_POST['haftungsausschluss']) ? 1 : 0;
        $datenschutz = isset($_POST['datenschutz']) ? 1 : 0;

        if (empty($vorname)) {
            $errors[] = "Bitte gib deinen Vornamen ein.";
        }
        if (empty($nachname)) {
            $errors[] = "Bitte gib deinen Nachnamen ein.";
        }
        if (empty($email) || !is_email($email)) {
            $errors[] = "Bitte gib eine gültige E-Mail-Adresse ein.";
        }
        if (empty($geburtsdatum)) {
            $errors[] = "Bitte gib dein Geburtsdatum ein.";
        }
        if (empty($beginn)) {
            $errors[] = "Bitte wähle einen Starttermin.";
        }
        if (empty($wie_erfahren)) {
            $errors[] = "Bitte wähle aus, wie du vom Aikido-Verein Freiburg erfahren hast.";
        }
        if ($wie_erfahren === 'sonstiges' && empty($wie_erfahren_sonstiges)) {
            $errors[] = "Bitte gib an, wie du vom Aikido-Verein Freiburg erfahren hast.";
        }
        if (!$haftungsausschluss) {
            $errors[] = "Bitte bestätige den Haftungsausschluss.";
        }
        if (!$datenschutz) {
            $errors[] = "Bitte akzeptiere die Datenschutzbestimmungen.";
        }

        if ($is_child && empty($telefon)) {
            $errors[] = "Bitte gib eine Telefonnummer ein.";
        }

        if ($is_child) {
            $vorname_eltern = sanitize_text_field($_POST['vorname_eltern'] ?? '');
            $nachname_eltern = sanitize_text_field($_POST['nachname_eltern'] ?? '');

            if (empty($vorname_eltern)) {
                $errors[] = "Bitte gib den Vornamen eines Elternteils ein.";
            }
            if (empty($nachname_eltern)) {
                $errors[] = "Bitte gib den Nachnamen eines Elternteils ein.";
            }

            $notizen = "Kontakt Eltern: $vorname_eltern $nachname_eltern, $email, $telefon";
        } else {
            $notizen = '';
        }

        if (!empty($errors)) {
            set_transient($transient_key, $errors, 0);
            $redirect_url = wp_get_referer();
            $redirect_url = add_query_arg('form_status', 'error', $redirect_url);
            wp_redirect($redirect_url);
            exit();
        }

        $beginn_date = new DateTime($beginn);
        $ende_date = clone $beginn_date;
        $ende_date->modify('+2 months');

        $wie_erfahren_value = $wie_erfahren === 'sonstiges' ? $wie_erfahren_sonstiges : $wie_erfahren;
        $schnupperkurs_art = sanitize_text_field($_POST['schnupperkurs_art']);

        $data = [
            'schnupperkurs_art' => $schnupperkurs_art,
            'vorname' => $vorname,
            'nachname' => $nachname,
            'email' => $email,
            'telefon' => $telefon,
            'geburtsdatum' => $geburtsdatum,
            'beginn' => $beginn,
            'ende' => $ende_date->format('Y-m-d'),
            'wie_erfahren' => $wie_erfahren_value,
            'notizen' => $notizen,
            'submission_date' => current_time('mysql')
        ];

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            Avf_Forms_Utils::send_schnupperkurs_confirmation_email($email, $vorname, $nachname, $schnupperkurs_art, $beginn);
            wp_redirect(home_url('/success'));
        } else {
            $error_key = $is_adult ? 'schnupperkurs_form_errors' : 'schnupperkurs_kind_form_errors';
            set_transient($error_key, ["Es ist ein Fehler aufgetreten. Bitte versuche es später erneut."], 0);
            $redirect_url = wp_get_referer();
            $redirect_url = add_query_arg('form_status', 'error', $redirect_url);
            wp_redirect($redirect_url);
        }
        exit();
    }
}