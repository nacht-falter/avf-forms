<?php

class Avf_Forms_Schnupperkurs_Children_Shortcodes
{

    public static function register()
    {
        add_shortcode('schnupperkurs_children_form', array( __CLASS__, 'Render_Schnupperkurs_Children_form' ));
        add_shortcode('schnupperkurs_children_csv_download', array( __CLASS__, 'Schnupperkurs_Children_Csv_shortcode' ));
    }

    public static function Render_Schnupperkurs_Children_form()
    {
        ob_start();
        ?>
        <form id="schnupperkurs_children-form" class="avf-form" method="post" action="">
            <h2>Angaben zum Kind</h2>
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
                        <label for="geburtsdatum">Geburtsdatum</label>
                        <input type="date" name="geburtsdatum" id="geburtsdatum" placeholder="Geburtsdatum" required>
                    </div>
                </div>
            </div>

            <h2>Kontaktdaten der Eltern</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="vorname_eltern">Vorname</label>
                        <input type="text" name="vorname_eltern" id="vorname_eltern" placeholder="Vorname" required>
                    </div>
                    <div class="half-width">
                        <label for="nachname_eltern">Nachname</label>
                        <input type="text" name="nachname_eltern" id="nachname_eltern" placeholder="Nachname" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="email">E-Mail</label>
                        <input type="email" name="email" id="email" placeholder="E-Mail" required>
                    </div>
                    <div class="half-width">
                        <label for="telefon">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" placeholder="Telefonnummer" required>
                    </div>
                </div>
            </div>

            <h2>Anmeldungsdetails</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="schnupperkurs-beginn">Schnupperkurs-Beginn</label>
                        <input type="date" name="schnupperkurs-beginn" id="schnupperkurs-beginn" placeholder="Schnupperkurs-Beginn" required>
                    </div>
                    <p class="mb-0"><strong>Hinweis:</strong> Der Schnupperkurs verlängert sich um die Dauer der Schulferien, wenn diese in den Zeitraum des Kurses fallen, da in den Ferien kein Kindertraining stattfindet.</small></p>
                </div>
                <div>
                    <p class="mt-3 mb-0"><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="datenschutz" id="datenschutz" required>
                    <label for="datenschutz">Ich habe die 
                        <a href="https://www.aikido-freiburg.de/wp-content/uploads/2021/07/Datenschutzordnung_1.0.pdf" title="Datenschutzerklärung" target="_blank">
                            <strong>Datenschutzordnung</strong>
                        </a> 
                         des Aikido-Verein Freiburg e.V. gelesen und erkenne diese hiermit an.
                    </label>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="hinweise" id="hinweise" required>
                    <label for="hinweise">Die Hinweise zum Haftungsausschluss habe ich zur Kenntnis genommen.</label>
                </div>
                <div class="flex-container">
                    <div>
                        <label class="font-weight-normal mb-0" for="wie_gefunden">Vom Aikido Verein Freiburg e.V. habe ich erfahren durch:</label>
                    </div>
                    <div class="half-width">
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
                <div class="flex-container">
                    <div class="bg-light p-3 mt-3">
                        <p class="mb-0"><small><strong>Überweisungen bitte innerhalb von zwei Wochen auf folgendes Konto:</strong></small></p>
                        <table class="mb-0">
                            <tr class="border-0">
                                <td>Empfänger</td>
                                <td>Aikido Verein Freiburg e.V.</td>
                            </tr>
                            <tr class="border-0">
                                <td>IBAN</td>
                                <td>DE34680900000024401901</td>
                            </tr>
                            <tr class="border-0">
                                <td>BIC</td>
                                <td>GENODE61FR1</td>
                            </tr>
                            <tr class="border-0">
                                <td>Bank</td>
                                <td>Volksbank Freiburg</td>
                            </tr>
                            <tr class="border-0">
                                <td>Verwendungszweck</td>
                                <td>Schnupperkurs + Name</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <input class="button" type="submit" name="schnupperkurs_children_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }

    public static function Schnupperkurs_Children_Csv_shortcode()
    {
        ob_start();

        if (is_user_logged_in() && current_user_can('edit_posts')) {
            $csv_url = esc_url(add_query_arg('download_schnupperkurs_children_csv', 'true', home_url('/')));
            echo '<p><a href="' . $csv_url . '">Schnupperkurs-Anmeldungen Kinder/Jugendliche als CSV herunterladen</a></p>';
        } else {
            echo 'You do not have permission to access this resource.';
        }

        return ob_get_clean();
    }
}
