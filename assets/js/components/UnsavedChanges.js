/* export UnsavedChanges */

/* globals $, jQuery */

/**
 * Opt-in dirty-form check.
 *
 * Takes a fingerprint of successful form fields after other plugins have
 * had a chance to initialise, then compares it on native field events and
 * on a short poll (values that change without an event still show up).
 *
 * Opt in with `data-unsaved-changes` on the form, or on
 * `.admin-floating-controls` (the closest form is bound).
 */

const SELECTOR_FORM = 'form[data-unsaved-changes]:not([data-unsaved-changes="false"])';
const SELECTOR_BAR = '.admin-floating-controls[data-unsaved-changes]:not([data-unsaved-changes="false"])';
const CLASS_BOUND = 'unsaved-changes--bound';
const CLASS_ALERT = 'admin-floating-controls__unsaved';
const SETTLE_MS = 500;
const POLL_MS = 1000;

class UnsavedChanges {

    /**
     * @param {Object} adminController
     * @return {UnsavedChanges}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.forms = new Set();

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        // A later scan picks up opt-in attributes stamped after the first
        // refreshUi (app JS, or controls that appear after plugins boot).
        setTimeout(() => {
            this.init(document);
        }, SETTLE_MS);

        this.poll = setInterval(() => {
            this.init(document);
            this.forms.forEach((boundForm) => this.check(boundForm));
        }, POLL_MS);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind any opted-in forms in scope
     * @param {HTMLElement|Document} domElement
     * @returns {UnsavedChanges}
     */
    init(domElement) {

        let $scope = $(domElement && domElement.nodeType ? domElement : document);

        $scope
            .find(SELECTOR_FORM)
            .addBack(SELECTOR_FORM)
            .add(
                $scope
                    .find(SELECTOR_BAR)
                    .addBack(SELECTOR_BAR)
                    .closest('form')
            )
            .each((index, element) => {
                this.bind(element);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Watch a single form
     * @param {HTMLFormElement} form
     * @returns {UnsavedChanges}
     */
    bind(form) {

        if (!form || form.nodeName !== 'FORM' || form.classList.contains(CLASS_BOUND)) {
            return this;
        }

        form.classList.add(CLASS_BOUND);
        this.forms.add(form);

        let state = {
            baseline: null,
            dirty: false,
            interacted: false,
            submitting: false,
        };

        form._unsavedChanges = state;

        this.ensureAlert(form);

        let onFieldEvent = () => {
            state.interacted = true;
            this.check(form);
        };

        form.addEventListener('input', onFieldEvent, true);
        form.addEventListener('change', onFieldEvent, true);

        form.addEventListener('submit', () => {
            state.submitting = true;
            this.setDirty(form, false);
        });

        // Immediate snapshot, then a second one after other plugins settle —
        // unless the user has already edited, in which case keep the first.
        state.baseline = this.fingerprint(form);

        setTimeout(() => {
            if (!state.interacted) {
                state.baseline = this.fingerprint(form);
            }
            this.check(form);
        }, SETTLE_MS);

        if (!this.beforeUnloadBound) {
            this.beforeUnloadBound = true;
            window.addEventListener('beforeunload', (event) => {
                let dirty = Array.from(this.forms).some((boundForm) => {
                    let boundState = boundForm._unsavedChanges;
                    return boundState && boundState.dirty && !boundState.submitting;
                });

                if (dirty) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Compare the current fingerprint with the baseline
     * @param {HTMLFormElement} form
     * @returns {UnsavedChanges}
     */
    check(form) {

        let state = form._unsavedChanges;

        if (!state || state.submitting || state.baseline === null) {
            return this;
        }

        this.setDirty(form, this.fingerprint(form) !== state.baseline);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Show or hide the save-bar notice
     * @param {HTMLFormElement} form
     * @param {Boolean} dirty
     * @returns {UnsavedChanges}
     */
    setDirty(form, dirty) {

        let state = form._unsavedChanges;

        if (!state || state.dirty === dirty) {
            return this;
        }

        state.dirty = dirty;
        form.classList.toggle('is-dirty', dirty);

        $(form)
            .find('.' + CLASS_ALERT)
            .prop('hidden', !dirty);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Insert the notice beside the save button
     * @param {HTMLFormElement} form
     * @returns {UnsavedChanges}
     */
    ensureAlert(form) {

        let $bar = $(form).find('.admin-floating-controls').first();

        if (!$bar.length || $bar.find('.' + CLASS_ALERT).length) {
            return this;
        }

        let $alert = $('<span>', {
            class: CLASS_ALERT,
            text: 'Unsaved changes',
            hidden: true,
            role: 'status',
            'aria-live': 'polite',
        });

        let $save = $bar.find('button[type="submit"]').first();

        if ($save.length) {
            $save.after($alert);
        } else {
            $bar.prepend($alert);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Stable string of current successful field values
     * @param {HTMLFormElement} form
     * @returns {String}
     */
    fingerprint(form) {

        let pairs = [];

        Array.from(form.elements).forEach((el) => {

            if (!el.name || el.disabled) {
                return;
            }

            if (/csrf/i.test(el.name)) {
                return;
            }

            let type = (el.type || el.tagName).toLowerCase();

            if (['submit', 'button', 'reset', 'image'].indexOf(type) !== -1) {
                return;
            }

            if (type === 'file') {
                let files = Array.from(el.files || []).map((file) => {
                    return file.name + ':' + file.size + ':' + file.lastModified;
                });
                pairs.push([el.name, files.join(',')]);
                return;
            }

            if (type === 'checkbox' || type === 'radio') {
                if (el.checked) {
                    pairs.push([el.name, el.value]);
                }
                return;
            }

            if (el.tagName === 'SELECT' && el.multiple) {
                Array.from(el.selectedOptions).forEach((option) => {
                    pairs.push([el.name, option.value]);
                });
                return;
            }

            pairs.push([el.name, el.value]);
        });

        pairs.sort((a, b) => {
            return a[0].localeCompare(b[0]) || a[1].localeCompare(b[1]);
        });

        return pairs
            .map((pair) => pair[0] + '=' + pair[1])
            .join('\n');
    }
}

export default UnsavedChanges;
