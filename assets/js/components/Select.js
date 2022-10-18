/* export Select */

/* globals $, jQuery */
class Select {

    /**
     * Construct Select
     * @return {Select}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Select
     * @param {HTMLElement} domElement
     * @returns {Select}
     */
    init(domElement) {

        $('select.select2:not(.select2-offscreen):not(.select2--processed):visible', domElement)
            .addClass('select2--processed')
            .each((index, element) => {
                $(element)
                    .data(
                        'select2',
                        new SelectInstance(
                            this.adminController,
                            element
                        )
                    );
            });

        return this;
    }
}

class SelectInstance {
    /**
     * Construct SearcherInstance
     *
     * @param {DOMElement} element
     */
    constructor(adminController, element) {

        this.adminController = adminController;
        this.$input = $(element);
        this.isMultiple = this.$input.data('multiple');
        this.isClearable = this.$input.data('clearable');
        this.placeholder = this.$input.data('placeholder') || 'Search for an item';

        this.$input
            .select2({
                placeholder: this.placeholder,
                multiple: this.isMultiple,
                allowClear: this.isClearable,
                width: this.$input.outerWidth()
            });
    }

    destroy() {
        this.$input
            .select2('destroy');
    }
}

export default Select;
