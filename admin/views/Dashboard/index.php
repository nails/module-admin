<?php

use Nails\Admin\Interfaces\Dashboard\Alert;
use Nails\Admin\Interfaces\Dashboard\Widget;
use Nails\Config;

/**
 * @var Alert[] $aAlerts
 * @var array[] $aWidgets
 */
?>
<div class="group-dashboard">
    <?php

    if (!empty($aAlerts)) {
        foreach ($aAlerts as $oAlert) {
            ?>
            <div class="alert alert-<?=$oAlert->getSeverity()?> alert--dashboard">
                <span class="alert__close">&times;</span>
                <?php

                $sTitle = $oAlert->getTitle();
                $sBody  = $oAlert->getBody();

                echo $sTitle ? '<strong>' . $sTitle . '</strong>' : '';
                echo $sBody ? '<p>' . $sBody . '</p>' : '';

                ?>
            </div>
            <?php
        }
        echo '<hr/>';
    }

    echo '<div id="dashboard-widgets" user-widgets="' . htmlentities(json_encode($aWidgets)) . '"></div>';

    ?>
</div>
