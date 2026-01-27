<?php
class Avf_Forms_Activator
{
    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $memberships_table = "{$wpdb->prefix}avf_memberships";

        $membership_sql = "
            CREATE TABLE IF NOT EXISTS $memberships_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                mitgliedschaft_art varchar(255) NOT NULL,
                vorname varchar(255) NOT NULL,
                nachname varchar(255) NOT NULL,
                vorname_eltern varchar(255) NULL,
                nachname_eltern varchar(255) NULL,
                geschwisterkind boolean NULL,
                email varchar(255) NOT NULL,
                telefon varchar(20),
                geburtsdatum date NOT NULL,
                strasse varchar(255) NOT NULL,
                hausnummer varchar(10) NOT NULL,
                plz varchar(10) NOT NULL,
                ort varchar(255) NOT NULL,
                beitrittsdatum date NOT NULL,
                kuendigungseingang date NULL,
                austrittsdatum date NULL,
                starterpaket boolean NULL,
                spende boolean NULL,
                spende_monatlich float NULL,
                spende_einmalig float NULL,
                satzung_datenschutz boolean NOT NULL,
                hinweise boolean NOT NULL,
                thgutscheine boolean NULL,
                sepa boolean NULL,
                kontoinhaber varchar(255) NULL,
                iban varchar(34) NULL,
                bic varchar(11) NULL,
                bank varchar(255) NULL,
                beitrag float NULL,
                wiedervorlage date NULL,
                wiedervorlage_grund varchar(255) NULL,
                notizen text NULL,
                submission_date datetime DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY  (id)
            ) $charset_collate;";

        $wpdb->query($membership_sql);

        // Update existing installations
        // Check if thgutscheine column exists before adding
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM $memberships_table LIKE %s",
                'thgutscheine'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $memberships_table ADD COLUMN thgutscheine boolean NULL AFTER hinweise");
        }

        // Make payment columns nullable for thgutscheine support
        $wpdb->query("ALTER TABLE $memberships_table MODIFY COLUMN sepa boolean NULL");
        $wpdb->query("ALTER TABLE $memberships_table MODIFY COLUMN kontoinhaber varchar(255) NULL");
        $wpdb->query("ALTER TABLE $memberships_table MODIFY COLUMN iban varchar(34) NULL");

        $schnupperkurs_table = "{$wpdb->prefix}avf_schnupperkurse";

        $schnupperkurs_sql = "
            CREATE TABLE IF NOT EXISTS $schnupperkurs_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            schnupperkurs_art varchar(255) NOT NULL,
            vorname varchar(255) NOT NULL,
            nachname varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefon varchar(20),
            geburtsdatum date NOT NULL,
            beginn date NOT NULL,
            ende date NULL,
            wie_erfahren varchar(255),
            notizen text NULL,
            submission_date datetime DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY  (id)
            ) $charset_collate;";

        $wpdb->query($schnupperkurs_sql);

        // Add treasurer role
        $editor = get_role('editor');
        if (!get_role('treasurer')) {
            add_role(
                'treasurer',
                'Treasurer',
                $editor->capabilities
            );
        }

        $role = get_role('treasurer');
        $role->add_cap('manage_memberships');
            $admin_role = get_role('administrator');

        if ($admin_role) {
            $admin_role->add_cap('manage_memberships');
        }

        // Schedule cron jobs
        if (!wp_next_scheduled('avf_schnupperkurs_notification')) {
            wp_schedule_event(time(), 'daily', 'avf_schnupperkurs_notification');
            error_log('Cron job scheduled at ' . date('Y-m-d H:i:s', time()));
        }

        if (!wp_next_scheduled('avf_delete_old_membership_data')) {
            wp_schedule_event(time(), 'daily', 'avf_delete_old_membership_data');
            error_log('Cron job scheduled at ' . date('Y-m-d H:i:s', time()));
        }

        // Define membership fees and store them in wp_options
        $membership_fees = [
        'aktiv' => 81,
        'aktiv_ermaessigt' => 60,
        'familie' => 60,
        'foerder' => 9,
        'sonder' => 30,
        'kind' => 45,
        'jugend' => 60,
        'geschwisterkind_discount' => 9,
        'passiv' => 0,
        ];

        update_option('avf_beitraege', $membership_fees);
    }
}
