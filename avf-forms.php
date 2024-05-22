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
 * Description:       A plugin for creating forms
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
require_once plugin_dir_path(__FILE__) . 'includes/membership_children/forms-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/membership_children/shortcodes.php';


register_activation_hook(__FILE__, 'Activate_Avf_forms');

function Activate_Avf_forms()
{
    Avf_Forms_Activator::activate();
}

function Run_Avf_forms()
{
    Avf_Forms_Membership_Shortcodes::register();
    Avf_Forms_Membership_Handler::register();
    Avf_Forms_Membership_Children_Shortcodes::register();
    Avf_Forms_Membership_Children_Handler::register();
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
