<?php

class Avf_Forms_Utils
{
    // Months after the end of a Schnupperkurs until its personal data is anonymized
    const SCHNUPPERKURS_RETENTION_MONTHS = 12;

    private static $emails = null;
    private static $bw_school_holidays = null;

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

    /**
     * Stores form errors under a random token and redirects back to the form.
     * The token in the URL ties the errors to this submission, so concurrent
     * visitors don't see each other's errors.
     */
    public static function redirect_with_form_errors(array $errors)
    {
        $token = bin2hex(random_bytes(16));
        set_transient('avf_form_errors_' . $token, $errors, 10 * MINUTE_IN_SECONDS);

        $redirect_url = add_query_arg(
            ['form_status' => 'error', 'avf_errors' => $token],
            wp_get_referer()
        );
        wp_redirect($redirect_url);
        exit();
    }

    /**
     * Returns the errors stored by redirect_with_form_errors() for the token in the URL.
     */
    public static function get_form_errors()
    {
        $token = isset($_GET['avf_errors']) ? sanitize_key($_GET['avf_errors']) : '';
        if ($token === '') {
            return [];
        }

        $key = 'avf_form_errors_' . $token;
        $errors = get_transient($key);
        delete_transient($key);

        return is_array($errors) ? $errors : [];
    }

    public static function render_form_errors()
    {
        $errors = self::get_form_errors();
        if (!$errors) {
            return '';
        }

        $html = '<div class="form-error" style="display: block; padding: 0.25rem 0.75rem;">';
        foreach ($errors as $error) {
            $html .= '<p>' . esc_html($error) . '</p>';
        }
        $html .= '</div>';

        return $html;
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

    public static function get_bank_details()
    {
        self::load_emails();
        
        if (!isset(self::$emails['bank_details']) || !is_array(self::$emails['bank_details'])) {
            return null;
        }
        
        $details = self::$emails['bank_details'];
        $rows = [];
        
        if (isset($details['empfaenger'])) {
            $rows[] = ['Empfänger', $details['empfaenger']];
        }
        if (isset($details['iban'])) {
            $rows[] = ['IBAN', $details['iban']];
        }
        if (isset($details['bic'])) {
            $rows[] = ['BIC', $details['bic']];
        }
        $rows[] = ['Verwendungszweck', 'Schnupperkurs + Name'];
        
        return empty($rows) ? null : $rows;
    }


    public static function send_membership_confirmation_email($email, $vorname, $nachname, $additional_data = array(), $record_id = 0)
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
            error_log("Failed to send membership confirmation email for Mitgliedschaft #$record_id");
        }

        $treasurer_email = self::get_emails_by_key('treasurer_email');

        $treasurer_subject = 'Neuer Mitgliedschaftsantrag eingegangen';
        $treasurer_message = "Neuer Mitgliedschaftsantrag von $vorname $nachname eingegangen.\n\n";

        // Add additional membership details if provided
        if (!empty($additional_data)) {
            if (isset($additional_data['mitgliedschaft_art'])) {
                $membership_type = isset(MITGLIEDSCHAFTSARTEN[$additional_data['mitgliedschaft_art']])
                    ? MITGLIEDSCHAFTSARTEN[$additional_data['mitgliedschaft_art']]
                    : ucfirst($additional_data['mitgliedschaft_art']);
                $treasurer_message .= "Mitgliedschaftsart: " . $membership_type . "\n";
            }

            // Adult membership specific fields
            if (isset($additional_data['starterpaket']) && $additional_data['starterpaket']) {
                $treasurer_message .= "Starterpaket: Ja\n";
            }
            if (isset($additional_data['spende_monatlich']) && $additional_data['spende_monatlich'] > 0) {
                $treasurer_message .= "Monatliche Spende: " . number_format($additional_data['spende_monatlich'], 2, ',', '.') . " €\n";
            }
            if (isset($additional_data['spende_einmalig']) && $additional_data['spende_einmalig'] > 0) {
                $treasurer_message .= "Einmalige Spende: " . number_format($additional_data['spende_einmalig'], 2, ',', '.') . " €\n";
            }

            // Children/Youth membership specific fields
            if (isset($additional_data['child_vorname']) && isset($additional_data['child_nachname'])) {
                $treasurer_message .= "Name des Kindes: " . $additional_data['child_vorname'] . " " . $additional_data['child_nachname'] . "\n";
            }
            if (isset($additional_data['geschwisterkind']) && $additional_data['geschwisterkind']) {
                $treasurer_message .= "Geschwisterkind: Ja\n";
            }
            if (isset($additional_data['thgutscheine']) && $additional_data['thgutscheine']) {
                $treasurer_message .= "Abrechnung über Teilhabegutscheine: Ja\n";
            }

            if (isset($additional_data['email'])) {
                $treasurer_message .= "E-Mail: " . $additional_data['email'] . "\n";
            }
            $treasurer_message .= "\n";
        }

