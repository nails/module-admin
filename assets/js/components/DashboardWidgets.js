import Grid from './Dashboard/Grid.vue';
import {createApp, h} from 'vue';

class DashboardWidgets {

    /**
     * Construct DashboardWidgets
     *
     * @param {_ADMIN_PROXY} adminController
     */
    constructor(adminController) {

        adminController
            .onRefreshUi(() => {

                this.container = document.getElementById('dashboard-widgets');

                if (this.container) {
                    this.dashboard = new Instance(
                        adminController,
                        this.container
                    );
                }
            });
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
}

export default DashboardWidgets;
