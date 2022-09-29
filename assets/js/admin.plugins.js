/**
 * Admin Plugins
 * This is the Admin Plugin system
 */

'use strict';
let _ADMIN, _ADMIN_PROXY;

/**
 * Source Imports 🛠
 */
import '../sass/admin.plugins.scss';
import Alerts from './components/Alerts.js';
import Confirm from './components/Confirm.js';
import CopyToClipboard from './components/CopyToClipboard.js';
import DashboardWidgets from './components/DashboardWidgets.js';
import DateTime from './components/DateTime.js';
import DisabledElements from './components/DisabledElements.js';
import DynamicTable from './components/DynamicTable.js';
import Fancybox from './components/Fancybox.js';
import IndexButtons from './components/IndexButtons.js';
import InputHelper from './components/InputHelper.js';
import MatchHeight from './components/MatchHeight.js';
import Modal from './components/Modal.js';
import Modalize from './components/Modalize.js';
//import Navigation from './components/Navigation.js';
import Notes from './components/Notes.js';
import Repeater from './components/Repeater.js';
import Revealer from './components/Revealer.js';
import ScrollToFirstError from './components/ScrollToFirstError.js'
import SearchBoxes from './components/SearchBoxes.js';
import Searcher from './components/Searcher.js';
import Select from './components/Select.js';
import Session from './components/Session.js';
import Sortable from './components/Sortable.js';
import Stripes from './components/Stripes.js';
import Tabs from './components/Tabs.js';
import TimeCode from './components/TimeCode.js';
import Toggles from './components/Toggles.js';
import Wysiwyg from './components/Wysiwyg.js';

/**
 * Plugin Classes 📦
 */

_ADMIN_PROXY = function(vendor, slug, instances) {
    return {
        'vendor': vendor,
        'slug': slug,
        'getInstance': function(plugin, vendor) {
            return window.NAILS.ADMIN.getInstance(plugin, vendor);
        },
        'onRefreshUi': function(callback) {
            window.NAILS.ADMIN.onRefreshUi(callback);
            return this;
        },
        'refreshUi': function(domElement) {
            this.log('🙋‍♀️ UI refresh requested', domElement || document);
            window.NAILS.ADMIN.refreshUi(domElement);
            return this;
        },
        'destroyUi': function(domElement) {
            window.NAILS.ADMIN.destroyUi(domElement);
            return this;
        },
        'onDestroyUi': function(callback) {
            window.NAILS.ADMIN.onDestroyUi(callback);
            return this;
        },
        'log': function() {
            if (typeof (console.log) === 'function') {
                console.log(
                    `%c[${this.vendor}: ${this.slug}]`,
                    'color: goldenrod',
                    ...arguments,
                );
            }
            return this;
        },
        'warn': function() {
            if (typeof (console.warn) === 'function') {
                console.warn(
                    `%c[${this.vendor}: ${this.slug}]`,
                    'color: goldenrod',
                    ...arguments
                );
            }
            return this;
        },
        'error': function() {
            if (typeof (console.error) === 'function') {
                console.error(
                    `%c[${this.vendor}: ${this.slug}]`,
                    'color: goldenrod',
                    ...arguments
                );
            }
            return this;
        }
    }
}

