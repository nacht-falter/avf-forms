<?php

class Avf_Forms_Utils
{
    public static function send_membership_confirmation_email($email, $vorname, $nachname)
    {
        $subject = '[Aikido Verein Freiburg e.V.] Mitgliedschaftsantrag erhalten';
        $message = "Hallo $vorname,\n\n";
        $message .= "Dein Antrag ist bei uns eingegangen. Wir werden uns in Kürze bei dir melden.\n\n";
        $message .= "Falls Du Fragen zur Mitgliedschaft hast, schreibe bitte eine Mail an schatzmeister@aikido-freiburg.de.";
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

    public static function send_schnupperkurs_confirmation_email($email, $vorname, $nachname, $betrag)
    {
        $subject = '[Aikido Verein Freiburg e.V.] Schnupperkurs-Anmeldung erhalten';
        $message = "Hallo $vorname,\n\n";
        $message .= "Deine Anmeldung ist bei uns eingegangen. Vielen Dank!\n\n";
        $message .= "Bitte zahle die Kursgebühr in Höhe von $betrag € innerhalb von zwei Wochen entweder in bar oder per Überweisung auf unser Konto:\n";
        $message .= "Empfänger: Aikido Verein Freiburg e.V.\n";
        $message .= "IBAN: DE34680900000024401901\n";
        $message .= "BIC: GENODE61FR1\n";
        $message .= "Bank: Volksbank Freiburg\n";
        $message .= "Verwendungszweck: Schnupperkurs $vorname $nachname\n\n";
        $message .= "Falls Du Fragen zur Mitgliedschaft hast, schreibe gerne eine Mail an schatzmeister@aikido-freiburg.de.";
        $message .= "Bei allen anderen Fragen, wende dich gerne an vorstand@aikido-freiburg.de oder sprich uns auf der Matte an.\n\n";
        $message .= "Viele Grüße\n";
        $message .= "Dein Aikido Verein Freiburg e.V.\n";
        wp_mail($email, $subject, $message);

        $admin_email = get_option('admin_email');
        $admin_subject = "Neue Schnupperkurs-Anmeldung";
        $admin_message = "Neue Schnupperkurs-Anmeldung von $vorname $nachname eingegangen.\n\n";
        $admin_message .= "Download CSV: " . home_url('/csv-download') . "\n";
        wp_mail($admin_email, $admin_subject, $admin_message);
    }
}
