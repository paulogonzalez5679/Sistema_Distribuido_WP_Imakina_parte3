<div>
    <div class="stm-lms-questions">

        <!--Simple Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-single_choice"
                 v-if="choice == 'single_choice' && questions.length">

                <div class="stm-lms-questions-single_answer" v-for="(v,k) in questions">
                    <label class="stm_lms_radio" v-bind:class="{'active' : v.isTrue}">
                        <input type="radio" v-bind:name="choice + '_' + origin" v-model="correctAnswer"
                               v-bind:value="v.text" @change="isAnswer()"/>
                        <i></i>
                        <input type="text" v-model="questions[k]['text']"/>

                        <textarea v-model="questions[k]['explain']"
                                  placeholder="<?php esc_html_e('Answer explanation (Will be shown in "Show Answers" section)', 'wp-custom-fields-theme-options') ?>"></textarea>
                    </label>
                    <div class="actions">
                        <i class="lnr lnr-trash" @click="deleteAnswer(k)"></i>
                    </div>
                </div>

            </div>
        </transition>

        <!--Multi Answer Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-multi_choice"
                 v-if="choice == 'multi_choice' && questions.length">

                <div class="stm-lms-questions-single_answer" v-for="(v,k) in questions">
                    <label class="stm_lms_checkbox" v-bind:class="{'active' : v.isTrue}">
                        <input type="checkbox" v-bind:name="choice" v-model="correctAnswers[v.text]"
                               v-bind:value="v.text" @change="isAnswers()"/>
                        <i class="fa fa-check"></i>
                        <input type="text" v-model="questions[k]['text']"/>

                        <textarea v-model="questions[k]['explain']"
                                  placeholder="<?php esc_html_e('Answer explanation (Will be shown in "Show Answers" section)', 'wp-custom-fields-theme-options') ?>"></textarea>

                    </label>
                    <div class="actions">
                        <i class="lnr lnr-trash" @click="deleteAnswer(k)"></i>
                    </div>
                </div>

            </div>
        </transition>

        <!--True False Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-true_false"
                 v-if="choice == 'true_false' && questions.length">

                <div class="stm-lms-questions-single_answer" v-for="(v,k) in questions">
                    <label class="stm_lms_radio" v-bind:class="{'active' : v.isTrue}">
                        <input type="radio" v-bind:name="choice" v-model="correctAnswer" v-bind:value="v.text"
                               @change="isAnswer()"/>
                        <i></i>
                        <span>{{ v.text }}</span>
                    </label>
                </div>

            </div>
        </transition>

        <!--Item Match Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-item_match"
                 v-if="choice == 'item_match' && questions.length">

                <div class="stm-lms-questions-single_answer" v-for="(v,k) in questions">
                    <label class="stm_lms_checkbox" v-bind:class="{'active' : v.isTrue}">
                        <div class="row">
                            <div class="column">
                                <h6><?php esc_html_e('Question', 'wp-custom-fields-theme-options'); ?></h6>
                                <input type="text" v-model="questions[k]['question']"/>
                            </div>
                            <div class="column">
                                <h6><?php esc_html_e('Match', 'wp-custom-fields-theme-options'); ?></h6>
                                <input type="text" v-model="questions[k]['text']"/>
                            </div>
                        </div>

                        <textarea v-model="questions[k]['explain']"
                                  placeholder="<?php esc_html_e('Answer explanation (Will be shown in "Show Answers" section)', 'wp-custom-fields-theme-options') ?>"></textarea>

                    </label>
                    <div class="actions">
                        <i class="lnr lnr-trash" @click="deleteAnswer(k)"></i>
                    </div>
                </div>

            </div>
        </transition>

        <!--Keywords Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-keywords"
                 v-if="choice == 'keywords' && questions.length">

                <div class="stm-lms-questions-single_keyword" v-for="(v,k) in questions">
                    <h4><?php esc_html_e('Keyword #'); ?> {{k + 1}}</h4>
                    <input type="text" v-model="questions[k]['text']"/>

                    <textarea v-model="questions[k]['explain']"
                              placeholder="<?php esc_html_e('Answer explanation (Will be shown in "Show Answers" section)', 'wp-custom-fields-theme-options') ?>"></textarea>
                </div>

            </div>
        </transition>

        <!--Fill the Gap Question-->
        <transition name="slide-fade">
            <div class="stm-lms-questions-single stm-lms-questions-fill_the_gap"
                 v-if="choice == 'fill_the_gap' && questions.length">

                <div class="stm-lms-questions-single_fill_the_gap">
                    <h4><?php esc_html_e('Enter text, separate answers with "|" symbol', 'wp-custom-fields-theme-options') ?></h4>
                    <p><strong>Example:</strong>
                        Deborah was angry at her son. Her son didn't <strong>|listen|</strong> to her.
                        Her son was 16 years old. Her son <strong>|thought|</strong> he knew everything.
                        Her son <strong>|yelled|</strong> at Deborah.
                    </p>
                    <textarea v-model="questions[0]['text']"
                              placeholder="<?php esc_html_e('Enter text, separate answers with "|" symbol', 'wp-custom-fields-theme-options') ?>">

                    </textarea>
                </div>

            </div>
        </transition>

        <?php if (get_post_type() !== 'stm-questions'): ?>
            <!--Question Bank-->
            <transition name="slide-fade">
                <div class="stm-lms-questions-single stm-lms-questions-question_bank"
                     v-if="choice == 'question_bank'">


                    <blockquote>
                        <?php esc_html_e('This type of question - not a question itself. 
                        This Bank - just a group of questions from certain category. 
                        Questions from category will be shown in a separate block, with group name you write above.',
                            'wp-custom-fields-theme-options'); ?>
                    </blockquote>

                    <div class="bank_category">
                        <h6><?php esc_html_e('Select categories to show questions from.', 'wp-custom-fields-theme-options') ?></h6>
                        <select v-model="question_bank_category" @change="selectQuestionBankCategory()">

                            <option v-bind:value="''">
                                <?php esc_html_e('Select category', 'wp-custom-fields-theme-options') ?>
                            </option>

                            <option v-for="term in question_bank" v-bind:value="term">
                                {{term.name}} ({{term.count}})
                            </option>

                        </select>

                        <div class="bank_category__list" v-if="typeof questions[0] !== 'undefined'">
                            <div class="bank_category__single" v-for="category in questions[0]['categories']">
                                {{category.name}}
                                <i class="lnricons-cross" @click="deleteCategory(category)"></i>
                            </div>
                        </div>

                    </div>

                    <div v-if="typeof questions[0] !== 'undefined'">
                        <h6><?php esc_html_e('Select number of questions.', 'wp-custom-fields-theme-options') ?></h6>
                        <input type="number"
                               placeholder="<?php esc_html_e('Number of questions to show', 'wp-custom-fields-theme-options'); ?>"
                               v-model="questions[0]['number']"/>
                    </div>

                </div>
            </transition>

        <?php endif; ?>

        <div class="stm_lms_answers_container"
             v-if="choice === 'single_choice' || choice === 'multi_choice' || choice === 'item_match' || choice === 'keywords' || (choice === 'fill_the_gap' && questions.length < 1)">
            <div class="stm_lms_answers_container__input">
                <input type="text"
                       v-model="new_answer"
                       v-bind:class="{'shake-it' : isEmpty}"
                       @keydown.enter.prevent="addAnswer()"
                       placeholder="<?php esc_html_e('Enter new Answer', 'wp-custom-fields-theme-options'); ?>"/>
            </div>
            <div class="stm_lms_answers_container__submit">
                <a class="button" @click="addAnswer()"><?php esc_html_e('Add Answer') ?></a>
            </div>
        </div>

    </div>
</div>