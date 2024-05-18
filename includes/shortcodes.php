<?php

class Avf_Forms_Shortcodes {

    public static function register() {
        add_shortcode( 'membership_form', array( __CLASS__, 'render_membership_form' ) );
    }

    public static function render_membership_form() {
        ob_start();
        ?>
        <form id="membership_form" class="custom-form" method="post" action="">
            <div class="personal-details">
                <h2>Persönliche Angaben</h2>
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

            <div class="address">
                <h2>Adresse</h2>
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

            <div class="membership">
                <h2>Mitgliedschaft</h2>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="mitgliedschaft">Art der Mitgliedschaft</label>
                        <select name="mitgliedschaft" id="mitgliedschaft" required>
                            <option value="aktiv">Aktives Mitglied</option>
                            <option value="familie">Familienmitglied</option>
                            <option value="foerder">Fördermitglied</option>
                        </select>
                    </div>
                    <div class="half-width">
                        <label for="beitrittsdatum">Beitrittsdatum</label>
                        <input type="date" name="beitrittsdatum" id="beitrittsdatum" placeholder="Beitrittsdatum" required>
                    </div>
                </div>
                <div>
                    <p><strong>Hinweis:</strong> Eine Kündigung der Mitgliedschaft hat bis spätestens 6 Wochen vor Quartalsende zu erfolgen.</p>
                    <p><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
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
            <div class="payment-details">
                <h2>Zahlungsdetails</h2>
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
                <div class="flex-container align-center no-wrap">
                    <label for="spende_einmalig" class="indent normal-weight">Monatlich:</label>
                    <input type="radio" name="spende_monatlich" value="5"> 5€
                    <input type="radio" name="spende_monatlich" value="10"> 10€
                    <input type="radio" name="spende_monatlich" value="15"> 15€
                    <input type="radio" name="spende_monatlich" value="freibetrag"> Freier Betrag
                    <input type="number" name="spende_monatlich_freibetrag" placeholder="Betrag">
                </div>
                <div class="flex-container align-center no-wrap">
                        <label for="spende_einmalig" class="indent normal-weight">Einmalig:</label>
                        <input type="number" name="spende_einmalig" id="spende_einmalig" placeholder="Betrag">
                </div>
                <div class="flex-container no-wrap align-baseline">
                    <input class="custom-checkbox" type="checkbox" name="sepa" id="sepa">
                    <label for="sepa">Hiermit ermächtige ich den Aikido Verein Freiburg e.V., Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die vom Aikido Verein Freiburg e.V. auf mein Konto gezogenen Lastschriften einzulösen. Die untenstehenden Hinweise habe ich zur Kenntnis genommen.</label>
                </div>
                <div>
                    <p><strong>Hinweise:</strong><br>
                    Der Aikido Verein Freiburg e.V. zieht die Mitgliedsbeiträge quartalsweise jeweils in den ersten beiden Wochen zu Beginn eines neuen Quartals ein. Mir ist bekannt, dass seitens des kontoführenden Kreditinstitutes keine Verpflichtung zur Einlösung besteht, wenn mein Konto die erforderliche Deckung nicht aufweist.</p>
                    <p>Der/die Kontoinhaber/in kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.</p>
                    <p>Falls dem Aikido-Verein Freiburg e.V. im Rahmen des Lastschriftverfahrens Kosten entstehen, die der Kontoinhaber zu vertreten hat, z.B. Rücklastschriftgebühren wegen mangelnder Kontodeckung oder fehlerhafter Angaben, sind diese Kosten vom Kontoinhaber zu tragen.</p>
             </div>
            <input class="button" type="submit" name="submit_form" value="Submit">
        </form>
        <?php
        return ob_get_clean();
    }
}
