/* export DynamicTable */

/* globals Mustache, $, jQuery */
class DynamicTable {

    /**
     * Construct DynamicTable
     * @return {DynamicTable}
     */
    constructor(adminController) {
        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                $('.js-admin-dynamic-table:not(.ready)')
                    .addClass('ready')
                    .each((index, element) => {
                        this.init($(element));
                    });
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialise a dynamic table
     * @param {jQuery} $table The table DOM element
     * @return {DynamicTable}
     */
    init($table) {

        $table.trigger('dynamic-table:starting');

        let $template = $table.find('.js-admin-dynamic-table__template');
        let $body = $table.find('.js-admin-dynamic-table__body');
        let data = $table.data('data') || [];
        let confirmDelete = $table.data('confirm-delete') === true;

        $table.data('template', $template.html());
        $table.data('index', 0);

        if (confirmDelete) {
            $table.data('modal', this.adminController.getInstance('Modal').create());
        }

        $body.empty();

        this.bindEvents($table, $body, confirmDelete);

        for (let i = 0, j = data.length; i < j; i++) {
            this.add($table, $body, data[i], false);
        }

        $table.trigger('dynamic-table:ready');

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Bind events
     * @param {jQuery} $table The table DOM element
     * @param {jQuery} $body The body DOM element
     * @param {Boolean} confirmDelete Whether to confirm deletion
     * @return {DynamicTable}
     */
    bindEvents($table, $body, confirmDelete) {
        $('.js-admin-dynamic-table__add', $table)
            .on('click', () => {
                this.add($table, $body, {}, true);
                return false;
            });

        $table
            .on('click', '.js-admin-dynamic-table__remove', (e) => {
                e.preventDefault();
                e.stopPropagation();

                if (confirmDelete) {

                    $table.data('modal')
                        .setTitle('Are you sure?')
                        .setBody('Remove this item?')
                        .clearActions()
                        .addAction('OK', ['btn-primary'], (event, modal) => {
                            this.remove($table, $(e.currentTarget).closest('tr'));
                            modal.hide();
                        })
                        .addAction('Cancel', ['btn-danger'], (event, modal) => {
                            modal.hide();
                        })
                        .show();

                } else {
                    this.remove($table, $(e.currentTarget).closest('tr'));
                }

                return false;
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Add a new row
     * @param {jQuery} $table The table DOM element
     * @param {jQuery} $body The body DOM element
     * @param {Object} data Data to render the row with
     * @param {Boolean} refreshUi Whetehr to refresh the UI after adding
     * @return {DynamicTable}
     */
    add($table, $body, data, refreshUi) {

        data = data || {};
        data.index = $table.data('index') || 0;

        let $row = $(Mustache.render(
            $table.data('template').replace('<\\/script>', '</script>'),
            data
        ));

        //  Set the value of any checkboxes
        $('input[type=checkbox]', $row)
            .each((index, item) => {

                let $checkbox = $(item);

                if ($checkbox[0].hasAttribute('data-dynamic-table-checked')) {
                    $checkbox.prop('checked', $checkbox.attr('data-dynamic-table-checked'))
                }
            });

        //  Set the value of any dropdowns
        $('select', $row)
            .each((index, item) => {

                let $select = $(item);

                //  Set value
                //  We use this work-around because the Mustache template is static
                if ($select[0].hasAttribute('data-dynamic-table-value')) {
                    let value = $select.data('dynamic-table-value');
                    $('option[value="' + value + '"]', $select).prop('selected', true);
                }

                //  Instanciate select2
                $select
                    .css('width', '100%')
                    .select2();
            });

        $body.append($row);
        $table.data('index', data.index + 1);
        $table.trigger('dynamic-table:add', [$row]);
        $table.find('.js-admin-sortable').trigger('sortable:sort');

        if (refreshUi) {
            this.adminController.refreshUi($row);
        }

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Remove a row
     * @param {jQuery} $table The table DOM element
     * @param {jQuery} $row The row DOM element
     * @return {DynamicTable}
     */
    remove($table, $row) {
        $row.remove();
        $table.trigger('dynamic-table:remove');
        $table.find('.js-admin-sortable').trigger('sortable:sort');
        return this;
    }
}

export default DynamicTable;
