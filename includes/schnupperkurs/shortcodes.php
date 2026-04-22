<?php

class Avf_Forms_Schnupperkurs_Shortcodes
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
        $datenschutz_link = $legal_links['datenschutz'] ?? null;

        ob_start();
        ?>
        <div>
            <input class="d-inline align-top mt-1" type="checkbox" name="datenschutz" id="datenschutz" required>
            <label class="d-inline align-top" for="datenschutz">
                Ich habe die
                <?php if ($datenschutz_link): ?>
                    <a href="<?php echo esc_url($datenschutz_link['url']); ?>" title="<?php echo esc_attr($datenschutz_link['label']); ?>" target="_blank">
                        <strong><?php echo esc_html($datenschutz_link['label']); ?></strong>
                    </a>
                <?php endif; ?>
                des Aikido-Verein Freiburg e.V. zur Kenntnis genommen und akzeptiere diese.
            </label>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function register()
    {
        add_shortcode('schnupperkurs_erwachsene', array( __CLASS__, 'render_schnupperkurs_erwachsene_form' ));
        add_shortcode('schnupperkurs_kind', array( __CLASS__, 'render_schnupperkurs_kind_form' ));
    }

    public static function render_schnupperkurs_erwachsene_form()
    {
        $errors = get_transient('schnupperkurs_form_errors');
        $success = get_transient('schnupperkurs_form_success');

        ob_start();
        ?>
        <form id="schnupperkurs-erwachsene-form" class="avf-form" method="post" action="">

        <?php
        if ($success) {
            echo '<div class="form-success" style="padding: 0.5rem; background: #d4edda; border: 1px solid #c3e6cb; margin-bottom: 1rem;">';
            echo '<p>' . esc_html($success) . '</p>';
            echo '</div>';
            delete_transient('schnupperkurs_form_success');
        }

        if ($errors) {
            echo '<div class="form-error" style="display: block; padding: 0.25rem 0.75rem;">';
            foreach ($errors as $error) {
                echo '<p>' . esc_html($error) . '</p>';
            }
            echo '</div>';
            delete_transient('schnupperkurs_form_errors');
        }
        ?>

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
                        <label for="geburtsdatum">Geburtsdatum</label>
                        <input type="date" name="geburtsdatum" id="geburtsdatum" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="beginn">Anmeldung ab:</label>
                        <input type="date" name="beginn" id="beginn" required>
                        <small>Der Schnupperkurs beginnt üblicherweise nach dem zweiten kostenlosen Probetraining.</small>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="wie_erfahren">Wie hast Du vom Aikido Verein Freiburg erfahren?</label>
                        <select id="wie_erfahren" name="wie_erfahren" required>
                            <option value="">Bitte auswählen</option>
                            <?php
                            foreach (WIE_ERFAHREN as $value => $display) {
                                echo "<option value=\"{$value}\">{$display}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="flex-container" id="wie-erfahren-sonstiges-container" style="display: none;">
                    <div class="half-width">
                        <label for="wie_erfahren_sonstiges">Sonstiges</label>
                        <input type="text" name="wie_erfahren_sonstiges" id="wie_erfahren_sonstiges" placeholder="Bitte angeben">
                    </div>
                </div>
            </div>

            <div class="container">
              <?php echo self::render_legal_agreement(); ?>
            </div>

            <div class="container">
                <p><i><strong>Haftungsausschluss</strong>: Der Aikido-Verein Freiburg e.V. weist ausdrücklich darauf hin, dass die Teilnahmegebühr keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</i></p>
                <div>
                    <input class="d-inline align-top mt-1" type="checkbox" name="haftungsausschluss" id="haftungsausschluss" required>
                    <label class="d-inline align-top" for="haftungsausschluss">Den <strong>Haftungsausschluss</strong> habe ich zur Kenntnis genommen.</label>
                </div>
            </div>

            <div class="container">
                <p><strong>Die Teilnahmegebühr für den Schnupperkurs beträgt <?php echo SCHNUPPERKURSPREISE['erwachsene'] ?? 30; ?> €.</strong></p>
            </div>

            <div class="container">
                <p>Bitte überweise den Betrag innerhalb von zwei Wochen auf folgendes Konto:</p>
                <table class="form-table bank-details-table">
                  <?php
                  $bank_details = Avf_Forms_Utils::get_bank_details();
                  if ($bank_details && is_array($bank_details)) {
                      foreach ($bank_details as $row) {
                          echo '<tr><td>' . esc_html($row[0]) . '</td><td><strong>' . esc_html($row[1]) . '</strong></td></tr>';
                      }
                  }
                  ?>
                </table>
            </div>

            <?php wp_nonce_field('schnupperkurs_form_submit', 'schnupperkurs_nonce'); ?>

            <input type="hidden" name="schnupperkurs_art" value="erwachsene">
            <input id="submit-btn" class="button" type="submit" name="schnupperkurs_erwachsene_submit" value="Anmeldung abschicken">
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wieErfahrenSelect = document.getElementById('wie_erfahren');
            const sonstigesContainer = document.getElementById('wie-erfahren-sonstiges-container');
            
            if (wieErfahrenSelect && sonstigesContainer) {
                wieErfahrenSelect.addEventListener('change', function() {
                    if (this.value === 'sonstiges') {
                        sonstigesContainer.style.display = 'flex';
                        document.getElementById('wie_erfahren_sonstiges').required = true;
                    } else {
                        sonstigesContainer.style.display = 'none';
                        document.getElementById('wie_erfahren_sonstiges').required = false;
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function render_schnupperkurs_kind_form()
    {
        $errors = get_transient('schnupperkurs_kind_form_errors');
        $success = get_transient('schnupperkurs_kind_form_success');

        ob_start();
        ?>
        <form id="schnupperkurs-kind-form" class="avf-form" method="post" action="">

        <?php
        if ($success) {
            echo '<div class="form-success" style="padding: 0.5rem; background: #d4edda; border: 1px solid #c3e6cb; margin-bottom: 1rem;">';
            echo '<p>' . esc_html($success) . '</p>';
            echo '</div>';
            delete_transient('schnupperkurs_kind_form_success');
        }

        if ($errors) {
            echo '<div class="form-error" style="display: block; padding: 0.25rem 0.75rem;">';
            foreach ($errors as $error) {
                echo '<p>' . esc_html($error) . '</p>';
            }
            echo '</div>';
            delete_transient('schnupperkurs_kind_form_errors');
        }
        ?>

            <h2>Angaben zum Kind</h2>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="vorname">Vorname</label>
                        <input type="text" name="vorname" id="vorname" placeholder="Vorname des Kindes" required>
                    </div>
                    <div class="half-width">
                        <label for="nachname">Nachname</label>
                        <input type="text" name="nachname" id="nachname" placeholder="Nachname des Kindes" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="geburtsdatum">Geburtsdatum</label>
                        <input type="date" name="geburtsdatum" id="geburtsdatum" required>
                    </div>
                </div>
                <div class="flex-container">
                    <div class="half-width">
                        <label for="beginn">Anmeldung ab:</label>
                        <input type="date" name="beginn" id="beginn" required>
                        <small>Der Schnupperkurs beginnt üblicherweise nach dem zweiten kostenlosen Probetraining.</small>
                  </div>
                </div>
            </div>
            <div class="container">
                <div class="flex-container">
                    <div class="half-width">
                        <label for="wie_erfahren">Wie hast Du vom Aikido Verein Freiburg erfahren?</label>
                        <select id="wie_erfahren" name="wie_erfahren" required>
                            <option value="">Bitte auswählen</option>
                            <?php
                            foreach (WIE_ERFAHREN as $value => $display) {
                                echo "<option value=\"{$value}\">{$display}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="flex-container" id="wie-erfahren-sonstiges-container" style="display: none;">
                    <div class="half-width">
                        <label for="wie_erfahren_sonstiges">Sonstiges</label>
                        <input type="text" name="wie_erfahren_sonstiges" id="wie_erfahren_sonstiges" placeholder="Bitte angeben">
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


            <div class="container">
              <?php echo self::render_legal_agreement(); ?>
            </div>

            <div class="container">
                <p><i><strong>Haftungsausschluss</strong>: Der Aikido-Verein Freiburg e.V. weist ausdrücklich darauf hin, dass die Teilnahmegebühr keine Versicherung einschließt. Jedes Mitglied ist für ausreichenden Versicherungsschutz selbst verantwortlich. Eine Haftung durch den Verein ist, außer bei Vorsatz und grober Fahrlässigkeit, ausgeschlossen.</i></p>
                <div>
                    <input class="d-inline align-top mt-1" type="checkbox" name="haftungsausschluss" id="haftungsausschluss" required>
                    <label class="d-inline align-top" for="haftungsausschluss">Den <strong>Haftungsausschluss</strong> habe ich zur Kenntnis genommen.</label>
                </div>
            </div>

            <div class="container">
                <p><strong>Die Teilnahmegebühr für den Schnupperkurs beträgt <?php echo SCHNUPPERKURSPREISE['kind'] ?? 20; ?> €.</strong></p>
                <p><strong>Hinweis:</strong> Der Schnupperkurs verlängert sich um die Dauer der Schulferien, wenn diese in den Zeitraum des Kurses fallen, da in den Ferien kein Kindertraining stattfindet.</p>
            </div>

            <div class="container">
                <p>Bitte überweise den Betrag innerhalb von zwei Wochen auf folgendes Konto:</p>
                <table class="form-table bank-details-table">
                <?php
                $bank_details = Avf_Forms_Utils::get_bank_details();
                if ($bank_details && is_array($bank_details)) {
                    foreach ($bank_details as $row) {
                        echo '<tr><td>' . esc_html($row[0]) . '</td><td><strong>' . esc_html($row[1]) . '</strong></td></tr>';
                    }
                }
                ?>
                </table>
            </div>

            <?php wp_nonce_field('schnupperkurs_form_submit', 'schnupperkurs_nonce'); ?>

            <input type="hidden" name="schnupperkurs_art" value="kind">
            <input id="submit-btn" class="button" type="submit" name="schnupperkurs_kind_submit" value="Anmeldung abschicken">
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wieErfahrenSelect = document.getElementById('wie_erfahren');
            const sonstigesContainer = document.getElementById('wie-erfahren-sonstiges-container');
            
            if (wieErfahrenSelect && sonstigesContainer) {
                wieErfahrenSelect.addEventListener('change', function() {
                    if (this.value === 'sonstiges') {
                        sonstigesContainer.style.display = 'flex';
                        document.getElementById('wie_erfahren_sonstiges').required = true;
                    } else {
                        sonstigesContainer.style.display = 'none';
                        document.getElementById('wie_erfahren_sonstiges').required = false;
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
