<?php

use Nails\Admin\Helper;

//  Page title
if (!empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->module->name . ' <i class="fa fa-angle-right"></i> ' . $page->title;

} elseif (empty($page->module->name) && !empty($page->title)) {
    $sPageTitle = $page->title;

} elseif (!empty($page->module->name)) {
    $sPageTitle = $page->module->name;
}

$aHeaderButtons = Helper::getHeaderButtons();

if (!empty($sPageTitle) || !empty($aHeaderButtons)) {
    ?>
    <div class="u-flex u-flex-space-b u-flex-center-v w-100">
        <?php

        if (!empty($sPageTitle)) {
            ?>
            <p class="breadcrumbs m-0">
                <strong>Admin</strong>
                <i class="fa fa-angle-right"></i>
                <?=$sPageTitle?>
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
