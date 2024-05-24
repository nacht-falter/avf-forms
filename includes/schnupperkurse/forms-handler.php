<?php

class Avf_Forms_Schnupperkurs_Handler
{

    public static function register()
    {
        add_action('init', array( __CLASS__, 'handle_schnupperkurs_form_submission' ));
        add_action('init', array(__CLASS__, 'handle_schnupperkurs_csv_download_request'));
    }

    public static function handle_schnupperkurs_form_submission()
    {
        if (isset($_POST['schnupperkurs_form_submit']) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_schnupperkurs_registrations';

            $vorname = isset($_POST['vorname']) ? sanitize_text_field($_POST['vorname']) : '';
            $nachname = isset($_POST['nachname']) ? sanitize_text_field($_POST['nachname']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $telefon = isset($_POST['telefon']) ? sanitize_text_field($_POST['telefon']) : '';
            $geburtsdatum = isset($_POST['geburtsdatum']) ? sanitize_text_field($_POST['geburtsdatum']) : '';
            $schnupperkurs_beginn = isset($_POST['schnupperkurs-beginn']) ? sanitize_text_field($_POST['schnupperkurs-beginn']) : '';
            $schnupperkurs_ende = $schnupperkurs_beginn ? date('Y-m-d', strtotime($schnupperkurs_beginn . ' +2 months')) : '';
            $wie_gefunden = isset($_POST['wie_gefunden']) ? sanitize_text_field($_POST['wie_gefunden']) : '';
            $datenschutz = isset($_POST['datenschutz']) ? 1 : 0;
            $hinweise = isset($_POST['hinweise']) ? 1 : 0;
            $zahlungsmethode = isset($_POST['zahlungsmethode']) ? sanitize_text_field($_POST['zahlungsmethode']) : '';

            $wpdb->insert(
                $table_name,
                array(
                    'vorname' => $vorname,
                    'nachname' => $nachname,
                    'email' => $email,
                    'telefon' => $telefon,
                    'geburtsdatum' => $geburtsdatum,
                    'schnupperkurs_beginn' => $schnupperkurs_beginn,
                    'schnupperkurs_ende' => $schnupperkurs_ende,
                    'wie_gefunden' => $wie_gefunden,
                    'datenschutz' => $datenschutz,
                    'hinweise' => $hinweise,
                    'zahlungsmethode' => $zahlungsmethode
                )
            );
            Avf_Forms_Utils::send_schnupperkurs_confirmation_email($email, $vorname, $nachname);
            wp_redirect(home_url('/success'));
            exit;
        }
    }

    public static function handle_schnupperkurs_csv_download_request()
    {
        if (isset($_GET['download_schnupperkurs_csv']) && $_GET['download_schnupperkurs_csv'] === 'true') {
            if (is_user_logged_in() && current_user_can('edit_posts')) {
                $csv_data = self::generate_schnupperkurs_csv_data();

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="schnupperkurs_form_submissions.csv"');
                echo $csv_data;
                exit;
            } else {
                wp_die('You do not have permission to access this resource.');
            }
        }
    }

    public static function generate_schnupperkurs_csv_data()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_schnupperkurs_registrations';
        $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        $csv_content = '';
        if (!empty($data)) {
            $csv_content .= "ID," .
                "Vorname," .
                "Nachname," .
                "E-Mail," .
                "Telefon," .
                "Geburtsdatum," .
                "Schnupperkurs Beginn," .
                "Schnupperkurs Ende," .
                "Wie gefunden," .
                "Datenschutz," .
                "Hinweise," .
                "Zahlungsmethode," .
                "Antragsdatum\n";
            foreach ($data as $row) {
                $csv_content .= $row['id'] . ',' .
                    $row['vorname'] . ',' .
                    $row['nachname'] . ',' .
                    $row['email'] . ',' .
                    $row['telefon'] . ',' .
                    date('d.m.Y', strtotime($row['geburtsdatum'])) . ',' .
                    date('d.m.Y', strtotime($row['schnupperkurs_beginn'])) . ',' .
                    date('d.m.Y', strtotime($row['schnupperkurs_ende'])) . ',' .
                    $row['wie_gefunden'] . ',' .
                    ($row['datenschutz'] ? "Akzeptiert" : "Nicht akzeptiert") . ',' .
                    ($row['hinweise'] ? "Gelesen" : "Nicht gelesen") . ',' .
                    $row['zahlungsmethode'] . ',' .
                    date('d.m.Y', strtotime($row['submission_date'])) . "\n";
            }
        } else {
            $csv_content = "No data found.";
        }
        return $csv_content;
    }
}
