<div class="group-dashboard help">
    <p>
        The following videos are available to you.
    </p>
    <?php

        echo adminHelper('loadSearch', $search);
        echo adminHelper('loadPagination', $pagination);

    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="name-desc">Name &amp; Description</th>
                    <th class="duration">Duration</th>
                    <th class="datetime">Added</th>
                    <th class="datetime">Modified</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php

            if ($videos) {

                foreach ($videos as $v) {

                    ?>
                    <tr>
                        <td class="name-desc">
                            <?=$v->label?>
                            <small><?=$v->description?></small>
                        </td>
                        <td class="duration">
                            <?=gmdate('H:i:s', $v->duration)?>
                        </td>
                        <?=adminHelper('loadDatetimeCell', $v->created);?>
                        <?=adminHelper('loadDatetimeCell', $v->modified);?>
                        <td class="actions">
                            <?php

                            echo anchor(
                                'http://player.vimeo.com/video/' . $v->vimeo_id . '?autoplay=true',
                                lang('action_view'),
                                'class="btn btn-xs btn-primary video-button"'
                            );

                            ?>
                        </td>
                    </tr>
                    <?php

                }

            } else {

                ?>
                <tr>
                    <td colspan="2" class="no-data">
                        <?=lang('no_records_found')?>
                    </td>
                </tr>
                <?php
            }

            ?>
            </tbody>
        </table>
    </div>
    <?php

        echo adminHelper('loadPagination', $pagination);

    ?>
</div>