/* export DateTime */

/* globals $, jQuery */

const STORAGE_KEY = 'nails.admin.datetime.alternateTimezones';
const CATALOGUE_ID = 'js-timezone-catalogue';
const CLASS_WRAP = 'timezone-channel-wrap';
const CLASS_FOOTER = 'ui-datepicker-timezones';

class DateTime {

    /**
     * Construct DateTime
     * @return {DateTime}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.processing = false;
        this.checkAgain = [];
        this.catalogue = null;
        this.timezoneFooterObserver = null;
        this.timezoneFooterInput = null;
        this.timezoneFooterTimer = null;
        this.processingFooter = false;

        this.adminController
            .onRefreshUi((e, domElement) => {
                this.init(domElement);
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Inits uninitiated elements
     * @param {HTMLElement} domElement
     * @returns {DateTime}
     */
    init(domElement) {
        this.adminController.log('Initiating new date/time inputs');
        let classes = [
            'input.date:not(.datetime--processed)',
            'input.datetime:not(.datetime--processed)',
            'input.time:not(.datetime--processed)',
        ];
        let $items = $(classes.join(','), domElement)
            .addClass('datetime--processed');

        this.adminController.log(`Found ${$items.length} unprocessed items`);

        $items
            .each((index, element) => {
                if (element.classList.contains('date')) {
                    this.initDate(element);
                } else if (element.classList.contains('datetime')) {
                    this.initDateTime(element);
                } else if (element.classList.contains('time')) {
                    this.initTime(element);
                }
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres Date inputs
     * @param element {HTMLElement}
     */
    initDate(element) {

        let $element = $(element);
        let dateFormat = $element.data('datepicker-dateformat') || 'yy-mm-dd';
        let yearRange = $element.data('datepicker-yearrange') || 'c-100:c+10';

        //  Instanciate datepicker
        $element
            .datepicker({
                'dateFormat': dateFormat,
                'changeMonth': true,
                'changeYear': true,
                'yearRange': yearRange
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres DateTime inputs
     * @param element {HTMLElement}
     */
    initDateTime(element) {

        let $element = $(element);
        let dateFormat = $element.data('datepicker-dateformat') || 'yy-mm-dd';
        let timeFormat = $element.data('datepicker-timeformat') || 'HH:mm:ss';
        let yearRange = $element.data('datepicker-yearrange') || 'c-100:c+10';
        let timezoneAware = $element.is('[data-timezone-aware]');

        let options = {
            'dateFormat': dateFormat,
            'timeFormat': timeFormat,
            'changeMonth': true,
            'changeYear': true,
            'yearRange': yearRange
        };

        if (timezoneAware) {
            this.wrapTimezoneChannel($element);
            options.beforeShow = () => this.scheduleTimezoneFooter(element);
            options.onSelect = () => this.scheduleTimezoneFooter(element);
            options.onChangeMonthYear = () => this.scheduleTimezoneFooter(element);
            options.onClose = () => this.unmountTimezoneFooter();
            $element.on('input.tz change.tz keyup.tz', () => this.refreshTimezoneFooter(element));
        }

        $element.datetimepicker(options);
    }

    // --------------------------------------------------------------------------

    /**
     * Initiatres Time inputs
     * @param element {HTMLElement}
     */
    initTime(element) {

        let $element = $(element);
        let timeFormat = $element.data('datepicker-timeformat') || 'HH:mm';

        $element
            .datetimepicker({
                'timeOnly': true,
                'timeFormat': timeFormat
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Sit the timezone channel in a wrap along the bottom of the control
     * @param {jQuery} $element
     */
    wrapTimezoneChannel($element) {

        if ($element.parent().hasClass(CLASS_WRAP)) {
            return;
        }

        let $channel = $element.closest('.input').find('small.timezone-channel').first();
        $element.wrap($('<span>', {class: CLASS_WRAP}));
        if ($channel.length) {
            $element.parent().append($channel);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * English labels keyed by IANA id, emitted once by the field helper
     * @returns {Object<string, string>}
     */
    getCatalogue() {

        if (this.catalogue) {
            return this.catalogue;
        }

        let node = document.getElementById(CATALOGUE_ID);
        if (!node) {
            this.catalogue = {};
            return this.catalogue;
        }

        try {
            this.catalogue = JSON.parse(node.textContent) || {};
        } catch (e) {
            this.catalogue = {};
        }

        return this.catalogue;
    }

    // --------------------------------------------------------------------------

    /**
     * @param {string} zone
     * @returns {string}
     */
    labelForZone(zone) {
        if (zone === 'UTC' || zone === 'GMT' || zone === 'Etc/UTC' || zone === 'Etc/GMT') {
            return 'UTC/GMT';
        }

        let catalogue = this.getCatalogue();
        let label = catalogue[zone] || zone;

        if (/Coordinated Universal Time/i.test(label)) {
            return 'UTC/GMT';
        }

        return label;
    }

    // --------------------------------------------------------------------------

    /**
     * Alternate IANA ids the user wants to see in the picker
     * @param {HTMLElement} input
     * @returns {string[]}
     */
    getAlternateZones(input) {

        let userTimezone = input.getAttribute('data-user-timezone') || '';
        let stored;

        try {
            stored = JSON.parse(window.localStorage.getItem(STORAGE_KEY));
        } catch (e) {
            stored = null;
        }

        if (!Array.isArray(stored)) {
            stored = [];
        }

        return stored.filter((zone) => zone && zone !== userTimezone);
    }

    // --------------------------------------------------------------------------

    /**
     * @param {string[]} zones
     */
    setAlternateZones(zones) {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(zones));
    }

    // --------------------------------------------------------------------------

    /**
     * Parse a naive `YYYY-MM-DD HH:mm:ss` string as wall time in `timeZone`
     * @param {string} value
     * @param {string} timeZone
     * @returns {Date|null}
     */
    parseNaiveInZone(value, timeZone) {

        if (!value) {
            return null;
        }

        let match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
        if (!match) {
            return null;
        }

        let year = parseInt(match[1], 10);
        let month = parseInt(match[2], 10);
        let day = parseInt(match[3], 10);
        let hour = parseInt(match[4], 10);
        let minute = parseInt(match[5], 10);
        let second = parseInt(match[6] || '0', 10);
        let desired = Date.UTC(year, month - 1, day, hour, minute, second);
        let utc = desired;

        for (let i = 0; i < 3; i++) {
            let parts = this.formatToParts(new Date(utc), timeZone);
            if (!parts) {
                return null;
            }
            let asUtc = Date.UTC(
                parseInt(parts.year, 10),
                parseInt(parts.month, 10) - 1,
                parseInt(parts.day, 10),
                parseInt(parts.hour, 10),
                parseInt(parts.minute, 10),
                parseInt(parts.second, 10)
            );
            utc += desired - asUtc;
        }

        return new Date(utc);
    }

    // --------------------------------------------------------------------------

    /**
     * @param {Date} date
     * @param {string} timeZone
     * @returns {Object<string, string>|null}
     */
    formatToParts(date, timeZone) {

        try {
            let formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: timeZone,
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23'
            });
            let parts = {};
            formatter.formatToParts(date).forEach(({type, value}) => {
                parts[type] = value;
            });
            if (parts.hour === '24') {
                parts.hour = '00';
            }
            return parts;
        } catch (e) {
            return null;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @param {Date} date
     * @param {string} timeZone
     * @returns {string}
     */
    formatInZone(date, timeZone) {

        let parts = this.formatToParts(date, timeZone);
        if (!parts) {
            return '—';
        }

        return [parts.year, parts.month, parts.day].join('-')
            + ' '
            + [parts.hour, parts.minute, parts.second].join(':');
    }

    // --------------------------------------------------------------------------

    /**
     * Datepicker redraws its popup after date clicks; remount on the next tick.
     * @param {HTMLElement} input
     */
    scheduleTimezoneFooter(input) {

        window.clearTimeout(this.timezoneFooterTimer);
        this.timezoneFooterTimer = window.setTimeout(() => {
            this.mountTimezoneFooter(input);
        }, 0);
    }

    // --------------------------------------------------------------------------

    /**
     * Inject the alternate-zone footer into the shared datepicker popup
     * @param {HTMLElement} input
     */
    mountTimezoneFooter(input) {

        let $dp = $('#ui-datepicker-div');
        if (!$dp.length || !$dp.is(':visible')) {
            return;
        }

        this.processingFooter = true;
        this.timezoneFooterInput = input;
        this.observeTimezoneFooter(input);

        this.destroyTimezoneSelect($dp);
        $dp.find('.' + CLASS_FOOTER).remove();

        let $footer = $(this.buildTimezoneFooterHtml(input));
        let $time = $dp.find('.ui-timepicker-div');
        if ($time.length) {
            $time.after($footer);
        } else {
            let $pane = $dp.find('.ui-datepicker-buttonpane');
            if ($pane.length) {
                $pane.before($footer);
            } else {
                $dp.append($footer);
            }
        }

        this.bindTimezoneFooter($footer, input);
        this.initTimezoneSelect($footer);
        this.refreshTimezoneFooter(input);

        $dp.find('.ui-slider')
            .off('slide.tz slidechange.tz')
            .on('slide.tz slidechange.tz', () => this.refreshTimezoneFooter(input));

        $dp.find('.ui_tpicker_time_input')
            .off('input.tz change.tz')
            .on('input.tz change.tz', () => this.refreshTimezoneFooter(input));

        window.setTimeout(() => {
            this.processingFooter = false;
            this.fitElementInViewport($('#ui-datepicker-div'));
        }, 0);
    }

    // --------------------------------------------------------------------------

    /**
     * Re-attach the footer if datepicker replaces the popup contents
     * @param {HTMLElement} input
     */
    observeTimezoneFooter(input) {

        let dp = document.getElementById('ui-datepicker-div');
        if (!dp) {
            return;
        }

        if (this.timezoneFooterObserver) {
            this.timezoneFooterObserver.disconnect();
        }

        this.timezoneFooterObserver = new MutationObserver(() => {
            if (this.processingFooter) {
                return;
            }
            if (!dp.querySelector('.' + CLASS_FOOTER) && $(dp).is(':visible')) {
                this.scheduleTimezoneFooter(input);
            }
        });

        this.timezoneFooterObserver.observe(dp, {childList: true});
    }

    // --------------------------------------------------------------------------

    unmountTimezoneFooter() {

        let $dp = $('#ui-datepicker-div');
        if ($dp.is(':visible')) {
            return;
        }

        window.clearTimeout(this.timezoneFooterTimer);
        this.timezoneFooterTimer = null;
        this.timezoneFooterInput = null;
        this.processingFooter = false;

        if (this.timezoneFooterObserver) {
            this.timezoneFooterObserver.disconnect();
            this.timezoneFooterObserver = null;
        }

        this.destroyTimezoneSelect($dp);
        $dp.find('.' + CLASS_FOOTER).remove();
        $(document).off('mousedown.tzSelect2');
    }

    // --------------------------------------------------------------------------

    /**
     * @param {jQuery} $footer
     */
    initTimezoneSelect($footer) {

        let $select = $footer.find('.js-timezone-add');
        if (!$select.length || !$.fn.select2) {
            return;
        }

        $select.select2({
            placeholder: 'Add a timezone…',
            width: '100%',
            dropdownCssClass: 'ui-datepicker-timezone-select2-drop'
        });

        $select.off('select2-open.tz').on('select2-open.tz', () => {
            window.setTimeout(() => {
                this.fitElementInViewport($('#select2-drop'));
            }, 0);
        });
    }

    // --------------------------------------------------------------------------

    /**
     * @param {jQuery} $context
     */
    destroyTimezoneSelect($context) {

        $context.find('.js-timezone-add').each((_, element) => {
            let $select = $(element);
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
        });
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLElement} input
     * @returns {string}
     */
    buildTimezoneFooterHtml(input) {

        let zones = this.getAlternateZones(input);
        let rows = zones.map((zone) => {
            let label = this.escapeHtml(this.labelForZone(zone));
            let escapedZone = this.escapeHtml(zone);
            return `<div class="ui-datepicker-timezone" data-zone="${escapedZone}">
                <span class="ui-datepicker-timezone__label" title="${escapedZone}">${label}</span>
                <span class="ui-datepicker-timezone__value">—</span>
                <button type="button" class="ui-datepicker-timezone__remove js-timezone-remove" data-zone="${escapedZone}" aria-label="Remove ${label}">&times;</button>
            </div>`;
        }).join('');

        if (!rows) {
            rows = '<p class="ui-datepicker-timezone__empty">No comparison timezones. Add one to see this time in another zone.</p>';
        }

        return `<div class="${CLASS_FOOTER}">
            <div class="ui-datepicker-timezone-list">${rows}</div>
            <label class="ui-datepicker-timezone-add">
                <span class="visually-hidden">Add a timezone</span>
                <select class="js-timezone-add">
                    ${this.buildTimezoneOptions(input, zones)}
                </select>
            </label>
        </div>`;
    }

    // --------------------------------------------------------------------------

    /**
     * @param {HTMLElement} input
     * @param {string[]} selected
     * @returns {string}
     */
    buildTimezoneOptions(input, selected) {

        let catalogue = this.getCatalogue();
        let userTimezone = input.getAttribute('data-user-timezone') || '';
        let chosen = new Set(selected.concat([userTimezone]));
        let options = ['<option></option>'];

        Object.keys(catalogue).forEach((zone) => {
            if (chosen.has(zone)) {
                return;
            }
            options.push(
                '<option value="' + this.escapeHtml(zone) + '">'
                + this.escapeHtml(this.labelForZone(zone))
                + '</option>'
            );
        });

        return options.join('');
    }

    // --------------------------------------------------------------------------

    /**
     * @param {jQuery} $footer
     * @param {HTMLElement} input
     */
    bindTimezoneFooter($footer, input) {

        $footer.on('mousedown', (e) => {
            e.stopPropagation();
        });

        $(document)
            .off('mousedown.tzSelect2')
            .on('mousedown.tzSelect2', '#select2-drop, .select2-drop-mask', (e) => {
                e.stopPropagation();
            });

        $footer.on('change', '.js-timezone-add', (e) => {
            let value = e.target.value;
            if (!value) {
                return;
            }
            let zones = this.getAlternateZones(input);
            if (zones.indexOf(value) === -1) {
                zones.push(value);
                this.setAlternateZones(zones);
            }
            this.mountTimezoneFooter(input);
        });

        $footer.on('click', '.js-timezone-remove', (e) => {
            e.preventDefault();
            e.stopPropagation();
            let zone = $(e.currentTarget).attr('data-zone');
            let zones = this.getAlternateZones(input).filter((item) => item !== zone);
            this.setAlternateZones(zones);
            this.mountTimezoneFooter(input);
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Update the clock values in the open picker footer
     * @param {HTMLElement} input
     */
    refreshTimezoneFooter(input) {

        let $footer = $('#ui-datepicker-div').find('.' + CLASS_FOOTER);
        if (!$footer.length) {
            return;
        }

        let userTimezone = input.getAttribute('data-user-timezone') || 'UTC';
        let instant = this.parseNaiveInZone($(input).val(), userTimezone);

        $footer.find('.ui-datepicker-timezone').each((index, row) => {
            let zone = row.getAttribute('data-zone');
            let $value = $(row).find('.ui-datepicker-timezone__value');
            $value.text(instant ? this.formatInZone(instant, zone) : '—');
        });
    }

    // --------------------------------------------------------------------------

    /**
     * @param {string} value
     * @returns {string}
     */
    escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // --------------------------------------------------------------------------

    /**
     * Keep a positioned popup inside the viewport so it does not grow the page
     * @param {jQuery} $el
     * @param {number} [pad]
     */
    fitElementInViewport($el, pad) {

        if (!$el || !$el.length || !$el.is(':visible')) {
            return;
        }

        pad = pad || 8;
        let rect = $el[0].getBoundingClientRect();
        let dx = 0;
        let dy = 0;

        if (rect.right > window.innerWidth - pad) {
            dx -= rect.right - (window.innerWidth - pad);
        }
        if (rect.left + dx < pad) {
            dx += pad - (rect.left + dx);
        }
        if (rect.bottom > window.innerHeight - pad) {
            dy -= rect.bottom - (window.innerHeight - pad);
        }
        if (rect.top + dy < pad) {
            dy += pad - (rect.top + dy);
        }
        if (!dx && !dy && getComputedStyle($el[0]).position === 'fixed') {
            return;
        }

        $el.css({
            position: 'fixed',
            left: rect.left + dx,
            top: rect.top + dy
        });
    }
}

export default DateTime;
