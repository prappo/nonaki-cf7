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

function nonaki_cf7_addon()
{

    // Load plugin file
    require_once(__DIR__ . '/includes/plugin.php');

    // Run the plugin
    \Nonaki_Addon\Plugin::instance();
}
add_action('plugins_loaded', 'nonaki_cf7_addon');
