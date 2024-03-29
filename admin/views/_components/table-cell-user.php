<?php

$sNoDataClass = isset($id) && $id ? '' : 'text-muted';

?>
<td class="user-cell <?=$sNoDataClass?>">
    <?php

    //  Profile image
    if (isset($profile_img) && $profile_img) {
        echo anchor(
            cdnServe($profile_img),
            img(cdnCrop($profile_img, 35, 35)),
            'class="fancybox"'
        );

    } else {
        $sGender = !empty($gender) ? $gender : 'undisclosed';
        echo img(cdnBlankAvatar(35, 35, $sGender));
    }

    ?>
    <span class="user-data">
        <?php

        $sName = !empty($first_name) ? $first_name . ' ' : '';
        $sName .= !empty($last_name) ? $last_name . ' ' : '';
        $sName = $sName ?: 'Unknown User';

        if (!empty($id) && userHasPermission(\Nails\Auth\Admin\Permission\Users\Edit::class)) {
            echo anchor(
                \Nails\Auth\Admin\Controller\Accounts::url('edit/' . $id),
                $sName,
                'class="fancybox" data-fancybox-type="iframe"'
            );

        } else {
            echo $sName;
        }

        if (!empty($email)) {
            echo '<small>' . mailto($email) . '</small>';

        } else {
            echo '<small>No email address</small>';
        }

        if ($id == activeUser('id')) {
            echo '<span class="badge rounded-pill bg-primary me-1">This is you</span>';
        }

        if (!empty($group)) {
            echo sprintf(
                '<span class="badge rounded-pill bg-secondary">%s</span>',
                $group
            );
        }

        ?>
    </span>
</td>
