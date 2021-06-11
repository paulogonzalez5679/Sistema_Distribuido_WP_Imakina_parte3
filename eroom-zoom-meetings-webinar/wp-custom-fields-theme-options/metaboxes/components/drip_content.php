<div class="stm-curriculum" v-bind:class="{\'loading\': loading}">

    <!--Add lesson which will be locked-->
    <a href="#" class="button" @click.prevent="addNewParent">Add Parent Lesson</a>

    <ul class="stm-lms-autocomplete-drips-list" v-if="items.length">
        <li v-for="(item, index) in items"
            @click="changeActive(index)"
            class="stm-lms-autocomplete-drips"
            v-bind:class="{\'active\' : item.active}">
            <div class="stm-lms-autocomplete-drips--title">
                Drip Content {{index + 1}}
                <i class="lnricons-cross" @click="items.splice(index, 1)"></i>
            </div>
            <div class="stm-lms-autocomplete-drips--content">

                <div class="stm-lms-autocomplete-drips--parents" v-if="item.parent">
                    <div class="stm-lms-autocomplete-drips--parent">
                        {{item.parent.title}}
                    </div>
                    <div class="stm-lms-autocomplete-drips--childs">
                        <div class="stm-lms-autocomplete-drips--child" v-for="(child, child_index) in item.childs">
                            {{child.title}}
                            <i class="lnricons-cross" @click="item.childs.splice(child_index, 1)"></i>
                        </div>
                    </div>
                </div>

                <v-select v-model="search"
                          label="title"
                          :options="options"
                          v-if="item.active"
                          @search="onSearch">
                </v-select>
            </div>
        </li>
    </ul>

</div>