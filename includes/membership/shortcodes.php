<?php
class Avf_Forms_Membership_Shortcodes
{

    private static function get_config()
    {
        static $config = null;
        if ($config === null) {
            $config = include AVF_PLUGIN_DIR . 'config.php';
        }
        return $config;
    }

    private static function render_legal_agreement()
    {
        $config = self::get_config();
        $legal_links = $config['legal_links'];

        ob_start();
        ?>
        <div>
            <input class="d-inline align-top mt-1" type="checkbox" name="satzung_datenschutz" id="satzung_datenschutz" required>
            <label class="d-inline align-top" for="satzung_datenschutz">Ich habe die
        <?php
        $keys = array_keys($legal_links);
        foreach ($keys as $index => $key) {
            $link = $legal_links[$key];
            echo '<a href="' . esc_url($link['url']) . '" title="' . esc_attr($link['label']) . '" target="_blank">' .
                 '<strong>' . esc_html($link['label']) . '</strong></a>';

            if ($index < count($keys) - 2) {
                echo ', die ';
            } elseif ($index === count($keys) - 2) {
                echo ' sowie die ';
            }
        }
        ?>
                des Aikido-Verein Freiburg e.V. zur Kenntnis genommen und erkenne diese hiermit an.
            </label>
       </div>
        <?php
        return ob_get_clean();
    }

    public static function register()
    {
        add_shortcode('membership_form', array( __CLASS__, 'render_membership_form' ));
        add_shortcode('membership_children_form', array( __CLASS__, 'render_membership_children_form' ));
    }

