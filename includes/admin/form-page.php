<?php
function Avf_Display_Membership_form()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';
    $id = isset($_GET['edit']) ? intval($_GET['edit']) : null;
    $record = $id ? $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id") : null;

    ?>
    <div class="wrap">
        <h1><?php echo $id ? 'Mitgliedschaft bearbeiten' : 'Neue Mitgliedschaft hinzufügen'; ?></h1>
        
        <form id="avf-membership-form">
            <div class="flex-container">
                <?php wp_nonce_field('avf_membership_action', '_ajax_nonce'); ?>
                <input type="hidden" name="action" value="avf_membership_action">
                <input type="hidden" name="action_type" value="<?php echo $id ? 'update' : 'create'; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <label for="mitgliedschaft_art">Art der Mitgliedschaft:</label>
                <select name="mitgliedschaft_art" required>
                    <?php
                    foreach (MITGLIEDSCHAFTSARTEN as $value => $display) {
                        $selected = selected($record->mitgliedschaft_art ?? '', $value, false);
                        echo "<option value=\"{$value}\" {$selected}>{$display}</option>";
                    }
                    ?>
                </select>

                <label for="vorname">Vorname:</label>
                <input type="text" name="vorname" value="<?php echo esc_attr($record->vorname ?? ''); ?>" required>

                <label for="nachname">Nachname:</label>
                <input type="text" name="nachname" value="<?php echo esc_attr($record->nachname ?? ''); ?>" required>

                <label for="vorname_eltern">Vorname der Eltern:</label>
                <input type="text" name="vorname_eltern" value="<?php echo esc_attr($record->vorname_eltern ?? ''); ?>">

                <label for="nachname_eltern">Nachname der Eltern:</label>
                <input type="text" name="nachname_eltern" value="<?php echo esc_attr($record->nachname_eltern ?? ''); ?>">

                <div class="form-group">
                    <label for="geschwisterkind">Geschwisterkind</label>
                    <input type="checkbox" name="geschwisterkind" value="1" <?php checked($record->geschwisterkind ?? 0, 1); ?>>
                </div>

                <label for="email">E-Mail:</label>
                <input type="email" name="email" value="<?php echo esc_attr($record->email ?? ''); ?>" required>

                <label for="telefon">Telefon:</label>
                <input type="tel" name="telefon" value="<?php echo esc_attr($record->telefon ?? ''); ?>">

                <label for="geburtsdatum">Geburtsdatum:</label>
                <input type="date" name="geburtsdatum" value="<?php echo esc_attr($record->geburtsdatum ?? ''); ?>" required>

                <label for="strasse">Straße:</label>
                <input type="text" name="strasse" value="<?php echo esc_attr($record->strasse ?? ''); ?>" required>

                <label for="hausnummer">Hausnummer:</label>
                <input type="text" name="hausnummer" value="<?php echo esc_attr($record->hausnummer ?? ''); ?>" required>

                <label for="plz">PLZ:</label>
                <input type="text" name="plz" value="<?php echo esc_attr($record->plz ?? ''); ?>" required>

                <label for="ort">Ort:</label>
                <input type="text" name="ort" value="<?php echo esc_attr($record->ort ?? ''); ?>" required>

                <label for="beitrittsdatum">Beitrittsdatum:</label>
                <input type="date" name="beitrittsdatum" value="<?php echo esc_attr($record->beitrittsdatum ?? ''); ?>" required>

                <div class="form-group">
                    <input type="checkbox" name="starterpaket" value="1" <?php checked($record->starterpaket ?? 0, 1); ?>>
                    <label for="starterpaket">Starterpaket</label>
                </div>

                <div class="form-group">
                    <input type="checkbox" name="spende" value="1" <?php checked($record->spende ?? 0, 1); ?>>
                    <label for="spende">Spende</label>
                </div>

                <label for="spende_monatlich">Monatliche Spende:</label>
                <input type="number" name="spende_monatlich" value="<?php echo esc_attr($record->spende_monatlich ?? ''); ?>" step="0.01">

                <label for="spende_einmalig">Einmalige Spende:</label>
                <input type="number" name="spende_einmalig" value="<?php echo esc_attr($record->spende_einmalig ?? ''); ?>" step="0.01">

                <div class="form-group">
                    <input type="checkbox" name="satzung_datenschutz" value="1" <?php checked($record->satzung_datenschutz ?? 0, 1); ?> required>
                    <label for="satzung_datenschutz">Satzung und Datenschutz akzeptiert</label>
                </div>

                <div class="form-group">
                    <input type="checkbox" name="hinweise" value="1" <?php checked($record->hinweise ?? 0, 1); ?>>
                    <label for="hinweise">Hinweise gelesen</label>
                </div>

                <div class="form-group">
                    <input type="checkbox" name="sepa" value="1" <?php checked($record->sepa ?? 0, 1); ?> required>
                    <label for="sepa">SEPA-Lastschrift zugestimmt</label>
                </div>

                <label for="kontoinhaber">Kontoinhaber:</label>
                <input type="text" name="kontoinhaber" value="<?php echo esc_attr($record->kontoinhaber ?? ''); ?>" required>

                <label for="iban">IBAN:</label>
                <input type="text" name="iban" value="<?php echo esc_attr($record->iban ?? ''); ?>" required>

                <label for="notizen">Notizen:</label>
                <textarea name="notizen"><?php echo esc_textarea($record->notizen ?? ''); ?></textarea>
            </div>
            <button type="submit" class="button button-primary">Mitgliedschaft <?php echo $id ? 'aktualisieren' : 'hinzufügen'; ?></button>
            <button type="button" class="button button-secondary" id="go-back">Zurück zur Übersicht</button>
        </form>
    </div>
    <?php
}
