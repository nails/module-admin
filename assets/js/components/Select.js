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
        this.$openEl = null;
        this.fitAttempts = 0;

        // Viewport coords + position:fixed. Mixing getBoundingClientRect with
        // scrollY for an absolute menu is wrong until the first scroll, which
        // is when Select2's own handler "snaps" it. Capture-phase scroll so
        // we run after Select2 and after nested overflow containers.
        this.onReposition = () => {
            if (!this.$openEl) {
                return;
            }
            if (this.raf) {
                return;
            }
            this.raf = window.requestAnimationFrame(() => {
                this.raf = null;
                if (this.$openEl) {
                    this.fitMenu(this.$openEl);
                }
            });
        };

        $(document)
            .on('select2-open.select2-shell select2:open.select2-shell', (e) => {
                this.open($(e.target));
            })
            .on('select2-loaded.select2-shell', (e) => {
                this.scheduleFit($(e.target));
            })
            .on('select2-close.select2-shell select2:close.select2-shell', () => {
                this.close();
            });

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Track the open field and keep the menu pinned to it
     * @param {jQuery} $el The original select/input
     * @returns {Select}
     */
    open($el) {

        this.$openEl = $el;
        this.fitAttempts = 0;
        window.addEventListener('scroll', this.onReposition, true);
        window.addEventListener('resize', this.onReposition);
        this.scheduleFit($el);
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Fit after Select2 has painted the menu
     * @param {jQuery} $el The original select/input
     * @returns {Select}
     */
    scheduleFit($el) {

        window.requestAnimationFrame(() => {
            this.fitMenu($el);
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

        if (inst && inst.dropdown && inst.container) {
            $container = inst.container;
            $drop = inst.dropdown;
        } else if (inst && inst.$dropdown && inst.$container) {
            $container = inst.$container;
            $drop = inst.$dropdown;
        } else if ($el.data('select2')) {
            $container = $el.select2('container');
            $drop = $('#select2-drop');
        }

        if (!$container.length || !$drop.length || !$drop.is(':visible')) {
            if (this.$openEl && this.fitAttempts < 8) {
                this.fitAttempts += 1;
                this.scheduleFit($el);
            }
            return this;
        }

        this.fitAttempts = 0;

        let rect = $container[0].getBoundingClientRect();
        let width = rect.width;
        let above = $drop.hasClass('select2-drop-above')
            || $drop.hasClass('select2-dropdown--above')
            || $container.hasClass('select2-drop-above')
            || $container.hasClass('select2-container--above');

        // Cover the field's joining 1px border so we can leave that border
        // in-flow (zeroing it collapsed the row by a pixel).
        let join = 1;
        let dropHeight = $drop.outerHeight();
        let top = above
            ? rect.top - dropHeight + join
            : rect.bottom - join;

        $drop.css({
            position: 'fixed',
            width: width + 'px',
            left: rect.left + 'px',
            top: top + 'px'
        });

        this.$shell
            .css({
                top: (above ? top : rect.top) + 'px',
                left: rect.left + 'px',
                width: width + 'px',
                height: (rect.height + dropHeight - join) + 'px'
            })
            .removeAttr('hidden');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Hide the shared focus ring
     * @returns {Select}
     */
    close() {

        this.$openEl = null;
        window.removeEventListener('scroll', this.onReposition, true);
        window.removeEventListener('resize', this.onReposition);
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
