/* export CollapsibleFieldsets */

/**
 * Admin UI: Collapsible Fieldsets
 *
 * Turns the legend of an opted-in fieldset into a disclosure button so the
 * group's contents can be folded away. Opt in with a `data-collapse` attribute
 * on the `<fieldset>`; its value sets the initial state:
 *
 *     <fieldset data-collapse>          — collapsible, open
 *     <fieldset data-collapse="open">   — collapsible, open
 *     <fieldset data-collapse="closed"> — collapsible, closed
 *
 * A fieldset which contains a field error always starts open, whatever the
 * attribute asks for: an error the user cannot see is an error they cannot fix.
 *
 * The markup is only rewritten once this runs, so a fieldset renders exactly as
 * it always did — open, with no chevron — if the JS never gets there.
 *
 * The styling which goes with this lives in assets/sass/admin-ui/objects/_fieldset.scss.
 */

//  Opting in; the class is both the style hook and the "already done" marker
const SELECTOR_FIELDSET = 'fieldset[data-collapse]';
const CLASS_COLLAPSIBLE = 'fieldset--collapsible';
const CLASS_COLLAPSED = 'fieldset--collapsed';
const CLASS_TOGGLE = 'fieldset__toggle';
const CLASS_LABEL = 'fieldset__toggle-label';
const CLASS_CHEVRON = 'fieldset__toggle-chevron';

//  Marks a fieldset which opted in but cannot be handled, so it is only complained about once
const CLASS_IGNORED = 'fieldset--collapse-ignored';

//  Values which ask for a closed fieldset; anything else opens it
const VALUES_CLOSED = ['closed', 'close', 'collapsed', 'false', '0'];
const VALUES_OPEN = ['', 'open', 'expanded', 'true', '1'];

//  What an error looks like — the same set Tabs.js uses, for the same reason
const SELECTOR_ERROR = 'div.field.error, .system-alert.error, .alert.alert-danger, .error.show-in-tabs';

//  The legend's contents are moved inside the toggle button, so a legend which
//  already holds a control of its own cannot be handled — nesting these inside
//  a button is invalid and browsers make their own minds up about it
const SELECTOR_INTERACTIVE = 'a[href], button, input, select, textarea, label, [tabindex], [contenteditable]';

class CollapsibleFieldsets {

