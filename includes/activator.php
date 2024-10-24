<?php
class Avf_Forms_Activator
{
    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name = "{$wpdb->prefix}avf_memberships";

        $sql = "
            CREATE TABLE IF NOT EXISTS $table_name (
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
                austrittsdatum date NULL,
                starterpaket boolean NULL,
                spende boolean NULL,
                spende_monatlich float NULL,
                spende_einmalig float NULL,
                satzung_datenschutz boolean NOT NULL,
                hinweise boolean NOT NULL,
                sepa boolean NOT NULL,
                kontoinhaber varchar(255) NOT NULL,
                iban varchar(34) NOT NULL,
                bic varchar(11) NULL,
                bank varchar(255) NULL,
                beitrag float NULL,
                notizen text NULL,
                submission_date datetime DEFAULT CURRENT_TIMESTAMP,
                
                PRIMARY KEY  (id)
            ) $charset_collate;";

        $wpdb->query($sql);

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
    }
}
