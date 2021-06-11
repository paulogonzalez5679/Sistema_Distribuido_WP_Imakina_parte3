(function ($) {
    $(document).ready(function () {

        $('[data-vue]').each(function () {

            let $this = $(this);

            let data_var = $this.attr('data-vue');
            let data_source = $this.attr('data-source');

            new Vue({
                el: $(this)[0],
                data: function () {
                    return {
                        loading: false,
                        data: '',
                    }
                },
                mounted: function () {
                    this.getSettings();
                },
                methods: {
                    getSettings: function () {
                        var _this = this;
                        _this.loading = true;

                        this.$http.get(stm_wpcfto_ajaxurl + '?action=stm_wpcfto_get_settings&source=' + data_source + '&name=' + data_var).then(function (r) {
                            _this.$set(_this, 'data', r.body);
                            _this.loading = false;
                        });

                    },
                    changeTab: function (tab) {
                        let $tab = $('#' + tab);
                        $tab.closest('.stm_metaboxes_grid__inner').find('.stm-lms-tab').removeClass('active');
                        $tab.addClass('active');

                        let $section = $('div[data-section="' + tab + '"]');
                        $tab.closest('.stm_metaboxes_grid__inner').find('.stm-lms-nav').removeClass('active');
                        $section.addClass('active');

                    },
                    saveSettings: function (id) {
                        var vm = this;
                        vm.loading = true;
                        this.$http.post(stm_wpcfto_ajaxurl + '?action=stm_save_settings&nonce=' + stm_wpcfto_nonces['stm_save_settings'] + '&name=' + id, JSON.stringify(vm.data)).then(function (response) {
                            vm.loading = false;
                        });
                    }
                }
            });

        });

    });
})(jQuery);

function WpcftoIsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}