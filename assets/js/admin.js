'use strict';
let _ADMIN, _ADMIN_PROXY;

import '../sass/admin.scss';
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
import Modal from './components/Modal.js';
import Modalize from './components/Modalize.js';
import Navigation from './components/Navigation.js';
import Notes from './components/Notes.js';
import QuickAction from './components/QuickAction.js';
import Repeater from './components/Repeater.js';
import Revealer from './components/Revealer.js';
import ScrollToFirstError from './components/ScrollToFirstError.js'
import Searcher from './components/Searcher.js';
import Select from './components/Select.js';
import Session from './components/Session.js';
import Sortable from './components/Sortable.js';
import Stripes from './components/Stripes.js';
import Tabs from './components/Tabs.js';
import TimeCode from './components/TimeCode.js';
import Toggles from './components/Toggles.js';
import Wysiwyg from './components/Wysiwyg.js';

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

window.NAILS.ADMIN = new _ADMIN();

window
    .NAILS
    .ADMIN
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Alerts',
        function(controller) {
            return new Alerts(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Confirm',
        function(controller) {
            return new Confirm(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'CopyToClipboard',
        function(controller) {
            return new CopyToClipboard(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DashboardWidgets',
        function(controller) {
            return new DashboardWidgets(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DateTime',
        function(controller) {
            return new DateTime(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DisabledElements',
        function(controller) {
            return new DisabledElements(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'DynamicTable',
        function(controller) {
            return new DynamicTable(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Fancybox',
        function(controller) {
            return new Fancybox(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'IndexButtons',
        function(controller) {
            return new IndexButtons(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'InputHelper',
        function(controller) {
            return new InputHelper(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Modal',
        function(controller) {
            return new Modal(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Modalize',
        function(controller) {
            return new Modalize(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Navigation',
        function(controller) {
            return new Navigation(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Notes',
        function(controller) {
            return new Notes(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'QuickAction',
        function(controller) {
            return new QuickAction(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Repeater',
        function(controller) {
            return new Repeater(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Revealer',
        function(controller) {
            return new Revealer(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'ScrollToFirstError',
        function(controller) {
            return new ScrollToFirstError(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Searcher',
        function(controller) {
            return new Searcher(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Select',
        function(controller) {
            return new Select(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Session',
        function(controller) {
            return new Session(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Sortable',
        function(controller) {
            return new Sortable(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Stripes',
        function(controller) {
            return new Stripes(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Tabs',
        function(controller) {
            return new Tabs(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'TimeCode',
        function(controller) {
            return new TimeCode(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Toggles',
        function(controller) {
            return new Toggles(controller);
        }
    )
    .registerPlugin(
        window.NAILS.ADMIN.namespace,
        'Wysiwyg',
        function(controller) {
            return new Wysiwyg(controller);
        }
    );
