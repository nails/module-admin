/* export Tabs */

import {scrollTabIntoView} from './TabScroller.js';

class Tabs {

    /**
     * Construct Tabs
     * @return {Tabs}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.groups = {};

        this.adminController
            .onRefreshUi((e, domElement) => {
                //  @todo (Pablo - 2020-01-17) - Support the domElement defined
                this.init();
            });

        return this;
    };

    /**
     * Instanciates new tab groups
     * @returns {Tabs}
     */
    init() {

        this
            .prepareDomElements()
            .instantiateTabs()

        for (let key in this.groups) {
            if (this.groups.hasOwnProperty(key)) {
                this.groups[key]
                    .setDefaultActiveTab()
                    .setControlsWithErrors();
            }
        }

        return this;
    }

    /**
     * Ensures all DOM elements have a tab group defined
     * @returns {Tabs}
     */
    prepareDomElements() {
        let classNames = ['ul.tabs', 'section.tabs'];
        for (let i = 0; i < classNames.length; i++) {

            let counter = 0;
            let node = document.querySelectorAll(classNames[i] + ':not(.ready)');
            for (let x = 0; x < node.length; x++) {
                if (typeof node[x].dataset.tabgroup === 'undefined') {
                    node[x].setAttribute('data-tabgroup', 'tab-group-' + counter++);
                }
            }

        }
        return this;
    }

    /**
     * Instanciates new tab groups
     * @returns {Tabs}
     */
    instantiateTabs() {

        let nodes, childNodes, i, group, j;

        nodes = document.querySelectorAll('ul.tabs:not(.ready)');
        for (i = 0; i < nodes.length; i++) {

            group = nodes[i].dataset.tabgroup;
            if (!this.groups.hasOwnProperty(group)) {
                this.groups[group] = new Group(group, this.adminController);
            }

            childNodes = nodes[i].querySelectorAll(':scope > li.tab');
            for (j = 0; j < childNodes.length; j++) {
                this.groups[group].addControl(
                    new Control(this.groups[group], childNodes[j])
                );
            }

            nodes[i].classList.add('ready');
        }

        nodes = document.querySelectorAll('section.tabs:not(.ready)');
        for (i = 0; i < nodes.length; i++) {

            group = nodes[i].dataset.tabgroup;
            childNodes = nodes[i].querySelectorAll(':scope > div.tab-page');
            for (j = 0; j < childNodes.length; j++) {
                this.groups[group].addPanel(
                    new Panel(this.groups[group], childNodes[j])
                );
            }

            nodes[i].classList.add('ready');
        }

        return this;
    }
}

/**
 * The class represents a tab group
 */
class Group {

    /**
     * @param {String} slug The group's slug
     * @returns {Group}
     */
    constructor(slug, adminController) {
        this.slug = slug;
        this.adminController = adminController;
        this.controls = [];
        this.panels = [];
        this.input = document.querySelectorAll('input[data-tabgroup="' + this.slug + '"]');
        this.defaultTabSet = false;
        return this;
    }

    /**
     * Adds a new control to the group
     * @param {Control} control The control to add
     */
    addControl(control) {
        this.controls.push(control);
    }

    /**
     * Adds a new panel to the group
     * @param {Panel} panel The panel to add
     */
    addPanel(panel) {
        this.panels.push(panel);
    }

    /**
     * Sets the default tab
     * @returns {Group}
     */
    setDefaultActiveTab() {

        if (this.defaultTabSet) {
            return this;
        } else {
            this.defaultTabSet = true;
        }

        //  Set the first tab which contains an error
        for (let i = 0; i < this.controls.length; i++) {

            let target = this.controls[i].getTarget();

            for (let j = 0; j < this.panels.length; j++) {

                let panel = this.panels[j];
                if (panel.targets(target) && panel.containsError()) {
                    this.goTo(target);
                    return this;
                }
            }
        }

        //  If theres an "active tab" input, go to that one
        if (this.input.length && this.input[0].value.length) {
            this.goTo(this.input[0].value);
            return this;
        }

        //  Fall back to the first tab
        if (this.controls.length > 0) {
            this.goTo(this.controls[0].getTarget());
        }

        return this;
    }

    /**
     * Sets the rrror class on controls whose target panel contains an error
     */
    setControlsWithErrors() {
        for (let i = 0; i < this.controls.length; i++) {

            let target = this.controls[i].getTarget();

            for (let j = 0; j < this.panels.length; j++) {

                let panel = this.panels[j];
                if (panel.targets(target)) {
                    if (panel.containsError()) {
                        this.controls[i].setError();
                    } else {
                        this.controls[i].setNoError();
                    }
                }
            }
        }
    }

