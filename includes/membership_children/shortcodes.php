<?php

class Avf_Forms_Membership_Children_Shortcodes
{

    public static function register()
    {
        add_shortcode('membership_children_form', array( __CLASS__, 'Render_Membership_Children_form' ));
        add_shortcode('membership_children_csv_download', array( __CLASS__, 'Membership_Children_Csv_shortcode' ));
    }

    public static function Render_Membership_Children_form()
    {
        ob_start();
        ?>
        <form id="membership-children-form" class="avf-form" method="post" action="">

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
                <div>
                    <label>Geschwisterkind<label>
                    <label for="geschwisterkind" class="font-weight-normal">
                        <input type="checkbox" name="geschwisterkind" id="geschwisterkind"> Mein Kind hat ein Geschwisterkind, das bereits Mitglied im Aikido-Verein Freiburg e.V. ist.
                    </label>
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
                        <label for="strasse">Strasse</label>
                        <input type="text" name="strasse" id="strasse" placeholder="Strasse" required>
                    </div>
                    <div class="half-width">
                        <label for="hausnummer">Hausnummer</label>
                        <input type="text" name="hausnummer" id="hausnummer" placeholder="Hausnummer" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="plz">PLZ</label>
                        <input type="text" name="plz" id="plz" placeholder="PLZ" required>
                    </div>
                    <div class="half-width">
                        <label for="ort">Ort</label>
                        <input type="text" name="ort" id="ort" placeholder="Ort" required>
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

            <h2>Mitgliedschaft</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="beitrittsdatum">Beitrittsdatum</label>
                        <input type="date" name="beitrittsdatum" id="beitrittsdatum" placeholder="Beitrittsdatum" required>
                    </div>
                </div>
                <div>
                    <h5>Hinweise</h5>
                    <p><strong>Kündigung:</strong> Eine Kündigung der Mitgliedschaft hat bis spätestens 6 Wochen vor Quartalsende zu erfolgen.</p>
                    <p><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="satzung_datenschutz" id="satzung_datenschutz" required>
                    <label for="satzung_datenschutz">Ich habe die 
                        <a href="https://www.aikido-freiburg.de/wp-content/uploads/2024/05/Satzung-2022.pdf" title="Satzung" target="_blank">
                            <strong>Satzung</strong>
                        </a> 
                        und die 
                        <a href="https://www.aikido-freiburg.de/wp-content/uploads/2021/07/Datenschutzordnung_1.0.pdf" title="Datenschutzerklärung" target="_blank">
                            <strong>Datenschutzordnung</strong>
                        </a> 
                        des Aikido-Verein Freiburg e.V. gelesen und erkenne diese hiermit an.
                    </label>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="hinweise" id="hinweise" required>
                    <label for="hinweise">Die Hinweise zum Haftungsausschluss und zur Kündigung habe ich zur Kenntnis genommen.</label>
                </div>
            </div>

            <h2>Zahlungsdetails</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="kontoinhaber">Kontoinhaber</label>
                        <input type="text" name="kontoinhaber" id="kontoinhaber" placeholder="Kontoinhaber" required>
                    </div>
                    <div class="half-width">
                        <label for="iban">IBAN</label>
                        <input type="text" name="iban" id="iban" placeholder="IBAN" required>
                    </div>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="sepa" id="sepa" required>
                    <label for="sepa">Hiermit ermächtige ich den Aikido Verein Freiburg e.V., Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die vom Aikido Verein Freiburg e.V. auf mein Konto gezogenen Lastschriften einzulösen. Die untenstehenden Hinweise habe ich zur Kenntnis genommen.</label>
                </div>
                <div>
                    <h5>Hinweise:</h5>
                    <p>Der Aikido Verein Freiburg e.V. zieht die Mitgliedsbeiträge quartalsweise jeweils in den ersten beiden Wochen zu Beginn eines neuen Quartals ein. Mir ist bekannt, dass seitens des kontoführenden Kreditinstitutes keine Verpflichtung zur Einlösung besteht, wenn mein Konto die erforderliche Deckung nicht aufweist.</p>
                    <p>Der/die Kontoinhaber/in kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.</p>
                    <p>Falls dem Aikido-Verein Freiburg e.V. im Rahmen des Lastschriftverfahrens Kosten entstehen, die der Kontoinhaber zu vertreten hat, z.B. Rücklastschriftgebühren wegen mangelnder Kontodeckung oder fehlerhafter Angaben, sind diese Kosten vom Kontoinhaber zu tragen.</p>
                </div>
            </div>
            <input class="button" type="submit" name="membership_children_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }

    public static function Membership_Children_Csv_shortcode()
    {
        ob_start();
        if (is_user_logged_in() && current_user_can('edit_posts')) {
            $csv_url = esc_url(add_query_arg('download_membership_children_csv', 'true', home_url('/')));
            echo '<p><a href="' . $csv_url . '">Mitgliedschaftsanträge Kinder/Jugendliche als CSV herunterladen</a></p>';
        } else {
            echo 'You do not have permission to access this resource.';
        }
        return ob_get_clean();
    }
}
