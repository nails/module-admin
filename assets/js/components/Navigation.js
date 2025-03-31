/* export Navigation */

import API from './API'
import services from './Services'
import Modal from './Modal/Instance';
import Search from './Navigation/Search';
import Section from './Navigation/Section';
import Sorting from './Navigation/Sorting';

class Navigation {

    /**
     * Construct Navigation
     * @return {Navigation}
     */
    constructor(adminController) {
        this.adminController = adminController;
        this.sections = [];
        this.init();
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises admin navigation
     */
    init() {

        this
            .initBoxes()
            .initSorting()
            .initSearching()
            .initButtons()
            .enableAnimation();
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises sidebar boxes
     * @returns {Navigation}
     */
    initBoxes() {
        document
            .querySelectorAll('.sidebar > ul.modules > li.module')
            .forEach((element) => {
                this.sections.push({
                    'element': element,
                    'instance': new Section(this, element)
                });
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises sidebar sorting
     * @returns {Navigation}
     */
    initSorting() {
        this.sorting = new Sorting(this);
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises sidebar searching
     * @returns {Navigation}
     */
    initSearching() {
        this.search = new Search(
            this,
            document.querySelector('.sidebar > .nav-search > input')
        );
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises sidebar action buttons
     * @returns {Navigation}
     */
    initButtons() {

        let buttons = document.getElementById('admin-nav-reset-buttons');

        if (buttons) {
            buttons.querySelector('a[data-action=reset]')
                .addEventListener('click', (e) => {
                    e.preventDefault();
                    this.resetNav();
                });

            buttons.querySelector('a[data-action=open]')
                .addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openAll();
                });

            buttons.querySelector('a[data-action=close]')
                .addEventListener('click', (e) => {
                    e.preventDefault();
                    this.closeAll();
                });
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Saves the sidebar's state
     * @returns {Navigation}
     */
    saveState() {
        clearTimeout(this.debounceSave);
        this.debounceSave = setTimeout(() => {
            this.adminController.log('🔄 Saving State...');

            let config = {};
            let order = 0;
            let sections = document.querySelectorAll('.sidebar > ul.modules > li');
            sections.forEach((element) => {
                this.sections.forEach((section) => {
                    if (section.element === element) {
                        config[section.element.dataset['grouping']] = {
                            open: section.instance.isOpen(),
                            order: order++,
                        }
                    }
                })
            });

            services
                .apiRequest({
                    method: 'post',
                    url: API.navigation.save,
                    data: {
                        preferences: config
                    }
                })
                .catch(() => {
                    this.adminController.error('😵 failed!');
                })
                .then(() => {
                    this.adminController.log('👍 done!');
                });

        }, 1000);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Resets the sidebar to its default state
     * @returns {Navigation}
     */
    resetNav() {

        services.apiRequest({
            method: 'post',
            url: API.navigation.reset,
        });

        let modal = (new Modal(this.adminController));
        let p1 = document.createElement('p');
        let p2 = document.createElement('p');
        let btnClose = document.createElement('button');
        let btnReload = document.createElement('button');

        p2.append(btnClose);
        p2.append(btnReload);

        p1.innerText = 'Your navigation has been reset, changes will take hold on the next page load.';

        btnClose.innerText = 'Close';
        btnClose.style.marginRight = '0.5em';
        btnClose.classList.add('btn', 'btn-primary');
        btnClose.addEventListener('click', (e) => {
            e.preventDefault();
            modal.hide();
            modal.container.remove();
        })

        btnReload.innerText = 'Reload';
        btnReload.classList.add('btn', 'btn-default');
        btnReload.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.reload();
        });

        modal
            .setTitle('Reset complete')
            .setBody([p1, p2])
            .show();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Opens all the sidebar sections
     * @returns {Navigation}
     */
    openAll() {
        this.disableAnimation();
        this.sections
            .forEach((section) => {
                section.instance.open();
            });
        this.saveState();
        this.enableAnimation();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Closes all the sidebar sections
     * @returns {Navigation}
     */
    closeAll() {
        this.disableAnimation();
        this.sections
            .forEach((section) => {
                section.instance.close();
            });
        this.saveState();
        this.enableAnimation();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Enables sidebar animation
     * Uses a set timeout so that we don't animate things we think should not be animating
     * @returns {Navigation}
     */
    enableAnimation() {
        clearTimeout(this.enableAnimationTimeout);
        this.enableAnimationTimeout = setTimeout(() => {
            var modules = document.querySelector('.sidebar > ul.modules');
            if (modules) {
                modules.classList.add('animate');
            }
        }, 1000);
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Disables sidebar animations
     * @returns {Navigation}
     */
    disableAnimation() {
        clearTimeout(this.enableAnimationTimeout);
        var modules = document.querySelector('.sidebar > ul.modules');
        if (modules) {
            modules.classList.remove('animate');
        }
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Enable sidebar sorting
     * @returns {Navigation}
     */
    enableSorting() {
        document.querySelectorAll('.sidebar li.module .ui-sortable-handle')
            .forEach((element) => {
                element.classList.remove('hidden');
            });
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Disables sidebar sorting
     * @returns {Navigation}
     */
    disableSorting() {
        document.querySelectorAll('.sidebar li.module .ui-sortable-handle')
            .forEach((element) => {
                element.classList.add('hidden');
            });
        return this;
    }
}

export default Navigation;
