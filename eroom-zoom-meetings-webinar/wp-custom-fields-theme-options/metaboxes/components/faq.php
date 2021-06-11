<div>
    <div class="stm-lms-faq">

        <div class="stm-lms-icon_input">
            <input type="text"
                   @keydown.enter.prevent="addNew()"
                   v-model="add_new"
                   v-bind:class="{'shake-it' : isEmpty}"
                   placeholder="<?php esc_html_e('Add new FAQ item', 'wp-custom-fields-theme-options') ?>"/>
            <i class="stm-lms-icon lnr lnr-checkmark-circle" @click="addNew()"></i>
        </div>


        <div class="stm_lms_faq">
            <div class="stm_lms_faq__single" v-for="(item, key) in faq">
                <div class="stm_lms_faq__single_top">
                    <label><?php esc_html_e('Question', 'wp-custom-fields-theme-options'); ?> {{ key + 1 }}</label>
                    <i class="lnr lnr-cross" @click="deleteItem(key)"></i>
                </div>

                <input type="text" v-model="item['question']" placeholder="<?php esc_html_e('Enter FAQ question', 'wp-custom-fields-theme-options') ?>" />
                <textarea v-model="item['answer']" placeholder="<?php esc_html_e('Enter FAQ answer', 'wp-custom-fields-theme-options') ?>"></textarea>
            </div>
        </div>

    </div>
</div>