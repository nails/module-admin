/* export Repeater */

//  @todo (Pablo - 2019-07-29) - Support sorting of elements

import Mustach from 'mustache';

/* globals $, jQuery */
class Repeater {

    /**
     * Construct Repeater
     * @return {Repeater}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialise
     * @param {HTMLElement} domElement
     * @returns {Repeater}
     */
    init(domElement) {

        $('.js-admin-repeater:not(.js-admin-repeater--processed)', domElement)
            .addClass('js-admin-repeater--processed')
            .each((index, element) => {

                let $element = $(element);
                let instance = new RepeaterInstance(
                    this.adminController,
                    $element,
                    $element.data('data')
                );

                $element.data('js-admin-repeater-instance', instance);
            });

        return this;
    }
}

// --------------------------------------------------------------------------

class RepeaterInstance {

    /**
     * Construct RepeaterInstance
     * @param $element
     * @returns {RepeaterInstance}
     */
    constructor(adminController, $element, data) {

        this.adminController = adminController;
        this.$element = $element
        this.index = 0;

        this.trigger('constructing');

        this.$target = $('.js-admin-repeater__target', this.$element);
        this.$add = $('.js-admin-repeater__add', this.$element);
        this.template = $('.js-admin-repeater__template', this.$element).html();
        $('.js-admin-repeater__template', this.$element).remove();

        this.bindEvents();
        this.load(data || []);

        this.trigger('constructed');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind events to the repeater
     */
    bindEvents() {

        this.trigger('binding');

        this.$add
            .on('click', (e) => {
                this.add();
                return false;
            });

        this.$target
            .on('click', '.js-admin-repeater__remove', (e) => {
                this.remove($(e.currentTarget));
                return false;
            });

        this.$element
            .on('js-admin-repeater:load', (e, data) => {
                this.load(data);
            })
            .on('js-admin-repeater:clear', (e) => {
                this.reset();
            });

        this.trigger('bound');
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new item to the repeaer
     * @param data Any data to sue when rendering the item
     */
    add(data) {

        data = data || {};
        data.index = this.index;

        this.trigger('adding', data);

        let $item = $('<li>').addClass('js-admin-repeater__target__item');
        let template = this.template;

        template = Mustach.render(template, data);

        $item.html(template);

        //  Set the value of any checkboxes
        $('input[type=checkbox]', $item)
            .each((index, item) => {

                let $checkbox = $(item);

                if ($checkbox[0].hasAttribute('data-repeater-checked')) {
                    $checkbox.prop('checked', $checkbox.attr('data-repeater-checked'))
                }
            });

        //  Set the value of any dropdowns
        $('select', $item)
            .each((index, item) => {

                let $select = $(item);

                //  Set value
                //  We use this work-around because the Mustache template is static
                if ($select[0].hasAttribute('data-repeater-value')) {
                    let value = $select.data('repeater-value');
                    $('option[value="' + value + '"]', $select).prop('selected', true);
                }

                //  Instanciate select2
                $select
                    .css('width', '100%')
                    .select2();
            });

        this.$target.append($item);
        this.index++;

        this.$element.find('.js-admin-sortable').trigger('sortable:sort');
        if (!this.noRefresh) {
            this.adminController.refreshUi($item);
        }
        this.trigger('added');
    }

    // --------------------------------------------------------------------------

    /**
     * Removes an item from the repeater
     * @param $btn The button which was clicked
     */
    remove($btn) {

        let $item = $btn
            .closest('.js-admin-repeater__target__item');

        this.trigger('removing', $item);
        $item.remove();
        this.$element.find('.js-admin-sortable').trigger('sortable:sort');
        this.trigger('removed', $item);
    }

    // --------------------------------------------------------------------------

    /**
     * Load an array of data
     * @param data
     */
    load(data) {
        this.trigger('loading', data);
        this.noRefresh = true;
        for (let key in data) {
            if (data.hasOwnProperty(key)) {
                this.add(data[key]);
            }
        }
        this.noRefresh = false;
        this.adminController.refreshUi(this.$element);
        this.trigger('loaded', data);
    }

    // --------------------------------------------------------------------------

    /**
     * Removes all items from the repeater and reset the idnex
     */
    reset() {
        this.trigger('resetting');
        $('.js-admin-repeater__target__item', this.$target)
            .remove();
        this.index = 0;
        this.trigger('reset');
    }

    // --------------------------------------------------------------------------

    /**
     * Triggers an event
     * @param event
     * @param data
     */
    trigger(event, data) {

        let eventData = {
            'instance': this,
            'data': data
        };

        this.adminController.log('Triggering Event: ' + event, eventData);
    }
}

// --------------------------------------------------------------------------

export default Repeater;
