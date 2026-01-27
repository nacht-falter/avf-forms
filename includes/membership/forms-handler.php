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
            $mitgliedschaft_art = sanitize_text_field($_POST['mitgliedschaft_art'] ?? '');
            $beitrittsdatum = sanitize_text_field($_POST['beitrittsdatum']);
            $starterpaket = isset($_POST['starterpaket']) ? 1 : 0;
            $mailinglist = isset($_POST['mailinglist']) ? 1 : 0;
            $satzung_datenschutz = isset($_POST['satzung_datenschutz']) ? 1 : 0;
            $hinweise = isset($_POST['hinweise']) ? 1 : 0;
            $thgutscheine = isset($_POST['thgutscheine']) ? 1 : 0;
            $sepa = isset($_POST['sepa']) ? 1 : 0;
            $kontoinhaber = sanitize_text_field($_POST['kontoinhaber'] ?? '');
            $iban = sanitize_text_field($_POST['iban'] ?? '');
            $bic = sanitize_text_field($_POST['bic'] ?? '');
            $bank = sanitize_text_field($_POST['bank'] ?? '');

            $errors = [];

            if (strlen($plz) > 5) {
                $errors[] = "Die Postleitzahl darf maximal 5 Zeichen lang sein.";
            }

            if (strlen($iban) > 34) {
                $errors[] = "Die IBAN darf maximal 34 Zeichen lang sein.";
            }

            if (strlen($bic) > 11) {
                $errors[] = "Die BIC darf maximal 11 Zeichen lang sein.";
            }

            if (!empty($errors)) {
                set_transient('form_validation_errors', $errors, 0);

                $redirect_url = wp_get_referer();
                $redirect_url = add_query_arg('form_status', 'error', $redirect_url);

                wp_redirect($redirect_url);
                exit();
            }

            // Donations
            if (isset($_POST['spende'])) {
                if ($_POST['spende'] === 'freibetrag' && isset($_POST['freibetrag-input']) && is_numeric($_POST['freibetrag-input'])) {
                    $spende_value = floatval($_POST['freibetrag-input']);
                } else {
                    $spende_value = floatval($_POST['spende']);
                }
            } else {
                $spende_value = 0;
            }

            $spende_monatlich = isset($_POST['intervall']) && $_POST['intervall'] === 'monatlich' ? $spende_value : 0;
            $spende_einmalig = isset($_POST['intervall']) && $_POST['intervall'] === 'einmalig' ? $spende_value : 0;


            // Child specific fields
            $vorname_eltern = isset($_POST['vorname_eltern']) ? sanitize_text_field($_POST['vorname_eltern']) : null;
            $nachname_eltern = isset($_POST['nachname_eltern']) ? sanitize_text_field($_POST['nachname_eltern']) : null;
            $geschwisterkind = isset($_POST['geschwisterkind']) ? 1 : 0;

            $notizen = '';

            $dob = new DateTime($geburtsdatum);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($age < 14) {
                $mitgliedschaft_art = 'kind';
            } elseif ($age < 18) {
                $mitgliedschaft_art = 'jugend';
            }

            $beitraege = get_option('avf_beitraege');

            if (array_key_exists($mitgliedschaft_art, $beitraege)) {
                $beitrag = $beitraege[$mitgliedschaft_art] ?? 0;
            }

            if ($beitrag && $geschwisterkind) {
                $discount = $beitraege['geschwisterkind_discount'] ?? 0; // Default to 0 if not set
                $beitrag = $beitrag - $discount;
                $notizen = '2. Kind, ' . $discount . ' € Rabatt';
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
                    'thgutscheine' => $thgutscheine,
                    'sepa' => $sepa,
                    'kontoinhaber' => $kontoinhaber,
                    'iban' => $iban,
                    'bic' => $bic,
                    'bank' => $bank,
                    'beitrag' => $beitrag,
                    'notizen' => $notizen,
                    'wiedervorlage' => current_time('mysql'),
                    'wiedervorlage_grund' => "SEPA anlegen"
                )
            );

            // Subscribe to mailinglist
            if ($mailinglist) {
                Avf_Forms_Utils::subscribe_to_mailinglist($email, "alle@aikido-freiburg.de");
            }

            // Send notification for starter kit
            if ($starterpaket) {
                Avf_Forms_Utils::send_starter_kit_notification($email, $telefon, $vorname, $nachname);
            }

            // Prepare additional data for staff notification based on membership type
            if ($mitgliedschaft_art == 'Kind' || $mitgliedschaft_art == 'Jugend') {
                // Children/Youth membership - include child name and thgutscheine
                $additional_data = array(
                    'mitgliedschaft_art' => $mitgliedschaft_art,
                    'child_vorname' => $vorname,
                    'child_nachname' => $nachname,
                    'geschwisterkind' => $geschwisterkind,
                    'thgutscheine' => $thgutscheine,
                    'email' => $email
                );
                Avf_Forms_Utils::send_membership_confirmation_email($email, $vorname_eltern, $nachname_eltern, $additional_data);
            } else {
                // Adult membership - include donations and starter kit
                $additional_data = array(
                    'mitgliedschaft_art' => $mitgliedschaft_art,
                    'starterpaket' => $starterpaket,
                    'spende_monatlich' => $spende_monatlich,
                    'spende_einmalig' => $spende_einmalig,
                    'email' => $email
                );
                Avf_Forms_Utils::send_membership_confirmation_email($email, $vorname, $nachname, $additional_data);
            }

            wp_redirect(home_url('/success'));
            exit;
        }
    }
}
