Vue.component('wpcfto_text', {
    props: ['fields', 'field_label', 'field_name', 'field_id', 'field_value'],
    data: function () {
        return {
            value: '',
        }
    },
    template: `
        <div class="wpcfto_generic_field">
            <label v-html="field_label"></label>
            <input type="text"
                v-bind:name="field_name"
                v-bind:placeholder="field_label"
                v-bind:id="field_id"
                v-model="value"
            />
        </div>
    `,
    mounted: function () {
        this.value = this.field_value;
    },
    methods: {},
    watch: {
        value: function (value) {
            this.$emit('wpcfto-get-value', value);
        }
    }
});