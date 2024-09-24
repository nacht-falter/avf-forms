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
                        <?php foreach ($results as $row) : ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" class="membership-checkbox" value="<?php echo esc_attr($row['id']); ?>">
                                </th>
                                <td><?php echo esc_html($row['id']); ?></td>
                                <td><?php echo esc_html(MITGLIEDSCHAFTSARTEN[$row['mitgliedschaft_art']] ?? 'Unbekannt'); ?></td>
                                <td><?php echo esc_html($row['vorname']); ?></td>
                                <td><?php echo esc_html($row['nachname']); ?></td>
                                <td><?php echo esc_html($row['email']); ?></td>
                                <td><?php echo esc_html(date('d.m.Y', strtotime($row['beitrittsdatum']))); ?></td>
                                <td class="notizen-col"><?php echo esc_html($row['notizen']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="bulk-actions">
                    <button type="button" id="update-membership" class="button button-primary" disabled>Ansehen/Bearbeiten</button>
                    <button type="button" id="delete-membership" class="button button-secondary" disabled>Löschen</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
