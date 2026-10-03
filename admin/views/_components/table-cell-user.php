<?php

use Nails\Cdn\Helper\Picture;
use Nails\Cdn\Resource\CdnObject;

$sNoDataClass = isset($id) && $id ? '' : 'text-muted';

$sName = !empty($first_name) ? $first_name . ' ' : '';
$sName .= !empty($last_name) ? $last_name . ' ' : '';
$sName = $sName ?: 'Unknown User';

?>
<td class="user-cell <?=$sNoDataClass?>">
    <?php

    //  70px is the 2x crop. Picture requests it as its own CDN URL, so a
    //  cached 35px file is never probed with an @2x suffix.
    if (isset($profile_img) && $profile_img) {
        $mCdnObject = $profile_img instanceof CdnObject
            ? $profile_img
            : (int) (is_object($profile_img) ? $profile_img->id : $profile_img);

        echo anchor(
            cdnServe($profile_img),
            (string) (new Picture($mCdnObject, 35, 35, htmlspecialchars($sName, ENT_QUOTES, 'UTF-8')))
                ->source(70, 70, null, 2),
            'class="fancybox"'
        );

    } else {
        $sGender = !empty($gender) ? $gender : 'undisclosed';
        echo img(cdnBlankAvatar(35, 35, $sGender));
    }

    ?>
    <span class="user-data">
        <?php

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
            echo '<span class="badge badge-primary rounded-pill me-1">This is you</span>';
        }

        if (!empty($group)) {
            echo sprintf(
                '<span class="badge badge-secondary rounded-pill">%s</span>',
                $group
            );
        }

        ?>
    </span>
</td>
