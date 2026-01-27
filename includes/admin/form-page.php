<?php
function avf_display_membership_form()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';
    $id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
    $record = $id ? $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id") : null;

    ?>
    <div class="wrap">
        <h1><?php echo $id ? 'Mitgliedschaft bearbeiten' : 'Neue Mitgliedschaft hinzufügen'; ?></h1>

        <?php if ($id) : ?>
            <button type="button" class="button button-secondary" id="go-back">Zurück zur Übersicht</button>
        <?php else : ?>
            <a href="admin.php?page=avf-membership-admin" class="button button-secondary">Zur Übersicht</a>
        <?php endif; ?>
        <form id="avf-membership-admin-form">
            <div id="admin-form-container">
                <?php wp_nonce_field('avf_membership_action', '_ajax_nonce'); ?>
                <input type="hidden" name="action" value="avf_membership_action">
                <input type="hidden" name="action_type" value="<?php echo $id ? 'update' : 'create'; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <label for="mitgliedschaft_art">Art der Mitgliedschaft</label>
                <div class="membership-status-label">
                    <select id="mitgliedschaft_art" name="mitgliedschaft_art" required>
                        <?php
                        foreach (MITGLIEDSCHAFTSARTEN as $value => $display) {
                            if ($value === 'geschwisterkind_discount') {
                                continue;
                            }
                            $selected = selected($record->mitgliedschaft_art ?? '', $value, false);
                            echo "<option value=\"{$value}\" {$selected}>{$display}</option>";
                        }
                        ?>
                    </select>
                    <span>
                    <?php
                    if (isset($record->austrittsdatum)) {
                        $austrittsdatum = strtotime($record->austrittsdatum);
                        $today = strtotime(date('Y-m-d'));

                        if ($austrittsdatum > $today) {
                            echo '<span class="dashicons dashicons-warning" style="color: red;"></span>&nbsp;Gekündigt zum ' . esc_attr(date('d.m.Y', $austrittsdatum));
                        } elseif ($austrittsdatum <= $today) 
                            echo '<span class="dashicons dashicons-dismiss" style="color: red;"></span>&nbsp;Ausgetreten zum ' . esc_attr(date('d.m.Y', $austrittsdatum));
                        }
                    ?>
                    </span>
                </div>

                <div class="form-group">
                    <div>
                        <label for="vorname">Vorname</label>
                        <input id="vorname" type="text" name="vorname" value="<?php echo esc_attr($record->vorname ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="nachname">Nachname</label>
                        <input id="nachname" type="text" name="nachname" value="<?php echo esc_attr($record->nachname ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="vorname_eltern">Vorname der Eltern</label>
                        <input id="vorname_eltern" type="text" name="vorname_eltern" value="<?php echo esc_attr($record->vorname_eltern ?? ''); ?>">
                    </div>
                    <div>
                        <label for="nachname_eltern">Nachname der Eltern</label>
                        <input id="nachname_eltern" type="text" name="nachname_eltern" value="<?php echo esc_attr($record->nachname_eltern ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">E-Mail</label>
                        <input id="email" type="email" name="email" value="<?php echo esc_attr($record->email ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="telefon">Telefon</label>
                        <input id="telefon" type="tel" name="telefon" value="<?php echo esc_attr($record->telefon ?? ''); ?>">
                    </div>
                </div>

                <label for="geburtsdatum">Geburtsdatum</label>
                <input id="geburtsdatum" type="date" name="geburtsdatum" value="<?php echo esc_attr($record->geburtsdatum ?? ''); ?>" required>

                <div class="form-group">
                    <label for="geschwisterkind">Geschwisterkind</label>
                    <input id="geschwisterkind" type="checkbox" name="geschwisterkind" value="1" <?php checked($record->geschwisterkind ?? 0, 1); ?>>
                </div>

                <div class="form-group">
                    <div>
                        <label for="strasse">Straße</label>
                        <input id="strasse" type="text" name="strasse" value="<?php echo esc_attr($record->strasse ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="hausnummer">Hausnummer</label>
                        <input id="hausnummer" type="text" name="hausnummer" value="<?php echo esc_attr($record->hausnummer ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="plz">PLZ</label>
                        <input id="plz" type="text" name="plz" maxlength="5" value="<?php echo esc_attr($record->plz ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="ort">Ort</label>
                        <input id="ort" type="text" name="ort" value="<?php echo esc_attr($record->ort ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="beitrittsdatum">Beitrittsdatum</label>
                        <input id="beitrittsdatum" type="date" name="beitrittsdatum" value="<?php echo esc_attr($record->beitrittsdatum ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="kuendigungseingang">Kündigungseingang</label>
                        <input id="kuendigungseingang" type="date" name="kuendigungseingang" value="<?php echo esc_attr($record->kuendigungseingang ?? ''); ?>">
                    </div>
                    <div>
                        <label for="austrittsdatum">Austrittsdatum</label>
                        <input id="austrittsdatum" type="date" name="austrittsdatum" value="<?php echo esc_attr($record->austrittsdatum ?? ''); ?>" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <input id="starterpaket" type="checkbox" name="starterpaket" value="1" <?php checked($record->starterpaket ?? 0, 1); ?>>
                    <label for="starterpaket">Starterpaket</label>
                </div>

                <div class="form-group">
                    <input id="spende" type="checkbox" name="spende" value="1" <?php checked($record->spende ?? 0, 1); ?>>
                    <label for="spende">Spende</label>
                </div>

                <div class="form-group">
                    <div>
                        <label for="spende_monatlich">Monatliche Spende</label>
                        <input id="spende_monatlich" type="number" name="spende_monatlich" value="<?php echo esc_attr($record->spende_monatlich ?? ''); ?>" step="0.01">
                    </div>
                    <div>
                        <label for="spende_einmalig">Einmalige Spende</label>
                        <input id="spende_einmalig" type="number" name="spende_einmalig" value="<?php echo esc_attr($record->spende_einmalig ?? ''); ?>" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <input id="satzung_datenschutz" type="checkbox" name="satzung_datenschutz" value="1" <?php checked($record->satzung_datenschutz ?? 0, 1); ?>>
                    <label for="satzung_datenschutz">Satzung und Datenschutz akzeptiert</label>
                </div>

                <div class="form-group">
                    <input id="hinweise" type="checkbox" name="hinweise" value="1" <?php checked($record->hinweise ?? 0, 1); ?>>
                    <label for="hinweise">Hinweise gelesen</label>
                </div>

                <div class="form-group">
                    <input id="thgutscheine" type="checkbox" name="thgutscheine" value="1" <?php checked($record->thgutscheine ?? 0, 1); ?>>
                    <label for="thgutscheine">Abrechnung über Teilhabegutscheine</label>
                </div>

                <div class="form-group">
                    <input id="sepa" type="checkbox" name="sepa" value="1" <?php checked($record->sepa ?? 0, 1); ?>>
                    <label for="sepa">SEPA-Lastschrift zugestimmt</label>
                </div>

                <div class="form-group">
                    <div>
                        <label for="kontoinhaber">Kontoinhaber</label>
                        <input id="kontoinhaber" type="text" name="kontoinhaber" value="<?php echo esc_attr($record->kontoinhaber ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="iban">IBAN</label>
                        <input id="iban" type="text" name="iban" maxlength="34" value="<?php echo esc_attr($record->iban ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="bic">BIC</label>
                        <input id="bic" type="text" name="bic" maxlength="11" value="<?php echo esc_attr($record->bic ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="bank">Bank</label>
                        <input id="bank" type="text" name="bank" value="<?php echo esc_attr($record->bank ?? ''); ?>" required>
                    </div>
                </div>

                <label for="beitrag">Mitgliedsbeitrag</label>
                <input id="beitrag" type="number" name="beitrag" value="<?php echo esc_attr($record->beitrag ?? ''); ?>" step="0.5" required>

                <table class="beitragsliste">
                    <tr>
                        <th>Beiträge</th>
                    </tr>
                    <?php
                    foreach (get_option('avf_beitraege') as $key => $value) {
                        $displayName = MITGLIEDSCHAFTSARTEN[$key] ?? $key;
                        echo "<tr><td>{$displayName}:</td><td>{$value} €</td></tr>";
                    }
                    ?>
                </table>

                <div class="form-group">
                    <div>
                        <label for="wiedervorlage">Wiedervorlage-Datum</label>
                        <input id="wiedervorlage" type="date" name="wiedervorlage" value="<?php echo esc_attr($record->wiedervorlage ?? ''); ?>">
                    </div>
                    <div>
                        <label for="wiedervorlage-grund">Wiedervorlage-Grund</label>
                        <textarea id="wiedervorlage-grund" name="wiedervorlage-grund" maxlength="255" rows="1"><?php echo esc_attr($record->wiedervorlage_grund ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="button button-secondary" id="delete-reminder">Wiedervorlage löschen</button>
                </div>

                <label for="notizen">Notizen</label>
                <textarea rows=5 id="notizen" name="notizen"><?php echo esc_textarea($record->notizen ?? ''); ?></textarea>

            </div>
            <button type="submit" class="button button-primary">Mitgliedschaft <?php echo $id ? 'aktualisieren' : 'hinzufügen'; ?></button>
            <?php if ($id) : ?>
                <button type="button" class="button button-secondary" id="cancel">Abbrechen</button>
                <button type="button" id="delete-single" class="button button-secondary btn-warning" data-id="<?php echo esc_attr($record->id ?? ''); ?>" data-type="membership">Mitgliedschaft löschen</button>
            <?php else : ?>
                <a href="admin.php?page=avf-membership-admin" class="button button-secondary">Abbrechen</a>
            <?php endif; ?>
        </form>
    </div>
    <?php
}

