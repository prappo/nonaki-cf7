<?php

namespace Nonaki\Services\WP;

defined('ABSPATH') || exit;
class Nonaki_Cf7_Service
{
    use Base;


    private function get_form_fields($form_id)
    {
        $forms = get_option('nonaki_addon_cf7_forms');
        if ($forms) {
            foreach ($forms as $form) {
                if ((int)$form['id'] === (int)$form_id) {
                    return $form['fields'];
                }
            }
        }
        return [];
    }

    public function add_elements($template_id, $type, $sub_type)
    {
        if ($type == 'cf7') {
            foreach ($this->get_form_fields($sub_type) as $field) {
?>
                <script type="module">
                    var blockManager = nonaki.BlockManager;

                    blockManager.add('cf7-<?php echo esc_html($field['id']) ?>', {
                        category: 'Contact Form 7',
                        label: `<?php echo esc_html($field['id']) ?>`,
                        editable: true,
                        attributes: {
                            class: 'fa fa-envelope',
                        },
                        content: `<mj-text><?php echo esc_html($field['label']) ?></mj-text>`,

                    });
                </script>
<?php
            }
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

                if ($form_id ===  $form_id_for_template) {
                    return  get_post_meta($post->ID, 'compiled_content', true);
                }
            }
        }

        wp_reset_query();
        wp_reset_postdata();

        return null;
    }
}
