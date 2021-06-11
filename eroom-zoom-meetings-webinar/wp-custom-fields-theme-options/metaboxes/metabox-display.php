<?php

if (!defined('ABSPATH')) exit; //Exit if accessed directly


/***
 * @var $post
 * @var $metabox
 * @var $args_id
 *
 */

$vue_id = '';


if (empty($id)) {
    /*We are on a post*/
    $post_id = $post->ID;
    $id = $metabox['id'];
    $sections = $metabox['args'][$id];
    $active = '';
    $vue_id = "data-vue='" . $id . "'";
    $source_id = "data-source='" . $post_id . "'";
}

?>

<div class="stm_metaboxes_grid" <?php echo stm_wpcfto_filtered_output($vue_id . ' ' . $source_id); ?>>

    <div class="stm_metaboxes_grid__inner" v-if="data !== ''">

        <div class="container">

            <div class="stm-lms-tab-nav">
                <?php
                $i = 0;
                foreach ($sections as $section_name => $section):
                    if ($i == 0) $active = $section_name;
                    ?>
                    <div class="stm-lms-nav <?php if ($active == $section_name) echo 'active'; ?>"
                         data-section="<?php echo esc_attr($section_name); ?>"
                         @click="changeTab('<?php echo esc_attr($section_name); ?>')">
                        <?php echo sanitize_text_field($section['name']); ?>
                    </div>
                    <?php $i++; endforeach; ?>
            </div>

            <?php foreach ($sections as $section_name => $section): ?>
                <div id="<?php echo esc_attr($section_name); ?>"
                     class="stm-lms-tab <?php if ($section_name == $active) echo 'active'; ?>">
                    <div class="container container-constructed">
                        <div class="row">

                            <div class="column">
                                <?php foreach ($section['fields'] as $field_name => $field):
                                    $dependency = stm_lms_metaboxes_deps($field, $section_name);
                                    $width = (empty($field['columns'])) ? 'column-1' : "column-{$field['columns']}";
                                    $is_pro = (!empty($field['pro'])) ? 'is_pro' : 'not_pro';
                                    $description = (!empty($field['description'])) ? $field['description'] : '';
                                    if (stm_wpcfto_is_pro()) $is_pro = '';
                                    ?>
                                    <transition name="slide-fade">
                                        <div class="<?php echo esc_attr($width . ' ' . $is_pro); ?>" <?php echo ($dependency); ?>>
                                            <?php if ($is_pro === 'is_pro'): ?>
                                                <span><?php _e('Available in <a href="https://stylemixthemes.com/plugins/masterstudy-pro/" target="_blank">Pro Version</a>', 'wp-custom-fields-theme-options'); ?></span>
                                            <?php endif; ?>

                                            <?php

                                            $field_data = $field;
                                            $field_type = $field['type'];

                                            $field = "data['{$section_name}']['fields']['{$field_name}']";
                                            $field_value = "{$field}['value']";
                                            $field_label = "{$field}['label']";
                                            $field_id = $section_name . '-' . $field_name;

                                            $file = apply_filters("wpcfto_lms_field_{$field_type}", STM_WPCFTO_PATH . '/metaboxes/fields/' . $field_type . '.php');

                                            include $file;

                                            ?>

                                            <?php if (!empty($description)): ?>
                                                <p class="description"><?php echo html_entity_decode($description); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </transition>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>


    </div>
</div>