function avf_display_schnupperkurs_form()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_schnupperkurse';

    $id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
    $record = $id ? $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id") : null;

    ?>
    <div class="wrap">
        <h1><?php echo $id ? 'Schnupperkurs-Anmeldung bearbeiten' : 'Neue Schnupperkurs-Anmeldung hinzufügen'; ?></h1>

        <?php if ($id) : ?>
            <button type="button" class="button button-secondary" id="go-back">Zurück zur Übersicht</button>
        <?php else : ?>
            <a href="admin.php?page=avf-schnupperkurs-admin" class="button button-secondary">Zur Übersicht</a>
        <?php endif; ?>
        <form id="avf-schnupperkurs-admin-form">
            <div id="admin-form-container">
                <?php wp_nonce_field('avf_membership_action', '_ajax_nonce'); ?>
                <input type="hidden" name="action" value="avf_schnupperkurs_action">
                <input type="hidden" name="action_type" value="<?php echo $id ? 'update' : 'create'; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <label for="schnupperkurs_art">Art des Schnupperkurses</label>
                <select id="schnupperkurs_art" name="schnupperkurs_art" required>
                    <?php
                    $current_value = $record->schnupperkurs_art ?? '';
                    echo '<option value="" ' . selected($current_value, '', false) . '>Bitte auswählen</option>';

                    foreach (SCHNUPPERKURSARTEN as $value => $display) {
                        $selected = selected($current_value, $value, false);
                        echo "<option value=\"{$value}\" {$selected}>{$display}</option>";
                    }
                    ?>
                </select>

                <div class="form-group">
                    <div>
                        <label for="vorname">Vorname</label>
                        <input id="vorname" type="text" name="vorname" value="<?php echo esc_attr($record->vorname ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="nachname">Nachname</label>
                        <input id="nachname" type="text" name="nachname" value="<?php echo esc_attr($record->nachname ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">E-Mail</label>
                        <input id="email" type="email" name="email" value="<?php echo esc_attr($record->email ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="telefon">Telefon</label>
                        <input id="telefon" type="tel" name="telefon" value="<?php echo esc_attr($record->telefon ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="geburtsdatum">Geburtsdatum</label>
                        <input id="geburtsdatum" type="date" name="geburtsdatum" value="<?php echo esc_attr($record->geburtsdatum ?? ''); ?>" required>
                    </div>

                    <div>
                        <label for="beginn">Beginn des Schnupperkurses</label>
                        <input id="beginn" type="date" name="beginn" value="<?php echo esc_attr($record->beginn ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="wie_erfahren">Wie vom Aikido Verein Freiburg erfahren?</label>
                        <select id="wie_erfahren" name="wie_erfahren">
                            <?php
                            $current_value = $record->wie_erfahren ?? '';
                            $is_custom_value = !empty($current_value) && !array_key_exists($current_value, WIE_ERFAHREN);

                            echo '<option value="" ' . selected($current_value, '', false) . '>Bitte auswählen</option>';

                            foreach (WIE_ERFAHREN as $value => $display) {
                                $selected = selected($current_value, $value, false);
                                if ($value === 'sonstiges' && $is_custom_value) {
                                    $selected = 'selected';
                                }
                                echo "<option value=\"{$value}\" {$selected}>{$display}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="wie_erfahren_sonstiges" style="display: none;">Sonstiges</label>
                        <input id="wie_erfahren_sonstiges"
                               type="text"
                               name="wie_erfahren_sonstiges"
                               value="<?php echo esc_attr($is_custom_value ? $current_value : ''); ?>"
                               placeholder="Sonstiges">
                    </div>
                </div>

                <label for="notizen">Notizen</label>
                <textarea id="notizen" name="notizen"><?php echo esc_textarea($record->notizen ?? ''); ?></textarea>

            </div>
            <button type="submit" class="button button-primary">Schnupperkurs-Anmeldung <?php echo $id ? 'aktualisieren' : 'hinzufügen'; ?></button>
            <?php if ($id) : ?>
                <button type="button" class="button button-secondary" id="cancel">Abbrechen</button>
                <button type="button" id="delete-single" class="button button-secondary btn-warning" data-id="<?php echo esc_attr($record->id ?? ''); ?>" data-type="schnupperkurs">Schnupperkurs-Anmeldung löschen</button>
            <?php else : ?>
                <a href="admin.php?page=avf-schnupperkurs-admin" class="button button-secondary">Abbrechen</a>
            <?php endif; ?>
        </form>
    </div>
    <?php
}

