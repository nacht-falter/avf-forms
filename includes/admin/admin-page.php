<?php
function Avf_Display_memberships()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'avf_memberships';

    $orderby = !empty($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'id';
    $order = !empty($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';
    $sql = "SELECT * FROM $table_name ORDER BY $orderby $order";
    $results = $wpdb->get_results($sql, ARRAY_A);

    ?>
    <div class="wrap">
        <h1>AVF-Mitgliedschaften</h1>
        <a href="admin.php?page=avf-membership-form-page" class="button button-primary">Neue Mitgliedschaft hinzufügen</a>
        <h2>Mitgliedschaften</h2>
        <?php if (empty($results)) : ?>
            <p>Keine Mitgliedschaften gefunden.</p>
        <?php else : ?>
            <form id="membership-form" method="post" action="">
                <div class="list-container">
                    <table class="wp-list-table widefat striped">
                        <thead>
                            <tr>
                                <th scope="col" class="check-column">
                                    <input type="checkbox" id="select-all" />
                                </th>
                                <?php
                                $column_headers = [
                                    'id'                 => 'ID',
                                    'mitgliedschaft_art' => 'Art der Mitgliedschaft',
                                    'vorname'            => 'Vorname',
                                    'nachname'           => 'Nachname',
                                    'email'              => 'E-Mail',
                                    'geburtsdatum'       => 'Geburtsdatum',
                                    'beitrittsdatum'     => 'Beitrittsdatum',
                                    'austrittsdatum'     => 'Austrittsdatum',
                                    'starterpaket'       => 'Starterpaket',
                                    'spende'             => 'Spende',
                                    'spende_monatlich'   => 'Spende monatlich',
                                    'spende_einmalig'    => 'Spende einmalig',
                                    'sepa'               => 'SEPA-Mandat',
                                    'kontoinhaber'       => 'Kontoinhaber',
                                    'iban'               => 'IBAN',
                                    'bic'                => 'BIC',
                                    'bank'               => 'Bank',
                                    'beitrag'            => 'Beitrag',
                                    'notizen'            => 'Notizen',
                                    'submission_date'    => 'Eingangsdatum'
                                ];

                                foreach ($column_headers as $column_key => $column_label) {
                                    echo '<th scope="col">';
                                    echo '<a href="?page=avf-membership-admin&orderby=' . esc_attr($column_key) . '&order=' . esc_attr(strtolower($order) == 'asc' ? 'desc' : 'asc') . '">';
                                    echo $column_label . ' ';
                                    echo ($orderby == $column_key) ? (strtolower($order) == 'asc' ? '&#9650;' : '&#9660;') : '';
                                    echo '</a>';
                                    echo '</th>';
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row) :
                                $checkAge = false;
                                $markInactive = false;
                                $markCustomBeitrag = false;
                                $age = date_diff(date_create($row['geburtsdatum']), date_create('now'))->y;
                                if (($age < 14 && $row['mitgliedschaft_art'] != 'kind')) {
                                    $checkAge = true;
                                } elseif ($age >= 14 && $age < 18 && $row['mitgliedschaft_art'] != 'jugend') {
                                    $checkAge = true;
                                } elseif ($age >= 18 && ($row['mitgliedschaft_art'] == 'kind' || $row['mitgliedschaft_art'] == 'jugend')) {
                                    $checkAge = true;
                                }

                                if (!empty($row['austrittsdatum'])) {
                                    $markInactive = true;
                                }
                                if (BEITRAEGE[$row['mitgliedschaft_art']] != $row['beitrag']) {
                                    $markCustomBeitrag = true;
                                }
                                ?>

                                <tr class="table-row-link <?php if ($checkAge) echo 'highlight-red'; if ($markInactive) echo 'highlight-yellow'; ?>"
                                    <?php if ($checkAge) echo 'title="Mitgliedschaftsart prüfen"'; if ($markInactive) echo 'title="Ausgetreten"'; ?>

                                    onclick="handleRowClick(event, <?php echo esc_attr($row['id']); ?>)">

                                    <th scope="row" class="check-column" style="cursor: initial;">
                                        <input type="checkbox" class="membership-checkbox" value="<?php echo esc_attr($row['id']); ?>">
                                    </th>
                                    <td><?php echo esc_html($row['id']); ?></td>
                                    <td>
                                    <?php
                                    echo esc_html(MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']] ?? 'Unbekannt');
                                    if ($checkAge) {
                                        echo '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Alter stimmt nicht mit Mitgliedschaftsart überein."></span>';
                                    }
                                    if ($markInactive) {
                                        echo '&nbsp;<span class="dashicons dashicons-warning" style="color: orange;" title="Austritt zum ' . date('d.m.Y', strtotime($row['austrittsdatum'])) . '"></span>';
                                    }
                                    ?>
                                    </td>
                                    <td><?php echo esc_html($row['vorname']); ?></td>
                                    <td><?php echo esc_html($row['nachname']); ?></td>
                                    <td><?php echo esc_html($row['email']); ?></td>
                                    <td><?php echo esc_html(Avf_Forms_Utils::format_date($row['geburtsdatum'])); ?></td>
                                    <td><?php echo esc_html(Avf_Forms_Utils::format_date($row['beitrittsdatum'])); ?></td>
                                    <td>
                                        <?php echo isset($row['austrittsdatum']) ? esc_html(date('d.m.Y', strtotime($row['austrittsdatum']))) : ''; ?>
                                    </td>
                                    <td><?php echo esc_html(Avf_Forms_Utils::format_bool($row['starterpaket'])); ?></td>
                                    <td><?php echo esc_html(Avf_Forms_Utils::format_bool($row['spende'])); ?></td>
                                    <td><?php echo esc_html($row['spende_monatlich'] ?? ''); ?></td>
                                    <td><?php echo esc_html($row['spende_einmalig'] ?? ''); ?></td>
                                    <td><?php echo $row['sepa'] ? 'Erteilt' : 'Nicht erteilt'; ?></td>
                                    <td><?php echo esc_html($row['kontoinhaber']); ?></td>
                                    <td><?php echo esc_html($row['iban']); ?></td>
                                    <td><?php echo esc_html($row['bic']); ?></td>
                                    <td><?php echo esc_html($row['bank']); ?></td>
                                    <td class="<?php if ($markCustomBeitrag) echo 'highlight-orange'; ?>" title="<?php if ($markCustomBeitrag) echo 'Beitrag angepasst'; ?>">
                                        <?php echo isset($row['beitrag']) ? esc_html($row['beitrag']) . ' €' : ''; ?>
                                    </td>
                                    <td class="notizen-col"><?php echo esc_html($row['notizen']); ?></td>
                                    <td><?php echo esc_html(date('d.m.Y', strtotime($row['submission_date']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="bulk-actions">
                    <button type="button" id="export-csv" class="button button-primary" disabled>Ausgewählte Mitgliedschaften als CSV exportieren</button>
                    <button type="button" id="delete-membership" class="button button-secondary" disabled>Ausgewählte Mitgliedschaften löschen</button>
                </div>
            </form>

            <table class="beitragsliste">
                <tr>
                    <th>Beiträge</th>
                </tr>
                <?php
                foreach (BEITRAEGE as $key => $value) {
                    $displayName = MITGLIEDSCHAFTSARTEN[$key] ?? $key;
                    echo "<tr><td>{$displayName}:</td><td>{$value} €</td></tr>";
                }
                ?>
            </table>

            <script>
            function handleRowClick(event, id) {
                if (event.target.tagName != 'INPUT' && event.target.tagName != 'TH') {
                    window.location.href = 'admin.php?page=avf-membership-form-page&edit=' + id;
                }
            }
            </script>
        <?php endif; ?>
    </div>
    <?php
}
