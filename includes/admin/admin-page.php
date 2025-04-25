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
    $secondary_filters = [
        'beitragsbefreit' => 'Beitragsbefreite ausblenden',
        'ausgetreten' => 'Ausgetretene ausblenden',
    ]

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
            <div>
            <?php foreach ($filters as $filter_key => $filter_label) { ?>
                <label>
                    <input type="checkbox" class="filter-checkbox" name="filter" value="<?php echo $filter_key; ?>" />
                    <?php echo $filter_label; ?>
                </label>
            <?php } ?>
            </div>
            <div>
            <?php foreach ($secondary_filters as $filter_key => $filter_label) { ?>
                <label>
                    <input type="checkbox" class="filter-checkbox" name="filter" value="<?php echo $filter_key; ?>" />
                    <?php echo $filter_label; ?>
                </label>
            <?php } ?>
            </div>
        </div>
        <div>
            <span>Gefunden: <span id="record-count">0</span></span>
        </div>

        <form id="membership-form" method="post" action="">
            <div id="membership-list-container" class="list-container">
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column no-link">
                                <input type="checkbox" class="no-link" id="select-all" title="Alle Einträge auswählen" />
                            </th>
                            <?php
                            foreach (COLUMN_HEADERS_MEMBERSHIPS as $column_key => $column_label) {
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
                        <td colspan="5" class="no-memberships-msg">Keine Mitgliedschaften gefunden.</td>
                        <td class="loading-spinner" style="visibility: visible"></td>
                    </tbody>
                </table>
            </div>

            <div class="bulk-actions">
                <button type="button" id="export-csv" class="button button-primary" title="Ausgewählte Mitgliedschaften als CSV exportieren" disabled>Als CSV exportieren</button>
                <button type="button" id="send-email" class="button button-primary" title="E-Mail an ausgewählte Mitglieder schreiben" disabled>E-Mail schreiben</button>
                <button type="button" id="delete-bulk" class="button button-secondary" title="Ausgewählte Mitgliedschaften löschen" disabled>Mitgliedschaften löschen</button>
            </div>
        </form>

        <div id="legend">
            <ul>
                <li><span class="dashicons dashicons-info" style="color: #3498db;"></span> Wiedervorlage</li>
                <li><span class="dashicons dashicons-warning" style="color: orange;"></span> Alter stimmt nicht mit Mitgliedschaftsart überein</li>
                <li><span class="dashicons dashicons-warning" style="color: red;"></span> Gekündigt</li>
                <li><span class="dashicons dashicons-dismiss" style="color: red;"></span> Ausgetreten</li>
                <li><span class="dashicons dashicons-edit" style="color: #2271b1;"></span> Beitrag angepasst</li>
            </ul>
        </div>

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
    <?php
}

function Avf_Display_schnupperkurse()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
    <div class="wrap">
        <h1>Schnupperkurse</h1>
        <div id="admin-page-header">
            <a href="admin.php?page=avf-schnupperkurs-form-page" class="button button-primary">Neuen Schnupperkurs hinzufügen</a>
        </div>
        <div>
            <span>Gefunden: <span id="record-count">0</span></span>
        </div>

        <form id="schnupperkurs-form" method="post" action="">
            <div id="schnupperkurs-list-container" class="list-container">
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all" title="Alle Einträge auswählen" />
                            </th>
                            <?php
                            foreach (COLUMN_HEADERS_SCHNUPPERKURSE as $column_key => $column_label) {
                                $title = esc_attr('Sortieren nach ' . $column_label);

                                echo '<th scope="col">';
                                echo '<a href="#" title="' . $title . '" class="table-header-link" data-column="' . $column_key . '">';
                                echo $column_label . ' ';
                                echo '</a></th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody id="schnupperkurs-table-body">
                        <td colspan="5" class="no-memberships-msg">Keine Schnupperkurse gefunden.</td>
                        <td class="loading-spinner" style="visibility: visible"></td>
                    </tbody>
                </table>
            </div>
            <div id="legend">
                <ul>
                    <li><span class="dashicons dashicons-warning" style="color: red;"></span> Schnupperkurs ist vorbei.</li>
                    <li><span class="dashicons dashicons-yes-alt" style="color: green;"></span> Beigetreten.</li>
                </ul>
            </div>
            <div class="bulk-actions">
                <button type="button" id="delete-bulk" class="button button-secondary" disabled>Ausgewählte Schnupperkurse löschen</button>
            </div>
        </form>
    </div>
    <?php
}

function Avf_Display_Follow_Ups()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
    <div class="wrap">
        <h1>Wiedervorlagen</h1>
        <div id="follow-ups" class="list-container"></div>
        <div id="legend">
            <ul>
                <li><span class="dashicons dashicons-info" style="color: #3498db;"></span> Wiedervorlage</li>
                <li><span class="dashicons dashicons-warning" style="color: red;"></span> Gekündigt</li>
                <li><span class="dashicons dashicons-dismiss" style="color: red;"></span> Ausgetreten</li>
            </ul>
        </div>
    </div>
    <?php
}

function Avf_Display_Membership_stats()
{
    if (!current_user_can('manage_memberships')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    ?>
    <div class="wrap">
        <h1>Mitglieder-Statistik</h1>
        <div id="membership-stats"></div>
    </div>
    <?php
}
