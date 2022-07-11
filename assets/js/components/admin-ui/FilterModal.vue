<template>
    <transition name="fade">
        <div
            v-show="open"
            class="u-modal__backdrop"
        >
            <div class="u-modal u-modal__filter">
                <div class="u-modal__header">
                    <h3 class="heading--md u-m0">
                        {{ header }}
                    </h3>
                    <button
                        class="btn btn__secondary u-ml15"
                        v-on:click="closeModal"
                    >
                        <i class="fa fa-times-circle" />
                        Close
                    </button>
                </div>

                <div class="u-modal__body u-modal__body--grey u-pt30 u-pb30">
                    <div class="filter__item">
                        <div class="filter__label title bold">
                            Status
                        </div>
                        <div class="filter__controller">
                            <check-box
                                v-model="status"
                                value="true"
                                label="Draft"
                                class="u-mr15"
                            />
                            <check-box
                                v-model="status"
                                value="true"
                                label="Published"
                            />
                        </div>
                    </div>
                    <div class="filter__item">
                        <div class="filter__label title bold">
                            Language
                        </div>
                        <div class="filter__controller">
                            <v-select
                                v-model="lang"
                                style="width:146px"
                                v-bind:options="['en', 'es']"
                                v-bind:clearable="false"
                                v-bind:components="{ OpenIndicator }"
                                placeholder="Select a language..."
                            />
                        </div>
                    </div>
                    <div class="filter__item">
                        <div class="filter__label title bold">
                            Sort by
                        </div>
                        <div class="filter__controller">
                            <v-select
                                v-model="date"
                                style="width:119px"
                                v-bind:options="['Date Created', 'Date Updated']"
                                v-bind:clearable="false"
                                v-bind:components="{ OpenIndicator }"
                                class="u-mr10"
                            />
                            <v-select
                                v-model="sort"
                                style="width:119px"
                                v-bind:options="['Descending', 'Ascending']"
                                v-bind:clearable="false"
                                v-bind:components="{ OpenIndicator }"
                            />
                        </div>
                    </div>
                    <div class="filter__item">
                        <div class="filter__label title bold">
                            Results per page
                        </div>
                        <div class="filter__controller">
                            <v-select
                                v-model="perPage"
                                style="width:52px"
                                v-bind:options="['5', '10', '15']"
                                v-bind:clearable="false"
                                v-bind:components="{ OpenIndicator }"
                            />
                        </div>
                    </div>

                    <div class="u-flex u-flex-space-fe u-mt30">
                        <button class="btn btn__default">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import CheckBox from './CheckBox.vue';

export default {
    components: {
        CheckBox
    },
    props: {
        header: {
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            status: 'Draft',
            open: false,
            perPage: 10,
            date: 'Date Created',
            sort: 'Descending',
            lang: null,
            OpenIndicator: {
                render: createElement => createElement('span', ''),
            },
        };
    },
    mounted() {
        this.$bus.$on('open-filter-modal', () => {
            document.body.style.overflow = 'hidden';
            this.open = true;
        });
        //Keydown listener
        this.listener = (e) => this.keysTrigger(e.key);
        window.addEventListener('keydown', this.listener);
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this.listener);
    },
    methods: {
        openModal() {
            this.open = true;
        },
        closeModal() {
            this.open = false;
            document.body.style.overflow = '';
        },
        keysTrigger(key) {
            if (key === 'Escape') {
                this.closeModal();
            }
        },
    }
};
</script>
