<?php

namespace Nonaki_Addon;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Plugin class.
 *
 * The main class that initiates and runs the addon.
 *
 * @since 1.0.0
 */
final class Plugin
{

    /**
     * Addon Version
     *
     * @since 1.0.0
     * @var string The addon version.
     */
    const VERSION = '1.0.0';



    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     * @var string Minimum PHP version required to run the addon.
     */
    const MINIMUM_PHP_VERSION = '7.3';

    /**
     * Instance
     *
     * @since 1.0.0
     * @access private
     * @static
     * @var \Nonaki_Addon\Plugin The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     * @return \Nonaki_Addon\Plugin An instance of the class.
     */
    public static function instance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * Perform some compatibility checks to make sure basic requirements are meet.
     * If all compatibility checks pass, initialize the functionality.
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {

        if ($this->is_compatible()) {
            $this->init();
        }
    }

    /**
     * Compatibility Checks
     *
     * Checks whether the site meets the addon requirement.
     *
     * @since 1.0.0
     * @access public
     */
    public function is_compatible()
    {

        // Check if Nonaki Email Template Builder activated
        if (file_exists(WP_PLUGIN_DIR . '/nonaki-email-template-customizer/nonaki.php') && !class_exists('Nonaki_Email')) {
            add_action('admin_notices', [$this, 'admin_notice_active_plugin']);
            return false;
        }

        // Check if Nonaki Email Template Builder installed
        if (!class_exists('Nonaki_Email')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return false;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return false;
        }

        return true;
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have Nonaki Email Template Builder installed.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_main_plugin()
    {

        if (isset($_GET['activate'])) unset($_GET['activate']);

        $nonaki_email_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=nonaki-email-template-customizer'), 'install-plugin_nonaki-email-template-customizer');
        $message = sprintf(
            /* translators: 1: Plugin name 2: Nonaki Email Template Builder */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated. %3$s', 'nonaki-addon'),
            '<strong>' . esc_html__('Contact form 7 email template builder', 'nonaki-addon') . '</strong>',
            '<strong>' . esc_html__('	
            Nonaki Email Template Builder', 'nonaki-addon') . '</strong>',
            '<a class="button-primary" href="' . $nonaki_email_url . '">Install 	
            Nonaki Email Template Builder</a>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }


    /**
     * Admin notice
     *
     * Warning when the site doesn't have Nonaki Email Template Builder  activated.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_active_plugin()
    {

        $nonaki_email_plugin = 'nonaki-email-template-customizer/nonaki.php';
        $nonaki_email_url = wp_nonce_url('plugins.php?action=activate&plugin=' . $nonaki_email_plugin, 'activate-plugin_' . $nonaki_email_plugin);
        $message = sprintf(
            /* translators: 1: Plugin name 2: Nonaki Email Template Builder */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated. %3$s', 'nonaki-addon'),
            '<strong>' . esc_html__('Contact form 7 email template builder', 'nonaki-addon') . '</strong>',
            '<strong>' . esc_html__('Nonaki', 'nonaki-addon') . '</strong>',
            '<a class="button-primary" href="' . $nonaki_email_url . '">Active 	
            Nonaki Email Template Builder</a>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_minimum_php_version()
    {

        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'nonaki-addon'),
            '<strong>' . esc_html__('Contact form 7 email template builder', 'nonaki-addon') . '</strong>',
            '<strong>' . esc_html__('PHP', 'nonaki-addon') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Initialize
     *
     * Load the addons functionality only after Nonaki Email Template Builder is initialized.
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function init()
    {
        if (defined('WPCF7_PLUGIN')) {
            add_filter('nonaki_template_types', function ($types) {
                $types['cf7'] = 'Contact Form 7';
                return $types;
            });


            add_filter('nonaki_template_sub_types', function ($args) {
                $forms = get_posts(array(
                    'post_type'     => 'wpcf7_contact_form',
                    'numberposts'   => -1
                ));

                foreach ($forms as $form) {
                    $args['cf7'][$form->ID] = $form->post_title;
                }

                return $args;
            });

            add_filter('nonaki_template_type_from_post_type', function ($all_types) {
                $all_types['cf7'] = 'mail';
                return $all_types;
            });

            add_action('wpcf7_before_send_mail', [$this, 'email_template']);
        }
    }

    public function email_template($contact_form)
    {
        error_log(print_r($contact_form, true));
        // $email = $contact_form->prop('mail');
        // $email2 = $contact_form->prop('mail_2');
        // $body = $email['body'];


        // $email['body'] = 'hello world [your-email]';
        // $email2['body'] = 'auto response [your-email]';

        // $contact_form->set_properties(array('mail' => $email));
        // $contact_form->set_properties(array('mail_2' => $email2));
    }
}
