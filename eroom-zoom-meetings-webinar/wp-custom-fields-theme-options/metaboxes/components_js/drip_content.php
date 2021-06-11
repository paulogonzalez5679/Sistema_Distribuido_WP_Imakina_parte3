<script>
    <?php
    ob_start();
    include STM_WPCFTO_PATH .'/metaboxes/components/drip_content.php';
    $template = preg_replace("/\r|\n/", "", ob_get_clean());
    ?>

    Vue.component('v-select', VueSelect.VueSelect);
    Vue.component('stm-autocomplete-drip-content', {
        props: ['posts', 'stored_ids'],
        data: function () {
            return {
                items: [
                    {
                        active: true
                    }
                ],
                search: '',
                options: [],
                loading: true,
            }
        },
        template: '<?php echo stm_wpcfto_filtered_output($template); ?>',
        created: function () {
            this.isLoading(false);


            if (this.stored_ids) {
                this.items = JSON.parse(this.stored_ids);
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
                var post_types = _this.posts.join(',');
                _this.getPosts(stm_wpcfto_ajaxurl + '?action=stm_curriculum&nonce='+ stm_wpcfto_nonces['stm_curriculum'] + '&s=' + search + '&post_types=' + post_types, 'options');
            },
            getPosts(url, variable) {
                var vm = this;
                vm.isLoading(true);
                this.$http.get(url).then(function (response) {
                    vm[variable] = response.body;
                    vm.isLoading(false);
                });
            },
            updateIds() {
                var vm = this;
                vm.$emit('autocomplete-ids', JSON.stringify(vm.items));
            },
            // removeItem(index) {
            //     this.items.splice(index, 1);
            // }
            addNewParent() {
                var vm = this;

                vm.items.push({
                    active: false
                });
            },
            getActiveItem() {
                var vm = this;
                var active = 0;
                vm.items.forEach(function (value, index) {
                    if (value.active) active = index;
                });

                return active;
            },
            removeAllActive() {
                var vm = this;
                vm.items.forEach(function (value, index) {
                    vm.$set(vm.items[index], 'active', false);
                });
            },
            changeActive(index) {
                var vm = this;
                vm.removeAllActive();
                vm.$set(vm.items[index], 'active', true);
            }
        },
        watch: {
            search: function (value) {
                var vm = this;
                if (typeof value === 'object' && value !== null) {
                    var active = vm.getActiveItem();
                    if (typeof vm.items[active].parent === 'undefined') {
                        vm.$set(vm.items[active], 'parent', value);
                    } else {
                        if (typeof vm.items[active].childs === 'undefined') {
                            vm.$set(vm.items[active], 'childs', []);
                        }
                        vm.items[active].childs.push(value);
                    }
                }
            },
            items: {
                deep: true,
                handler: function () {
                    this.updateIds();
                }
            }
        }
    })
</script>