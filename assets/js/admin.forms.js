/* globals console, Mustache */
let _nails_forms = function() {
    var base = this;

    // --------------------------------------------------------------------------

    base.__construct = function() {

        //  @todo: move into CDN module
        base.initMultiFiles();
    };

    // --------------------------------------------------------------------------

    base.initMultiFiles = function() {

        //  Add new rows to the picker
        $(document)
            .on('click', '.js-cdn-multi-action-add', function() {

                base.log('MultiFile: Adding Row');

                var _parent = $(this).closest('.field');
                var _existing = _parent.data('items') || [];
                var newItem = {
                    'id': null,
                    'object_id': null,
                };
                newItem[_parent.data('label-key')] = null;

                _existing.push(newItem);
                _parent.data('items', _existing);

                base.renderMultFiles(_parent, _existing);

                return false;
            });

        //  Remove a row from the picker
        $(document)
            .on('click', '.js-cdn-multi-action-remove', function() {

                base.log('MultiFile: Removing Row');

                var _removeIndex = $(this).data('index');
                var _parent = $(this).closest('.field');
                var _existing = _parent.data('items') || [];
                var _newItems = [];

                for (var i = 0; i < _existing.length; i++) {
                    if (i !== _removeIndex) {
                        _newItems.push(_existing[i]);
                    }
                }

                base.renderMultFiles(_parent, _newItems);
                _parent.data('items', _newItems);

                return false;
            });

        //  Apply listeners to any existing multifile
        $('.field.cdn-multi').each(function() {

            var _defaults = $(this).data('defaults');
            var _labelKey = $(this).data('label-key');
            $(this)
                .data('items', _defaults)
                .data('label-key', _labelKey);

            //  CDN Picker
            $(this).find('.cdn-object-picker')
                .on('picked', function() {
                    base.multiCdnPicked($(this));
                });

            //  Label
            $(this).find('.js-label')
                .on('keyup', function() {
                    base.multiLabelChanged($(this));
                });
        });
    };

    base.renderMultFiles = function(element, items) {

        base.log('MultiFile: Rendering', items);

        var _target = element.find('.js-row-target');
        var _tpl = element.children('.js-row-tpl').html();
        var _render = '';

        _target.empty();

        for (var i = 0; i < items.length; i++) {

            //  Prepare the object
            items[i].index = i;

            //  Render the HTML
            _render = Mustache.render(_tpl, items[i]);

            //  Apply listeners
            _render = $(_render);
            _render.find('.cdn-object-picker')
                .on('picked', function() {
                    base.multiCdnPicked($(this));
                });
            _render.find('.js-label')
                .on('keyup', function() {
                    base.multiLabelChanged($(this));
                });

            //  Add to the target
            _target.append(_render);
        }

        //  Init the CDN Pickers
        window.NAILS.ADMIN.getInstance('ObjectPicker', 'nails/module-cdn').initPickers();
    };

    // --------------------------------------------------------------------------

    base.multiCdnPicked = function(element) {

        var _updateIndex = element.data('index');
        var _parent = element.closest('.field');
        var _existing = _parent.data('items') || [];

        for (var i = 0; i < _existing.length; i++) {
            if (i === _updateIndex) {
                base.log('Updating object ID to ' + element.find('.cdn-object-picker__input').val());
                base.log('At index: ' + _updateIndex);
                _existing[i].object_id = parseInt(element.find('.cdn-object-picker__input').val(), 10) || null;
                break;
            }
        }

        _parent.data('items', _existing);
    };

    // --------------------------------------------------------------------------

    base.multiLabelChanged = function(element) {

        var _updateIndex = element.data('index');
        var _parent = element.closest('.field');
        var _existing = _parent.data('items') || [];

        for (var i = 0; i < _existing.length; i++) {
            if (i === _updateIndex) {
                base.log('Updating label');
                _existing[i][_parent.data('label-key')] = element.val();
                break;
            }
        }

        _parent.data('items', _existing);
    };

    // --------------------------------------------------------------------------

    /**
     * Write a log to the console
     * @param  {String} message The message to log
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    base.log = function(message, payload) {
        if (typeof (console.log) === 'function') {
            if (payload !== undefined) {
                console.log('Nails Forms:', message, payload);
            } else {
                console.log('Nails Forms:', message);
            }
        }
    };

    // --------------------------------------------------------------------------

    /**
     * Write a warning to the console
     * @param  {String} message The message to warn
     * @param  {mixed}  payload Any additional data to display in the console
     * @return {void}
     */
    base.warn = function(message, payload) {
        if (typeof (console.warn) === 'function') {
            if (payload !== undefined) {
                console.warn('Nails Forms:', message, payload);
            } else {
                console.warn('Nails Forms:', message);
            }
        }
    };

    // --------------------------------------------------------------------------

    return base.__construct();
}();
