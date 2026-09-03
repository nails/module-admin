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
            .onRefreshUi(() => {
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

        let modals = document.querySelectorAll('.modal:not(.modal--processed)');
        modals.forEach(modal => {
            modal.classList.add('modal--processed');
            this.modals.push(new Instance(
                this.adminController,
                {
                    el: modal
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
