<?php
/**
 * @var $field_name
 * @var $section_name
 *
 */

$field_key = "data['{$section_name}']['fields']['{$field_name}']";
$sections = "data['{$section_name}']['fields']['curriculum_sections']['value']";

include STM_WPCFTO_PATH .'/metaboxes/components_js/drip_content.php';
?>
<label v-html="<?php echo esc_attr($field_key); ?>['label']"></label>

<stm-autocomplete-drip-content v-bind:posts="<?php echo esc_attr($field_key) ?>['post_type']"
        v-bind:stored_ids="<?php echo esc_attr($field_key) ?>['value']"
        v-on:autocomplete-ids="<?php echo esc_attr($field_key) ?>['value'] = $event"></stm-autocomplete-drip-content>


<input type="hidden"
       name="<?php echo esc_attr($field_name); ?>"
       v-model="<?php echo esc_attr($field_key); ?>['value']" />