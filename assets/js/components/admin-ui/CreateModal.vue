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
                <div class="u-modal__header">
                    <h3 class="heading--md u-m0">
                        {{ header }}
                    </h3>
                    <button
                        class="btn btn__secondary u-ml15"
                        v-on:click="closeModal"
                    >
                        <i class="fa fa-times-circle" />
                        <span class="btn__label">Close</span>
                    </button>
                </div>

                <div class="u-modal__body">
                    <ul class="create-item__list">
                        <li
                            v-for="(item, index) in items"
                            v-bind:key="index"
                            class="create-item"
                        >
                            <div class="u-flex w-100">
                                <div class="create-item__badge u-mr10">
                                    <i
                                        class="fa fa-map"
                                        v-bind:class="`${item.icon.class}`"
                                    />
                                </div>
                                <div class="w-100">
                                    <div class="create-item__title u-m0">
                                        {{ item.label }}
                                    </div>
                                    <div class="create-item__description u-m0">
                                        {{ item.description }}
                                    </div>
                                </div>
                            </div>
                            <a
                                v-bind:href="item.url"
                                class="btn btn__primary"
                            >
                                <i class="fa fa-plus-circle" />
                                <span class="btn__label">Create</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>

import API from '../API';
import services from '../Services'

export default {
    props: {
        header: {
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            open: false,
            items: []
        };
    },
    mounted() {
        this.$bus.$on('open-create-modal', () => {
            document.body.style.overflow = 'hidden';
            this.open = true;
        });
        //Keydown listener
        this.listener = (e) => this.keysTrigger(e.key);
        window.addEventListener('keydown', this.listener);

        services
            .apiRequest({
                url: API.UI.header.button.create
            })
            .then((response) => {
                this.items = response.data.data;
            })
            .catch((error) => {
                //  @todo (Pablo 2022-09-21) - handle error
                console.log('error', error);
            });

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
