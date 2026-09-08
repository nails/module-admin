<template>
    <label class="wrapper flex items-center">
        {{ label }}
        <input
            class="checkbox"
            type="checkbox"
            v-bind:checked="isChecked"
            v-bind:value="value"
            v-on:change="updateInput"
        >
        <span class="checkmark" />
    </label>
</template>

<script>
export default {
    props: {
        value: {type: String, default: ''},
        modelValue: {type: [String, Array, Boolean], default: ''},
        label: {type: String, required: true},
        trueValue: {type: Boolean, default: true},
        falseValue: {type: Boolean, default: false}
    },
    emits: ['update:modelValue'],
    computed: {
        isChecked() {
            if (this.modelValue instanceof Array) {
                return this.modelValue.includes(this.value);
            }
            // Note that `true-value` and `false-value` are camelCase in the JS
            return this.modelValue === this.trueValue;
        }
    },
    methods: {
        updateInput(event) {
            let isChecked = event.target.checked;

            if (this.modelValue instanceof Array) {
                let newValue = [...this.modelValue];

                if (isChecked) {
                    newValue.push(this.value);
                } else {
                    newValue.splice(newValue.indexOf(this.value), 1);
                }

                this.$emit('update:modelValue', newValue);
            } else {
                this.$emit('update:modelValue', isChecked ? this.trueValue : this.falseValue);
            }
        }
    }
};
</script>
