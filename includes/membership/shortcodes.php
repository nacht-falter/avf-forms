<?php

class Avf_Forms_Membership_Shortcodes
{

    public static function register()
    {
        add_shortcode('membership_form', array( __CLASS__, 'Render_Membership_form' ));
        add_shortcode('membership_csv_download', array( __CLASS__, 'Membership_Csv_shortcode' ));
    }

    public static function Render_Membership_form()
    {
        ob_start();
        ?>
        <form id="membership-form" class="avf-form" method="post" action="">
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
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" placeholder="E-Mail" required>
                    </div>
                    <div class="half-width">
                        <label for="telefon">Telefon</label>
                        <input type="tel" name="telefon" id="telefon" placeholder="Telefonnummer">
                    </div>
                </div>
                <div>
                    <label for="geburtsdatum">Geburtsdatum</label>
                    <input type="date" name="geburtsdatum" id="geburtsdatum" placeholder="Geburtsdatum" required>
                </div>
            </div>

            <h2>Adresse</h2>
            <div class="container">
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
            </div>

            <h2>Mitgliedschaft</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="mitgliedschaft">Art der Mitgliedschaft</label>
                        <select name="mitgliedschaft" id="mitgliedschaft" required>
                            <option value="aktiv">Aktives Mitglied</option>
                            <option value="familie">Familienmitglied (ab der 3. Person)</option>
                            <option value="foerder">Fördermitglied</option>
                        </select>
                    </div>
                    <div class="half-width">
                        <label for="beitrittsdatum">Beitrittsdatum</label>
                        <input type="date" name="beitrittsdatum" id="beitrittsdatum" placeholder="Beitrittsdatum" required>
                    </div>
                </div>
                <div>
                    <h5>Hinweise</h5>
                    <p><strong>Kündigung:</strong> Eine Kündigung der Mitgliedschaft hat bis spätestens zum 15. des Kündigungsmonats zu erfolgen.</p>
                    <p><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="satzung_datenschutz" id="satzung_datenschutz" required>
                    <label for="satzung_datenschutz">Ich habe die <a>Satzung</a> und die <a>Datenschutzordnung</a> des Aikido-Verein Freiburg e.V. gelesen und erkenne diese
    hiermit an.</label>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="hinweise" id="hinweise" required>
                    <label for="hinweise">Die Hinweise zum Haftungsausschluss und zur Kündigung habe ich zur Kenntnis genommen.</label>
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="starterpaket" id="starterpaket">
                    <label for="starterpaket">Ich möchte das <strong>vergünstigte Starter-Angebot</strong> in Anspruch nehmen (Aikido-Anzug und Vereins-T-Shirt für 35 €). Ich bin damit einverstanden, dass der Betrag einmalig mit dem ersten Mitgliedsbeitrag per Lastschrift eingezogen wird.</label>
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
                    <input class="custom-checkbox" type="checkbox" name="spende" id="spende">
                    <label for="spende">Ich möchte den Aikido Verein Freiburg gerne zusätzlich mit einer freiwilligen Spende unterstützen (gegen Erhalt einer Spendenquittung):</label>
                </div>
                <div id="spende-details">
                    <div class="flex-container align-center no-wrap indent">
                        <label for="intervall-mtl" class="disabled font-weight-normal"><input class="disabled" type="radio" name="intervall" id="intervall-mtl" value="monatlich" disabled> Monatlich</label>
                        <label for="intervall-einmal" class="disabled font-weight-normal"><input class="disabled" type="radio" name="intervall" id="intervall-einmal" value="einmalig" disabled> Einmalig</label>
                    </div>
                    <div class="flex-container flex-row indent">
                            <label for="spende-5" class="disabled font-weight-normal"><input class="disabled" type="radio" name="spende" value="5" id="spende-5" disabled> 5 €</label>
                            <label for="spende-10" class="disabled font-weight-normal"><input class="disabled" type="radio" name="spende" value="10" id="spende-10" disabled> 10 €</label>
                            <label for="spende-15" class="disabled font-weight-normal"><input class="disabled" type="radio" name="spende" value="15" id="spende-15" disabled> 15 €</label>
                            <label for="spende-freibetrag" class="disabled font-weight-normal"><input class="disabled" type="radio" name="spende" value="freibetrag" id="spende-freibetrag" disabled> Freibetrag</label>
                            <input id="freibetrag-input" type="number" name="spende" placeholder="Betrag">
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
            <input class="button" type="submit" name="membership_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }

    public static function Membership_Csv_shortcode()
    {
        ob_start();

        if (is_user_logged_in() && current_user_can('edit_posts')) {
            $csv_url = esc_url(add_query_arg('download_membership_csv', 'true', home_url('/')));
            echo '<p><a href="' . $csv_url . '">Mitgliedschaftsanträge Erwachsene als CSV herunterladen</a></p>';
        } else {
            echo 'You do not have permission to access this resource.';
        }

        return ob_get_clean();
    }
}
