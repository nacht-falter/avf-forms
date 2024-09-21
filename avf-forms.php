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

if (! defined('WPINC') ) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'includes/activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/utils.php';
require_once plugin_dir_path(__FILE__) . 'includes/membership/forms-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/membership/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-page.php';


register_activation_hook(__FILE__, 'Activate_Avf_forms');

function Activate_Avf_forms()
{
    Avf_Forms_Activator::activate();
}

function Run_Avf_forms()
{
    Avf_Forms_Membership_Shortcodes::register();
    Avf_Forms_Membership_Handler::register();
}

Run_Avf_forms();

function Avf_Enqueue_scripts()
{
    wp_enqueue_script(
        'avf-form-scripts',
        plugin_dir_url(__FILE__) . 'assets/js/form-scripts.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'Avf_Enqueue_scripts');

function Avf_Enqueue_styles()
{
    wp_enqueue_style(
        'avf-form-styles',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(), // No dependencies
        null // Version number (optional)
    );
}
add_action('wp_enqueue_scripts', 'Avf_Enqueue_styles');

add_action('admin_menu', 'Avf_forms_add_admin_menu');

function Avf_forms_add_admin_menu() {
    add_menu_page(
        'AVF Mitgliederverwaltung', // Page title
        'AVF Mitgliederverwaltung', // Menu title
        'manage_options', // Capability
        'avf-membership-admin', // Menu slug
        'avf_display_submissions', // Callback function
        'dashicons-feedback', // Icon URL
        6 // Position
    );
}

function Avf_enqueue_admin_styles() {
    wp_enqueue_style('avf-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'Avf_enqueue_admin_styles');

