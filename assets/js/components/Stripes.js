/* export Stripes */

/* globals $, jQuery */
class Stripes {

    /**
     * Construct Stripes
     * @return {Stripes}
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
     * Inits Stripes
     * @param {HTMLElement} domElement
     * @returns {Stripes}
     */
    init(domElement) {

        $('fieldset,.fieldset', domElement)
            .each(function() {
                stripeFields($('div.field:visible', this));
            });

        //  Section tabs dump fields straight into the panel; they still need
        //  the same odd/even paint as a fieldset. Direct children only, so a
        //  nested fieldset keeps its own sequence.
        $('div.tab-page', domElement)
            .each(function() {
                stripeFields($(this).children('div.field:visible'));
            });

        return this;
    }
}

/**
 * @param {jQuery} $fields
 * @returns {void}
 */
function stripeFields($fields) {
    $fields.removeClass('odd even');
    $fields.filter(':odd').addClass('odd');
    $fields.filter(':even').addClass('even');
}

export default Stripes;