function avf_manage_membership_fees()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $beitraege = get_option('avf_beitraege', []);
    ?>
    <div class="wrap">
        <h1>AVF-Mitgliedsbeiträge</h1>
        <br>
        <form id="avf-membership-fees-form">
            <div id="admin-form-container">
                <?php wp_nonce_field('avf_membership_action', '_ajax_nonce'); ?>
                <input type="hidden" name="action" value="avf_membership_action">
                <input type="hidden" name="action_type" value="update_fees">
                <?php foreach ($beitraege as $key => $value) : ?>
                <div >
                    <label for="<?php echo esc_attr($key); ?>"><?php echo MITGLIEDSCHAFTSARTEN[esc_html($key)]; ?>:</label>
                    <input class="fee-input" type="number" step="0.5" name="avf_beitraege[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" required><br>
                </div>
                <?php endforeach; ?>
                <br>
            </div>
            <input id="update-existing-fees" type="checkbox" name="update-existing-fees" value="1" checked>
            <label for="update-existing-fees"><strong>Beiträge bestehender Mitglieder aktualisieren?</strong></label>
            <div class="notice notice-info inline">
                <p>
                    Wenn dieser Haken gesetzt ist, werden die Beiträge von Mitgliedern, die den Regelbeitrag zahlen, auf die angegebenen Beiträge für die jeweilige Mitgliedschaftsart gesetzt.
                    Beiträge von Mitgliedern, die einen individuell vereinbarten Beitrag zahlen, werden nicht verändert.
                </p>
            </div>
            <input type="submit" value="Beiträge aktualisieren" class="button button-primary">
        </form>
    </div>
    <?php
}
