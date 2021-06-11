<div>

    <div class="stm-lms-addons" v-bind:class="{'loading' : loading}">
        <div class="stm-lms-addon" v-for="(addon, key) in addons_list" v-bind:class="{'active' : addons[key]}">
            <div class="stm-lms-addon__image">
                <a v-bind:href="addon.settings" target="_blank" v-if="addons[key] && addon.settings">
                    <i class="lnr lnr-cog"></i>
                </a>
                <img v-bind:src="addon.url" />
            </div>
            <div class="stm-lms-addon__install">
                <a href="#" class="stm-lms-addon-enable" @click.prevent="enableAddon(key)">
                    <span v-if="!addons[key]"><?php esc_html_e('Enable Addon', 'wp-custom-fields-theme-options') ?></span>
                    <span v-else=""><?php esc_html_e('Disable Addon', 'wp-custom-fields-theme-options') ?></span>
                </a>
                <h4>{{addon.name}}</h4>
            </div>
        </div>
    </div>
</div>