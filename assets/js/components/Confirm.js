/* export Confirm */

/* globals $, jQuery */
class Confirm {

    /**
     * Construct Confirm
     * @return {Confirm}
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
     * Inits Confirm
     * @param domElement {HTMLElement} The domElement to focus on
     * @returns {Confirm}
     */
    init(domElement) {

        $('a.confirm:not(.confirm--processed)', domElement)
            .addClass('confirm--processed')
            .on('click', (e) => {

                let $link = $(e.currentTarget);
                let body = $link.data('body') || 'Please confirm you\'d like to continue with this action.';
                let title = $link.data('title') || 'Are you sure?';

                body.replace(/\\n/g, '\n');

                if (body.length) {

                    $('<div>')
                        .html(body)
                        .dialog({
                            'title': title,
                            'resizable': false,
                            'draggable': false,
                            'modal': true,
                            'dialogClass': 'no-close',
                            'buttons': {
                                'OK': function() {

                                    let href = $link.attr('href');
                                    let target = $link.attr('target');

                                    if (target === '_blank') {
                                        window.open(href, '_blank');

                                    } else if (target === '_parent') {
                                        window.parent.location.href = href;

                                    } else if (target === '_top') {
                                        window.top.location.href = href;

                                    } else if (target) {
                                        window.open(href, target);

                                    } else {
                                        window.location.href = href;
                                    }

                                    $(this).dialog('close');

                                },
                                'Cancel': function() {
                                    $(this).dialog('close');
                                }
                            }
                        });

                    return false;

                } else {
                    //  No message, just let the event bubble as normal.
                    return true;
                }
            });

        return this;
    }
}

export default Confirm;
