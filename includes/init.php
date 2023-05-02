<?php

namespace Nonaki_Addon;

class Init
{
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

    public function cf7_forms()
    {
        $forms = [];

        if (!class_exists('WPCF7_ContactForm')) {
            return $forms;
        }

        $args = [
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
        ];

        $cf7_query = new \WP_Query($args);

        if (!$cf7_query->have_posts()) {
            return $forms;
        } else {
            foreach ($cf7_query->posts as $post) {

                $cf7 = \WPCF7_ContactForm::get_instance($post->ID);

                $form = [
                    'id'     => $post->ID,
                    'title'  => $post->post_title,
                    'fields' => [],
                ];

                foreach ($cf7->collect_mail_tags() as $tag) {
                    $form['fields'][] = [
                        'id'    => $tag,
                        'label' => "[{$tag}]",
                    ];
                }

                $forms[] = $form;
            }
            wp_reset_query();
        }
        return $forms;
    }

    protected function get_value($fields, $tag)
    {
        $value = '';
        if ($fields) {
            foreach ($fields as $svalue) {
                if ($svalue['id'] == $tag) {
                    $value = $svalue['value'] == null ? '' : $svalue['value'];
                    break;
                }
            }
        }
        return $value;
    }

    public function __construct()
    {
        update_option('nonaki_addon_cf7_forms', $this->cf7_forms());
    }
}
