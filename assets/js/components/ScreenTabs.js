/* export ScreenTabs */

import {scrollTabIntoView} from './TabScroller.js';

/**
 * Admin UI: Screen Tabs
 *
 * Top-level workspaces on an edit screen (e.g. Details | Related). Sits above
 * any nested tab groups, so the controls are rendered server-side rather than
 * derived from the content.
 *
 * Markup:
 *
 *     <nav class="screen-tabs js-screen-tabs" role="tablist">
 *       <button type="button" class="screen-tabs__tab js-screen-tab" data-screen="details">
 *         Details
 *         <span class="screen-tabs__count">3</span>
 *       </button>
 *     </nav>
 *     <div class="screen-tabs__panel js-screen-panel" data-screen="details">…</div>
 *
 * A panel with `screen-tabs__panel--no-save` hides `.admin-floating-controls`
 * while it is active. The URL hash deep-links to a workspace (`#details`); the
 * first workspace is the empty hash. Set `data-hash="off"` on the nav to leave
 * the hash alone (several groups on one page, as on the styleguide).
 *
 * The styling which goes with this lives in assets/sass/admin-plugins/ScreenTabs.scss.
 */

const SELECTOR_NAV = '.js-screen-tabs';
const SELECTOR_TAB = '.js-screen-tab';
const SELECTOR_PANEL = '.js-screen-panel';
const SELECTOR_ERROR = 'div.field.error, .system-alert.error, .alert.alert-danger, .error.show-in-tabs';
const SELECTOR_FLOATING_CONTROLS = '.admin-floating-controls';
const CLASS_READY = 'screen-tabs--ready';
const CLASS_ACTIVE = 'is-active';
const CLASS_ERROR = 'has-error';
const CLASS_NO_SAVE = 'screen-tabs__panel--no-save';
const CLASS_HIDDEN = 'hidden';

class ScreenTabs {

    /**
     * Construct ScreenTabs
     * @param adminController
     * @return {ScreenTabs}
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
     * Binds any new screen-tab groups in scope
     * @param {HTMLElement} [domElement]
     * @returns {ScreenTabs}
     */
    init(domElement) {

        let scope = domElement instanceof Element || domElement instanceof Document
            ? domElement
            : document;

        let nodes = Array.from(scope.querySelectorAll(`${SELECTOR_NAV}:not(.${CLASS_READY})`));

        if (scope instanceof Element && scope.matches(`${SELECTOR_NAV}:not(.${CLASS_READY})`)) {
            nodes.unshift(scope);
        }

        for (let i = 0; i < nodes.length; i++) {
            new ScreenTabGroup(nodes[i], this.adminController);
        }

        return this;
    }
}

class ScreenTabGroup {

    /**
     * @param {HTMLElement} nav The .js-screen-tabs element
     * @param adminController
     */
    constructor(nav, adminController) {

        this.nav = nav;
        this.adminController = adminController;
        this.panels = {};
        this.order = [];
        this.hashEnabled = nav.dataset.hash !== 'off';

        this.scope = nav.closest('form') || nav.parentElement;

        if (!this.scope) {
            return;
        }

        let tabs = Array.from(nav.querySelectorAll(SELECTOR_TAB));

        for (let i = 0; i < tabs.length; i++) {

            let tab = tabs[i];
            let slug = tab.dataset.screen;
            let panel = this.panelFor(slug);

            if (panel) {
                this.panels[slug] = {tab: tab, panel: panel};
                this.order.push(slug);
            }
        }

        if (this.order.length < 2) {
            return;
        }

        this.bind();
        this.activateInitial();

        nav.classList.add(CLASS_READY);
    }

    // --------------------------------------------------------------------------

    /**
     * The panel that belongs to this nav. Searching the whole form binds
     * every group to the first match, so a later group drives the first
     * group's panels and its own stay hidden.
     * @param {String} slug
     * @returns {HTMLElement|null}
     */
    panelFor(slug) {

        //  The tab scroller wraps this nav, so the panels are siblings of
        //  the wrapper, not of the nav.
        let start = this.nav.parentElement
            && this.nav.parentElement.classList.contains('tabs-scroller')
            ? this.nav.parentElement
            : this.nav;

        let el = start.nextElementSibling;

        while (el) {

            if (el.matches(SELECTOR_NAV) || el.querySelector(SELECTOR_NAV)) {
                break;
            }

            if (el.matches(`${SELECTOR_PANEL}[data-screen="${slug}"]`)) {
                return el;
            }

            let nested = el.querySelector(`${SELECTOR_PANEL}[data-screen="${slug}"]`);

            if (nested) {
                return nested;
            }

            el = el.nextElementSibling;
        }

        return null;
    }

    // --------------------------------------------------------------------------

    bind() {

        this.nav.addEventListener('click', (e) => {

            let tab = e.target.closest(SELECTOR_TAB);

            if (!tab || !this.nav.contains(tab)) {
                return;
            }

            e.preventDefault();
            this.activate(tab.dataset.screen);
        });

        if (this.hashEnabled) {
            window.addEventListener('hashchange', () => {

                let slug = this.slugFromHash();

                if (slug) {
                    this.activate(slug, true, false);
                }
            });
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @return {String|null} The panel slug encoded in the URL hash, if any
     */
    slugFromHash() {

        let slug = (window.location.hash || '').replace(/^#/, '');

        return this.panels[slug] ? slug : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Shows the requested workspace, hides the rest
     * @param {String} slug
     * @param {Boolean} [refresh] Whether to ask the admin controller to refresh widgets
     * @param {Boolean} [updateHash] Whether to reflect the workspace in the URL hash
     * @returns {ScreenTabGroup}
     */
    activate(slug, refresh, updateHash) {

        if (typeof refresh === 'undefined') {
            refresh = true;
        }

        if (typeof updateHash === 'undefined') {
            updateHash = true;
        }

        if (!this.panels[slug]) {
            return this;
        }

        for (let i = 0; i < this.order.length; i++) {

            let key = this.order[i];
            let isActive = key === slug;

            this.panels[key].tab.classList.toggle(CLASS_ACTIVE, isActive);
            this.panels[key].panel.classList.toggle(CLASS_ACTIVE, isActive);
        }

        let noSave = this.panels[slug].panel.classList.contains(CLASS_NO_SAVE);
        let controls = this.scope.querySelectorAll(SELECTOR_FLOATING_CONTROLS);

        for (let i = 0; i < controls.length; i++) {
            controls[i].classList.toggle(CLASS_HIDDEN, noSave);
        }

        if (updateHash && this.hashEnabled) {
            let hash = slug === this.order[0] ? '' : '#' + slug;
            history.replaceState(null, '', window.location.pathname + window.location.search + hash);
        }

        scrollTabIntoView(this.panels[slug].tab);

        if (refresh) {
            this.adminController.refreshUi(this.panels[slug].panel);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Picks the workspace to show on load: validation errors win, then the
     * URL hash, then the first workspace
     */
    activateInitial() {

        let firstError = null;

        for (let i = 0; i < this.order.length; i++) {

            let slug = this.order[i];
            let hasError = this.panels[slug].panel.querySelector(SELECTOR_ERROR) !== null;

            this.panels[slug].tab.classList.toggle(CLASS_ERROR, hasError);

            if (hasError && !firstError) {
                firstError = slug;
            }
        }

        let slug = firstError || (this.hashEnabled && this.slugFromHash()) || this.order[0];

        this.activate(slug, true, false);
    }
}

export default ScreenTabs;
