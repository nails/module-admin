import Grid from './Dashboard/Grid.vue';
import {createApp, h} from 'vue';

class DashboardWidgets {

    /**
     * Construct DashboardWidgets
     *
     * @param {_ADMIN_PROXY} adminController
     */
    constructor(adminController) {

        this.dashboard = null;

        adminController
            .onRefreshUi(() => {

                const container = document.getElementById('dashboard-widgets');

                if (!container) {
                    this.destroy();
                    return;
                }

                // Vue 3 mounts into the host element rather than replacing it,
                // so the #dashboard-widgets node survives. Modal setup calls
                // refreshUi, which would remount forever without this guard.
                if (this.dashboard && this.dashboard.el === container) {
                    return;
                }

                this.destroy();
                this.dashboard = new Instance(adminController, container);
            })
            .onDestroyUi(() => {
                this.destroy();
            });
    }

    /**
     * Unmount the dashboard Vue app if it is running
     *
     * @returns {void}
     */
    destroy() {
        if (this.dashboard) {
            this.dashboard.unmount();
            this.dashboard = null;
        }
    }
}

class Instance {

    /**
     * Construct Instance
     *
     * @param {_ADMIN_PROXY} adminController
     * @param {Element} el
     */
    constructor(adminController, el) {

        //  Class properties
        this.adminController = adminController;
        this.el = el;

        let userWidgets = JSON.parse(this.el.getAttribute('user-widgets')) || [];

        //  Initialise Vue
        this.app = createApp({
            render: () => h(Grid, {
                adminController: adminController,
                userWidgets: userWidgets,
            }),
        });
        this.vue = this.app.mount(this.el);
    }

    /**
     * Unmount the Vue app
     *
     * @returns {void}
     */
    unmount() {
        if (this.app) {
            this.app.unmount();
            this.app = null;
            this.vue = null;
        }
    }
}

export default DashboardWidgets;
