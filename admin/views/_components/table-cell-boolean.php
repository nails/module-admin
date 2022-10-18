<?php

    /**
     * Renders a tick/cross table cell
     * @param $value    The value to 'truthy' test
     * @param $dateTime A datetime to show (for truthy values only)
     */

    $value    = !empty($value) ? true: false;
    $dateTime = !empty($dateTime) ? $dateTime : null;

    if ($value) {

        echo '<td class="boolean table-success align-middle">';
            echo '<b class="fa fa-check-circle fa-lg"></b>';
            if (!is_null($dateTime)) {

                echo '<small class="nice-time">';
                    echo niceTime($dateTime);
                echo '</small>';
            }
        echo '</td>';

    } else {

        echo '<td class="boolean table-danger">';
            echo '<b class="fa fa-times-circle fa-lg"></b>';
        echo '</td>';
    }
