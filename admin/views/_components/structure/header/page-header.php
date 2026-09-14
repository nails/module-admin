<?php

use Nails\Admin\Constants;
use Nails\Admin\Helper;
use Nails\Common\Service;
use Nails\Factory;

/**
 * @var Service\MetaData $oMetaData
 */

$oBreadcrumbs = Factory::service('Breadcrumb', Constants::MODULE_SLUG);
$aCrumbs      = $oBreadcrumbs->getItems();
$sPageTitle   = empty($aCrumbs)
    ? $oMetaData
        ->getTitles()
        ->implode('</span><i class="fa fa-angle-right"></i><span>')
    : '';

$aHeaderButtons = Helper::getHeaderButtons();

if (!empty($aCrumbs) || !empty($sPageTitle) || !empty($aHeaderButtons)) {
    ?>
    <div class="u-flex u-flex-space-b u-flex-center-v w-100">
        <?php

        if (!empty($aCrumbs)) {
            ?>
            <p class="breadcrumbs m-0">
                <?php

                foreach ($aCrumbs as $iIndex => $oCrumb) {

                    if ($iIndex > 0) {
                        echo '<i class="fa fa-angle-right"></i>';
                    }

                    ?>
                    <span>
                        <?php

                        if ($oCrumb->hasUrl()) {
                            echo '<a href="' . htmlspecialchars($oCrumb->getUrl(), ENT_QUOTES, 'UTF-8') . '">' . $oCrumb->getLabel() . '</a>';
                        } else {
                            echo $oCrumb->getLabel();
                        }

                        ?>
                    </span>
                    <?php
                }

                ?>
            </p>
            <?php
        } elseif (!empty($sPageTitle)) {
            ?>
            <p class="breadcrumbs m-0">
                <span><?=$sPageTitle?></span>
            </p>
            <?php
        }

        if (!empty($aHeaderButtons)) {

            echo '<span class="header-buttons">';
            foreach ($aHeaderButtons as $aButton) {

                $aClasses = array_filter([
                    'btn',
                    'btn-xs',
                    'btn-' . $aButton['context'],
                    $aButton['confirmTitle'] || $aButton['confirmBody'] ? 'confirm' : '',
                    is_array($aButton['url']) ? 'dropdown-toggle' : '',
                ]);
                $aAttr    = array_filter([
                    'class="' . implode(' ', $aClasses) . '"',
                    $aButton['confirmTitle'] ? 'data-title="' . $aButton['confirmTitle'] . '"' : '',
                    $aButton['confirmBody'] ? 'data-body="' . $aButton['confirmBody'] . '"' : '',
                    $aButton['target'] ? 'target="' . $aButton['target'] . '"' : '',
                ]);

                if (is_array($aButton['url'])) {

                    ?>
                    <div class="btn-group">
                        <button type="button" <?=implode(' ', $aAttr)?> data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?=$aButton['label']?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php
                            foreach ($aButton['url'] as $sLabel => $sItemUrl) {
                                ?>
                                <li>
                                    <a href="<?=siteUrl($sItemUrl)?>">
                                        <?=$sLabel?>
                                    </a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php

                } else {

                    if ($aButton['context'] === 'danger') {
                        $aButton['label'] = '<i class="fa fa-exclamation-triangle"></i>' . $aButton['label'];
                    }

                    echo anchor(
                            $aButton['url'],
                            $aButton['label'],
                            implode(' ', $aAttr)
                        ) . ' ';
                }
            }
            echo '</span>';
        }

        ?>
    </div>
    <hr />
    <?php
}
