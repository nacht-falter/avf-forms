<?php

class Avf_Forms_Membership_Handler
{

    public static function register()
    {
        add_action('init', array( __CLASS__, 'handle_membership_form_submission' ));
    }

    public static function handle_membership_form_submission()
    {
        if (isset($_POST['membership_form_submit']) ) {

            // Check nonce
            if (!isset($_POST['membership_nonce']) || !wp_verify_nonce($_POST['membership_nonce'], 'membership_form_submit')) {
                wp_die();
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_memberships';

            $vorname = sanitize_text_field($_POST['vorname']);
            $nachname = sanitize_text_field($_POST['nachname']);
            $email = sanitize_email($_POST['email']);
            $telefon = sanitize_text_field($_POST['telefon']);
            $geburtsdatum = sanitize_text_field($_POST['geburtsdatum']);
            $strasse = sanitize_text_field($_POST['strasse']);
            $hausnummer = sanitize_text_field($_POST['hausnummer']);
            $plz = sanitize_text_field($_POST['plz']);
            $ort = sanitize_text_field($_POST['ort']);
            $mitgliedschaft_art = sanitize_text_field($_POST['mitgliedschaft_art']);
            $beitrittsdatum = sanitize_text_field($_POST['beitrittsdatum']);
            $starterpaket = isset($_POST['starterpaket']) ? 1 : 0;
            $mailinglist = isset($_POST['mailinglist']) ? 1 : 0;
            $spende_monatlich = isset($_POST['spende']) && isset($_POST['intervall']) && $_POST['intervall'] === 'monatlich' ? floatval($_POST['spende']) : 0;
            $spende_einmalig = isset($_POST['spende']) && isset($_POST['intervall']) && $_POST['intervall'] === 'einmalig' ? floatval($_POST['spende']) : 0;
            $satzung_datenschutz = isset($_POST['satzung_datenschutz']) ? 1 : 0;
            $hinweise = isset($_POST['hinweise']) ? 1 : 0;
            $sepa = isset($_POST['sepa']) ? 1 : 0;
            $kontoinhaber = sanitize_text_field($_POST['kontoinhaber']);
            $iban = sanitize_text_field($_POST['iban']);

            // Child specific fields
            $vorname_eltern = isset($_POST['vorname_eltern']) ? sanitize_text_field($_POST['vorname_eltern']) : null;
            $nachname_eltern = isset($_POST['nachname_eltern']) ? sanitize_text_field($_POST['nachname_eltern']) : null;
            $geschwisterkind = isset($_POST['geschwisterkind']) ? 1 : 0;

            $dob = new DateTime($geburtsdatum);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($age < 14) {
                $mitgliedschaft_art = 'kind';
            } elseif ($age < 18) {
                $mitgliedschaft_art = 'jugend';
            }

            $wpdb->insert(
                $table_name,
                array(
                    'mitgliedschaft_art' => $mitgliedschaft_art,
                    'vorname' => $vorname,
                    'nachname' => $nachname,
                    'vorname_eltern' => $vorname_eltern,
                    'nachname_eltern' => $nachname_eltern,
                    'email' => $email,
                    'telefon' => $telefon,
                    'geburtsdatum' => $geburtsdatum,
                    'strasse' => $strasse,
                    'hausnummer' => $hausnummer,
                    'plz' => $plz,
                    'ort' => $ort,
                    'geschwisterkind' => $geschwisterkind,
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

            // Subscribe to mailinglist
            if ($mailinglist) {
                Avf_Forms_Utils::subscribe_to_mailinglist($email, "alle@aikido-freiburg.de");
            }

            // Send confirmation email
            if ($mitgliedschaft_art == 'Kind' || $mitgliedschaft_art == 'Jugend') {
                Avf_Forms_Utils::send_membership_confirmation_email($email, $vorname_eltern, $nachname_eltern);
            } else {
                Avf_Forms_Utils::send_membership_confirmation_email($email, $vorname, $nachname);
            }

            wp_redirect(home_url('/success'));
            exit;
        }
    }
}
