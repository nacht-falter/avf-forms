<?php

class Avf_Forms_Handler {

    public static function register() {
        add_action( 'init', array( __CLASS__, 'handle_membership_form_submission' ) );
    }

    public static function handle_membership_form_submission() {
        if ( isset( $_POST['membership_form_submit'] ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_membership_entries';

            $vorname = sanitize_text_field( $_POST['vorname'] );
            $nachname = sanitize_text_field( $_POST['nachname'] );
            $email = sanitize_email( $_POST['email'] );
            $telefon = sanitize_text_field( $_POST['telefon'] );
            $geburtsdatum = sanitize_text_field( $_POST['geburtsdatum'] );
            $strasse = sanitize_text_field( $_POST['strasse'] );
            $hausnummer = sanitize_text_field( $_POST['hausnummer'] );
            $plz = sanitize_text_field( $_POST['plz'] );
            $ort = sanitize_text_field( $_POST['ort'] );
            $mitgliedschaft = sanitize_text_field( $_POST['mitgliedschaft'] );
            $beitrittsdatum = sanitize_text_field( $_POST['beitrittsdatum'] );
            $starterpaket = isset( $_POST['starterpaket'] ) ? 1 : 0;
            $spende = isset( $_POST['spende'] ) ? 1 : 0;
            $spende_monatlich = isset( $_POST['spende_monatlich'] ) ? intval( $_POST['spende_monatlich'] ) : null;
            $spende_einmalig = isset( $_POST['spende_einmalig'] ) ? intval( $_POST['spende_einmalig'] ) : null;
            $satzung_datenschutz = isset( $_POST['satzung_datenschutz'] ) ? 1 : 0;
            $hinweise = isset( $_POST['hinweise'] ) ? 1 : 0;
            $sepa = isset( $_POST['sepa'] ) ? 1 : 0;
            $kontoinhaber = sanitize_text_field( $_POST['kontoinhaber'] );
            $iban = sanitize_text_field( $_POST['iban'] );

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
                    'spende' => $spende,
                    'spende_monatlich' => $spende_monatlich,
                    'spende_einmalig' => $spende_einmalig,
                    'satzung_datenschutz' => $satzung_datenschutz,
                    'hinweise' => $hinweise,
                    'sepa' => $sepa,
                    'kontoinhaber' => $kontoinhaber,
                    'iban' => $iban
                )
            );
        }
    }
}
