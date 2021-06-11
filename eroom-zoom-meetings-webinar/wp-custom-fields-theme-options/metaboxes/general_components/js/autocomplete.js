Vue.component('v-select', VueSelect.VueSelect);
Vue.component('wpcfto_autocomplete', {
    props: ['fields', 'field_label', 'field_name', 'field_id', 'field_value'],
    data: function () {
        return {
            ids: [],
            items: [],
            search: '',
            options: [],
            loading: true,
            value: ''
        }
    },
    template: `

        <div class="wpcfto_generic_field">
            <label v-html="field_label"></label>
            <div class="stm-curriculum" v-bind:class="{'loading': loading}">
    
                <v-select v-model="search"
                          label="title"
                          :options="options"
                          @search="onSearch">
                </v-select>
            
                <ul class="stm-lms-autocomplete">
                    <li v-for="(item, index) in items">
                        <span v-html="item.title"></span>
                        <i class="lnr lnr-cross" @click="removeItem(index)"></i>
                    </li>
                </ul>
                
                
                <input type="hidden"
                       v-bind:name="field_name"
                       v-model="value"/>
            
            </div>
        </div>
    `,
    created: function () {
        if (this.field_value) {
            this.getPosts(stm_wpcfto_ajaxurl + '?action=stm_curriculum&nonce=' + stm_wpcfto_nonces['stm_curriculum'] + '&posts_per_page=-1&orderby=post__in&ids=' + this.field_value + '&post_types=' + this.fields.post_type.join(','), 'items');
        } else {
            this.isLoading(false);
        }
    },
    methods: {
        isLoading(isLoading) {
            this.loading = isLoading;
        },
        onSearch(search) {
            var _this = this;
            var exclude = _this.ids.join(',');
            var post_types = _this.fields['post_type'].join(',');
            _this.getPosts(
                stm_wpcfto_ajaxurl + '?action=stm_curriculum&nonce=' +
                stm_wpcfto_nonces['stm_curriculum'] +
                '&exclude_ids=' + exclude +
                '&s=' + search +
                '&post_types=' + post_types,
                'options'
            );
        },
        getPosts(url, variable) {
            var vm = this;
            vm.isLoading(true);

            /*Adding field ID to filters then*/

            url += '&name=' + vm.field_name;

            this.$http.get(url).then(function (response) {
                vm[variable] = response.body;
                vm.isLoading(false);
            });

        },
        updateIds() {
            var vm = this;
            vm.ids = [];
            
            this.items.forEach(function (value, key) {
                vm.ids.push(value.id);
            });
            vm.$set(this, 'value', vm.ids);
            vm.$emit('wpcfto-get-value', vm.ids);
        },
        callFunction(functionName, item, model) {
            functionName(item, model);
        },
        containsObject(obj, list) {
            var i;
            for (i = 0; i < list.length; i++) {
                if (list[i]['id'] === obj['id']) {
                    return true;
                }
            }

            return false;
        },
        removeItem(index) {
            this.items.splice(index, 1);
        }
    },
    watch: {
        search: function (value) {
            if (typeof value === 'object' && value !== null && !this.containsObject(value, this.items)) {
                this.items.push(value);
            }
        },
        items: function () {
            this.updateIds();
        }
    }
})