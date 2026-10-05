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
                let sortsTableRows = $item.is('tbody');

                let options = {
                    handle: handle,
                    axis: axis,
                    containment: containment,
                    forceHelperSize: true,
                    helper: function(e, sorted) {
                        let $originals = sorted.children();
                        let $helper = sorted.clone();
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
                };

                // A dragged <tr> cannot keep a height once it leaves its table.
                // Lists keep the helper above; only row sortables get a table
                // wrapper, a visible placeholder, and cell heights.
                if (sortsTableRows) {
                    options.forcePlaceholderSize = true;
                    options.placeholder = 'js-admin-sortable__placeholder';
                    options.appendTo = 'body';
                    options.helper = function(e, tr) {
                        let $originals = tr.children();
                        let $helper = tr.clone();
                        let widths = [];
                        let heights = [];

                        // jQuery UI hides the original row before `start`. Record
                        // sizes while it is still in flow. A hidden <tr> still
                        // reports a height, but it is the collapsed one.
                        $originals.each(function() {
                            widths.push($(this).outerWidth());
                            heights.push($(this).outerHeight());
                        });
                        tr.data('sortableRowHeight', tr.outerHeight());
                        tr.data('sortableCellWidths', widths);
                        tr.data('sortableCellHeights', heights);

                        let $wrapper = $('<table class="js-admin-sortable__helper"><tbody></tbody></table>');
                        $wrapper.find('tbody').append($helper);
                        $wrapper
                            .width(tr.closest('table').outerWidth())
                            .appendTo('body');

                        // Sizes have to be applied once the clone is in the
                        // document, or the cell padding is not subtracted and
                        // the preview comes out wider than the table.
                        $helper.children().each(function(index) {
                            $(this)
                                .outerWidth(widths[index])
                                .outerHeight(heights[index]);
                        });

                        return $wrapper;
                    };
                    options.start = function(e, ui) {
                        if (!ui.item.is('tr')) {
                            return;
                        }

                        let $cells = ui.placeholder.children('td');
                        let height = ui.item.data('sortableRowHeight');

                        if ($cells.length && height) {
                            ui.placeholder.outerHeight(height);
                            $cells.outerHeight(height);
                        }

                        // forceHelperSize writes the <tr> size onto the wrapper
                        // after the helper is built. Put the table width back
                        // and re-apply the cell sizes now that padding applies.
                        if (ui.helper && ui.helper.hasClass('js-admin-sortable__helper')) {
                            let widths = ui.item.data('sortableCellWidths') || [];
                            let cellHeights = ui.item.data('sortableCellHeights') || [];

                            ui.helper.width(ui.item.closest('table').outerWidth());
                            ui.helper.find('tr').children().each(function(index) {
                                if (widths[index]) {
                                    $(this).outerWidth(widths[index]);
                                }
                                if (cellHeights[index]) {
                                    $(this).outerHeight(cellHeights[index]);
                                }
                            });
                            if (height) {
                                ui.helper.outerHeight(height);
                            }
                        }
                    };
                }

                $item
                    .sortable(options)
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
