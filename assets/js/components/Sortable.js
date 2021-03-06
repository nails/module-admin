/* export Sortable */

/* globals $, jQuery */
class Sortable {

    /**
     * Construct Sortable
     * @return {Sortable}
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
     * Initialise
     * @param {HTMLElement} domElement
     * @returns {Sortable}
     */
    init(domElement) {
        $('.js-admin-sortable:not(.js-admin-sortable--processed)', domElement)
            .addClass('js-admin-sortable--processed')
            .each(function() {

                let $item = $(this);
                let handle = $item.data('handle') || null;
                let axis = $item.data('axis') || 'y';
                let containment = $item.data('containment') || 'parent';

                $item
                    .sortable({
                        handle: handle,
                        axis: axis,
                        containment: containment,
                        forceHelperSize: true,
                        helper: function(e, tr) {
                            let $originals = tr.children();
                            let $helper = tr.clone();
                            $helper
                                .children()
                                .each(function(index) {
                                    // Set helper cell sizes to match the original sizes
                                    $(this).width($originals.eq(index).outerWidth());
                                });
                            return $helper;
                        },
                        stop: function() {
                            $item.trigger('sortable:sort');
                        }
                    })
                    .on('sortable:sort', () => {
                        $item
                            .find('.js-admin-sortable__order')
                            .each(function(index) {
                                $(this).val(index);
                            });
                    });

                if (handle) {
                    $item.find(handle).addClass('js-admin-sortable__handle');
                }
            });

        return this;
    }
}

export default Sortable;
