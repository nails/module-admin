import services from './Services'
import API from "./API";

class Session {
    /**
     * Construct Session
     * @return {Session}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.bpm = 10;
        this.hiddenPulseInterval = 60 * 1000; // Background tabs are throttled to ~1/min anyway
        this.eventsBound = false;
        this.inactiveAfter = 60 * 5 * 1000; // 5 mins
        this.token = null;
        this.active = true;
        this.state = 'NOT_READY';
        this.elements = [];

        adminController
            .onRefreshUi((e, domElement) => {

                $('.js-admin-session--also-here:not(.js-admin-session--also-here--processed)', domElement)
                    .addClass('js-admin-session--also-here--processed')
                    .each((index, element) => {
                        this.elements.push(element);
                    });

                if (this.elements.length) {
                    this.init();
                }
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initialises the class, if in the correct state
     */
    init() {
        if (this.state === 'NOT_READY') {
            this.state = 'SETTING_UP';
            this.create()
                .then(() => {
                    this.state = 'READY';
                });
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Creates a new session and sets things up
     * @returns {Promise<*>}
     */
    create() {
        return services
            .apiRequest({
                method: 'post',
                url: API.session.create,
                data: {
                    url: window.location.href
                }
            })
            .then((res) => {

                this.token = res.data.data.token;
                this.active = true;
                this.adminController.log('Starting new session', this.token);
                this
                    .bindEvents()
                    .pulse()
                    .setActive()
                    .updateUI(res.data.data.here);
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Destroys the current session
     */
    destroy() {
        if (this.token) {
            this.adminController.log('Destroying session', this.token);
            navigator.sendBeacon(`${window.SITE_URL}api/${API.session.destroy(this.token)}`);
            this.token = null;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Binds all the events
     * @returns {Session}
     */
    bindEvents() {

        if (this.eventsBound) {
            return this;
        }
        this.eventsBound = true;

        ['mousemove', 'keyup', 'click'].map((event) => {
            document
                .addEventListener(event, () => {
                    // Interaction implies the tab is in use; ignore synthetic input while hidden
                    if (!document.hidden) {
                        this.setActive();
                    }
                });
        });

        /**
         * pagehide is the only reliable "going away" signal (beforeunload fires
         * even when navigation is cancelled, unload is unreliable and blocks the
         * bfcache). The explicit leave means the server-side heartbeat expiry
         * is only a fallback for crashes and lost connections.
         */
        window
            .addEventListener('pagehide', () => {
                this.destroy();
            });

        /**
         * Page restored from the back/forward cache: pagehide destroyed our
         * session, so start a fresh one
         */
        window
            .addEventListener('pageshow', (e) => {
                if (e.persisted && !this.token && this.state === 'READY') {
                    this.create();
                }
            });

        /**
         * Hidden tabs are marked inactive immediately and heartbeat slowly;
         * returning to the tab makes us active again straight away
         */
        document
            .addEventListener('visibilitychange', () => {
                if (!this.token) {
                    return;
                }
                if (document.hidden) {
                    clearTimeout(this.inactiveTimeout);
                    this.setInactive();
                } else {
                    this.setActive();
                }
                this.pulse();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sends a heartbeat pulse
     * @returns {Session}
     */
    pulse() {

        clearTimeout(this.pulseTimeout);

        this.pulseTimeout = setTimeout(() => {

            if (this.token) {

                this.adminController.log('Pulse');

                services
                    .apiRequest({
                        method: 'put',
                        url: API.session.heartbeat(this.token)
                    })
                    .then((res) => {
                        this.updateUI(res.data.data.here);
                    })
                    .finally((res) => {
                        this.pulse();
                    });

            } else {
                this.pulse();
            }

        }, document.hidden ? this.hiddenPulseInterval : (60 / this.bpm) * 1000);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Schedules the "setInactive" call
     * @returns {Session}
     */
    scheduleInactive() {

        clearTimeout(this.inactiveTimeout);

        this.inactiveTimeout = setTimeout(() => {
            this.setInactive();
        }, this.inactiveAfter);

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the session as inactive
     * @returns {Session}
     */
    setActive() {
        if (!this.active && this.token) {

            this.active = true;
            this.adminController.log('Setting session as active');

            services
                .apiRequest({
                    method: 'delete',
                    url: API.session.inactive(this.token)
                })
                .then((res) => {
                    this.updateUI(res.data.data.here);
                });
        }

        this.scheduleInactive();

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the session as inactive
     * @returns {Session}
     */
    setInactive() {
        if (this.token && this.active) {

            this.adminController.log('Setting session as inctive');

            this.active = false;

            services
                .apiRequest({
                    method: 'put',
                    url: API.session.inactive(this.token)
                })
                .then((res) => {
                    this.updateUI(res.data.data.here);
                });
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Updates the "who's here" UI
     * @param {Array} here The array of other sessions
     * @returns {Session}
     */
    updateUI(here) {
        this.adminController.log('Updating UI');
        this.elements.map((el) => {
            el.innerHTML = '';

            if (here.length === 0) {
                return;
            }

            let list = document.createElement('ul');
            let item = document.createElement('li');

            item.innerHTML = el.getAttribute('data-prefix') || 'Also on this page:';
            list.appendChild(item);

            here.map((here) => {

                let item = document.createElement('li');
                let spanName = document.createElement('span');
                let spanLastSeen = document.createElement('span');

                spanName.classList.add('name');
                spanName.innerHTML = here.user.name;
                spanName.title = `On page for ${here.created}`

                spanLastSeen.classList.add('last-seen');
                if (here.inactive) {
                    spanLastSeen.innerHTML = ` (inactive for ${here.inactive})`;
                }

                item.appendChild(spanName);
                item.appendChild(spanLastSeen);

                list.appendChild(item);
            });

            el.appendChild(list);
        });
        return this;
    }
}

export default Session
