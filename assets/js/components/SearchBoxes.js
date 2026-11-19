/* export SearchBoxes */

/* globals $, jQuery */
class SearchBoxes {

    /**
     * Construct SearchBoxes
     * @return {SearchBoxes}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.viewportBound = false;

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits SearchBoxes
     * @returns {SearchBoxes}
     */
    init(domElement) {

        var timeout;

        //  Bind submit to select changes
        $('div.search select:not(.processed), div.search input[type=checkbox]:not(.processed)', domElement)
            .addClass('processed')
            .on('change', function() {
                var $form = $(this).closest('form');
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    $form.submit();
                }, 500);
            });

        // --------------------------------------------------------------------------

        //  Show mask when submitting form
        $('div.search form:not(.processed)', domElement)
            .addClass('processed')
            .on('submit', function() {
                $(this).closest('div.search').find('div.mask').show();
            });

        // --------------------------------------------------------------------------

        //  Filter Checkboxes
        $('div.search .filterOption input[type=checkbox]:not(.processed)', domElement)
            .addClass('processed')
            .on('change', function() {

                if ($(this).is(':checked')) {
                    $(this).closest('.filterOption').addClass('checked');

                } else {
                    $(this).closest('.filterOption').removeClass('checked');
                }
            });

        //  Initial styles
        $('div.search .filterOption input[type=checkbox]:not(.processed)', domElement)
            .addClass('processed')
            .each(function() {

                if ($(this).is(':checked')) {
                    $(this).closest('.filterOption').addClass('checked');

                } else {
                    $(this).closest('.filterOption').removeClass('checked');
                }
            });

        // --------------------------------------------------------------------------

        //  Phone filter modal. The launch control is hidden from the desktop
        //  layout, so these listeners are idle there.
        var self = this;

        $('.search__launch:not(.processed)', domElement)
            .addClass('processed')
            .on('click', function() {
                self.open($(this).closest('.search'));
            });

        $('.search [data-search-close]:not(.processed)', domElement)
            .addClass('processed')
            .on('click', function() {
                self.close($(this).closest('.search'), true);
            });

        this.bindViewport();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * The phone layout is the one that shows the launch control. Keeping the
     * check on that element means the breakpoint lives in the stylesheet.
     * @returns {Boolean}
     */
    isPhoneLayout() {

        var launch = document.querySelector('.search__launch');

        if (!launch) {
            return false;
        }

        return window.getComputedStyle(launch).display !== 'none';
    }

    // --------------------------------------------------------------------------

    /**
     * Document listeners, once per page.
     * @returns {SearchBoxes}
     */
    bindViewport() {

        if (this.viewportBound) {
            return this;
        }

        this.viewportBound = true;
        this.wasPhone = this.isPhoneLayout();

        var self = this;

        $(window).on('resize.searchFilters orientationchange.searchFilters', function() {
            var phone = self.isPhoneLayout();

            if (phone === self.wasPhone) {
                return;
            }

            self.wasPhone = phone;

            // Selects are measured only while visible. Crossing back to the
            // desktop layout reveals the form, so Select2 has to run then.
            if (phone) {
                return;
            }

            $('.search').each(function() {
                var $search = $(this);

                if ($search.find('.search__modal').hasClass('is-open')) {
                    self.close($search, false);
                }

                self.adminController.refreshUi(this);
            });
        });

        $(document).on('keydown.searchFilters', function(e) {
            if (e.key !== 'Escape' || !$('.search__modal.is-open').length) {
                return;
            }

            // Select2 uses Escape to close its own menu.
            if ($('#select2-drop').is(':visible') || $('.select2-container--open').length) {
                return;
            }

            $('.search__modal.is-open').each(function() {
                self.close($(this).closest('.search'), true);
            });
        });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Open the phone filter modal and let Select2 measure the now-visible fields.
     * @param {jQuery} $search
     * @returns {SearchBoxes}
     */
    open($search) {

        var $modal = $search.find('.search__modal');
        var titleId = $modal.find('.search__title').attr('id');

        $modal
            .addClass('is-open')
            .attr({
                'role': 'dialog',
                'aria-modal': 'true',
                'aria-labelledby': titleId || null
            });

        $search.find('.search__launch').attr('aria-expanded', 'true');
        $('body').addClass('no-scroll');

        this.adminController.refreshUi($modal.get(0));

        window.setTimeout(function() {
            var $field = $modal.find('input[name="keywords"]');

            if ($field.length) {
                $field.trigger('focus');
                return;
            }

            $modal.find('[data-search-close]').first().trigger('focus');
        }, 30);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Close the phone filter modal.
     * @param {jQuery} $search
     * @param {Boolean} restoreFocus
     * @returns {SearchBoxes}
     */
    close($search, restoreFocus) {

        var $modal = $search.find('.search__modal');

        $modal
            .removeClass('is-open')
            .removeAttr('role')
            .removeAttr('aria-modal')
            .removeAttr('aria-labelledby');

        $search.find('.search__launch').attr('aria-expanded', 'false');

        if (!$('.search__modal.is-open').length) {
            $('body').removeClass('no-scroll');
        }

        if (restoreFocus) {
            $search.find('.search__launch').trigger('focus');
        }

        return this;
    }
}

export default SearchBoxes;
