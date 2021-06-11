<script>
	<?php

    $terms = get_terms('stm_lms_question_taxonomy', array(
        'hide_empty' => false,
        'count' => true
    ));
	ob_start();
	include STM_WPCFTO_PATH .'/metaboxes/components/answers.php';
	$template = preg_replace( "/\r|\n/", "", addslashes(ob_get_clean()));
	?>
    Vue.component('stm-answers', {
        props: ['stored_answers', 'choice', 'origin'],
        data: function () {
            return {
                questions: [],
                new_answer: '',
                correctAnswer: '',
                correctAnswers: {},
                previousAnswers: [],
                previousCorrectAnswer: '',
                isEmpty: false,
                timeout : '',
                question_bank : <?php echo json_encode($terms, true); ?>,
                question_bank_category : {}
            }
        },
        mounted: function() {
            var vm = this;
            this.questions = this.stored_answers;


            if(this.questions !== '' && typeof this.questions !== 'undefined') {
                this.questions.forEach(function (answer) {
                    if (answer.isTrue === '1' || answer.isTrue === 1) {
                        vm.correctAnswer = answer.text;

                        vm.$set(vm.correctAnswers, answer.text, answer.isTrue);
                    }
                })
            } else {
                this.questions = [];
            }
        },
        template: '<?php echo stm_wpcfto_filtered_output($template); ?>',
        methods: {
            addAnswer: function () {
                var vm = this;
                var exists = false;
                vm.isEmpty = false;
                clearTimeout(vm.timeout);
                /*Check if answer is typed*/
                if(this.new_answer.length < 1) {
                    vm.isEmpty = true;
                    vm.timeout = setTimeout(function(){
                        vm.isEmpty = false;
                    }, 800);

                    return false;
                }
                /*Check if answer exists*/

                if(typeof vm.questions !== 'undefined') {
                    vm.questions.forEach(function (v, k) {
                        if (v['text'] == vm.new_answer) exists = true;
                    });
                } else {
                    exists = false;
                }

                if(exists) return;

                this.questions.push({
                    text : vm.new_answer,
                    isTrue : 0
                });

                this.new_answer = '';
            },
            isAnswer() {
                var vm = this;
                this.questions.forEach(function(value, key){
                    vm.questions[key]['isTrue'] = (vm.correctAnswer === value.text) ? 1 : 0;
                });
            },
            isAnswers() {
                var vm = this;
                this.questions.forEach(function(value, key){
                    var answer = value.text;
                    vm.questions[key]['isTrue'] = (typeof vm.correctAnswers[answer] !== 'undefined' && vm.correctAnswers[answer]) ? 1 : 0;
                });
            },
            deleteAnswer(k) {
                this.questions.splice(k, 1);
            },
            selectQuestionBankCategory() {
                var vm = this;

                vm.createSimpleCategory();

                var cats = vm.questions[0]['categories'];

                if(typeof vm.question_bank_category.term_id !== 'undefined') {
                    vm.$set(cats, vm.question_bank_category.slug, vm.question_bank_category);
                    vm.question_bank_category = '';
                }

            },
            createSimpleCategory() {
                var vm = this;

                if(typeof this.questions === 'undefined' ) {
                    vm.$set(vm, 'questions', []);
                }

                if(!this.questions.length) {
                    vm.questions.push({
                        'number' : 0,
                        'categories' : {}
                    });
                }

                if(typeof this.questions[0]['categories'] === 'undefined') {
                    vm.$set(vm.questions[0], 'categories', {});
                }
            },
            deleteCategory(category){
                var vm = this;

                vm.createSimpleCategory();

                var cats = vm.questions[0]['categories'];

                vm.$delete(cats, category.slug);
            }
        },
        watch: {
            questions: function(value) {
                this.$emit('get-answers', value);
                console.log(value);
            },
            choice: function(value) {
                var vm = this;
                if(value === 'true_false') {
                    vm.previousAnswers = vm.questions.slice(0);
                    vm.previousCorrectAnswer = vm.correctAnswer;
                    vm.questions = [
                        {
                            'text' : '<?php esc_html_e('True', 'wp-custom-fields-theme-options'); ?>',
                            'isTrue' : 1,
                        },
                        {
                            'text' : '<?php esc_html_e('False', 'wp-custom-fields-theme-options'); ?>',
                            'isTrue' : 0,
                        },
                    ];

                    vm.correctAnswer = "<?php esc_html_e('True', 'wp-custom-fields-theme-options'); ?>";

                } else {
                    if(vm.previousAnswers.length) {
                        vm.correctAnswer = vm.previousCorrectAnswer;
                        vm.questions = vm.previousAnswers;
                    }
                }
            }
        }
    })
</script>