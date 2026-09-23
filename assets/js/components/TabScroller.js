/* export TabScroller */

/**
 * Admin UI: Tab Scroller
 *
 * When a tab bar has more labels than will fit, the track scrolls sideways
 * instead of wrapping. Chevrons fade in at each end of the overflow; the
 * active tab is kept in view when the selection changes.
 *
 * Applies to `ul.tabs` (underline and segmented) and `.js-screen-tabs`.
 * The original element stays in the tree so existing sibling/descendant
 * selectors still match after a `:has()` hop through the wrapper.
 */

const SELECTOR_TRACK = 'ul.tabs, .js-screen-tabs';
const CLASS_SCROLLER = 'tabs-scroller';
const CLASS_BAR = 'tabs-scroller--bar';
const CLASS_INSET = 'tabs-scroller--inset';
const CLASS_OVERFLOWING = 'is-overflowing';
const CLASS_OVERFLOW_START = 'is-overflow-start';
const CLASS_OVERFLOW_END = 'is-overflow-end';
const OVERFLOW_PX = 4;

/**
 * Scroll `el` into its nearest tab track, leaving room for the chevrons
 * @param {HTMLElement} el
 */
export function scrollTabIntoView(el) {

    if (!el) {
        return;
    }

    let track = el.closest(SELECTOR_TRACK);

    if (!track) {
        return;
    }

    let elRect = el.getBoundingClientRect();
    let trackRect = track.getBoundingClientRect();
    let pad = 48;

    if (elRect.left < trackRect.left + pad) {
        track.scrollBy({
            left: elRect.left - trackRect.left - pad,
            behavior: 'smooth'
        });
    } else if (elRect.right > trackRect.right - pad) {
        track.scrollBy({
            left: elRect.right - trackRect.right + pad,
            behavior: 'smooth'
        });
    }
}

class TabScroller {

    /**
     * @param adminController
     * @return {TabScroller}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.bound = new WeakSet();

        this.adminController
            .onRefreshUi(() => {
                this.init();
            });

        window.addEventListener('resize', () => {
            this.refreshAll();
        });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Wrap any new tab tracks and measure overflow
     * @returns {TabScroller}
     */
    init() {

        let nodes = document.querySelectorAll(SELECTOR_TRACK);

        for (let i = 0; i < nodes.length; i++) {
            this.ensure(nodes[i]);
        }

        this.refreshAll();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLElement} track
     * @returns {HTMLElement} The scroller wrapper
     */
    ensure(track) {

        if (track.parentElement && track.parentElement.classList.contains(CLASS_SCROLLER)) {
            this.bindTrack(track, track.parentElement);
            return track.parentElement;
        }

        let wrap = document.createElement('div');
        wrap.className = CLASS_SCROLLER;

        if (track.matches('ul.tabs') && !track.classList.contains('tabs--segmented')) {
            wrap.classList.add(CLASS_BAR);
        }

        if (track.closest('fieldset')) {
            wrap.classList.add(CLASS_INSET);
        }

        let prev = this.makeButton('prev');
        let next = this.makeButton('next');

        track.parentNode.insertBefore(wrap, track);
        wrap.appendChild(prev);
        wrap.appendChild(track);
        wrap.appendChild(next);

        this.bindTrack(track, wrap);

        return wrap;
    }

    // --------------------------------------------------------------------------

    /**
     * @param {String} dir `prev` or `next`
     * @returns {HTMLButtonElement}
     */
    makeButton(dir) {

        let btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'tabs-scroller__btn tabs-scroller__btn--' + dir;
        btn.setAttribute(
            'aria-label',
            dir === 'prev' ? 'Show previous tabs' : 'Show next tabs'
        );
        btn.innerHTML = '<i class="fa fa-chevron-' + (dir === 'prev' ? 'left' : 'right') + '" aria-hidden="true"></i>';

        btn.addEventListener('click', (e) => {

            e.preventDefault();
            e.stopPropagation();

            let wrap = btn.parentElement;
            let track = wrap.querySelector(SELECTOR_TRACK);
            let amount = Math.max(track.clientWidth * 0.65, 160);

            track.scrollBy({
                left: dir === 'prev' ? -amount : amount,
                behavior: 'smooth'
            });
        });

        return btn;
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLElement} track
     * @param {HTMLElement} wrap
     */
    bindTrack(track, wrap) {

        if (this.bound.has(track)) {
            return;
        }

        this.bound.add(track);

        track.addEventListener('scroll', () => {
            this.update(wrap, track);
        }, {passive: true});

        if (typeof ResizeObserver === 'function') {
            new ResizeObserver(() => {
                this.update(wrap, track);
            }).observe(track);
        }
    }

    // --------------------------------------------------------------------------

    refreshAll() {

        let wrappers = document.querySelectorAll('.' + CLASS_SCROLLER);

        for (let i = 0; i < wrappers.length; i++) {
            let track = wrappers[i].querySelector(SELECTOR_TRACK);
            if (track) {
                this.update(wrappers[i], track);
            }
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLElement} wrap
     * @param {HTMLElement} track
     */
    update(wrap, track) {

        let bg = window.getComputedStyle(track).backgroundColor;
        if (bg && bg !== 'transparent' && bg !== 'rgba(0, 0, 0, 0)') {
            wrap.style.setProperty('--tabs-scroller-fade', bg);
        }

        let max = track.scrollWidth - track.clientWidth;
        let overflowing = max > OVERFLOW_PX;

        wrap.classList.toggle(CLASS_OVERFLOWING, overflowing);
        wrap.classList.toggle(CLASS_OVERFLOW_START, overflowing && track.scrollLeft > OVERFLOW_PX);
        wrap.classList.toggle(
            CLASS_OVERFLOW_END,
            overflowing && track.scrollLeft < max - OVERFLOW_PX
        );
    }
}

export default TabScroller;
