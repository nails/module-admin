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
                window.cancelAnimationFrame(this.raf);
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
        //  Select2 has just run positionDropdown(); overwrite it now and
        //  again on the next frame once the results list has a height.
        this.fitMenu($el);
        this.scheduleFit($el);
        window.setTimeout(() => {
            if (this.$openEl && this.$openEl[0] === $el[0]) {
                this.fitMenu($el);
            }
        }, 0);
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

        //  Do not call $el.select2('container'): SelectInstance used to
        //  overwrite data('select2'), and even now the open widget is the
        //  .select2-dropdown-open container + singleton #select2-drop.
        let $drop = $('#select2-drop');
        let $container = $('.select2-container.select2-dropdown-open').first();
        let inst = $el.data('select2');

        if (!$container.length && inst && inst.container) {
            $container = inst.container;
        }
        if (!$drop.length && inst && inst.dropdown) {
            $drop = inst.dropdown;
        }
        if (!$container.length) {
            $container = $el.next('.select2-container');
        }

        if (!$container.length || !$drop.length || !$drop.is(':visible')) {
            if (this.$openEl && this.fitAttempts < 8) {
                this.fitAttempts += 1;
                this.scheduleFit($el);
            }
            return this;
        }

        this.fitAttempts = 0;

        //  Force layout. Offset/rect can be 0,0 for a field that was in a
        //  display:none tab until this click's ancestor reflow.
        void $container[0].offsetWidth;

        let rect = $container[0].getBoundingClientRect();
        let width = rect.width;
        let join = 1;
        let spaceBelow = window.innerHeight - rect.bottom;
        let spaceAbove = rect.top;
        let $results = $drop.find('.select2-results');
        $results.css('max-height', '');
        let naturalHeight = $drop.outerHeight();
        let above = naturalHeight > spaceBelow && spaceAbove > spaceBelow;
        let available = (above ? spaceAbove : spaceBelow) - join;

        if (available < 80) {
            above = spaceAbove > spaceBelow;
            available = Math.max(spaceBelow, spaceAbove, 80) - join;
        }

        $results.css('max-height', Math.floor(Math.max(available - 8, 80)) + 'px');
        $drop.toggleClass('select2-drop-above', above);
        $container.toggleClass('select2-drop-above', above);

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
                new SelectInstance(
                    this.adminController,
                    element
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
                width: '100%'
            });
    }

    destroy() {
        this.$input
            .select2('destroy');
    }
}

export default Select;
