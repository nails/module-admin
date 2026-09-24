/* export Select */

/* globals $, jQuery */
class Select {

    /**
     * Construct Select
     * @return {Select}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.$shell = $('<div class="select2-focus-shell" hidden />')
            .appendTo(document.body);

        // Select2 3 mounts #select2-drop on <body> and sizes it with rounded
        // outerWidth, so the menu can sit a fraction of a pixel off the field
        // and the two focus rings stack. Snap the menu to the field and draw
        // one ring around both.
        $(document)
            .on('select2-open.select2-shell select2:open.select2-shell', (e) => {
                this.fitMenu($(e.target));
            })
            .on('select2-loaded.select2-shell', (e) => {
                this.fitMenu($(e.target));
            })
            .on('select2-close.select2-shell select2:close.select2-shell', () => {
                this.hideShell();
            });

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Align the open menu with the field and size the shared focus ring
     * @param {jQuery} $el The original select/input
     * @returns {Select}
     */
    fitMenu($el) {

        let $container = $();
        let $drop = $();
        let inst = $el.data('select2');

        if (inst && inst.$dropdown && inst.$container) {
            $container = inst.$container;
            $drop = inst.$dropdown;
        } else if ($el.data('select2')) {
            $container = $el.select2('container');
            $drop = $('#select2-drop');
        }

        if (!$container.length || !$drop.length || !$drop.is(':visible')) {
            return this;
        }

        let rect = $container[0].getBoundingClientRect();
        let width = rect.width;
        let left = rect.left + window.scrollX;
        let above = $drop.hasClass('select2-drop-above')
            || $drop.hasClass('select2-dropdown--above')
            || $container.hasClass('select2-drop-above')
            || $container.hasClass('select2-container--above');

        $drop.css({
            width: width + 'px',
            left: left + 'px'
        });

        let dropHeight = $drop.outerHeight();
        let top = above
            ? rect.top + window.scrollY - dropHeight
            : rect.bottom + window.scrollY;

        $drop.css({
            top: top + 'px'
        });

        this.$shell
            .css({
                top: (above ? top : rect.top + window.scrollY) + 'px',
                left: left + 'px',
                width: width + 'px',
                height: (rect.height + dropHeight) + 'px'
            })
            .removeAttr('hidden');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Hide the shared focus ring
     * @returns {Select}
     */
    hideShell() {

        this.$shell.attr('hidden', true);
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
