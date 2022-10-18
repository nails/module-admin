<template>
    <button
        class="btn"
        v-bind:class="btnClass"
        v-on:click.prevent="openModal"
    >
        <slot />
    </button>
</template>
<script>
export default {
    props: {
        modalName: {
            type: String,
            required: true
        },
        btnClass: {
            type: String,
            default: '',
            required: false
        }
    },
    methods: {
        openModal() {
            this.$bus.$emit(`open-${this.modalName}-modal`);
        }
    }
};
</script>