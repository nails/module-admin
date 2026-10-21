/* export Progress */

/* globals $, jQuery */

const SELECTOR = '.js-admin-progress:not(.js-admin-progress--processed)';
const CLASS_PROCESSED = 'js-admin-progress--processed';
const CLASS_TRACK = 'js-admin-progress__track';
const CLASS_BAR = 'js-admin-progress__bar';
const CLASS_LABEL = 'js-admin-progress__label';
const DEFAULT_MAX = 100;

class Progress {

    /**
     * Construct Progress
     * @param {Object} adminController
     * @return {Progress}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.instances = new Map();

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
     * Bind any new progress bars in scope
     * @param {HTMLElement|Document} domElement
     * @returns {Progress}
     */
    init(domElement) {

        let $scope = $(domElement && domElement.nodeType ? domElement : document);

        $scope
            .find(SELECTOR)
            .addBack(SELECTOR)
            .each((index, element) => {
                this.bind(element);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Paint the bar and watch data-value / data-max
     * @param {HTMLElement} element
     * @returns {Progress}
     */
    bind(element) {

        if (this.instances.has(element)) {
            return this;
        }

        element.classList.add(CLASS_PROCESSED);

        let track = element.querySelector('.' + CLASS_TRACK);
        let bar = element.querySelector('.' + CLASS_BAR);
        let label = element.querySelector('.' + CLASS_LABEL);

        if (!track || !bar || !label) {
            element.textContent = '';

            track = document.createElement('span');
            track.className = CLASS_TRACK;
            track.setAttribute('role', 'progressbar');
            track.setAttribute('aria-valuemin', '0');

            bar = document.createElement('span');
            bar.className = CLASS_BAR;
            track.appendChild(bar);

            label = document.createElement('span');
            label.className = CLASS_LABEL;

            element.appendChild(track);
            element.appendChild(label);
        }

        const paint = () => this.paint(element, track, bar, label);
        paint();

        let observer = null;

        if (typeof MutationObserver !== 'undefined') {
            observer = new MutationObserver(paint);
            observer.observe(element, {
                attributes: true,
                attributeFilter: ['data-value', 'data-max'],
            });
        }

        this.instances.set(element, {
            destroy() {
                if (observer) {
                    observer.disconnect();
                }
                element.classList.remove(CLASS_PROCESSED);
            }
        });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sync the fill, label, and aria values from the host attributes
     * @param {HTMLElement} element
     * @param {HTMLElement} track
     * @param {HTMLElement} bar
     * @param {HTMLElement} label
     */
    paint(element, track, bar, label) {

        let value = parseInt(element.getAttribute('data-value'), 10);
        value = Number.isFinite(value) ? Math.max(0, value) : 0;

        let max;
        if (element.hasAttribute('data-max')) {
            max = parseInt(element.getAttribute('data-max'), 10);
            max = Number.isFinite(max) ? Math.max(0, max) : 0;
        } else {
            max = DEFAULT_MAX;
        }

        let percent = max > 0 ? Math.min(100, Math.floor((value / max) * 100)) : 0;

        bar.style.width = percent + '%';
        label.textContent = value + ' / ' + max;
        track.setAttribute('aria-valuenow', String(value));
        track.setAttribute('aria-valuemax', String(max));
    }

    // --------------------------------------------------------------------------

    /**
     * Disconnect observers whose host falls within domElement
     * @param {HTMLElement|Document} domElement
     * @returns {Progress}
     */
    destroy(domElement) {

        let root = (domElement && domElement.nodeType) ? domElement : document;

        this.instances.forEach((instance, element) => {
            if (root === document || root === element || $.contains(root, element)) {
                instance.destroy();
                this.instances.delete(element);
            }
        });

        return this;
    }
}

export default Progress;
