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

        ['mousemove', 'keyup', 'click'].map((event) => {
            document
                .addEventListener(event, () => {
                    this.setActive();
                });
        });

        /**
         * Unreliable at best, but if we can, try to delete the session
         * Support for these event handlers is patchy, so try both and
         * accept that in some circumstances (e.g. mobile app switcher)
         * we just can't reliably detect a page being closed and must
         * rely on heartbeat expiration
         */
        window
            .addEventListener('beforeunload', () => {
                this.destroy();
            });

        window
            .addEventListener('unload', () => {
                this.destroy();
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

        }, (60 / this.bpm) * 1000);

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
