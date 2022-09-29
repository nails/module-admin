/* export Confirm */

/* globals $, jQuery */
class Confirm {

    /**
     * Construct Confirm
     * @return {Confirm}
     */
    constructor(adminController) {
        this.adminController = adminController;
        this.adminController
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

        if (!this.modal) {
            this.modal = this.adminController.getInstance('Modal').create();
        }

        $('a.confirm:not(.confirm--processed)', domElement)
            .addClass('confirm--processed')
            .on('click', (e) => {

                let $link = $(e.currentTarget);
                let body = $link.data('body') || 'Please confirm you\'d like to continue with this action.';
                let title = $link.data('title') || 'Are you sure?';

                body.replace(/\\n/g, '\n');

                if (body.length) {

                    this.modal
                        .setTitle(title)
                        .setBody(body)
                        .clearActions()
                        .addAction('OK', ['btn-primary'], (event, modal) => {
                            window.location.href = $link.attr('href');
                        })
                        .addAction('Cancel', ['btn-danger'], (event, modal) => {
                            modal.hide();
                        })
                        .show();

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