    // Render Membership Adults form
    public static function render_membership_form()
    {
        $errors = get_transient('form_validation_errors');

        ob_start();
        ?>
        <form id="membership-form" class="avf-form" method="post" action="">

        <?php
         $errors = get_transient('form_validation_errors');

        if ($errors) {
            echo '<div class="form-error" style="display: block; padding: 0.25rem 0.75rem;">';
            foreach ($errors as $field => $error) {
                echo '<p class="error-' . esc_attr($field) . '">' . esc_html($error) . '</p>';
            }
            echo '</div>';
            delete_transient('form_validation_errors');
        }
        ?>

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
                    <div class="half-width mt-3 p-2">
                        <span id="age-error" class="form-error"></span>
                    </div>
                </div>
            </div>

            <h2>Adresse</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="strasse">Straße</label>
                        <input type="text" name="strasse" id="strasse" placeholder="Straße" required>
                    </div>
                    <div class="half-width">
                        <label for="hausnummer">Hausnummer</label>
                        <input type="text" name="hausnummer" id="hausnummer" placeholder="Hausnummer" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="plz">PLZ</label>
                        <input type="text" name="plz" id="plz" placeholder="PLZ" maxlength="5" size="5" required>
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
                        <select name="mitgliedschaft_art" id="mitgliedschaft_art" required>
                            <option value="aktiv">Aktives Mitglied</option>
                            <option value="aktiv_ermaessigt">Aktives Mitglied (ermäßigt)</option>
                            <option value="familie">Familienmitglied (ab der 3. Person)</option>
                            <option value="foerder">Fördermitglied</option>
                            <option value="sonder">Sondermitglied (Mitglieder anderer DANBW-Dojos)</option>
                        </select>
                    </div>
                    <div class="half-width">
                        <label for="beitrittsdatum">Beitrittsdatum</label>
                        <input type="date" name="beitrittsdatum" id="beitrittsdatum" required>
                    </div>
                    <p><small>*Anspruch auf eine Beitragsermäßigung bei aktiver Mitgliedschaft haben Schüler*innen ab 18 Jahren, Studierende, Auszubildende, Menschen mit Schwerbehinderung, FSJler*innen, Wehrpflichtige und Arbeitslose.</small></p>
                </div>
                <div>
                    <h5>Hinweise</h5>
                    <p><strong>Kündigung:</strong> Eine Kündigung der Mitgliedschaft hat bis spätestens 6 Wochen vor Quartalsende zu erfolgen.</p>
                    <p><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <?php echo self::render_legal_agreement(); ?>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="hinweise" id="hinweise" required>
                    <label class="d-inline align-top" for="hinweise">Die Hinweise zum <strong>Haftungsausschluss</strong> und zur <strong>Kündigung</strong> habe ich zur Kenntnis genommen.</label>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="mailinglist" id="mailinglist">
                    <label class="d-inline align-top" for="mailinglist">Ich möchte mich in die <strong>Mailingliste des Aikido Verein Freiburg</strong> eintragen, um wichtige Informationen zum Training, zu Lehrgängen und zum Vereinsleben zu erhalten.</label>
                </div>
                <p class="my-0"><small>Nach dem Absenden des Formulars erhältst Du eine E-Mail, in der du deine Mailinglisten-Anmeldung bestätigen musst. Du kannst dich jederzeit wieder abmelden.</small></p>
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
                        <input type="text" name="iban" id="iban" placeholder="IBAN" maxlength="34" size="34" required>
                    </div>
                    <div class="half-width">
                        <label for="bic">BIC</label>
                        <input type="text" name="bic" id="bic" placeholder="BIC" maxlength="11" size="11" required>
                    </div>
                    <div class="half-width">
                        <label for="bank">Bank</label>
                        <input type="text" name="bank" id="bank" placeholder="Bank" required>
                    </div>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="sepa" id="sepa" required>
                    <label class="d-inline align-top" for="sepa">Hiermit ermächtige ich den Aikido Verein Freiburg e.V., Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die vom Aikido Verein Freiburg e.V. auf mein Konto gezogenen Lastschriften einzulösen. Die untenstehenden Hinweise habe ich zur Kenntnis genommen.</label>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="starterpaket" id="starterpaket">
                    <label class="d-inline align-top" for="starterpaket">Ich möchte das <strong>vergünstigte Starter-Angebot</strong> in Anspruch nehmen (Aikido-Anzug und Vereins-T-Shirt für 35 €). Ich bin damit einverstanden, dass der Betrag einmalig mit dem ersten Mitgliedsbeitrag per Lastschrift eingezogen wird.</label>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="spende" id="spende">
                    <label class="d-inline align-top" for="spende">Ich möchte den Aikido Verein Freiburg gerne zusätzlich mit einer freiwilligen Spende unterstützen (gegen Erhalt einer Spendenquittung):</label>
                </div>
                <div id="spende-details">
                    <div class="flex-container align-center no-wrap indent">
                        <label for="intervall-mtl" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="intervall" id="intervall-mtl" value="monatlich" disabled required> Monatlich
                        </label>
                        <label for="intervall-einmal" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="intervall" id="intervall-einmal" value="einmalig" disabled> Einmalig
                        </label>
                    </div>

                    <div class="flex-container flex-row indent">
                        <label for="spende-5" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="spende" value="5" id="spende-5" disabled required> 5 €
                        </label>
                        <label for="spende-10" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="spende" value="10" id="spende-10" disabled> 10 €
                        </label>
                        <label for="spende-15" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="spende" value="15" id="spende-15" disabled> 15 €
                        </label>
                        <label for="spende-freibetrag" class="disabled font-weight-normal">
                            <input class="disabled" type="radio" name="spende" value="freibetrag" id="spende-freibetrag" disabled> Freibetrag
                        </label>
                        <input id="freibetrag-input" type="number" name="freibetrag-input" placeholder="Betrag" disabled>
                    </div>
                </div>
                <div>
                    <h5>Hinweise:</h5>
                    <p>Der Aikido Verein Freiburg e.V. zieht die Mitgliedsbeiträge quartalsweise jeweils in den ersten beiden Wochen zu Beginn eines neuen Quartals ein. Mir ist bekannt, dass seitens des kontoführenden Kreditinstitutes keine Verpflichtung zur Einlösung besteht, wenn mein Konto die erforderliche Deckung nicht aufweist.</p>
                    <p>Der/die Kontoinhaber/in kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.</p>
                    <p>Falls dem Aikido-Verein Freiburg e.V. im Rahmen des Lastschriftverfahrens Kosten entstehen, die der Kontoinhaber zu vertreten hat, z.B. Rücklastschriftgebühren wegen mangelnder Kontodeckung oder fehlerhafter Angaben, sind diese Kosten vom Kontoinhaber zu tragen.</p>
                </div>
            </div>

            <?php wp_nonce_field('membership_form_submit', 'membership_nonce'); ?>

            <input id="submit-btn" class="button" type="submit" name="membership_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }

    // Render Membership Children form
    public static function render_membership_children_form()
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
                        <input type="date" name="geburtsdatum" id="geburtsdatum" required>
                    </div>
                    <div class="half-width mt-3 p-2">
                        <span id="age-error" class="form-error"></span>
                    </div>
                </div>
                <div>
                    <label>Geschwisterkind<label>
                    <input class="d-inline align-top" type="checkbox" name="geschwisterkind" id="geschwisterkind">
                    <label class="d-inline align-top" for="geschwisterkind" class="font-weight-normal">Mein Kind hat ein Geschwisterkind, das bereits Mitglied im Aikido-Verein Freiburg e.V. ist.</label>
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
                        <label for="strasse">Straße</label>
                        <input type="text" name="strasse" id="strasse" placeholder="Straße" required>
                    </div>
                    <div class="half-width">
                        <label for="hausnummer">Hausnummer</label>
                        <input type="text" name="hausnummer" id="hausnummer" placeholder="Hausnummer" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="plz">PLZ</label>
                        <input type="text" name="plz" id="plz" placeholder="PLZ" maxlength="5" size="5" required>
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
                        <input type="date" name="beitrittsdatum" id="beitrittsdatum" required>
                    </div>
                </div>
                <div>
                    <h5>Hinweise</h5>
                    <p><strong>Kündigung:</strong> Eine Kündigung der Mitgliedschaft hat bis spätestens 6 Wochen vor Quartalsende zu erfolgen.</p>
                    <p><strong>Haftungsausschluss:</strong> Der Aikido-Verein Freiburg e.V. weist ausdrücklich daraufhin, dass der Vereinsbeitritt keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</p>
                </div>
                <?php echo self::render_legal_agreement(); ?>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="hinweise" id="hinweise" required>
                    <label class="d-inline align-top" for="hinweise">Die Hinweise zum Haftungsausschluss und zur Kündigung habe ich zur Kenntnis genommen.</label>
                </div>
            </div>

            <h2>Zahlungsdetails</h2>
            <div class="container">
                <div class="flex-container">
                    <div>
                      <input class="d-inline align-top mt-4" type="checkbox" name="thgutscheine" id="thgutscheine">
                      <label class="d-inline align-top" for="thgutscheine">Wir erhalten Teilhabegutscheine von der Stadt Freiburg und möchten die Mitgliedsbeiträge darüber abrechnen.</label>
                     </div>
                    <div class="half-width">
                        <label for="kontoinhaber">Kontoinhaber</label>
                        <input type="text" name="kontoinhaber" id="kontoinhaber" placeholder="Kontoinhaber" required>
                    </div>
                    <div class="half-width">
                        <label for="iban">IBAN</label>
                        <input type="text" name="iban" id="iban" placeholder="IBAN" maxlength="34" size="34" required>
                    </div>
                    <div class="half-width">
                        <label for="bic">BIC</label>
                        <input type="text" name="bic" id="bic" placeholder="BIC" maxlength="11" size="11" required>
                    </div>
                    <div class="half-width">
                        <label for="bank">Bank</label>
                        <input type="text" name="bank" id="bank" placeholder="Bank" required>
                    </div>
                </div>
                <div>
                    <input class="d-inline align-top mt-4" type="checkbox" name="sepa" id="sepa" required>
                    <label class="d-inline align-top" for="sepa">Hiermit ermächtige ich den Aikido Verein Freiburg e.V., Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die vom Aikido Verein Freiburg e.V. auf mein Konto gezogenen Lastschriften einzulösen. Die untenstehenden Hinweise habe ich zur Kenntnis genommen.</label>
                </div>
                <div>
                    <h5>Hinweise:</h5>
                    <p>Der Aikido Verein Freiburg e.V. zieht die Mitgliedsbeiträge quartalsweise jeweils in den ersten beiden Wochen zu Beginn eines neuen Quartals ein. Mir ist bekannt, dass seitens des kontoführenden Kreditinstitutes keine Verpflichtung zur Einlösung besteht, wenn mein Konto die erforderliche Deckung nicht aufweist.</p>
                    <p>Der/die Kontoinhaber/in kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.</p>
                    <p>Falls dem Aikido-Verein Freiburg e.V. im Rahmen des Lastschriftverfahrens Kosten entstehen, die der Kontoinhaber zu vertreten hat, z.B. Rücklastschriftgebühren wegen mangelnder Kontodeckung oder fehlerhafter Angaben, sind diese Kosten vom Kontoinhaber zu tragen.</p>
                    <p>Teilhabegutscheine sind dem Verein unaufgefordert in ausreichender Höhe vorzulegen. Der Verein ist unverzüglich zu informieren, wenn die Mitgliedsbeiträge nicht mehr über Teilhabegutscheine abgerechnet werden können/sollen.</p>
                    <p>Für alle Fragen zur Mitgliedschaft sowie zu Mitgliedsbeiträgen ist die Schatzmeisterin/der Schatzmeister zuständig: <?php
                        $treasurer_emails = Avf_Forms_Utils::get_emails_by_key('treasurer_email');
                        $treasurer_email = $treasurer_emails[0] ?? 'schatzmeister@aikido-freiburg.de';
                        echo '<a href="mailto:' . esc_attr($treasurer_email) . '">' . esc_html($treasurer_email) . '</a>';
                    ?></p>
                </div>
            </div>

            <?php wp_nonce_field("membership_form_submit", 'membership_nonce'); ?>

            <input id="submit-btn" class="button" type="submit" name="membership_form_submit" value="Antrag abschicken">
        </form>
        <?php
        return ob_get_clean();
    }
}
