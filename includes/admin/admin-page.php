<?php
function Avf_Display_memberships()
{
    if (!current_user_can('manage_options')) {
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
                                    'beitrittsdatum'     => 'Beitrittsdatum',
                                    'austrittsdatum'     => 'Austrittsdatum',
                                    'starterpaket'       => 'Starterpaket',
                                    'spende'             => 'Spende',
                                    'spende_monatlich'   => 'Spende monatlich',
                                    'spende_einmalig'    => 'Spende einmalig',
                                    'satzung_datenschutz' => 'Satzung und Datenschutz',
                                    'hinweise'           => 'Hinweise',
                                    'sepa'               => 'SEPA-Mandat',
                                    'kontoinhaber'       => 'Kontoinhaber',
                                    'iban'               => 'IBAN',
                                    'beitrag'            => 'Beitrag',
                                    'submission_date'    => 'Eingangsdatum',
                                    'notizen'            => 'Notizen'
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
                                $age = date_diff(date_create($row['geburtsdatum']), date_create('now'))->y;
                                echo '<script>console.log(' . json_encode($age) . ');</script>';
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
                                ?>

                                <tr class="table-row-link <?php if ($checkAge) echo 'highlight-row-red'; if ($markInactive) echo 'highlight-row-yellow'; ?>"
                                    <?php if ($checkAge) echo 'title="Mitgliedschaftsart prüfen"'; if ($markInactive) echo 'title="Ausgetreten"'; ?>

                                    onclick="handleRowClick(event, <?php echo esc_attr($row['id']); ?>)">

                                    <th scope="row" class="check-column" style="cursor: initial;">
                                        <input type="checkbox" class="membership-checkbox" value="<?php echo esc_attr($row['id']); ?>">
                                    </th>
                                    <td><?php echo esc_html($row['id']); ?></td>
                                    <td>
                                    <?php
                                    if ($markInactive) {
                                        echo "Ausgetreten ";
                                    } else {
                                        echo esc_html(MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']] ?? 'Unbekannt');
                                        if ($checkAge) {
                                            echo '&nbsp;<span class="dashicons dashicons-warning" style="color: red;" title="Alter stimmt nicht mit Mitgliedschaftsart überein."></span>';
                                        }
                                    }
                                    ?>
                                    </td>
                                    <td><?php echo esc_html($row['vorname']); ?></td>
                                    <td><?php echo esc_html($row['nachname']); ?></td>
                                    <td><?php echo esc_html($row['email']); ?></td>
                                    <td><?php echo esc_html(date('d.m.Y', strtotime($row['beitrittsdatum']))); ?></td>
                                    <td>
                                        <?php echo !empty($row['austrittsdatum']) ? esc_html(date('d.m.Y', strtotime($row['austrittsdatum']))) : ''; ?>
                                    </td>
                                    <td><?php echo $row['starterpaket'] ? 'Ja' : 'Nein'; ?></td>
                                    <td><?php echo esc_html($row['spende'] ? 'Ja' : 'Nein'); ?></td>
                                    <td><?php echo esc_html($row['spende_monatlich'] ?? ''); ?></td>
                                    <td><?php echo esc_html($row['spende_einmalig'] ?? ''); ?></td>
                                    <td><?php echo $row['satzung_datenschutz'] ? 'Akzeptiert' : 'Nicht akzeptiert'; ?></td>
                                    <td><?php echo $row['hinweise'] ? 'Gelesen' : 'Nicht gelesen'; ?></td>
                                    <td><?php echo $row['sepa'] ? 'Erteilt' : 'Nicht erteilt'; ?></td>
                                    <td><?php echo esc_html($row['kontoinhaber']); ?></td>
                                    <td><?php echo esc_html($row['iban']); ?></td>
                                    <td><?php echo esc_html($row['beitrag'] ?? ''); ?></td>
                                    <td><?php echo esc_html(date('d.m.Y', strtotime($row['submission_date']))); ?></td>
                                    <td class="notizen-col"><?php echo esc_html($row['notizen']); ?></td>
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