    /**
     * Goes to particular tab
     * @param {String} target The target tab
     */
    goTo(target) {
        let i;
        for (i = 0; i < this.controls.length; i++) {
            if (this.controls[i].targets(target)) {
                this.controls[i].setActive();
            } else {
                this.controls[i].setInactive();
            }
        }

        for (i = 0; i < this.panels.length; i++) {
            if (this.panels[i].targets(target)) {
                this.panels[i].setActive();
            } else {
                this.panels[i].setInactive();
            }
        }

        if (this.input.length) {
            for (let j = 0; j < this.input.length; j++) {
                this.input[j].value = target;
            }
        }

        this.openNestedWorkspaces();

        this.adminController.refreshUi();
    }

    /**
     * Nested groups inside a panel that was hidden at init can come up with
     * no active page. Give each one a visible page once this panel is shown.
     */
    openNestedWorkspaces() {

        for (let i = 0; i < this.panels.length; i++) {

            if (!this.panels[i].isActive()) {
                continue;
            }

            let lists = this.panels[i].element.querySelectorAll('ul.tabs');

            for (let j = 0; j < lists.length; j++) {

                let group = lists[j].dataset.tabgroup;

                if (!group) {
                    continue;
                }

                let section = this.panels[i].element.querySelector(
                    'section.tabs[data-tabgroup="' + group + '"]'
                );

                if (!section || section.querySelector(':scope > .tab-page.active')) {
                    continue;
                }

                let link = lists[j].querySelector(':scope > li.tab > a');
                let target = link ? link.dataset.tab : '';

                if (!target) {
                    continue;
                }

                let items = lists[j].querySelectorAll(':scope > li.tab');

                for (let k = 0; k < items.length; k++) {

                    let anchor = items[k].querySelector('a');
                    items[k].classList.toggle(
                        'active',
                        !!(anchor && anchor.dataset.tab === target)
                    );
                }

                let pages = section.querySelectorAll(':scope > .tab-page');

                for (let k = 0; k < pages.length; k++) {
                    pages[k].classList.toggle('active', pages[k].classList.contains(target));
                }
            }
        }
    }
}

/**
 * This class represents an individual control
 */
class Control {

    /**
     *
     * @param {Group} group The group this control is within
     * @param {HTMLLIElement} element The control's DOM element
     * @returns {Control}
     */
    constructor(group, element) {

        this.group = group;
        this.element = element;
        this.link = this.element.querySelector('a');
        this.target = this.link.dataset.tab;

        this.element.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.group.goTo(this.target);
        });
        return this;
    }

    /**
     * Returns the target for this control
     * @returns {String}
     */
    getTarget() {
        return this.target;
    }

    /**
     * Determines whether this control targets the specified item
     * @param {String} target The target to test
     * @returns {boolean}
     */
    targets(target) {
        return this.target === target;
    }

    /**
     * Determines if the control is currently active
     * @returns {boolean}
     */
    isActive() {
        return this.element.classList.contains('active');
    }

    /**
     * Sets the control as active
     * @returns {Control}
     */
    setActive() {
        this.element.classList.add('active');
        scrollTabIntoView(this.element);
        return this;
    }

    /**
     * Sets the control as inactive
     * @returns {Control}
     */
    setInactive() {
        this.element.classList.remove('active');
        return this;
    }

    /**
     * Sets the control as errored
     * @returns {Control}
     */
    setError() {
        this.link.classList.add('error');
        return this;
    }

    /**
     * Sets the control as not errored
     * @returns {Control}
     */
    setNoError() {
        this.link.classList.remove('error');
        return this;
    }
}

/**
 * This class represents an individual panel
 */
class Panel {

    /**
     *
     * @param {Group} group The group this panel belongs to
     * @param {HTMLDivElement} element The DOM element this panel is attached to
     * @returns {Panel}
     */
    constructor(group, element) {
        this.group = group;
        this.element = element;
        return this;
    }

    /**
     * Determines whether this panel targets the specified item
     * @param {String} target The target to test
     * @returns {boolean}
     */
    targets(target) {
        return this.element.classList.contains(target);
    }

    /**
     * Determines if the control is currently active
     * @returns {boolean}
     */
    isActive() {
        return this.element.classList.contains('active');
    }

    /**
     * Sets the panel as active
     * @returns {Panel}
     */
    setActive() {
        this.element.classList.add('active');
    }

    /**
     * Sets the panel as inactive
     * @returns {Panel}
     */
    setInactive() {
        this.element.classList.remove('active');
    }

    /**
     * Determines whether the panel contains an error
     * @returns {boolean}
     */
    containsError() {
        let errorNodes = this
            .element
            .querySelectorAll('div.field.error, .system-alert.error, .alert.alert-danger, .error.show-in-tabs');

        return errorNodes !== null && errorNodes.length > 0;
    }
}

export default Tabs;
