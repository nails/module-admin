<template>
    <transition name="fade">
        <div
            v-show="open"
            class="u-modal__backdrop"
        >
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
                    class="u-modal-tab"
                >
                    <li
                        v-for="(item, index) in sections"
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
                    <ul
                        v-if="filteredItems.length"
                        class="create-item__list"
                    >
                        <li
                            v-for="(item, index) in filteredItems"
                            v-bind:key="index"
                            class="create-item"
                        >
                            <div class="u-flex w-100">
                                <div
                                    class="create-item__badge u-mr10"
                                    v-bind:class="{
                                        'create-item__badge--light': item.type === 'secondary'
                                    }"
                                >
                                    <i
                                        class="fa"
                                        v-bind:class="`fa-${item.icon}`"
                                    />
                                </div>
                                <div class="w-100">
                                    <div class="create-item__title u-m0">
                                        {{ item.label }}
                                    </div>
                                    <div class="create-item__description u-m0">
                                        {{ item.info }}
                                    </div>
                                </div>
                            </div>
                            <a
                                v-for="(btn, i) in item.buttons"
                                v-bind:key="i"
                                v-bind:href="btn.url"
                                class="btn u-ml5 u-md-ml10"
                                v-bind:class="`btn__${btn.type}`"
                            >
                                <i
                                    class="fa"
                                    v-bind:class="`fa-${btn.icon}`"
                                />
                                <span class="btn__label">{{ btn.label }}</span>
                            </a>
                        </li>
                    </ul>
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
import axios from 'axios';
import debounce from 'debounce';

const PER_PAGE = 15;
export default {
    props: {
        placeholder: {
            type: String,
            required: false,
            default: 'Begin typing to search...'
        }
    },
    data() {
        return {
            open: false,
            sections: [],
            results: [],
            query: null,
            filter: null,
            loading: false,
            url: '/',
            dummyData: {
                'sections': [
                    {
                        'label': 'All',
                        'count': 1234
                    },
                    {
                        'label': 'Admin Section',
                        'count': 1234
                    },
                    {
                        'label': 'Site Content',
                        'count': 1234
                    }
                ],
                'results': [
                    {
                        'label': 'Manage Insight Articles',
                        'info': 'Admin Section',
                        'icon': 'map',
                        'type': 'primary',
                        'buttons': [
                            {
                                'label': 'Index',
                                'url': 'https://example.com',
                                'icon': 'external-link-alt',
                                'type': 'primary'
                            },
                            {
                                'label': 'Create',
                                'url': 'https://example.com',
                                'icon': 'cog',
                                'type': 'secondary'
                            }
                        ]
                    },
                    {
                        'label': 'Another Result',
                        'info': 'Admin Section',
                        'icon': 'map',
                        'type': 'secondary',
                        'buttons': [
                            {
                                'label': 'View',
                                'url': 'https://example.com',
                                'icon': 'external-link-alt',
                                'type': 'primary'
                            },
                            {
                                'label': 'Create',
                                'url': 'https://example.com',
                                'icon': 'cog',
                                'type': 'secondary'
                            }
                        ]
                    }
                ]
            }
        };
    },
    computed: {
        filteredItems() {
            if (!this.filter || this.filter.toLowerCase() === 'all') {
                return this.results;
            }
            return this.results.filter(item => item.info === this.filter);
        }
    },
    watch: {
        query(val) {
            this.url = '/search';
            this.sections = [];
            this.results = [];
            this.searching();
        }
    },
    mounted() {
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
            if (!this.query || this.query === '') {
                this.sections = [];
                this.results = [];
                return false;
            }
            this.doSearch();
        }, 500),
        doSearch() {
            // this.loading = true;
            // axios.get(this.url, {
            //     params: {
            //         query: this.query.replace(/\s+/g, '+'),
            //         perPage: PER_PAGE
            //     }
            // })
            //     .then(({data}) => {
            //         //
            //         this.loading = false;
            //     })
            //     .catch((error) => {
            //         //
            //         this.loading = false;
            //         this.$nextTick(() => {
            //             this.$refs.input.focus();
            //         });
            //     });

            this.sections = this.dummyData['sections'];
            this.results = this.dummyData['results'];
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
            if (e.key === 'Escape') {
                this.closeModal();
            }
            if (e.metaKey && e.key === 'k') {
                this.openModal();
            }
        },
    }
};
</script>
