/**
 * Admin UI
 * This JS powers the main Admin UI, i.e the sidebar and header.
 */

/**
 * Source Imports 🛠
 */
import '../sass/admin.ui.scss';
import CreateModal from './components/admin-ui/CreateModal.vue';
import FilterModal from './components/admin-ui/FilterModal.vue';
import MenuCollapse from './components/admin-ui/MenuCollapse.vue';
import MenuToggle from './components/admin-ui/MenuToggle.vue';
import ModalButton from './components/admin-ui/ModalButton.vue';
import SearchModal from './components/admin-ui/SearchModal.vue';
import SideNav from './components/admin-ui/SideNav.vue';
import Vue from 'vue/dist/vue.esm';
import vSelect from 'vue-select';
import VueSweetalert2 from 'vue-sweetalert2';

Vue.component('v-select', vSelect);
Vue.use(VueSweetalert2);

/**
 * Component Imports 🏗
 */
Vue.component('CreateModal', CreateModal);
Vue.component('FilterModal', FilterModal);
Vue.component('MenuCollapse', MenuCollapse);
Vue.component('MenuToggle', MenuToggle);
Vue.component('ModalButton', ModalButton);
Vue.component('SearchModal', SearchModal);
Vue.component('SideNav', SideNav);

/**
 * Bus 🚌
 */
Vue.prototype.$bus = new Vue();

/**
 * App kickoff 🚀
 */
for (let el of document.getElementsByClassName('admin-vue-app')) {
    new Vue({
        el: el
    });
}
