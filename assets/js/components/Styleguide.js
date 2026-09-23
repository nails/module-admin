/* export Styleguide */

/**
 * Admin UI: Styleguide
 *
 * Highlights the in-page nav item for the section currently in view.
 * A no-op on every other admin screen.
 */

const SELECTOR_ROOT = '.group-admin-styleguide';
const SELECTOR_NAV = '.styleguide-nav';
const CLASS_ACTIVE = 'is-active';

class Styleguide {

    /**
     * @param adminController
     * @return {Styleguide}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.started = false;

        this.adminController
            .onRefreshUi(() => {
                this.init();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    init() {

        if (this.started) {
            return this;
        }

        this.root = document.querySelector(SELECTOR_ROOT);

        if (!this.root) {
            return this;
        }

        this.nav = this.root.querySelector(SELECTOR_NAV);

        if (!this.nav) {
            return this;
        }

        this.started = true;
        this.links = Array.from(this.nav.querySelectorAll('a[href^="#"]'));
        this.sections = this.links
            .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
            .filter(Boolean);

        this.bind();
        this.observe();
        this.syncActive();

        return this;
    }

    // --------------------------------------------------------------------------

    bind() {

        this.nav.addEventListener('click', (e) => {

            let link = e.target.closest('a[href^="#"]');

            if (!link || !this.nav.contains(link)) {
                return;
            }

            let target = document.getElementById(link.getAttribute('href').slice(1));

            if (!target) {
                return;
            }

            e.preventDefault();
            target.scrollIntoView({behavior: 'smooth', block: 'start'});
            history.replaceState(null, '', link.getAttribute('href'));
            this.setActive(link);
        });
    }

    // --------------------------------------------------------------------------

    observe() {

        window.addEventListener('scroll', () => {
            this.syncActive();
        }, {passive: true});
    }

    // --------------------------------------------------------------------------

    /**
     * Mark the nav item for the section whose heading is nearest the topbar
     */
    syncActive() {

        if (!this.sections.length) {
            return;
        }

        let offset = 100;
        let current = this.sections[0];

        for (let i = 0; i < this.sections.length; i++) {
            if (this.sections[i].getBoundingClientRect().top <= offset) {
                current = this.sections[i];
            }
        }

        let link = this.links.find((item) => item.getAttribute('href') === '#' + current.id);

        if (link) {
            this.setActive(link);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLAnchorElement} link
     */
    setActive(link) {

        for (let i = 0; i < this.links.length; i++) {
            this.links[i].classList.toggle(CLASS_ACTIVE, this.links[i] === link);
        }
    }
}

export default Styleguide;
