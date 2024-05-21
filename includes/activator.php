<?php
class Avf_Forms_Activator
{
    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $tables = array(
            'avf_membership_applications' => "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}avf_membership_applications (
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
                    spende_monatlich float NOT NULL,
                    spende_einmalig float NOT NULL,
                    satzung_datenschutz boolean NOT NULL,
                    hinweise boolean NOT NULL,
                    sepa boolean NOT NULL,
                    kontoinhaber varchar(255) NOT NULL,
                    iban varchar(34) NOT NULL,
                    submission_date datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
                ) $charset_collate;",
            'avf_membership_children_applications' => "
                CREATE TABLE IF NOT EXISTS {$wpdb->prefix}avf_membership_children_applications (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    vorname varchar(255) NOT NULL,
                    nachname varchar(255) NOT NULL,
                    email varchar(255) NOT NULL,
                    telefon varchar(20) NOT NULL,
                    geburtsdatum date NOT NULL,
                    strasse varchar(255) NOT NULL,
                    hausnummer varchar(10) NOT NULL,
                    plz varchar(10) NOT NULL,
                    ort varchar(255) NOT NULL,
                    geschwisterkind boolean NOT NULL,
                    beitrittsdatum date NOT NULL,
                    satzung_datenschutz boolean NOT NULL,
                    hinweise boolean NOT NULL,
                    sepa boolean NOT NULL,
                    kontoinhaber varchar(255) NOT NULL,
                    iban varchar(34) NOT NULL,
                    submission_date datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY  (id)
                ) $charset_collate;"
        );

        foreach ($tables as $table_name => $sql) {
            $table_name_with_prefix = $wpdb->prefix . $table_name;
            $wpdb->query($sql);
        }
    }
}

