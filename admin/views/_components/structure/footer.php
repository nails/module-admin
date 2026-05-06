                <footer>
                    <small rel="tooltip-r" title="<?=lang('admin_rendered_in_tip')?>">
                        <?=lang('admin_rendered_in', '{elapsed_time}')?>
                    </small>
                    <small class="right">
                        <?php

                        echo lang(
                            'admin_version',
                            [
                                Nails\Components::getApp()->version,
                                Nails\Components::getBySlug(\Nails\Admin\Constants::MODULE_SLUG)->version
                            ]
                        );

                        if (NAILS_BRANDING) {
                            ?>
                            <span>
                                // <?=lang('admin_powered_by', 'http://nailsapp.co.uk')?>
                            </span>
                            <?php
                        }

                        ?>
                    </small>
                </footer>
            </div><!--  /.content_inner -->
        </div>
        <!--    CLEARFIX    -->
        <div class="clear"></div>
        <div class="background">
            <div class="sidebar admin-branding-background-primary"></div>
        </div>
        <!--    GLOBAL JS   -->
        <?php

        foreach(\Nails\Admin\Helper::getModals() as $oModal) {
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

        $oAsset = \Nails\Factory::service('Asset');
        $oAsset->output('JS');
        $oAsset->output('JS-INLINE-FOOTER');

        ?>
    </body>
</html>
