<?php

namespace Nonaki\Services\WP;

defined('ABSPATH') || exit;
class Nonaki_Cf7_Service
{
    use Base;

    public function filter($message, $template_content)
    {
        $filters = [
            '{content}' => $message,
        ];

        return strtr($template_content, $filters);
    }



    public function add_elements($template_id, $type, $sub_type)
    {
        if ($type == 'cf7') {
            $form_id = 20; // Replace with your form ID
            $form = \WPCF7_ContactForm::get_instance($form_id);

            $tags = $form->collect_mail_tags();

            error_log(print_r($tags, true));


?>
            <script type="module">
                var blockManager = nonaki.BlockManager;

                blockManager.add('cf7-user-to', {
                    category: 'Contact Form 7',
                    label: `New User Email`,
                    editable: true,
                    attributes: {
                        class: 'fa fa-envelope',
                    },
                    content: `<mj-text>{{to}}</mj-text>`,

                });

                blockManager.add('cf7-user-first-name', {
                    category: 'Contact Form 7',
                    label: `First Name`,
                    editable: true,
                    attributes: {
                        class: 'fa fa-user',
                    },
                    content: `<mj-text>{{first_name}}</mj-text>`,

                });
            </script>
<?php
        }
    }

    public static function get_template($form_id)
    {
        $args = array(
            'post_type'      => 'nonaki',
            'no_found_rows'  => true,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'template_type',
                    'value'   => 'cf7',
                ),
                array(
                    'key'     => 'template_sub_type',
                    'value'   => $form_id,
                    'compare' => '='
                ),
                array(
                    'key'     => 'nonaki_status',
                    'value'   => 'active',
                ),
            ),
        );

        $query = new \WP_Query($args);


        if ($query->posts) {
            foreach ($query->posts as $post) {
                $form_id_for_template = get_post_meta($post->ID, 'template_sub_type', true);

                if ((int) $form_id === (int) $form_id_for_template) {

                    return  get_post_meta($post->ID, 'compiled_content', true);
                }
            }
        }

        return null;
    }

    public static function filter_message($args, $user, $blogname)
    {

        $filtered_message = strtr(self::get_template(), [
            '{{to}}' => $args['to'],
            '{{content}}' => $args['message'],
            '{{first_name}}' => $user->first_name,
            '{{last_name}}' => $user->last_name,
            '{{site_url}}' => $blogname,

        ]);
        $args['message'] = $filtered_message;
        return $args;
    }
}
