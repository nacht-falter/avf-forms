<?php

class Avf_Forms_Utils
{
    private static $emails = null;

    private static function load_emails()
    {
        if (self::$emails === null) {
            $config_path = AVF_PLUGIN_DIR . 'config.php';
            if (!file_exists($config_path)) {
                error_log("Config file not found at: " . $config_path);
                self::$emails = [];
                return;
            }

            $config = file_exists($config_path) ? include $config_path : [];

            if (!is_array($config)) {
                error_log("Invalid config format. Expected an array.");
                self::$emails = [];
                return;
            }

            self::$emails = $config;
        }
    }

    public static function get_emails_by_key($key)
    {
        self::load_emails();

        // Convert strings to array
        if (isset(self::$emails[$key])) {
            return is_array(self::$emails[$key]) ? self::$emails[$key] : [self::$emails[$key]];
        }

        return [get_option('admin_email')];
    }

    public static function send_membership_confirmation_email($email, $vorname, $nachname)
    {
        $member_subject = '[Aikido Verein Freiburg e.V.] Mitgliedschaftsantrag erhalten';
        $member_message = "Hallo $vorname,\n\n";
        $member_message .= "Dein Antrag ist bei uns eingegangen. Wir werden uns in Kürze bei dir melden.\n\n";
        $member_message .= "Falls Du Fragen zur Mitgliedschaft hast, schreibe bitte eine Mail an schatzmeister@aikido-freiburg.de. ";
        $member_message .= "Bei allen anderen Fragen, wende dich gerne an vorstand@aikido-freiburg.de oder sprich uns auf der Matte an.\n\n";
        $member_message .= "Viele Grüße\n";
        $member_message .= "Dein Aikido Verein Freiburg e.V.\n";

        $member_headers = array(
            'From: Aikido Verein Freiburg <noreply@aikido-freiburg.de>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        if (!wp_mail($email, $member_subject, $member_message, $member_headers)) {
            error_log("Failed to send membership confirmation email to $email");
        }

        $treasurer_email = self::get_emails_by_key('treasurer_email');

        $treasurer_subject = 'Neuer Mitgliedschaftsantrag eingegangen';
        $treasurer_message = "Neuer Mitgliedschaftsantrag von $vorname $nachname eingegangen.\n\n";
        $treasurer_message .= "Zur Mitgliedschaftsverwaltung: " . home_url('/wp-admin/admin.php?page=avf-membership-admin') . "\n";

        $treasurer_headers = array(
            'From: Aikido Verein Freiburg <noreply@aikido-freiburg.de>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        foreach ($treasurer_email as $to_email) {
            if (!wp_mail($to_email, $treasurer_subject, $treasurer_message, $treasurer_headers)) {
                error_log("Failed to send treasurer notification to: " . $to_email);
            }
        }
    }

    public static function send_starter_kit_notification($email, $telefon, $vorname, $nachname)
    {
        $starterkit_email = self::get_emails_by_key('starterkit_email');

        $subject = "[Aikido Verein Freiburg e.V.] Starterpaket für $vorname $nachname";
        $message = "Hallo,\n\n";
        $message .= "$vorname $nachname hat beim Vereinsbeitritt ein Starterpaket bestellt.\n\n";
        $message .= "E-Mail-Adresse: $email\n";
        $message .= "Tel: $telefon\n\n";
        $message .= "Viele Grüße!\n";

        $headers = array(
            'From: Aikido Verein Freiburg <noreply@yourdomain.com>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        foreach ($starterkit_email as $to_email) {
            if (!wp_mail($to_email, $subject, $message, $headers)) {
                error_log("Failed to send email to: " . $to_email);
            }
        }
    }

    public static function subscribe_to_mailinglist($email, $listname)
    {
        $data = array(
            'subscribe_r' => 'subscribe',
            'mailaccount_r' => $email,
            'mailaccount2_r' => $email,
            'FBMLNAME' => $listname,
            'FBLANG' => 'de',
            'FBURLERROR_L' => 'https://ml.kundenserver.de/mailinglist/error.de.html',
            'FBURLSUBSCRIBE_L' => 'https://ml.kundenserver.de/mailinglist/subscribe.de.html',
            'FBURLUNSUBSCRIBE_L' => 'https://ml.kundenserver.de/mailinglist/unsubscribe.de.html',
            'FBURLINVALID_L' => 'https://ml.kundenserver.de/mailinglist/invalid.de.html'
        );

        $ch = curl_init('https://ml.kundenserver.de/cgi-bin/mailinglist.cgi');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    public static function format_date($date)
    {
        return !empty($date) ? date('d.m.Y', strtotime($date)) : '';
    }

    public static function format_bool($value)
    {
        if (!empty($value) && $value !== '0') {
            return 'Ja';
        }
        return '';
    }

    public static function avf_schnupperkurs_notification()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_schnupperkurse';

        $query = "SELECT * FROM $table_name WHERE DATE(ende) = CURDATE()";

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            error_log('AVF-Mitgliedschaftsverwaltung: Keine beendeten Schnupperkurse gefunden');
            return;
        }

