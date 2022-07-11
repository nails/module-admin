<template>
    <div class="sidebar">
        <div class="sidenav">
            <vue-nestable
                keyProp="label"
                v-model="sidebarItems"
                v-bind:max-depth="1"
                v-bind:children-prop="null"
                v-on:change="changeOrder"
            >
                <template
                    slot-scope="{ item }"
                >
                    <menu-collapse
                        v-if="item.actions && item.actions.length"
                        v-bind:title="item.label"
                        v-bind:icon="item.icon"
                        v-bind:is-open="item.is_open"
                        v-on:change="changeOrder"
                        v-on:toggle="toggleMenuItem(item)"
                    >
                        <template slot="handler">
                            <VueNestableHandle
                                v-bind:item="item"
                            >
                                <span
                                    class="sidenav__icon"
                                    v-on:click.stop="() => {}"
                                >
                                    <span class="handle fa fa-bars" />
                                    <i
                                        v-bind:class="[
                                            `fa ${item.icon || 'fa-cog'}`,
                                            {
                                            // 'sidenav__item--open': open
                                            }
                                        ]"
                                    />
                                </span>
                            </VueNestableHandle>
                        </template>
                        <ul>
                            <li
                                v-for="(action, index) in item.actions"
                                v-bind:key="index"
                            >
                                <a
                                    v-bind:href="action.url"
                                    class="sidenav__subitem"
                                >
                                    {{ action.label }}
                                    <span class="u-flex">
                                        <span
                                            v-for="(alert, i) in action.alerts"
                                            v-bind:key="i"
                                            class="sidenav__count hint--bottom"
                                            v-bind:class="`sidenav__count--${alert.severity}`"
                                            v-bind:aria-label="alert.label"
                                        >
                                            {{ alert.value }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </menu-collapse>
                    <a
                        v-else
                        href="#"
                        class="sidenav__item"
                        v-bind:class="{'sidenav__item--active': item.is_open}"
                    >
                        <!-- Handler -->
                        <VueNestableHandle
                            v-bind:item="item"
                        >
                            <span class="sidenav__icon">
                                <span class="handle fa fa-bars" />
                                <i
                                    class="fa"
                                    v-bind:class="`${item.icon || 'fa-cog'}`"
                                />
                            </span>
                        </VueNestableHandle>
                        {{ item.label }}
                    </a>
                </template>
            </vue-nestable>
            <ul class="mt-3">
                <li>
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="resetNav"
                    >
                        Reset Nav
                    </a>
                </li>
                <li>
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="openAll"
                    >
                        Open All
                    </a>
                </li>
                <li>
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="closeAll"
                    >
                        Close All
                    </a>
                </li>
            </ul>
        </div>
        <div
            v-show="isMobileMenuOpen"
            class="sidenav sidenav__mobile"
        >
            <ul>
                <li
                    v-for="item in sidebarItems"
                    v-bind:key="item.label"
                    class="sidenav__list-item"
                >
                    <menu-collapse
                        v-if="item.actions && item.actions.length"
                        v-bind:title="item.label"
                        v-bind:icon="item.icon"
                        v-bind:is-open="item.is_open"
                        v-on:toggle="toggleMenuItem(item)"
                    >
                        <template slot="handler">
                            <span class="sidenav__icon">
                                <i
                                    class="fa"
                                    v-bind:class="`${item.icon || 'fa-cog'}`"
                                />
                            </span>
                        </template>
                        <ul>
                            <li
                                v-for="(action, index) in item.actions"
                                v-bind:key="index"
                            >
                                <a
                                    v-bind:href="action.url"
                                    class="sidenav__subitem"
                                >
                                    {{ action.label }}
                                    <span class="u-flex">
                                        <span
                                            v-for="(alert, i) in action.alerts"
                                            v-bind:key="i"
                                            class="sidenav__count hint--bottom"
                                            v-bind:class="`sidenav__count--${alert.severity}`"
                                            v-bind:aria-label="alert.label"
                                        >
                                            {{ alert.value }}
                                        </span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </menu-collapse>
                    <a
                        v-else
                        href="#"
                        class="sidenav__item"
                        v-bind:class="{'sidenav__item--active': item.is_open}"
                    >
                        <span class="sidenav__icon">
                            <i
                                class="fa"
                                v-bind:class="`${item.icon || 'fa-cog'}`"
                            />
                        </span>
                        {{ item.label }}
                    </a>
                </li>
                <li class="mt-5">
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="resetNav"
                    >
                        Reset Nav
                    </a>
                </li>
                <li>
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="openAll"
                    >
                        Open All
                    </a>
                </li>
                <li class="u-mb10">
                    <a
                        href="#"
                        class="sidenav__item sidenav__item--small text-center"
                        v-on:click="closeAll"
                    >
                        Close All
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import { VueNestable, VueNestableHandle } from 'vue-nestable';
import VueSimpleScrollbar from 'vue-simple-scrollbar';
export default {
    components: {
        VueNestable,
        VueNestableHandle,
        VueSimpleScrollbar
    },
    props: {
        menuItems: {
            type: Array,
            required: false, //true
            default: () => []
        },
    },
    data() {
        return {
            items: [],
            isMobileMenuOpen: false
        };
    },
    computed: {
        sidebarItems: {
            get: function() {
                return this.items;
            },
            set: function(newValue) {
                this.items = newValue;
            }
        }
    },
    mounted() {
        this.$bus.$on('toggle-mobile-menu', (value) => {
            document.body.style.overflow = value ? 'hidden' : 'auto';
            this.isMobileMenuOpen = value;
        });
    },
    created() {
        this.items = [...this.menuItems];
    },
    methods: {
        async resetNav() {
            await axios.post('/api/admin/nav/reset', {});
            this.$swal({
                title: 'Reset complete',
                text: 'Your navigation has been reset, changes will take hold on the next page reload.',
                showCancelButton: true,
                showCloseButton: true,
                cancelButtonText: 'Close',
                confirmButtonText: 'Reload',
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        },
        openAll() {
            this.sidebarItems = [
                ...this.sidebarItems.map(item => {
                    return {
                        ...item,
                        is_open: true
                    }
                })
            ];
        },
        closeAll() {
            this.sidebarItems = [
                ...this.sidebarItems.map(item => {
                    return {
                        ...item,
                        is_open: false
                    }
                })
            ];
        },
        toggleMenuItem(item) {
            let updatedItem = this.sidebarItems.find(el => el.label === item.label);
            updatedItem.is_open = !updatedItem.is_open;
            this.sidebarItems = [
                ...this.sidebarItems
            ];
        },
        changeOrder() {
            const obj = {};
            const keys = this.sidebarItems.map(item => item.label);
            console.log(keys);
            for (let i = 0; i < keys.length; i++) {
                obj[keys[i]] = this.sidebarItems[i].is_open;
            }
            axios.post('/api/admin/nav/save', obj)
                .then(({data}) => {

                });
        },
    }
};
</script>
