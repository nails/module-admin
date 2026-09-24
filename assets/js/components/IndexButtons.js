/* export IndexButtons */

/* globals $, jQuery */
class IndexButtons {

    /**
     * Construct IndexButtons
     * @return {IndexButtons}
     */
    constructor(adminController) {

        adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    /**
     * Inits the index buttoins
     * @param {HTMLElement} domElement
     * @returns {IndexButtons}
     */
    init(domElement) {
        $('td.actions', domElement)
            .each((index, element) => {
                //  Hidden screen-tab panels report every button at offset 0,
                //  which looks like a single row and strips btn-block. Skip
                //  until the panel is shown and refreshUi runs again.
                if (!$(element).is(':visible')) {
                    return;
                }

                let $buttons = $('> .btn, > .btn-group', element);
                let offsets = [];
                $buttons.each((index, element) => {
                    offsets.push(element.offsetTop);
                })

                if (Math.max(...offsets) !== Math.min(...offsets)) {
                    $buttons.addClass('btn-block');
                    $buttons.filter('.btn-group').find('> .btn').addClass('btn-block');
                } else {
                    $buttons.removeClass('btn-block');
                    $buttons.filter('.btn-group').find('> .btn').removeClass('btn-block');
                }
            });

        return this;
    }
}

export default IndexButtons;
