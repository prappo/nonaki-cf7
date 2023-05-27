<?php

/**
 * Plugin Name: Contact form 7 email template builder
 * Description: Contact form 7 email template builder addon for Nonaki.
 * Plugin URI:  https://wpcox.com/
 * Version:     1.0.0
 * Author:      WPcox
 * Author URI:  https://wpcox.com
 * Text Domain: nonaki-addon
 * 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


define('NONAKI_CF7_URL', plugin_dir_url(__FILE__));
define('NONAKI_CF7_ASSETS_URL', NONAKI_CF7_URL . '/assets');

function nonaki_cf7_addon()
{

    // Load plugin file
    require_once(__DIR__ . '/includes/plugin.php');

    // Run the plugin
    \Nonaki_Addon\Cf7::instance();
}

function nonaki_cf7_addon_init()
{
    // Load init file
    require_once(__DIR__ . '/includes/init.php');

    \Nonaki_Addon\Init::instance();
}

add_action('plugins_loaded', 'nonaki_cf7_addon');
add_action('wp', 'nonaki_cf7_addon_init');
