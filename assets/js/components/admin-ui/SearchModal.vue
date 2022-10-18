<template>
    <transition name="fade">
        <div
            v-show="open"
            class="u-modal__inner"
        >
            <div
                class="u-modal__backdrop"
                v-on:click="closeModal"
            />
            <div class="u-modal">
                <div class="u-modal__header u-modal__header--search">
                    <div class="search-field">
                        <input
                            ref="input"
                            v-model="query"
                            type="text"
                            v-bind:placeholder="placeholder"
                        >
                    </div>
                    <button
                        v-if="query"
                        class="btn btn__secondary u-ml15"
                        v-on:click="closeModal"
                    >
                        <i class="fa fa-times-circle" />
                        <span class="btn__label">Close</span>
                    </button>
                </div>

                <ul
                    v-if="query"
                    class="u-modal-tab list-unstyled"
                >
                    <li
                        v-for="(item, index) in results"
                        v-bind:key="index"
                    >
                        <button
                            class="u-modal-tab__item"
                            v-bind:class="{
                                'u-modal-tab__item--active': filter === item.label || !filter && index === 0
                            }"
                            v-on:click="setFilter(item.label)"
                        >
                            {{ item.label }}
                            <i
                                class="u-modal-tab__count"
                                v-bind:class="{
                                    'u-modal-tab__count--brand': filter === item.label || !filter && index === 0
                                }"
                            >
                                {{ item.count }}
                            </i>
                        </button>
                    </li>
                </ul>

                <div
                    v-if="query"
                    class="u-modal__body u-modal__body--search u-pt0"
                >
                    <div
                        v-if="loading"
                        class="u-modal__body u-modal__body--empty">
                        <Loader />
                    </div>
                    <ul
                        v-else-if="filteredItems"
                        class="create-item__list list-unstyled"
                    >
                        <li
                            v-for="(item, index) in filteredItems"
                            v-bind:key="index"
                            class="create-item"
                        >
                            <div class="u-flex w-100">
                                <div
                                    class="create-item__badge u-mr10"
                                >
                                    <i
                                        class="fa"
                                        v-bind:class="item.icon['class']"
                                    />
                                </div>
                                <div class="u-flex u-flex-center-v w-100">
                                    <div class="create-item__title u-m0">
                                        <span v-html="item.label" />
                                        <div class="create-item__description u-mt1">
                                            <span v-html="item.description" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a
                                v-for="(btn, index) in item.actions"
                                v-bind:key="index"
                                v-bind:href="btn.url"
                                v-bind:target="btn.new_tab ? '_blank' : ''"
                                class="btn btn-primary u-ml5 u-md-ml10"
                            >
                                <i
                                    v-if="btn.icon['class']"
                                    class="fa"
                                    v-bind:class="btn.icon['class']"
                                />
                                <span class="btn__label">{{ btn.label }}</span>
                            </a>
                        </li>
                    </ul>
                    <div
                        v-else-if="searched"
                        class="u-modal__body u-modal__body--empty "
                    >
                        No results found
                    </div>
                    <div
                        v-else
                        class="u-pt20"
                    />
                </div>
            </div>
        </div>
    </transition>
</template>
<script>

import API from '../API';
import services from '../Services'
import debounce from 'debounce'

import Loader from '../../../svg/spinning-circles.svg';

const COMMAND_KEYS = ['k', 'Escape']

export default {
    props: {
        placeholder: {
            type: String,
            required: false,
            default: 'Begin typing to search...'
        }
    },
    components: {
        Loader
    },
    data() {
        return {
            open: false,
            results: [],
            query: null,
            filter: null,
            loading: false,
            searched: false,
            listener: null
        };
    },

    computed: {
        filteredItems() {
            return this.results.find(item => item.label === this.filter)?.results ?? [];
        },
    },

    watch: {
        query(val) {
            this.sections = [];
            this.searching();
        }
    },

    mounted() {

        // Add open listener
        this.$bus.$on('open-search-modal', () => {
            document.body.style.overflow = 'hidden';
            this.open = true;
            this.$nextTick(() => {
                this.$refs.input.focus();
            });
        });

        //Keydown listener
        this.listener = (e) => this.keysTrigger(e);
        window.addEventListener('keydown', this.listener);
    },


    beforeDestroy() {
        window.removeEventListener('keydown', this.listener);
    },

    methods: {
        searching: debounce(function() {
            if (!this.query || this.query === '' || this.query.length <= 2) {
                this.sections = [];
                this.results = [];
                return false;
            }
            this.doSearch();
        }, 500),
        doSearch() {
            this.loading = true;
            this.searched = false;
            services
                .apiRequest({
                    url: API.UI.header.button.search(this.query)
                })
                .then((response) => {

                    if (response.status !== 200) {
                        throw Error('There was an error searching. Please try agin soon or contact and administrator')
                    } else {
                        // Store results
                        this.results = response.data.data;

                        // Set first selected
                        this.filter = this.results[0].label;
                    }

                })
                .catch((error) => {
                    //  @todo (Pablo 2022-09-21) - handle error
                    console.log(error);
                })
                .finally(() => {
                    this.searched = true;
                    this.loading = false;
                });
        },
        setFilter(value) {
            this.filter = value;
        },
        openModal() {
            this.open = true;
            this.$nextTick(() => {
                this.$refs.input.focus();
            });
        },
        closeModal() {
            this.open = false;
            document.body.style.overflow = '';
        },

        keysTrigger(e) {

            // If escape close
            if (e.key === 'Escape') {
                this.closeModal();
                return;
            }

            // If event comes with ctrl/cmd modifier...
            if (e.ctrl || e.metaKey) {

                // Command keys maps the keys we want to overload their meta modifiers
                // If not in command keys, bail from the function
                if (!COMMAND_KEYS.includes(e.key) && (e.ctrl || e.metaKey)) {
                    return;
                }

                e.preventDefault();

                // Add your overloaded shortcut kere
                switch (e.key) {
                    case 'k':
                        this.openModal();
                    default:
                        return;
                }
            }

        }
    }
};
</script>
