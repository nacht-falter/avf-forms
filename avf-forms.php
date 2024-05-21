<?php

/**
 * The plugin bootstrap file
 *
 * @link     https://johannesbernet.com
 * @since    1.0.0
 * @package  Avf_Forms
 * @license  GPL-2.0+
 * @author   Johannes Bernet
 * @category Plugin
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
require_once plugin_dir_path(__FILE__) . 'includes/forms-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';

register_activation_hook(__FILE__, 'Activate_Avf_forms');

function Activate_Avf_forms()
{
    Avf_Forms_Activator::activate();
}

function Run_Avf_forms()
{
    Avf_Forms_Shortcodes::register();
    Avf_Forms_Handler::register();
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