        $memberships_table = $wpdb->prefix . 'avf_memberships';

        foreach ($results as $result) {
            $is_member = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $memberships_table
                    WHERE LOWER(vorname) = LOWER(%s)
                    AND LOWER(nachname) = LOWER(%s)
                    AND geburtsdatum = DATE(%s)",
                    $result->vorname, $result->nachname, $result->geburtsdatum
                )
            );

            if ($is_member > 0) {
                error_log("AVF-Mitgliedschaftsverwaltung: Schnupperkurs von $result->vorname $result->nachname beendet, aber Mitgliedschaft bereits vorhanden. Keine Benachrichtigung versendet.");
                continue;
            }

            // Send notification
            $vorname = sanitize_text_field($result->vorname);
            $nachname = sanitize_text_field($result->nachname);
            $email = sanitize_email($result->email);
            $telefon = sanitize_text_field($result->telefon);
            $schnupperkurs_art = SCHNUPPERKURSARTEN[sanitize_text_field($result->schnupperkurs_art)];

            $to = get_option('admin_email');
            $subject = sprintf(
                '[%s] Schnupperkurs von %s %s endet heute',
                get_bloginfo('name'),
                $vorname,
                $nachname
            );

            $message = sprintf(
                "Hallo,\n\n" .
                "Der Schnupperkurs von %s %s endet heute.\n\n" .
                "Schnupperkursart: %s\n" .
                "E-Mail: %s\n" .
                "Telefon: %s\n\n" .
                "Bitte erinnere %s daran, einen Mitgliedsantrag zu stellen.\n\n" .
                "Diese E-Mail wurde automatisch generiert.",
                $vorname,
                $nachname,
                $schnupperkurs_art,
                $email,
                $telefon,
                $vorname
            );

            $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $to . '>'
            );

            try {
                $sent = wp_mail($to, $subject, $message, $headers);

                if ($sent) {
                    error_log(
                        sprintf(
                            'AVF-Mitgliedschaftsverwaltung: Schnupperkurs-Benachrichtigung für %s %s wurde am %s an %s gesendet',
                            $vorname,
                            $nachname,
                            current_time('mysql'),
                            $to
                        )
                    );
                } else {
                    error_log(
                        sprintf(
                            'AVF-Mitgliedschaftsverwaltung: Fehler beim Senden der Schnupperkurs-Benachrichtigung für %s %s am %s an %s',
                            $vorname,
                            $nachname,
                            current_time('mysql'),
                            $to
                        )
                    );
                }
            } catch (Exception $e) {
                error_log("Fehler beim Senden der Schnupperkurs-Benachrichtigung: " . $e->getMessage());
            }
        }
    }

    public static function avf_delete_old_membership_data()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_memberships';

        $query = "UPDATE $table_name
            SET telefon = NULL,
                geburtsdatum = NULL,
                strasse = NULL,
                hausnummer = NULL,
                plz = NULL,
                ort = NULL,
                sepa = NULL,
                kontoinhaber = NULL,
                iban = NULL,
                bic = NULL,
                bank = NULL,
                notizen = CASE
                    WHEN notizen NOT LIKE '%Daten wegen Austritt bereinigt%'
                    THEN CONCAT(CURDATE(), ': Daten wegen Austritt bereinigt. ', CHAR(10), notizen)
                    ELSE notizen
                END
            WHERE austrittsdatum IS NOT NULL
              AND austrittsdatum < DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              AND (
                    telefon IS NOT NULL OR
                    geburtsdatum IS NOT NULL OR
                    strasse IS NOT NULL OR
                    hausnummer IS NOT NULL OR
                    plz IS NOT NULL OR
                    ort IS NOT NULL OR
                    sepa IS NOT NULL OR
                    kontoinhaber IS NOT NULL OR
                    iban IS NOT NULL OR
                    bic IS NOT NULL OR
                    bank IS NOT NULL
              )";

        $affected_rows = $wpdb->query($query);

        if ($affected_rows === false) {
            error_log('AVF-Mitgliedschaftsverwaltung: Fehler beim Bereinigen der Daten.');
        } elseif ($affected_rows === 0) {
            error_log('AVF-Mitgliedschaftsverwaltung: Keine zu bereinigenden Daten gefunden.');
        } else {
            error_log("AVF-Mitgliedschaftsverwaltung: $affected_rows Datensätze bereinigt.");
        }
    }
}
