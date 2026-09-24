/* export Revealer */

/* globals $, jQuery */
class Revealer {

    /**
     * Construct Revealer.
     * @return {Revealer}
     */
    constructor(adminController) {

        this.groups = {};
        this.elements = [];
        this.adminController = adminController;

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            })
            .onDestroyUi((e, domElement) => {
                this.destroy(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits Revealer
     * @param {HTMLElement} domElement
     * @returns {Revealer}
     */
    init(domElement) {

        let exclude = [
            '.revealer--processed',
            '[data-reveal-on]',
            '[data-reveal-not-on]',
        ];

        let selector = '[data-revealer]';
        for (let i = 0; i < exclude.length; i++) {
            selector += `:not(${exclude[i]})`;
        }

        $(selector, domElement)
            .filter(':input')
            .filter('input[type=checkbox], select, input[data-api]')
            .addClass('revealer--processed')
            .each((index, element) => {

                let group = $(element)
                    .data('revealer');

                if (typeof this.groups[group] !== 'undefined') {
                    this.adminController.warn(`Duplicate group "${group}"`);
                    return;
                }

                this.groups[group] = new Group(this, group, element);
            });

        this.findElements(domElement);
        this.elements.forEach((element) => element.sync());

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Binds target elements. An element may list several groups in
     * `data-revealer` (comma-separated); it is shown if any listed group
     * matches `data-reveal-on`.
     * @param {HTMLElement} domElement
     * @returns {Revealer}
     */
    findElements(domElement) {

        $('[data-revealer]', domElement)
            .filter(':not(.revealer--processed)')
            .filter('[data-reveal-on], [data-reveal-not-on]')
            .filter(':not(input[type=checkbox], select)')
            .addClass('revealer--processed')
            .each((index, element) => {
                this.elements.push(new Element(this, element));
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Re-evaluate every element that listens to this group
     * @param {String} group
     */
    evaluateForGroup(group) {

        this.adminController.log('Toggling elements for group', group);

        for (let i = 0; i < this.elements.length; i++) {
            if (this.elements[i].listensTo(group)) {
                this.elements[i].sync();
            }
        }

        this.adminController.refreshUi();
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys groups whose controller falls within domElement
     * @param domElement
     */
    destroy(domElement) {
        for (let key in this.groups) {
            if (this.groups.hasOwnProperty(key)) {
                if (domElement === null || $.contains(domElement, this.groups[key].$control[0] || this.groups[key].$control)) {
                    this.groups[key].destroy();
                    delete this.groups[key];
                }
            }
        }

        this.elements = this.elements.filter((element) => {
            if (element.groupNames.every((name) => typeof this.groups[name] === 'undefined')) {
                element.destroy();
                return false;
            }

            element.sync();
            return true;
        });
    }
}

/**
 * Represents a revealer group
 */
class Group {

    /**
     * Construct Group.
     * @param revealer {Revealer}
     * @param group {String} The string this group represents
     * @param control {HTMLElement} The DOM element responsible for controlling this group
     */
    constructor(revealer, group, control) {

        this.revealer = revealer;
        this.adminController = revealer.adminController;
        this.group = group;
        this.$control = $(control);

        this.$control
            .on('change.revealer', () => {
                this.revealer.evaluateForGroup(this.group);
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current value of the control
     * @returns {String|Boolean}
     */
    getControlValue() {
        let value;
        if (this.$control.is('[type=checkbox]')) {
            value = this.$control.is(':checked');
        } else {
            value = this.$control.val();
        }

        return value;
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the group
     */
    destroy() {
        this.$control.off('change.revealer');
        this.$control.removeClass('revealer--processed');
    }
}

/**
 * Represents a single element
 */
class Element {

    /**
     * Construct Element.
     * @param revealer {Revealer}
     * @param element {HTMLElement} The DOM element
     */
    constructor(revealer, element) {

        this.revealer = revealer;
        this.$element = $(element);
        this.delimiter = this.$element.data('reveal-delimiter') || ',';
        this.groupNames = (element.getAttribute('data-revealer') || '')
            .split(this.delimiter)
            .map((name) => name.trim())
            .filter(Boolean);

        this.values = element.hasAttribute('data-reveal-on')
            ? element.getAttribute('data-reveal-on')
            : null;

        if (this.values !== null) {
            this.values = this.values.split(this.delimiter)
        }

        this.bangValues = element.hasAttribute('data-reveal-not-on')
            ? element.getAttribute('data-reveal-not-on')
            : null;

        if (this.bangValues !== null) {
            this.bangValues = this.bangValues.split(this.delimiter)
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Whether this element listens to the named group
     * @param {String} group
     * @returns {boolean}
     */
    listensTo(group) {
        return this.groupNames.indexOf(group) > -1;
    }

    // --------------------------------------------------------------------------

    /**
     * Show or hide based on every listed group (OR)
     * @returns {Element}
     */
    sync() {
        let show = false;

        for (let i = 0; i < this.groupNames.length; i++) {
            let group = this.revealer.groups[this.groupNames[i]];
            if (group && this.isShown(group.getControlValue())) {
                show = true;
                break;
            }
        }

        return show ? this.show() : this.hide();
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the element should be shown for the supplied value
     * @param value {String|Boolean} The value to test
     * @returns {boolean}
     */
    isShown(value) {

        let showOn = this.testValue(this.values, value, false);
        let showNotOn = this.testValue(this.bangValues, value, true);

        return showOn || showNotOn;
    }

    // --------------------------------------------------------------------------

    /**
     * Performs the test of the supplied value against the stored values
     * @param values
     * @param testString
     * @returns {null|boolean}
     */
    testValue(values, testString, negativeTest) {

        if (values !== null) {

            if (testString.length && !values.length) {
                return true;
            }

            for (let i = 0; i < values.length; i++) {

                let value = values[i];

                if (typeof testString === "boolean") {
                    if (
                        (!negativeTest && testString && [true, 1, "true", "1"].indexOf(value) > -1) ||
                        (!negativeTest && !testString && [false, 0, "false", "0"].indexOf(value) > -1) ||
                        (negativeTest && testString && [true, 1, "true", "1"].indexOf(value) === -1) ||
                        (negativeTest && !testString && [false, 0, "false", "0"].indexOf(value) === -1)
                    ) {
                        return true;
                    }

                } else if (!negativeTest && value == testString) {
                    return true;

                } else if (negativeTest && value != testString) {
                    return true;
                }
            }
        }

        return false;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the element
     * @returns {Element}
     */
    show() {
        this.$element.show();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Hides the element
     * @returns {Element}
     */
    hide() {
        this.$element.hide();
        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Handles destruction
     */
    destroy() {
        this.$element.removeClass('revealer--processed');
        this.show();
    }
}

export default Revealer;
