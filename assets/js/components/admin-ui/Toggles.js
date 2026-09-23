/* export Toggles */

/**
 * Admin UI: Boolean toggles
 *
 * Paints an iOS-style switch over a checkbox. The checkbox is still the form
 * control: this is only the thing the user sees and clicks. Markup is an
 * empty `.form-bool` immediately followed by the checkbox; `Form::boolean()`
 * emits that pair. Optional data attributes on the checkbox:
 *
 *     data-text-on / data-text-off   Labels inside the track. Default ON/OFF
 *                                    is treated as unlabeled (compact pill).
 *     data-toggle-width / -height    Optional size overrides.
 *
 * The styling which goes with this lives in assets/sass/admin-ui/objects/_toggle.scss.
 *
 * Apps which used to drive jquery-toggles via
 * `.data('instance').container.data('toggles').toggle(state, noanimate, silent)`
 * still can: the same chain is kept as a shim.
 */

const SELECTOR = '.form-bool';
const CLASS_READY = 'admin-toggle';
const CLASS_ON = 'admin-toggle--on';
const CLASS_LABELED = 'admin-toggle--labeled';
const CLASS_READONLY = 'admin-toggle--readonly';
const CLASS_NO_ANIMATE = 'admin-toggle--no-animate';
const CLASS_LEGACY = 'toggled';
const CLASS_INPUT = 'admin-toggle__input';

const DEFAULT_ON = 'on';
const DEFAULT_OFF = 'off';

let labelId = 0;

class Toggles {

