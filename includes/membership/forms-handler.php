<?php

class Avf_Forms_Membership_Handler
{

    public static function register()
    {
        add_action('init', array( __CLASS__, 'handle_membership_form_submission' ));
        add_action('init', array(__CLASS__, 'handle_membership_csv_download_request'));
    }

    public static function handle_membership_form_submission()
    {
        if (isset($_POST['membership_form_submit']) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_membership_applications';

            $vorname = sanitize_text_field($_POST['vorname']);
            $nachname = sanitize_text_field($_POST['nachname']);
            $email = sanitize_email($_POST['email']);
            $telefon = sanitize_text_field($_POST['telefon']);
            $geburtsdatum = sanitize_text_field($_POST['geburtsdatum']);
            $strasse = sanitize_text_field($_POST['strasse']);
            $hausnummer = sanitize_text_field($_POST['hausnummer']);
            $plz = sanitize_text_field($_POST['plz']);
            $ort = sanitize_text_field($_POST['ort']);
            $mitgliedschaft = sanitize_text_field($_POST['mitgliedschaft']);
            $beitrittsdatum = sanitize_text_field($_POST['beitrittsdatum']);
            $starterpaket = isset($_POST['starterpaket']) ? 1 : 0;
            $spende = isset($_POST['spende']) ? 1 : 0;
            $spende_monatlich = isset($_POST['spende']) && $_POST['intervall'] === 'monatlich' ? floatval($_POST['spende']) : 0;
            $spende_einmalig = isset($_POST['spende']) && $_POST['intervall'] === 'einmalig' ? floatval($_POST['spende']) : 0;
            $satzung_datenschutz = isset($_POST['satzung_datenschutz']) ? 1 : 0;
            $hinweise = isset($_POST['hinweise']) ? 1 : 0;
            $sepa = isset($_POST['sepa']) ? 1 : 0;
            $kontoinhaber = sanitize_text_field($_POST['kontoinhaber']);
            $iban = sanitize_text_field($_POST['iban']);

            $wpdb->insert(
                $table_name,
                array(
                    'vorname' => $vorname,
                    'nachname' => $nachname,
                    'email' => $email,
                    'telefon' => $telefon,
                    'geburtsdatum' => $geburtsdatum,
                    'strasse' => $strasse,
                    'hausnummer' => $hausnummer,
                    'plz' => $plz,
                    'ort' => $ort,
                    'mitgliedschaft' => $mitgliedschaft,
                    'beitrittsdatum' => $beitrittsdatum,
                    'starterpaket' => $starterpaket,
                    'spende_monatlich' => $spende_monatlich,
                    'spende_einmalig' => $spende_einmalig,
                    'satzung_datenschutz' => $satzung_datenschutz,
                    'hinweise' => $hinweise,
                    'sepa' => $sepa,
                    'kontoinhaber' => $kontoinhaber,
                    'iban' => $iban
                )
            );
            Avf_Forms_Utils::send_confirmation_email($email, $vorname, $nachname);
            wp_redirect(home_url('/success'));
            exit;
        }
    }

    public static function handle_membership_csv_download_request()
    {
        if (isset($_GET['download_csv']) && $_GET['download_csv'] === 'true') {
            if (is_user_logged_in() && current_user_can('edit_posts')) {
                $csv_data = self::generate_membership_csv_data();

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="membership_form_submissions.csv"');
                echo $csv_data;
                exit;
            } else {
                wp_die('You do not have permission to access this resource.');
            }
        }
    }

    public static function generate_membership_csv_data()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_membership_applications';
        $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        $csv_content = '';
        if (!empty($data)) {
            $csv_content .= "ID," .
                "Vorname," .
                "Nachname," .
                "E-Mail," .
                "Telefon," .
                "Geburtsdatum," .
                "Strasse," .
                "Hausnummer," .
                "PLZ," .
                "Ort," .
                "Mitgliedschaft," .
                "Beitrittsdatum," .
                "Starterpaket," .
                "Spende," .
                "Spende einmalig," .
                "Spende monatlich," .
                "Satzung und Datenschutz," .
                "Hinweise," .
                "SEPA-Mandat," .
                "Kontoinhaber," .
                "IBAN," .
                "Antragsdatum\n";
            foreach ($data as $row) {
                $csv_content .= $row['id'] . ',' .
                    $row['vorname'] . ',' .
                    $row['nachname'] . ',' .
                    $row['email'] . ',' .
                    $row['telefon'] . ',' .
                    date('d.m.Y', strtotime($row['geburtsdatum'])) . "\n";
                    $row['strasse'] . ',' .
                    $row['hausnummer'] . ',' .
                    $row['plz'] . ',' .
                    $row['ort'] . ',' .
                    $row['mitgliedschaft'] . ',' .
                    date('d.m.Y', strtotime($row['beitrittsdatum'])) . ',' .
                    ($row['starterpaket'] ? "Ja" : "Nein") . ',' .
                    ($row['spende'] ? "Ja" : "Nein") . ',' .
                    $row['spende_einmalig'] . ',' .
                    $row['spende_monatlich'] . ',' .
                    ($row['satzung_datenschutz'] ? "Akzeptiert" : "Nicht akzeptiert") . ',' .
                    ($row['hinweise'] ? "Gelesen" : "Nicht gelesen") . ',' .
                    ($row['sepa'] ? "Erteilt" : "Nicht erteilt") . ',' .
                    $row['kontoinhaber'] . ',' .
                    $row['iban'] . ',' .
                    date('d.m.Y', strtotime($row['submission_date'])) . "\n";
            }
        } else {
            $csv_content = "No data found.";
        }
        return $csv_content;
    }
}
