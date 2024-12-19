<?php

class Avf_Forms_Utils
{
    public static function send_membership_confirmation_email($email, $vorname, $nachname)
    {
        $subject = '[Aikido Verein Freiburg e.V.] Mitgliedschaftsantrag erhalten';
        $message = "Hallo $vorname,\n\n";
        $message .= "Dein Antrag ist bei uns eingegangen. Wir werden uns in Kürze bei dir melden.\n\n";
        $message .= "Falls Du Fragen zur Mitgliedschaft hast, schreibe bitte eine Mail an schatzmeister@aikido-freiburg.de. ";
        $message .= "Bei allen anderen Fragen, wende dich gerne an vorstand@aikido-freiburg.de oder sprich uns auf der Matte an.\n\n";
        $message .= "Viele Grüße\n";
        $message .= "Dein Aikido Verein Freiburg e.V.\n";
        wp_mail($email, $subject, $message);

        $admin_email = get_option('treasurer_email');
        $admin_subject = 'Neuer Mitgliedschaftsantrag eingegangen';
        $admin_message = "Neuer Mitgliedschaftsantrag von $vorname $nachname eingegangen.\n\n";
        $admin_message .= "Zur Migliedschaftsverwaltung: " . home_url('/wp-admin/admin.php?page=avf-membership-admin') . "\n";
        wp_mail($admin_email, $admin_subject, $admin_message);
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

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name}
        WHERE DATE(ende) <= CURDATE()"
        );

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            error_log('Keine beendeten Schnupperkurse gefunden');
            return;
        }

        foreach ($results as $result) {
            $is_member = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}avf_memberships
                    WHERE vorname = %s AND nachname = %s AND email = %s",
                    $result->vorname, $result->nachname, $result->email
                )
            );

            if ($is_member > 0) {
                error_log("Schnupperkurs von $result->vorname $result->nachname beendet, aber Mitgliedschaft bereits vorhanden. Keine Benachrichtigung versendet.");
                continue;
            }

            // Send notification
            $vorname = sanitize_text_field($result->vorname);
            $nachname = sanitize_text_field($result->nachname);
            $email = sanitize_email($result->email);
            $telefon = sanitize_text_field($result->telefon);

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
                "E-Mail: %s\n" .
                "Telefon: %s\n\n" .
                "Bitte erinnere %s daran, einen Mitgliedsantrag zu stellen.\n\n" .
                "Diese E-Mail wurde automatisch generiert.",
                $vorname,
                $nachname,
                $email,
                $telefon,
                $vorname
            );

            $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            );

            try {
                $sent = wp_mail($to, $subject, $message, $headers);

                if ($sent) {
                    error_log(
                        sprintf(
                            'Schnupperkurs-Benachrichtigung wurde am %s an %s %s gesendet',
                            current_time('mysql'),
                            $vorname,
                            $nachname
                        )
                    );
                } else {
                    error_log(
                        sprintf(
                            'Fehler beim Senden der Schnupperkurs-Benachrichtigung am %s an %s %s',
                            current_time('mysql'),
                            $vorname,
                            $nachname
                        )
                    );
                }
            } catch (Exception $e) {
                error_log("Fehler beim Senden der Schnupperkurs-Benachrichtigung: " . $e->getMessage());
            }
        }
    }
}
