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
    'BEITRAEGE', [
        'aktiv' => 72,
        'aktiv_ermaessigt' => 54,
        'familie' => 45,
        'foerder' => 15,
        'sonder' => 30,
        'kind' => 42,
        'jugend' => 54,
        'geschwisterkind_discount' => 9,
        'passiv' => 0,
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

register_activation_hook(__FILE__, 'Activate_Avf_forms');
function Activate_Avf_forms()
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
            'Avf_Display_memberships', // Callback function
            'dashicons-feedback', // Icon URL
        );
        add_submenu_page(
            'avf-membership-page',             // Parent slug
            'Mitgliedschaft bearbeiten',  // Page title
            'Neue Mitgliedschaft',              // Menu title
            'manage_memberships',              // Capability
            'avf-membership-form-page',        // Slug for the new membership page
            'Avf_Display_Membership_form',      // Function to display the new membership form
        );
    }
}

Avf_Forms_Plugin::init();