    /**
     * Construct CollapsibleFieldsets
     * @param adminController
     * @return {CollapsibleFieldsets}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.fieldsets = [];
        this.counter = 0;

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
     * Makes any new opted-in fieldsets collapsible
     * @param {HTMLElement} domElement The DOM element to restrict the search to
     * @returns {CollapsibleFieldsets}
     */
    init(domElement) {

        let scope = domElement instanceof Element || domElement instanceof Document
            ? domElement
            : document;

        let selector = `${SELECTOR_FIELDSET}:not(.${CLASS_COLLAPSIBLE}):not(.${CLASS_IGNORED})`;
        let nodes = Array.from(scope.querySelectorAll(selector));

        //  A refresh can be pointed at the fieldset itself, which querySelectorAll would miss
        if (scope instanceof Element && scope.matches(selector)) {
            nodes.unshift(scope);
        }

        for (let i = 0; i < nodes.length; i++) {
            this.fieldsets.push(
                new Fieldset(this.adminController, nodes[i], ++this.counter)
            );
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Restores any fieldsets which fall within domElement
     * @param {HTMLElement} domElement The DOM element to restrict the search to
     * @returns {CollapsibleFieldsets}
     */
    destroy(domElement) {

        this.fieldsets = this.fieldsets
            .filter((fieldset) => {

                if (domElement && domElement !== document && !domElement.contains(fieldset.element)) {
                    return true;
                }

                fieldset.destroy();
                return false;
            });

        return this;
    }
}

/**
 * This class represents a single collapsible fieldset
 */
class Fieldset {

    /**
     * Construct Fieldset
     * @param adminController
     * @param {HTMLFieldSetElement} element The fieldset to make collapsible
     * @param {Number} index A number unique to this fieldset, used when one needs an ID
     * @returns {Fieldset}
     */
    constructor(adminController, element, index) {

        this.adminController = adminController;
        this.element = element;
        this.legend = this.element.querySelector(':scope > legend');
        this.expanded = true;

        //  Nothing to click, or something already clickable in the way of it
        if (this.legend === null) {
            this.element.classList.add(CLASS_IGNORED);
            this.adminController.warn('Ignoring [data-collapse] fieldset without a legend', this.element);
            return this;

        } else if (this.legend.querySelector(SELECTOR_INTERACTIVE) !== null) {
            this.element.classList.add(CLASS_IGNORED);
            this.adminController.warn('Ignoring [data-collapse] fieldset whose legend contains a control', this.element);
            return this;
        }

        //  The toggle points at what it opens, so the fieldset needs a name to be pointed at
        if (!this.element.id) {
            this.element.id = `collapsible-fieldset-${index}`;
        }

        this.element.classList.add(CLASS_COLLAPSIBLE);
        this.button = this.buildToggle();

        this.setExpanded(this.getInitialState(), false);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Moves the legend's contents into a button, and adds the chevron alongside them
     * @returns {HTMLButtonElement}
     */
    buildToggle() {

        let label = document.createElement('span');
        label.classList.add(CLASS_LABEL);
        while (this.legend.firstChild) {
            label.appendChild(this.legend.firstChild);
        }

        let chevron = document.createElement('span');
        chevron.classList.add(CLASS_CHEVRON, 'fa', 'fa-chevron-down');
        chevron.setAttribute('aria-hidden', 'true');

        let button = document.createElement('button');
        button.setAttribute('type', 'button');
        button.classList.add(CLASS_TOGGLE);

        //  `aria-controls` names the region the button hides; that region is the
        //  fieldset itself, less the band the button sits in
        button.setAttribute('aria-controls', this.element.id);
        button.appendChild(label);
        button.appendChild(chevron);
        button.addEventListener('click', () => {
            this.toggle();
        });

        this.legend.appendChild(button);

        return button;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines the state the fieldset should start in
     * @returns {Boolean} Whether the fieldset should start expanded
     */
    getInitialState() {

        //  An error the user cannot see is an error they cannot fix
        if (this.containsError()) {
            return true;
        }

        let value = (this.element.dataset.collapse || '').trim().toLowerCase();

        if (VALUES_CLOSED.indexOf(value) > -1) {
            return false;

        } else if (VALUES_OPEN.indexOf(value) === -1) {
            this.adminController
                .warn(`Unrecognised data-collapse value "${value}", opening fieldset`, this.element);
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Determines whether the fieldset contains an error
     * @returns {Boolean}
     */
    containsError() {
        let errorNodes = this.element.querySelectorAll(SELECTOR_ERROR);
        return errorNodes !== null && errorNodes.length > 0;
    }

    // --------------------------------------------------------------------------

    /**
     * Flips the fieldset between open and closed
     * @returns {Fieldset}
     */
    toggle() {
        return this.setExpanded(!this.expanded);
    }

    // --------------------------------------------------------------------------

    /**
     * Opens or closes the fieldset
     * @param {Boolean} expanded Whether the fieldset should be open
     * @param {Boolean} refreshUi Whether to refresh the UI once opened
     * @returns {Fieldset}
     */
    setExpanded(expanded, refreshUi = true) {

        this.expanded = expanded;
        this.element.classList.toggle(CLASS_COLLAPSED, !expanded);
        this.button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

        //  Anything which measures itself could not do so while it was hidden
        if (expanded && refreshUi) {
            this.adminController.refreshUi(this.element);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Puts the legend back the way it was found
     * @returns {Fieldset}
     */
    destroy() {

        if (this.button) {

            let label = this.button.querySelector(`.${CLASS_LABEL}`);
            while (label.firstChild) {
                this.legend.appendChild(label.firstChild);
            }

            this.button.remove();
            this.button = null;
        }

        this.element.classList.remove(CLASS_COLLAPSIBLE, CLASS_COLLAPSED, CLASS_IGNORED);

        return this;
    }
}

export default CollapsibleFieldsets;
