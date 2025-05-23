<?php

/**
 * The plugin bootstrap file
 *
 * @category Plugin
 * @package  Avf_Forms
 * @author   "Johannes Bernet <contact@johannesbernet.com>
 * @license  GPL-2.0+
 * @link     https://johannesbernet.com
 * @since    1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       AVF Forms
 * Plugin URI:        http://github.com/nacht-falter/avf-forms/
 * Description:       A plugin for membership forms
 * Version:           1.0.0
 * Author:            Johannes Bernet
 * Author URI:        http://johannesbernet.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       avf-forms
 * Domain Path:       /languages
 */
if (!defined('WPINC')) {
    die;
}

// Define constants for plugin path and URL
define('AVF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AVF_PLUGIN_URL', plugin_dir_url(__FILE__));
define(
    'MITGLIEDSCHAFTSARTEN', [
        'aktiv' => 'Aktives Mitglied',
        'aktiv_ermaessigt' => 'Aktives Mitglied (ermäßigt)',
        'familie' => 'Familienmitglied',
        'foerder' => 'Fördermitglied',
        'sonder' => 'Sondermitglied',
        'passiv' => 'Passives Mitglied',
        'kind' => 'Kind',
        'jugend' => 'Jugend',
        'geschwisterkind_discount' => 'Geschwisterkind-Rabatt',
    ]
);
define(
    'MITGLIEDSCHAFTSARTEN_PLURAL', [
        'aktiv' => 'Aktive Mitglieder',
        'aktiv_ermaessigt' => 'Aktive Mitglieder (ermäßigt)',
        'familie' => 'Familienmitglieder',
        'foerder' => 'Fördermitglieder',
        'sonder' => 'Sondermitglieder',
        'passiv' => 'Passive Mitglieder',
        'kind' => 'Kinder',
        'jugend' => 'Jugendliche',
    ]
);
define(
    'SCHNUPPERKURSARTEN', [
        'kind' => 'Kind/Jugend',
        'erwachsene' => 'Erwachsene',
    ]
);
define(
    'SCHNUPPERKURSPREISE', [
        'kind' => 20,
        'erwachsene' => 30,
    ]
);
define(
    'COLUMN_HEADERS_MEMBERSHIPS', [
        'id'                 => 'ID',
        'mitgliedschaft_art' => 'Art der Mitgliedschaft',
        'vorname'            => 'Vorname',
        'nachname'           => 'Nachname',
        'email'              => 'E-Mail',
        'geburtsdatum'       => 'Geburtsdatum',
        'beitrittsdatum'     => 'Beitrittsdatum',
        'kuendigungseingang' => 'Kündigungseingang',
        'austrittsdatum'     => 'Austrittsdatum',
        'starterpaket'       => 'Starterpaket',
        'spende'             => 'Spende',
        'spende_monatlich'   => 'Spende mtl.',
        'spende_einmalig'    => 'Spende einm.',
        'sepa'               => 'SEPA-Mandat',
        'kontoinhaber'       => 'Kontoinhaber',
        'iban'               => 'IBAN',
        'bic'                => 'BIC',
        'bank'               => 'Bank',
        'beitrag'            => 'Beitrag',
        'notizen'            => 'Notizen',
        'submission_date'    => 'Eingangsdatum'
    ]
);
define(
    'COLUMN_HEADERS_SCHNUPPERKURSE', [
        'id'              => 'ID',
        'schnupperkurs_art' => 'Art des Schnupperkurses',
        'vorname'         => 'Vorname',
        'nachname'        => 'Nachname',
        'email'           => 'E-Mail',
        'telefon'         => 'Telefon',
        'geburtsdatum'    => 'Geburtsdatum',
        'beginn'          => 'Beginn',
        'ende'            => 'Ende',
        'wie_erfahren'    => 'Wie vom AVF erfahren?',
        'notizen'         => 'Notizen',
        'submission_date' => 'Eingangsdatum'
    ]
);
define(
    'WIE_ERFAHREN', [
        'webseite' => 'Webseite',
        'social_media' => 'Soziale Medien',
        'Plakat' => 'Plakat',
        'flyer' => 'Flyer',
        'empfehlung' => 'Empfehlung',
        'sonstiges' => 'Sonstiges',
    ]
);