    /**
     * Construct Toggles
     * @param adminController
     * @return {Toggles}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.instances = [];

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
     * Turns any new `.form-bool` mounts into switches
     * @param {HTMLElement} domElement The DOM element to restrict the search to
     * @returns {Toggles}
     */
    init(domElement) {

        let scope = domElement instanceof Element || domElement instanceof Document
            ? domElement
            : document;

        let selector = `${SELECTOR}:not(.${CLASS_READY})`;
        let nodes = Array.from(scope.querySelectorAll(selector));

        if (scope instanceof Element && scope.matches(selector)) {
            nodes.unshift(scope);
        }

        for (let i = 0; i < nodes.length; i++) {
            let instance = new ToggleInstance(this.adminController, nodes[i]);
            if (instance.ready) {
                this.instances.push(instance);
            }
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Restores any switches which fall within domElement
     * @param {HTMLElement} domElement The DOM element to restrict the search to
     * @returns {Toggles}
     */
    destroy(domElement) {

        this.instances = this.instances
            .filter((instance) => {

                if (domElement && domElement !== document && !domElement.contains(instance.element)) {
                    return true;
                }

                instance.destroy();
                return false;
            });

        return this;
    }
}

/**
 * This class represents a single switch
 */
class ToggleInstance {

    /**
     * Construct ToggleInstance
     * @param adminController
     * @param {HTMLElement} element The `.form-bool` mount
     * @returns {ToggleInstance}
     */
    constructor(adminController, element) {

        this.adminController = adminController;
        this.element = element;
        this.ready = false;
        this.updating = false;
        this.container = window.jQuery ? window.jQuery(element) : null;

        this.checkbox = this.findCheckbox();
        if (!this.checkbox) {
            this.adminController.warn('Ignoring .form-bool without a following checkbox', element);
            return this;
        }

        this.readonly = this.checkbox.disabled
            || this.checkbox.classList.contains('readonly')
            || this.checkbox.closest('div.field.readonly') !== null;

        this.textOn = this.readAttr(this.checkbox, 'textOn', 'ON');
        this.textOff = this.readAttr(this.checkbox, 'textOff', 'OFF');
        this.labeled = !this.isUnlabeled(this.textOn, this.textOff);
        this.width = this.readAttr(this.checkbox, 'toggleWidth', null);
        this.height = this.readAttr(this.checkbox, 'toggleHeight', null);

        this.build();
        this.bind();
        this.syncUi({animate: false});

        this.ready = true;

        if (this.container) {
            this.container.data('instance', this);
            this.container.data('toggles', this);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * The checkbox is the next sibling of the mount; Form::boolean() emits that pair
     * @returns {HTMLInputElement|null}
     */
    findCheckbox() {

        let next = this.element.nextElementSibling;
        if (next && next.matches('input[type="checkbox"]')) {
            return next;
        }

        return null;
    }

    // --------------------------------------------------------------------------

    /**
     * Reads a data-* attribute, falling back to a default
     * @param {HTMLElement} node
     * @param {String} key camelCase dataset key
     * @param {*} fallback
     * @returns {*}
     */
    readAttr(node, key, fallback) {

        if (node.dataset[key] === undefined || node.dataset[key] === '') {
            return fallback;
        }

        return node.dataset[key];
    }

    // --------------------------------------------------------------------------

    /**
     * Default ON/OFF (any case) is chrome, not a label the author chose
     * @param {String} on
     * @param {String} off
     * @returns {Boolean}
     */
    isUnlabeled(on, off) {
        return String(on).trim().toLowerCase() === DEFAULT_ON
            && String(off).trim().toLowerCase() === DEFAULT_OFF;
    }

    // --------------------------------------------------------------------------

    /**
     * Turns a data-toggle-* value into a CSS length
     * @param {String} value
     * @returns {String}
     */
    toCssLength(value) {
        return /^\d+(\.\d+)?$/.test(String(value)) ? `${value}px` : String(value);
    }

    // --------------------------------------------------------------------------

    /**
     * Builds the switch chrome inside the empty mount
     */
    build() {

        this.element.replaceChildren();
        this.element.classList.add(CLASS_READY, CLASS_LEGACY);
        this.element.setAttribute('role', 'switch');
        this.element.setAttribute('tabindex', this.readonly ? '-1' : '0');

        if (this.labeled) {
            this.element.classList.add(CLASS_LABELED);
        }

        if (this.readonly) {
            this.element.classList.add(CLASS_READONLY);
            this.element.setAttribute('aria-disabled', 'true');
        }

        if (this.width) {
            this.element.style.setProperty('--admin-toggle-width', this.toCssLength(this.width));
        }

        if (this.height) {
            this.element.style.setProperty('--admin-toggle-height', this.toCssLength(this.height));
        }

        this.labelOn = document.createElement('span');
        this.labelOn.className = 'admin-toggle__label admin-toggle__label--on';
        this.labelOn.textContent = this.textOn;
        this.labelOn.setAttribute('aria-hidden', 'true');

        this.labelOff = document.createElement('span');
        this.labelOff.className = 'admin-toggle__label admin-toggle__label--off';
        this.labelOff.textContent = this.textOff;
        this.labelOff.setAttribute('aria-hidden', 'true');

        this.thumb = document.createElement('span');
        this.thumb.className = 'admin-toggle__thumb';
        this.thumb.setAttribute('aria-hidden', 'true');

        this.element.append(this.labelOn, this.labelOff, this.thumb);

        this.checkbox.classList.add(CLASS_INPUT);
        this.checkbox.setAttribute('tabindex', '-1');
        this.checkbox.setAttribute('aria-hidden', 'true');

        let field = this.element.closest('div.field');
        let fieldLabel = field
            ? field.querySelector(':scope > .label, :scope > span.label')
            : null;

        if (fieldLabel) {
            if (!fieldLabel.id) {
                fieldLabel.id = `admin-toggle-label-${++labelId}`;
            }
            this.element.setAttribute('aria-labelledby', fieldLabel.id);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Click, keyboard, and outside changes to the checkbox
     */
    bind() {

        this.onActivate = (event) => {
            event.preventDefault();
            if (this.readonly) {
                return;
            }
            this.setChecked(!this.checkbox.checked);
        };

        this.onKeydown = (event) => {
            if (event.key !== ' ' && event.key !== 'Enter') {
                return;
            }
            this.onActivate(event);
        };

        this.onCheckboxChange = () => {
            if (this.updating) {
                return;
            }
            this.syncUi();
        };

        this.element.addEventListener('click', this.onActivate);
        this.element.addEventListener('keydown', this.onKeydown);
        this.checkbox.addEventListener('change', this.onCheckboxChange);
    }

    // --------------------------------------------------------------------------

    /**
     * jquery-toggles API: toggle(state, noanimate, silent)
     * @param {Boolean|undefined} state
     * @param {Boolean} noanimate
     * @param {Boolean} silent
     * @returns {ToggleInstance}
     */
    toggle(state, noanimate, silent) {

        let checked = typeof state === 'undefined'
            ? !this.checkbox.checked
            : this.coerceChecked(state);

        return this.setChecked(checked, {
            animate: !noanimate,
            silent: !!silent
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Checkboxes, serializeArray, and old callers pass a mix of types
     * @param {*} value
     * @returns {Boolean}
     */
    coerceChecked(value) {

        if (typeof value === 'boolean') {
            return value;
        }

        if (typeof value === 'number') {
            return value !== 0;
        }

        let normalised = String(value).trim().toLowerCase();
        if (normalised === 'false' || normalised === '0' || normalised === 'off' || normalised === '') {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the checkbox and paints the switch to match
     * @param {Boolean} checked
     * @param {Object} options
     * @returns {ToggleInstance}
     */
    setChecked(checked, options = {}) {

        if (this.readonly && !options.silent) {
            return this;
        }

        let next = !!checked;
        let animate = options.animate !== false;
        let silent = !!options.silent;
        let changed = this.checkbox.checked !== next;

        this.updating = true;
        this.checkbox.checked = next;

        if (next) {
            this.checkbox.setAttribute('checked', 'checked');
        } else {
            this.checkbox.removeAttribute('checked');
        }

        this.syncUi({animate});

        if (changed && !silent) {
            this.dispatchChange(next);
        }

        this.updating = false;

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Paints classes and ARIA from the checkbox, which is the source of truth
     * @param {Object} options
     */
    syncUi(options = {}) {

        let on = this.checkbox.checked;
        let animate = options.animate !== false;

        this.element.classList.toggle(CLASS_NO_ANIMATE, !animate);
        this.element.classList.toggle(CLASS_ON, on);
        this.element.setAttribute('aria-checked', on ? 'true' : 'false');

        if (!animate) {
            requestAnimationFrame(() => {
                this.element.classList.remove(CLASS_NO_ANIMATE);
            });
        }
    }

    // --------------------------------------------------------------------------

    /**
     * The old plugin fired `toggle` then `change` on the checkbox, and `toggle`
     * on the mount. Revealer and app code listen on the checkbox.
     * @param {Boolean} value
     */
    dispatchChange(value) {

        this.element.dispatchEvent(new CustomEvent('toggle', {
            bubbles: true,
            detail: value
        }));

        if (window.jQuery) {
            window.jQuery(this.checkbox).trigger('toggle', [value]).trigger('change');
            window.jQuery(this.element).trigger('toggle', [value]);
            return;
        }

        this.checkbox.dispatchEvent(new Event('change', {bubbles: true}));
    }

    // --------------------------------------------------------------------------

    /**
     * Restores the empty mount and the visible checkbox
     */
    destroy() {

        this.element.removeEventListener('click', this.onActivate);
        this.element.removeEventListener('keydown', this.onKeydown);
        this.checkbox.removeEventListener('change', this.onCheckboxChange);

        this.element.replaceChildren();
        this.element.removeAttribute('role');
        this.element.removeAttribute('tabindex');
        this.element.removeAttribute('aria-checked');
        this.element.removeAttribute('aria-disabled');
        this.element.removeAttribute('aria-labelledby');
        this.element.removeAttribute('style');
        this.element.classList.remove(
            CLASS_READY,
            CLASS_ON,
            CLASS_LABELED,
            CLASS_READONLY,
            CLASS_NO_ANIMATE,
            CLASS_LEGACY
        );

        this.checkbox.classList.remove(CLASS_INPUT);
        this.checkbox.removeAttribute('tabindex');
        this.checkbox.removeAttribute('aria-hidden');

        if (this.container) {
            this.container.removeData('instance');
            this.container.removeData('toggles');
        }

        this.ready = false;
    }
}

export default Toggles;
