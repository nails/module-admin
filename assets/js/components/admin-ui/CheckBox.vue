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
    model: {
        prop: 'modelValue',
        event: 'change'
    },
    props: {
        value: {type: String, default: ''},
        modelValue: {type: String, default: ''},
        label: {type: String, required: true},
        trueValue: {type: Boolean, default: true},
        falseValue: {type: Boolean, default: false}
    },
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

                this.$emit('change', newValue);
            } else {
                this.$emit('change', isChecked ? this.trueValue : this.falseValue);
            }
        }
    }
};
</script>
