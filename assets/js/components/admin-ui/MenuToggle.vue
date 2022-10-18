<template>
    <button
        class="btn btn__link color--white"
        v-on:click.prevent="toggleMobileMenu"
    >
        <i
            class="fa"
            v-bind:class="isOpen ? 'fa-times' : 'fa-bars'"
        />
    </button>
</template>
<script>
export default {
    data() {
        return {
            isOpen: false
        };
    },
    methods: {
        toggleMobileMenu() {
            this.isOpen = !this.isOpen;
            this.$bus.$emit('toggle-mobile-menu', this.isOpen);
        }
    }
};
</script>