// Autoload required files
$includes = [
    'includes/activator.php',
    'includes/utils.php',
    'includes/membership/forms-handler.php',
    'includes/membership/shortcodes.php',
    'includes/admin/admin-page.php',
    'includes/admin/form-page.php',
    'includes/ajax-handler.php'
];

foreach ($includes as $file) {
    include_once AVF_PLUGIN_DIR . $file;
}

register_activation_hook(__FILE__, 'activate_avf_forms');

function avf_clear_cronjob()
{
    wp_clear_scheduled_hook('avf_schnupperkurs_notification');
}

register_deactivation_hook(__FILE__, 'avf_clear_cronjob');

function activate_avf_forms()
{
    Avf_Forms_Activator::activate();
}

class Avf_Forms_Plugin
{
    public static function init()
    {
        Avf_Forms_Membership_Shortcodes::register();
        Avf_Forms_Membership_Handler::register();

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_public_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('avf_schnupperkurs_notification', ['Avf_Forms_Utils', 'schnupperkurs_notification']);
        add_action('avf_delete_old_membership_data', ['Avf_Forms_Utils', 'delete_old_membership_data']);
    }

    public static function enqueue_public_assets()
    {
        wp_enqueue_script(
            'avf-form-scripts',
            AVF_PLUGIN_URL . 'assets/js/form-scripts.js',
            array(),
            null,
            true
        );

        wp_enqueue_style(
            'avf-form-styles',
            AVF_PLUGIN_URL . 'assets/css/style.css',
            array(),
            null
        );
    }

    public static function enqueue_admin_assets()
    {
        wp_enqueue_script(
            'avf-admin-scripts',
            AVF_PLUGIN_URL . 'assets/js/admin-scripts.js',
            array('jquery'),
            null,
            true
        );

        wp_localize_script(
            'avf-admin-scripts', 'avf_ajax_admin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('avf_membership_action'),
            ]
        );

        wp_enqueue_style('avf-admin-styles', AVF_PLUGIN_URL . 'assets/css/admin-styles.css');
    }

    public static function add_admin_menu()
    {
        add_menu_page(
            'AVF Mitgliederverwaltung', // Page title
            'AVF Mitgliederverwaltung', // Menu title
            'manage_memberships', // Capability
            'avf-membership-admin', // Menu slug
            'avf_display_memberships', // Callback function
            'dashicons-feedback' // Icon URL
        );

        add_submenu_page(
            'avf-membership-admin',           // Parent slug (must match the menu slug from add_menu_page)
            'Mitgliedschaft hinzufügen/bearbeiten',      // Page title
            'Neue Mitgliedschaft hinzufügen',            // Menu title
            'manage_memberships',             // Capability
            'avf-membership-form-page',       // Slug for the new membership page
            'avf_display_membership_form'     // Function to display the new membership form
        );

        add_submenu_page(
            'avf-membership-admin',
            'Wiedervorlagen',
            'Wiedervorlagen',
            'manage_memberships',
            'avf-follow-ups',
            'avf_display_followups'
        );

        add_submenu_page(
            'avf-membership-admin',           // Parent slug (must match the menu slug from add_menu_page)
            'Schnupperkurs-Verwaltung',      // Page title
            'Schnupperkurs-Verwaltung',      // Menu title
            'manage_memberships',             // Capability
            'avf-schnupperkurs-admin',       // Slug for the new membership page
            'avf_display_schnupperkurse'     // Function to display the new membership form
        );

        add_submenu_page(
            'avf-membership-admin',
            'Schnupperkurs hinzufügen/bearbeiten',
            'Neuen Schnupperkurs hinzufügen',
            'manage_memberships',
            'avf-schnupperkurs-form-page',
            'avf_display_schnupperkurs_form'
        );

        add_submenu_page(
            'avf-membership-admin',
            'Mitglieder-Statisik',
            'Mitglieder-Statistik',
            'manage_memberships',
            'avf-membership-stats',
            'avf_display_membership_stats'
        );

        add_submenu_page(
            'avf-membership-admin',
            'Mitgliedsbeiträge verwalten',
            'Mitgliedsbeiträge verwalten',
            'manage_memberships',
            'avf-membership-fee-admin',
            'avf_manage_membership_fees'
        );
    }
}

Avf_Forms_Plugin::init();
