/* export Select */

/* globals $, jQuery */
class Select {

    /**
     * Construct Select
     * @return {Select}
     */
    constructor(adminController) {

        this.adminController = adminController;

        // Select2 mounts the menu on <body>, so the field and dropdown cannot
        // share a radius or focus ring. Re-parent it into the container on
        // open; CSS then lays them out as one control (including opening up).
        $(document)
            .on('select2:open.select2-shell', (e) => {
                this.nestDropdown($(e.target));
            })
            .on('select2-open.select2-shell', (e) => {
                this.nestDropdown($(e.target));
            });

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Put the open dropdown inside the Select2 container
     * @param {jQuery} $el The original select/input
     * @returns {Select}
     */
    nestDropdown($el) {

        let inst = $el.data('select2');

        if (inst && inst.$dropdown && inst.$container) {
            inst.$dropdown.appendTo(inst.$container);
            return this;
        }

        // v3: container() + #select2-drop
        let $container = $el.data('select2') ? $el.select2('container') : $();
        let $drop = $('#select2-drop');

        if ($container && $container.length && $drop.length) {
            $drop.appendTo($container);
        }

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
