/* export SearchBoxes */

/* globals $, jQuery */
class SearchBoxes {

    /**
     * Construct SearchBoxes
     * @return {SearchBoxes}
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

        return this;
    }
}

export default SearchBoxes;
