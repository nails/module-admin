/**
 * Admin UI
 * This JS powers the main Admin UI, i.e the sidebar and header.
 */

/**
 * Source Imports 🛠
 */
import '../sass/admin.ui.scss';
import {createApp} from 'vue';
import mitt from 'mitt';
import CreateModal from './components/admin-ui/CreateModal.vue';
import FilterModal from './components/admin-ui/FilterModal.vue';
import MenuCollapse from './components/admin-ui/MenuCollapse.vue';
import MenuToggle from './components/admin-ui/MenuToggle.vue';
import ModalButton from './components/admin-ui/ModalButton.vue';
import SearchModal from './components/admin-ui/SearchModal.vue';
import SideNav from './components/admin-ui/SideNav.vue';
import vSelect from 'vue-select';
import VueSweetalert2 from 'vue-sweetalert2';

const emitter = mitt();
const $bus = {
    $on: (...args) => emitter.on(...args),
    $off: (...args) => emitter.off(...args),
    $emit: (...args) => emitter.emit(...args),
};

const components = {
    'v-select': vSelect,
    CreateModal,
    FilterModal,
    MenuCollapse,
    MenuToggle,
    ModalButton,
    SearchModal,
    SideNav,
};

/**
 * App kickoff 🚀
 *
 * Each .admin-vue-app island is a separate createApp() so PHP in-DOM
 * templates (header, sidenav, footer) compile independently, while
 * sharing one event bus and the same component / plugin registrations.
 */
for (let el of document.getElementsByClassName('admin-vue-app')) {
    const app = createApp({});

    Object.entries(components).forEach(([name, component]) => {
        app.component(name, component);
    });

    app.use(VueSweetalert2);
    app.config.globalProperties.$bus = $bus;
    app.mount(el);
}
