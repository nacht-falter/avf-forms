<?php

class Avf_Forms_Utils
{
    public static function send_confirmation_email($email, $vorname, $nachname)
    {
        $subject = '[Aikido Verein Freiburg e.V.] Mitgliedschaftsantrag erhalten';
        $message = "Hallo $vorname,\n\n";
        $message .= "Dein Antrag ist bei uns eingegangen. Wir werden uns in Kürze bei dir melden.\n\n";
        $message .= "Falls Du Fragen zur Mitgliedschaft hast, schreiben gerne eine Mail an schatzmeister@aikido-freiburg.de.";
        $message .= "Bei allen anderen Fragen, wende dich gerne an vorstand@aikido-freiburg.de oder sprich uns auf der Matte an.\n\n";
        $message .= "Viele Grüße\n";
        $message .= "Dein Aikido Verein Freiburg e.V.\n";
        wp_mail($email, $subject, $message);

        $admin_email = get_option('admin_email');
        $admin_subject = 'Neuer Mitgliedschaftsantrag eingegangen';
        $admin_message = "Neuer Mitgliedschaftsantrag von $vorname $nachname eingegangen.\n\n";
        $admin_message .= "Download CSV: " . home_url('/csv-download') . "\n";
        wp_mail($admin_email, $admin_subject, $admin_message);
    }
}
