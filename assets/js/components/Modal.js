/* export Modal */

import Instance from './Modal/Instance';

class Modal {

    /**
     * Construct Modal
     * @return {Modal}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.modals = [];

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Modal
     * @returns {Modal}
     */
    init() {

        $('.modal:not(.modal--processed)')
            .addClass('modal--processed')
            .each((index, el) => {

                let inner = $('.modal__inner', el).get(0);
                let close = $('.modal__close', el).get(0);
                let title = $('.modal__title', el).get(0);
                let body = $('.modal__body', el).get(0);

                this.modals.push(new Instance(
                    this.adminController,
                    {
                        el: el
                    }
                ));
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new modal instance
     * @returns {Instance}
     */
    create() {

        let instance = new Instance(this.adminController);

        this.modals.push(instance);

        return instance;
    }
}

export default Modal;