        $treasurer_message .= "Zur Mitgliedschaftsverwaltung: " . home_url('/wp-admin/admin.php?page=avf-membership-admin') . "\n";

        $treasurer_headers = array(
            'From: Aikido Verein Freiburg <noreply@aikido-freiburg.de>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        foreach ($treasurer_email as $to_email) {
            if (!wp_mail($to_email, $treasurer_subject, $treasurer_message, $treasurer_headers)) {
                error_log("Failed to send treasurer notification for Mitgliedschaft #$record_id");
            }
        }
    }

    private static function load_bw_school_holidays()
    {
        if (self::$bw_school_holidays === null) {
            $path = AVF_PLUGIN_DIR . 'includes/data/bw-schulferien.php';
            self::$bw_school_holidays = file_exists($path) ? include $path : [];
        }
        return self::$bw_school_holidays;
    }

    public static function calculate_schnupperkurs_ende($beginn, $schnupperkurs_art)
    {
        $beginn_date = new DateTime($beginn);
        $ende_date = clone $beginn_date;
        $ende_date->modify('+2 months');

        if ($schnupperkurs_art === 'kind') {
            $ende_date = self::extend_for_school_holidays($beginn_date, $ende_date);
        }

        return $ende_date;
    }

    private static function extend_for_school_holidays(DateTime $beginn_date, DateTime $ende_date)
    {
        $holidays = self::load_bw_school_holidays();
        $applied = array_fill(0, count($holidays), false);
        $changed = true;

        while ($changed) {
            $changed = false;
            foreach ($holidays as $i => $holiday) {
                if ($applied[$i]) {
                    continue;
                }
                $h_start = new DateTime($holiday['start']);
                $h_end = new DateTime($holiday['end']);

                if ($h_start > $ende_date || $h_end < $beginn_date) {
                    continue;
                }

                $overlap_start = $h_start > $beginn_date ? $h_start : $beginn_date;
                $overlap_end = $h_end < $ende_date ? $h_end : $ende_date;
                $days = (int) $overlap_start->diff($overlap_end)->days + 1;

                $ende_date = (clone $ende_date)->modify("+{$days} days");
                $applied[$i] = true;
                $changed = true;
            }
        }

        return $ende_date;
    }

    public static function send_schnupperkurs_confirmation_email($email, $vorname, $nachname, $schnupperkurs_art, $beginn, $ende, $record_id = 0)
    {
        $headers = array(
            'From: Aikido Verein Freiburg <noreply@aikido-freiburg.de>',
            'Content-Type: text/plain; charset=UTF-8'
        );

        $art_label = SCHNUPPERKURSARTEN[$schnupperkurs_art] ?? $schnupperkurs_art;
        $preis = SCHNUPPERKURSPREISE[$schnupperkurs_art] ?? '–';
        $beginn_formatted = date_i18n('d.m.Y', strtotime($beginn));
        $ende_formatted = date_i18n('d.m.Y', strtotime($ende));

        $member_subject = '[Aikido Verein Freiburg e.V.] Schnupperkurs-Anmeldung erhalten';
        $member_message = "Hallo $vorname,\n\n";
        $member_message .= "Deine Anmeldung zum Schnupperkurs ($art_label) ist bei uns eingegangen.\n\n";
        $member_message .= "Startdatum: $beginn_formatted\n";
        $member_message .= "Enddatum: $ende_formatted\n";
        $member_message .= "Teilnahmegebühr: $preis €\n\n";
        $member_message .= "Bitte überweise den Betrag innerhalb von zwei Wochen auf folgendes Konto:\n\n";
        $bank_details = self::get_bank_details();
        if ($bank_details) {
            foreach ($bank_details as $row) {
                $member_message .= $row[0] . ": " . $row[1] . "\n";
            }
        }
        $member_message .= "\n";
        $member_message .= "Bei Fragen wende dich gerne an vorstand@aikido-freiburg.de oder sprich uns auf der Matte an.\n\n";
        $member_message .= "Viele Grüße\n";
        $member_message .= "Dein Aikido Verein Freiburg e.V.\n";

        if (!wp_mail($email, $member_subject, $member_message, $headers)) {
            error_log("Failed to send Schnupperkurs confirmation email for Schnupperkurs #$record_id");
        }

        $treasurer_email = self::get_emails_by_key('treasurer_email');

        $treasurer_subject = 'Neue Schnupperkurs-Anmeldung eingegangen';
        $treasurer_message = "Neue Schnupperkurs-Anmeldung von $vorname $nachname eingegangen.\n\n";
        $treasurer_message .= "Schnupperkursart: $art_label\n";
        $treasurer_message .= "Startdatum: $beginn_formatted\n";
        $treasurer_message .= "E-Mail: $email\n\n";
        $treasurer_message .= "Zur Schnupperkurs-Verwaltung: " . home_url('/wp-admin/admin.php?page=avf-schnupperkurs-admin') . "\n";

        foreach ($treasurer_email as $to_email) {
            if (!wp_mail($to_email, $treasurer_subject, $treasurer_message, $headers)) {
                error_log("Failed to send Schnupperkurs treasurer notification for Schnupperkurs #$record_id");
            }
        }
    }

    public static function send_starter_kit_notification($email, $telefon, $vorname, $nachname, $record_id = 0)
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
                error_log("Failed to send starter kit notification for Mitgliedschaft #$record_id");
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

    public static function schnupperkurs_notification()
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
                error_log("AVF-Mitgliedschaftsverwaltung: Schnupperkurs #$result->id beendet, aber Mitgliedschaft bereits vorhanden. Keine Benachrichtigung versendet.");
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
                            'AVF-Mitgliedschaftsverwaltung: Schnupperkurs-Benachrichtigung für Schnupperkurs #%d wurde am %s gesendet',
                            $result->id,
                            current_time('mysql')
                        )
                    );
                } else {
                    error_log(
                        sprintf(
                            'AVF-Mitgliedschaftsverwaltung: Fehler beim Senden der Schnupperkurs-Benachrichtigung für Schnupperkurs #%d am %s',
                            $result->id,
                            current_time('mysql')
                        )
                    );
                }
            } catch (Exception $e) {
                // The exception message may contain email addresses, so only the type is logged.
                error_log("Fehler beim Senden der Schnupperkurs-Benachrichtigung für Schnupperkurs #$result->id: " . get_class($e));
            }
        }
    }

    public static function delete_old_membership_data()
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
                thgutscheine = NULL,
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

    /**
     * Anonymizes Schnupperkurse that ended more than SCHNUPPERKURS_RETENTION_MONTHS ago.
     * Before the personal data is removed, the membership match used for the
     * statistics is stored in mitglied_seit, so conversion rates stay available.
     */
    public static function anonymize_old_schnupperkurs_data()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'avf_schnupperkurse';
        $memberships_table = $wpdb->prefix . 'avf_memberships';

        $column_exists = $wpdb->get_results(
            $wpdb->prepare("SHOW COLUMNS FROM $table_name LIKE %s", 'mitglied_seit')
        );
        if (empty($column_exists)) {
            error_log('AVF-Mitgliedschaftsverwaltung: Spalte mitglied_seit fehlt, Schnupperkurse nicht anonymisiert. Plugin bitte neu aktivieren.');
            return;
        }

        // Free text entered for "Sonstiges" may contain names
        $wie_erfahren_keys = array_keys(WIE_ERFAHREN);
        $placeholders = implode(', ', array_fill(0, count($wie_erfahren_keys), '%s'));

        // MySQL applies the assignments left to right, so mitglied_seit is
        // computed before the name and date of birth are removed.
        $query = $wpdb->prepare(
            "UPDATE $table_name AS sk
            SET sk.mitglied_seit = (
                    SELECT MIN(m.beitrittsdatum)
                    FROM $memberships_table AS m
                    WHERE LOWER(m.vorname) = LOWER(sk.vorname)
                    AND LOWER(m.nachname) = LOWER(sk.nachname)
                    AND m.geburtsdatum = DATE(sk.geburtsdatum)
                    AND m.beitrittsdatum >= sk.beginn
                ),
                sk.vorname = NULL,
                sk.nachname = NULL,
                sk.email = NULL,
                sk.telefon = NULL,
                sk.geburtsdatum = NULL,
                sk.notizen = NULL,
                sk.wie_erfahren = CASE
                    WHEN sk.wie_erfahren IN ($placeholders) THEN sk.wie_erfahren
                    ELSE 'sonstiges'
                END
            WHERE sk.vorname IS NOT NULL
            AND COALESCE(sk.ende, sk.beginn) < DATE_SUB(CURDATE(), INTERVAL %d MONTH)",
            ...array_merge($wie_erfahren_keys, [self::SCHNUPPERKURS_RETENTION_MONTHS])
        );

        $affected_rows = $wpdb->query($query);

        if ($affected_rows === false) {
            error_log('AVF-Mitgliedschaftsverwaltung: Fehler beim Anonymisieren der Schnupperkurse.');
        } elseif ($affected_rows > 0) {
            error_log("AVF-Mitgliedschaftsverwaltung: $affected_rows Schnupperkurse anonymisiert.");
        }
    }
}