_ADMIN = function() {
    return {

        /**
         * The admin module namespace
         */
        'namespace': 'nails/module-admin',

        /**
         * All the registered plugins
         */
        'instances': {},

        /**
         * Returns a plugin instance
         *
         * @param {String} plugin The plugin's slug
         * @param {String} vendor The plugin's vendor
         * @returns {*}
         */
        'getInstance': function(plugin, vendor) {
            vendor = vendor || window.NAILS.ADMIN.namespace;
            return window.NAILS.ADMIN.instances[vendor][plugin];
        },

        /**
         * Registers a new plugin
         * @param {String} slug The name of the plugin
         * @param plugin
         */
        'registerPlugin': function(vendor, slug, plugin) {

            if (typeof this.instances[vendor] === 'undefined') {
                this.instances[vendor] = {};
            }

            if (typeof plugin === 'function') {

                this.instances[vendor][slug] = plugin(
                    new _ADMIN_PROXY(vendor, slug)
                );

            } else {
                this.instances[vendor][slug] = plugin;
            }

            return this;
        },

        /**
         * Triggers an event
         * @param eventName
         */
        'trigger': function(eventName, detail) {
            document
                .dispatchEvent(
                    new CustomEvent(eventName, {detail: detail})
                );
            return this;
        },

        /**
         * Allows plugins to listen for events
         * @param {String} event The event to listen for
         * @param {function} callback The callback to execute
         */
        'on': function(event, callback) {
            document
                .addEventListener(event, callback);
            return this;
        },

        /**
         * The UI refresh set
         */
        'refreshUiSet': new Set(),

        /**
         * Triggers a UI refresh
         * @param domElement The domElement to focus the refresh on
         */
        'refreshUi': function(domElement) {

            domElement = domElement || document;

            if (this.uiIsRefreshing) {
                this.refreshUiSet.add(domElement);
            }

            this.refreshUiSet.add(domElement);

            clearTimeout(this.refreshTimeout);

            this.refreshTimeout = setTimeout(() => {
                if (this.refreshUiSet.size > 0) {
                    this.uiIsRefreshing = true;
                    this.log(`🔄 Refreshing UI (${this.refreshUiSet.size} items)`);

                    this.refreshUiSet
                        .forEach(domElement => {
                            this.log('👷‍♂️ Refreshing:', domElement);
                            this.trigger('admin:refresh-ui', {domElement: domElement});
                            this.refreshUiSet.delete(domElement);
                            this.log('🙌🏻 Refreshed:', domElement);
                        });

                    this.log('✅ Refreshed UI');
                    this.uiIsRefreshing = false;
                }
            }, 10);

            return this;
        },

        /**
         * Allows plugins to register callbacks for UI refreshing
         * @param {function} callback The callback to execute
         */
        'onRefreshUi': function(callback) {
            this.on('admin:refresh-ui', function(e) {
                callback(e, e.detail ? e.detail.domElement : null);
            });
            return this;
        },

        /**
         * Triggers a UI destroy
         * @param domElement The domElement to focus the destroy on
         */
        'destroyUi': function(domElement) {
            this.log('Destroying UI', domElement || document);
            this.trigger('admin:destroy-ui', {domElement: domElement || document});
            return this;
        },

        /**
         * Allows plugins to register callbacks for UI destruction
         * @param {function} callback The callback to execute
         */
        'onDestroyUi': function(callback) {
            this.on('admin:destroy-ui', function(e) {
                callback(e, e.detail ? e.detail.domElement : null);
            });
            return this;
        },

        // --------------------------------------------------------------------------

        /**
         * Write a log to the console
         * @return {void}
         */
        'log': function() {
            if (typeof (console.log) === 'function') {
                console.log(
                    `%c[${this.namespace}]`,
                    'color: goldenrod',
                    ...arguments
                );
            }
        },

        // --------------------------------------------------------------------------

        /**
         * Write a warning to the console
         * @return {void}
         */
        'warn': function(message, payload) {
            if (typeof (console.warn) === 'function') {
                console.warn(
                    `%c[${this.namespace}]`,
                    'color: goldenrod',
                    ...arguments
                );
            }
        }
    };
};

/**
 * Plugin Manager kick off 🚀
 */
window.NAILS.ADMIN = new _ADMIN();
let namespace = window.NAILS.ADMIN.namespace;

/**
 * Register bundled plugins 📋
 */
window
    .NAILS
    .ADMIN
    .registerPlugin(namespace, 'Alerts', (controller) => new Alerts(controller))
    .registerPlugin(namespace, 'Confirm', (controller) => new Confirm(controller))
    .registerPlugin(namespace, 'CopyToClipboard', (controller) => new CopyToClipboard(controller))
    .registerPlugin(namespace, 'DashboardWidgets', (controller) => new DashboardWidgets(controller))
    .registerPlugin(namespace, 'DateTime', (controller) => new DateTime(controller))
    .registerPlugin(namespace, 'DisabledElements', (controller) => new DisabledElements(controller))
    .registerPlugin(namespace, 'DynamicTable', (controller) => new DynamicTable(controller))
    .registerPlugin(namespace, 'Fancybox', (controller) => new Fancybox(controller))
    .registerPlugin(namespace, 'IndexButtons', (controller) => new IndexButtons(controller))
    .registerPlugin(namespace, 'InputHelper', (controller) => new InputHelper(controller))
    .registerPlugin(namespace, 'MatchHeight', (controller) => new MatchHeight(controller))
    .registerPlugin(namespace, 'Modal', (controller) => new Modal(controller))
    .registerPlugin(namespace, 'Modalize', (controller) => new Modalize(controller))
    .registerPlugin(namespace, 'Notes', (controller) => new Notes(controller))
    .registerPlugin(namespace, 'Repeater', (controller) => new Repeater(controller))
    .registerPlugin(namespace, 'Revealer', (controller) => new Revealer(controller))
    .registerPlugin(namespace, 'ScrollToFirstError', (controller) => new ScrollToFirstError(controller))
    .registerPlugin(namespace, 'SearchBoxes', (controller) => new SearchBoxes(controller))
    .registerPlugin(namespace, 'Searcher', (controller) => new Searcher(controller))
    .registerPlugin(namespace, 'Select', (controller) => new Select(controller))
    .registerPlugin(namespace, 'Session', (controller) => new Session(controller))
    .registerPlugin(namespace, 'Sortable', (controller) => new Sortable(controller))
    .registerPlugin(namespace, 'Stripes', (controller) => new Stripes(controller))
    .registerPlugin(namespace, 'Tabs', (controller) => new Tabs(controller))
    .registerPlugin(namespace, 'TimeCode', (controller) => new TimeCode(controller))
    .registerPlugin(namespace, 'Toggles', (controller) => new Toggles(controller))
    .registerPlugin(namespace, 'Wysiwyg', (controller) => new Wysiwyg(controller));
