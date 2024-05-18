<?php

class Avf_Forms_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'avf_membership_entries';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            vorname varchar(255) NOT NULL,
            nachname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefon varchar(20),
            geburtsdatum date NOT NULL,
            strasse varchar(255) NOT NULL,
            hausnummer varchar(10) NOT NULL,
            plz varchar(10) NOT NULL,
            ort varchar(255) NOT NULL,
            mitgliedschaft varchar(255) NOT NULL,
            beitrittsdatum date NOT NULL,
            starterpaket boolean NOT NULL,
            spende boolean NOT NULL,
            spende_monatlich int(11),
            spende_einmalig int(11),
            satzung_datenschutz boolean NOT NULL,
            hinweise boolean NOT NULL,
            sepa boolean NOT NULL,
            kontoinhaber varchar(255) NOT NULL,
            iban varchar(34) NOT NULL,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
