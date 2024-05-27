<?php

class Avf_Forms_Schnupperkurs_Shortcodes
{

    public static function register()
    {
        add_shortcode('schnupperkurs_form', array( __CLASS__, 'Render_Schnupperkurs_form' ));
        add_shortcode('schnupperkurs_csv_download', array( __CLASS__, 'Schnupperkurs_Csv_shortcode' ));
    }

    public static function Render_Schnupperkurs_form()
    {
        ob_start();
        ?>
        <form id="schnupperkurs-form" class="avf-form" method="post" action="">
            <h2>Persönliche Angaben</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="vorname">Vorname</label>
                        <input type="text" name="vorname" id="vorname" placeholder="Vorname" required>
                    </div>
                    <div class="half-width">
                        <label for="nachname">Nachname</label>
                        <input type="text" name="nachname" id="nachname" placeholder="Nachname" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="email">E-Mail</label>
                        <input type="email" name="email" id="email" placeholder="E-Mail" required>
                    </div>
                    <div class="half-width">
                        <label for="telefon">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" placeholder="Telefonnummer">
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="geburtsdatum">Geburtsdatum</label>
                        <input type="date" name="geburtsdatum" id="geburtsdatum" required>
                    </div>
                </div>
            </div>

            <h2>Anmeldungsdetails</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="schnupperkurs-beginn">Schnupperkurs-Beginn</label>
                        <input type="date" name="schnupperkurs-beginn" id="schnupperkurs-beginn" required>
                    </div>
                </div>
                <h5>Hinweise</h5>
                <div>
                    <p class="mt-3"><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <div>
                    <input class="d-inline align-top mt-1" type="checkbox" name="datenschutz" id="datenschutz" required>
                    <label class="d-inline align-top" for="datenschutz">Ich habe die 
                        <a href="https://www.aikido-freiburg.de/wp-content/uploads/2021/07/Datenschutzordnung_1.0.pdf" title="Datenschutzerklärung" target="_blank">
                            <strong>Datenschutzordnung</strong>
                        </a> 
                         des Aikido-Verein Freiburg e.V. gelesen und erkenne diese hiermit an.
                    </label>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="hinweise" id="hinweise" required>
                    <label class="d-inline align-top" for="hinweise">Die Hinweise zum Haftungsausschluss habe ich zur Kenntnis genommen.</label>
                </div>
                <div class="flex-container">
                    <div class="no-wrap">
                        <label class="font-weight-normal mb-0" for="wie_gefunden">Vom Aikido Verein Freiburg e.V. habe ich erfahren durch:</label>
                        <select name="wie_gefunden" id="wie_gefunden" required>
                            <option value="" disabled hidden selected>Bitte wählen</option>
                            <option value="Freunde">Freunde</option>
                            <option value="Familie">Familie</option>
                            <option value="Internet">Internet</option>
                            <option value="Social Media">Social Media</option>
                            <option value="Flyer">Flyer</option>
                            <option value="Plakat">Plakat</option>
                            <option value="Anzeige">Anzeige</option>
                            <option value="Veranstaltung">Veranstaltung</option>
                            <option value="Sonstiges">Sonstiges</option>
                        </select>
                    </div>
                </div>
            </div>
            <h2>Zahlungsdetails</h2>
            <div class="container">
                <div class="flex-container">
                    <p class="pt-3 pb-0 mb-0">Die Teilnahmegebür für den Schnupperkurs beträgt € 30 für zwei Monate.</p>
                    <div>
                        <label for="ueberweisung" class="font-weight-normal"><input type="radio" name="zahlungsmethode" id="ueberweisung" value="überweisung" required> Den Betrag werde ich überweisen</label>
                        <label for="barzahlung" class="font-weight-normal"><input type="radio" name="zahlungsmethode" id="barzahlung" value="barzahlung" required> Den Betrag werde ich bar bezahlen</label>
                    </div>
                </div>
                <div class="d-flex bg-light flex-column fs-small p-3 mt-3">
                    <div class="mb-2"><strong>Überweisungen bitte innerhalb von zwei Wochen auf folgendes Konto:</strong></div>
                    <div class="flex-column md-flex-row d-flex">
                        <div class="flex-1"><strong>Empfänger:</strong></div>
                        <div class="flex-1">Aikido Verein Freiburg e.V.</div>
                    </div>
                    <div class="flex-column md-flex-row d-flex">
                        <div class="flex-1"><strong>IBAN:</strong></div>
                        <div class="flex-1">DE34680900000024401901</div>
                    </div>
                    <div class="flex-column md-flex-row d-flex">
                        <div class="flex-1"><strong>BIC:</strong></div>
                        <div class="flex-1">GENODE61FR1</div>
                    </div>
                    <div class="flex-column md-flex-row d-flex">
                        <div class="flex-1"><strong>Bank</strong></div>
                        <div class="flex-1">Volksbank Freiburg:</div>
                    </div>
                    <div class="flex-column md-flex-row d-flex">
                        <div class="flex-1"><strong>Verwendungszweck:</strong></div>
                        <div class="flex-1">Schnupperkurs + Name</div>
                    </div>
                </div>
            </div>
            <input class="button" type="submit" name="schnupperkurs_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }

    public static function Schnupperkurs_Csv_shortcode()
    {
        ob_start();

        if (is_user_logged_in() && current_user_can('edit_posts')) {
            $csv_url = esc_url(add_query_arg('download_schnupperkurs_csv', 'true', home_url('/')));
            echo '<p><a href="' . $csv_url . '">Schnupperkurs-Anmeldungen Erwachsene als CSV herunterladen</a></p>';
        } else {
            echo 'You do not have permission to access this resource.';
        }

        return ob_get_clean();
    }
}
