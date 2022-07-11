<template>
    <div
        v-bind:class="classes.container"
    >
        <div
            v-bind:class="classes.empty"
            v-if="!grid.length"
        >
            <p>
                <i><b class="fa fa-th-large"></b>Your dashboard is empty</i>
                <small>
                    Dashboard widgets show small pieces of data regarding your website.
                    Your dashboard is completely customiseable.
                </small>
            </p>
            <p>
                <button
                    class="btn btn-secondary"
                    v-on:click="add"
                    v-bind:disabled="!widgets.length"
                >
                    Add a widget
                </button>
            </p>
        </div>
        <div
            v-bind:class="classes.grid"
        >
            <grid-layout
                :layout.sync="grid"
                :col-num="colNum"
                :row-height="rowHeight"
                :is-draggable="draggable"
                :is-resizable="resizable"
                :vertical-compact="true"
                :margin="[10, 10]"
                :use-css-transforms="true"
                @layout-updated="saveLayout"
            >

                <grid-item v-for="(item, i) in grid"
                           :x="item.x"
                           :y="item.y"
                           :w="item.w"
                           :h="item.h"
                           :i="item.i"
                           :key="item.i"
                           drag-allow-from="legend"
                >

                    <fieldset
                        v-bind:class="classes.instance.self"
                    >
                        <legend>
                            {{ item.title }}
                            <span
                                v-bind:class="classes.instance.controls"
                            >
                                <button
                                    class="btn btn-xs btn-secondary"
                                    v-on:click="loadBody(item)"
                                >
                                    <b class="fa fa-sync-alt"></b></button>
                                <button
                                    class="btn btn-xs btn-secondary"
                                    v-on:click="configure(item)"
                                    v-if="item.configurable"
                                >
                                    <b class="fa fa-pencil-alt"></b></button>
                                <button
                                    class="btn btn-xs btn-danger"
                                    v-on:click="remove(item)"
                                >
                                    <b class="fa fa-times"></b></button>
                            </span>
                        </legend>
                        <div
                            v-bind:class="item.padded ? classes.instance.padding : ''"
                            v-html="item.body"
                        />
                    </fieldset>

                </grid-item>
            </grid-layout>
        </div>
        <div
            v-bind:class="classes.instance.add"
            v-if="grid.length && widgets.length"
            v-on:click="add"
            title="Add a widget"
        >
            <b class="fa fa-plus"></b>
        </div>
    </div>
</template>
<script>

import API from '../API'
import services from '../Services'
import VueGridLayout from 'vue-grid-layout';
import Instance from '../Modal/Instance'

let vm;

