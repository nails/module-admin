<?php

use Nails\Admin\Helper;
use Nails\Common\Service\Asset;
use Nails\Factory;

echo '<hr />';

if (empty($isModal)) {
    ?>
    <footer class="clearfix">
        <small class="float-start">
            Rendered in {elapsed_time} seconds
        </small>
        <?php

        if (\Nails\Config::get('NAILS_BRANDING')) {
            ?>
            <small class="float-end">
                Powered by <a href="https://nailsapp.co.uk" target="_blank">Nails</a>
            </small>
            <?php
        }

        ?>
    </footer>
    <?php

    //  Elements opened in another view, helps IDE
    echo '</div><!--  /.content -->';
    echo '</div><!--  /.main -->';
}

foreach (Helper::getModals() as $oModal) {
    ?>
    <div class="modal<?=$oModal->open ? ' modal--open' : ''?>">
        <div class="modal__inner">
            <div class="modal__close">&times;</div>
            <div class="modal__title"><?=$oModal->title?></div>
            <div class="modal__body"><?=$oModal->body?></div>
        </div>
    </div>
    <?php
}

?>

    <div class="admin-vue-app">
        <!--  Create Modal -->
        <create-modal header="What would you like to create?"></create-modal>
        <!--  Search Modal -->
        <search-modal></search-modal>
        <!--  Filter Modal -->
        <filter-modal header="Sort & Filter"></filter-modal>
    </div>

<?php

/** @var Asset $oAsset */
$oAsset = Factory::service('Asset');
$oAsset->output($oAsset::TYPE_JS);
$oAsset->output($oAsset::TYPE_JS_INLINE_FOOTER);

//  Elements opened in another view, helps IDE
echo '</body>';
echo '</html>';
