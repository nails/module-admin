<template>
    <div class="w-100">
        <button
            class="sidenav__item "
            v-bind:class="{
                'sidenav__item--active': isOpen,
                'sidenav__item--open': isOpen
            }"
            v-on:click="toggle"
        >
            <!-- Handler -->
            <slot name="handler" />

            {{ title }}

            <div
                class="sidenav__caret"
                v-bind:class="{
                    'sidenav__caret--open': isOpen
                }"
            />
        </button>
        <ul v-show="isOpen">
            <transition>
                <slot />
            </transition>
        </ul>
    </div>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            required: true,
        },
        icon: {
            type: String,
            required: true,
        },
        isOpen: {
            type: Boolean,
            default: false,
            required: false
        }
    },
    methods: {
        toggle() {
            this.$emit('toggle', this.isOpen);
            this.$emit('change');
        }
    }
};
</script>