export default {

    name: 'DashboardGrid',

    components: {
        GridLayout: VueGridLayout.GridLayout,
        GridItem: VueGridLayout.GridItem
    },

    props: {
        adminController: {
            type: Object,
            required: true
        },
        userWidgets: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            modals: {},
            widgets: [],
            grid: [],
            draggable: true,
            resizable: true,
            colNum: 4,
            rowHeight: 100,
            index: 0,
            defaultWidth: 4,
            defaultHeight: 3,
            classes: {
                container: 'dashboard-widgets',
                empty: 'dashboard-widgets__empty',
                grid: 'dashboard-widgets__grid',
                instance: {
                    add: 'dashboard-widgets__add',
                    cards: 'dashboard-widgets__add__cards',
                    card: {
                        self: 'dashboard-widgets__add__cards__card',
                        details: 'dashboard-widgets__add__cards__card__details',
                    },
                    self: 'dashboard-widgets__instance',
                    controls: 'dashboard-widgets__instance__controls',
                    padding: 'dashboard-widgets__instance__padding',
                },
            }
        }
    },

    mounted() {

        vm = this;
        this.loadWidgets();
        this.setUpModals();

        this.userWidgets.map((widget) => {
            this.addItem(widget)
        });
    },

    methods: {

        /**
         * Sets up the modals the component needs
         */
        setUpModals() {
            this.modals.choose = new Instance(this.adminController, {defaultTitle: 'Select a widget', minWidth: '80%'});
            this.modals.configure = new Instance(this.adminController, {minWidth: '50%'});
            this.modals.confirm = new Instance(this.adminController, {defaultTitle: 'Are you sure?'});
        },

        /**
         * Opens the widget chooser modal
         */
        add() {
            this.modals.choose.show();
        },

        /**
         * Opens the configuration modal for an instance at a aprticulat index
         * @param {object} instance The widget instance
         */
        configure(instance) {

            this.adminController.log(`Configuring widget at index ${instance.i}`);

            let widget = this.getWidget(instance.slug);

            this.modals.configure
                .setTitle(`Configuring: ${widget.title}`)
                .setBody('Loading...')
                .show();

            this.loadConfig(instance);
        },

        /**
         * Adds a new widget instance
         * @param {object} widget The widget to add
         */
        addItem(widget) {

            this.adminController.log(`Adding widget: ${widget.slug}`, widget.config || {});

            let instance = {
                x: widget.x ?? ((this.grid.length * this.defaultWidth) % this.colNum),
                y: widget.y ?? (this.grid.length + this.colNum),
                w: widget.w ?? (this.defaultWidth),
                h: widget.h ?? (this.defaultHeight),
                i: this.index,
                id: widget.id || null,
                slug: widget.slug,
                config: widget.config || {},
                title: widget.title,
                description: widget.description || null,
                image: widget.image || null,
                body: widget.body || null,
                padded: widget.padded,
                configurable: widget.configurable,
            };

            this.grid.push(instance);

            if (!widget.body) {
                this.loadBody(instance);
            }

            this.index++;
        },

        async loadBody(instance) {

            if (instance.padded) {
                instance.body = `<p>Loading...</p>`;
            } else {
                instance.body = `<p class="${this.classes.instance.padding}">Loading...</p>`;
            }

            let res = await services.apiRequest({
                method: 'post',
                url: API.dashboard.widgets.body,
                data: {
                    slug: instance.slug,
                    config: instance.config
                }
            });

            instance.body = res.data.data;
        },

        async loadConfig(instance) {

            let res = await services.apiRequest({
                method: 'post',
                url: API.dashboard.widgets.config,
                data: {
                    slug: instance.slug,
                    config: instance.config
                }
            });

            let form = document.createElement('form');
            let p = document.createElement('p');
            let button = document.createElement('button');

            p.classList.add('d-grid', 'gap-2');

            form.innerHTML = res.data.data;
            button.classList.add('btn', 'btn-primary');
            button.innerHTML = 'Save';
            button.addEventListener('click', (e) => {
                this.saveConfig(instance, form);
            });

            p.appendChild(button);
            this.modals.configure.setBody([form, p]);
            button.focus();
        },

        saveConfig(instance, form) {

            let config = {};
            Array.from(form.elements).map((element) => {
                if (element.name) {
                    config[element.name] = element.value;
                }
            });

            instance.config = config;

            this.loadBody(instance);
            this.modals.configure.hide();
            this.saveGrid();
        },

        remove(instance) {

            let p = document.createElement('p');
            let confirm = document.createElement('button');
            let cancel = document.createElement('button');

            p.classList.add('d-grid', 'gap-2');

            confirm.classList.add('btn', 'btn-danger');
            confirm.innerText = 'Remove Widget';
            confirm.addEventListener('click', () => {
                this.removeItem(instance);
                this.modals.confirm.hide();
            });

            cancel.classList.add('btn', 'btn-secondary');
            cancel.innerText = 'Cancel';
            cancel.addEventListener('click', () => {
                this.modals.confirm.hide();
            });

            p.appendChild(confirm);
            p.appendChild(cancel);

            this.modals.confirm
                .setBody(p)
                .show();

            confirm.focus();
        },

        /**
         * Removes a widget from the stack
         * @param {object} instance The widget instance
         */
        removeItem: function(instance) {
            this.grid
                .splice(
                    this.grid.map(item => item.i).indexOf(instance.i),
                    1
                );
        },

        /**
         * Loads available widgets and builds the UI
         */
        async loadWidgets() {

            let res = await services.apiRequest({
                method: 'get',
                url: API.dashboard.widgets.fetch,
            });

            this.widgets = res.data.data.widgets;

            this.buildWidgetSelectUi();
        },

        /**
         * Returns a widget definition
         * @param {string} slug The widget's slug
         * @returns {object}
         */
        getWidget(slug) {
            return this.widgets.find((widget) => widget.slug === slug);
        },

        /**
         * Returns a widget insatcne
         * @param {number} index The instance's index
         * @returns {object}
         */
        getInstance(index) {
            return this.grid.find((instance) => instance.i === index);
        },

        /**
         * Builds the widget select UI
         */
        buildWidgetSelectUi() {


            let list = document.createElement('ul');
            list.classList.add(this.classes.instance.cards);


            this.widgets.map((widget) => {
                let li = document.createElement('li');
                let item = document.createElement('div');
                let details = document.createElement('div');
                let title = document.createElement('p');
                let description = document.createElement('p');

                item.classList.add(this.classes.instance.card.self);
                details.classList.add(this.classes.instance.card.details);

                title.innerHTML = widget.title;
                description.innerHTML = widget.description;
                if (widget.image) {
                    item.style.backgroundImage = `url(${widget.image})`;
                }

                item.addEventListener('click', () => {
                    this.addItem(widget);
                    this.modals.choose.hide();
                });

                details.append(title);
                details.append(description);
                item.append(details);
                li.append(item)
                list.appendChild(li);
            });

            this.modals.choose.setBody(list);
        },

        saveLayout() {
            if (!this.first_layout_event_has_happened) {
                this.first_layout_event_has_happened = true;
                return;
            }
            this.saveGrid();
        },

        async saveGrid() {

            this.adminController.log('Saving Grid');

            let grid = [];
            this.grid.map((instance) => {
                grid.push({
                    x: instance.x,
                    y: instance.y,
                    w: instance.w,
                    h: instance.h,
                    i: instance.i,
                    id: instance.id || null,
                    slug: instance.slug,
                    config: instance.config,
                });
            });

            let res = await services.apiRequest({
                method: 'put',
                url: API.dashboard.widgets.save,
                data: {
                    grid: grid,
                }
            });

            this.adminController.log('Saved');

            res.data.data.map((widget) => {
                let instance = this.getInstance(widget.i);
                if (instance) {
                    instance.id = widget.id;
                }
            });
        },
    },
}

</script>
