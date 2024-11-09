<?php
function Avf_Display_memberships()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $filters = [
        'aktiv' => 'Aktive Mitglieder',
        'kind' => 'Kinder/Jugendliche',
        'sonder' => 'Sondermitglieder',
        'passiv' => 'Passive Mitglieder',
        'foerder' => 'Fördermitglieder',
    ];

    ?>
    <div class="wrap">
        <h1>AVF-Mitgliedschaften</h1>
        <div id="admin-page-header">
            <a href="admin.php?page=avf-membership-form-page" class="button button-primary">Neue Mitgliedschaft hinzufügen</a>
            <div id="search-container">
                <label for="search" class="dashicons dashicons-search" id="search-label"></label>
                <input type="search" id="search" placeholder="Suche" />
            </div>
        </div>
        <div id="filter-list">
            <span><strong>Anzeigen:&nbsp;</strong></span>
            <div>
            <?php foreach ($filters as $filter_key => $filter_label) { ?>
                <label>
                    <input type="checkbox" class="filter-checkbox" name="filter" value="<?php echo $filter_key; ?>" />
                    <?php echo $filter_label; ?>
                </label>
            <?php } ?>
            </div>
        </div>

        <form id="membership-form" method="post" action="">
            <div id="membership-list-container" class="list-container">
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all" title="Alle Einträge auswählen" />
                            </th>
                            <?php
                            foreach (COLUMN_HEADERS as $column_key => $column_label) {
                                $title = esc_attr('Sortieren nach ' . $column_label);

                                echo '<th scope="col">';
                                echo '<a href="#" title="' . $title . '" class="table-header-link" data-column="' . $column_key . '">';
                                echo $column_label . ' ';
                                echo '</a></th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody id="membership-table-body">
                        <td class="loading-spinner" style="visibility: visible"></td>
                    </tbody>
                </table>
            </div>
            <div id="legend">
                <ul>
                    <li><span class="dashicons dashicons-warning" style="color: red;"></span> Alter stimmt nicht mit Mitgliedschaftsart überein</li>
                    <li><span class="dashicons dashicons-warning" style="color: orange;"></span> Ausgetreten</li>
                    <li><span class="dashicons dashicons-edit" style="color: #2271b1;"></span> Beitrag angepasst</li>
                </ul>
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
    </div>
    <?php
}
