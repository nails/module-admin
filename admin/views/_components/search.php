<div class="search clearfix">
    <div class="mask">
        <b class="fa fa-refresh fa-2x"></b>
    </div>
    <?php

    $oInput = \Nails\Factory::service('Input');
    parse_str($oInput->server('QUERY_STRING'), $aQuery);

    unset($aQuery['keywords']);
    unset($aQuery['sortOn']);
    unset($aQuery['sortOrder']);
    unset($aQuery['perPage']);
    unset($aQuery['page']);
    unset($aQuery['cbF']);
    unset($aQuery['ddF']);

    $aFormAttr = [
        'method' => 'GET',
    ];

    echo form_open(null, $aFormAttr);

    foreach ($aQuery as $sKey => $sValue) {
        echo form_hidden($sKey, $sValue);
    }

    if ($searchable) {
        echo '<div class="search-text">';
        echo form_input(
            'keywords',
            $keywords,
            'autocomplete="off" placeholder="Type your search term and hit enter"'
        );
        echo '</div>';
    }

    // --------------------------------------------------------------------------

    if (!empty($injectHtml)) {

        echo '<div class="search-inject">';
        echo $injectHtml;
        echo '</div>';
    }

    // --------------------------------------------------------------------------

    //  Filters
    if (!empty($dropdownFilter)) {

        echo !empty($injectHtml) || $searchable ? '<hr />' : '';
        foreach ($dropdownFilter as $iFilterIndex => $oFilter) {

            echo '<span class="filterGroup dropdown">';
            echo '<span class="filterLabel">';
            echo $oFilter->getLabel();
            echo '</span>';

            echo '<span class="filterDropdown">';
            echo '<select name="ddF[' . $iFilterIndex . ']" class="select2">';
            foreach ($oFilter->getOptions() as $iOptionIndex => $oOption) {

                //  Checked or not?
                if (!empty($_GET)) {
                    $bSelected = isset($_GET['ddF'][$iFilterIndex]) && $_GET['ddF'][$iFilterIndex] == $iOptionIndex;
                } else {
                    $bSelected = $oOption->isSelected();
                }

                $sSelected = $bSelected ? 'selected="selected"' : '';

                echo '<option value="' . $iOptionIndex . '" ' . $sSelected . '>';
                echo $oOption->getLabel();
                echo '</option>';
            }
            echo '</select>';
            echo '</span>';

            echo '</span>';
        }
    }

    if (!empty($checkboxFilter)) {

        echo '<hr>';
        echo !empty($injectHtml) || $searchable || !empty($dropdownFilter) ? '<hr />' : '';
        foreach ($checkboxFilter as $iFilterIndex => $oFilter) {

            echo '<span class="filterGroup">';
            echo '<span class="filterLabel">';
            echo $oFilter->getLabel();
            echo '</span>';

            foreach ($oFilter->getOptions() as $iOptionIndex => $oOption) {

                //  Checked or not?
                if (!empty($_GET)) {
                    $bChecked = !empty($_GET['cbF'][$iFilterIndex][$iOptionIndex]);
                } else {
                    $bChecked = $oOption->isSelected();
                }

                $sChecked = $bChecked ? 'checked="checked"' : '';

                echo '<label class="filterOption">';
                echo '<input type="checkbox" name="cbF[' . $iFilterIndex . '][' . $iOptionIndex . ']" ' . $sChecked . ' value="1">';
                echo $oOption->getLabel();
                echo '</label>';
            }

            echo '</span>';
        }
    }

    // --------------------------------------------------------------------------

    echo !empty($injectHtml) || $searchable || !empty($dropdownFilter) || !empty($checkboxFilter) ? '<hr />' : '';
    echo '<span class="filterText">';

    if (!empty($sortColumns)) {

        //  Sort Column
        echo 'Sort records by';
        echo form_dropdown('sortOn', $sortColumns, $sortOn, 'class="select2 filter--sort"');

    } else {

        echo 'Sort results';
    }

    //  Sort order
    $options = [
        'asc'  => 'Ascending',
        'desc' => 'Descending',
    ];

    echo form_dropdown('sortOrder', $options, $sortOrder, 'class="select2 filter--order"');

    echo '</span>';

    // --------------------------------------------------------------------------

    if ($perPage) {
        echo '<span class="filterText">';

        //  Results per page
        $options = [
            10  => 10,
            25  => 25,
            50  => 50,
            75  => 75,
            100 => 100,
        ];

        echo 'Show';
        echo form_dropdown('perPage', $options, $perPage, 'class="select2 filter--per-page"');
        echo 'records per page.';

        echo '</span>';
    }

    // --------------------------------------------------------------------------

    echo '<div class="actions">';

    $resetUrl = uri_string();
    $resetUrl .= $aQuery ? '?' . http_build_query($aQuery) : '';

    echo '<button type="submit" class="btn btn-xs btn-primary">Search</button> ';
    echo anchor($resetUrl, 'Reset', 'class="btn btn-xs btn-default"');

    echo '</div>';

    // --------------------------------------------------------------------------

    echo form_close();

    ?>
</div>
