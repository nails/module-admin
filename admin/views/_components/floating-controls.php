<?php

/** @var \Nails\Common\Resource\Entity $oItem */
$oItem = $aFloatingConfig['item'] ?? $oItem ?? null;

$bSaveBtnEnabled = (bool) ($aFloatingConfig['save']['enabled'] ?? true);
$sSaveBtnText    = $aFloatingConfig['save']['text'] ?? 'Save Changes';
$sSaveBtnClass   = $aFloatingConfig['save']['class'] ?? 'btn btn-primary';
$sSaveBtnName    = $aFloatingConfig['save']['name'] ?? null;
$sSaveBtnValue   = $aFloatingConfig['save']['value'] ?? null;

$aSaveBtnAttributes = array_filter([
    'type="submit"',
    $sSaveBtnClass ? 'class="' . $sSaveBtnClass . '"' : null,
    $sSaveBtnName ? 'name="' . $sSaveBtnName . '"' : null,
    $sSaveBtnValue ? 'value="' . $sSaveBtnValue . '"' : null,
]);

$sHtmlLeft   = $aFloatingConfig['html']['left'] ?? null;
$sHtmlCenter = $aFloatingConfig['html']['center'] ?? null;
$sHtmlRight  = $aFloatingConfig['html']['right'] ?? null;

$bLastModifiedEnabled      = $aFloatingConfig['last_modified']['enabled'] ?? false;
$sLastModifiedId           = $aFloatingConfig['last_modified']['last_modified']['id'] ?? null;
$sLastModifiedKey          = $aFloatingConfig['last_modified']['last_modified']['key'] ?? 'last_modified';
$sLastModifiedOverWriteId  = $aFloatingConfig['last_modified']['overwrite']['id'] ?? null;
$sLastModifiedOverWriteKey = $aFloatingConfig['last_modified']['overwrite']['key'] ?? 'overwrite';

$bNotesEnabled  = $aFloatingConfig['notes']['enabled'] ?? false;
$sNotesBtnText  = $aFloatingConfig['notes']['button']['text'] ?? 'Notes';
$sNotesBtnClass = $aFloatingConfig['notes']['button']['class'] ?? 'btn btn-default pull-right';
$sNotesModel    = $aFloatingConfig['notes']['model'] ?? null;
$sNotesProvider = $aFloatingConfig['notes']['provider'] ?? null;

?>
<div class="admin-floating-controls">
    <?php

    echo $sHtmlLeft;

    if ($bSaveBtnEnabled) {
        ?>
        <button <?=implode(' ', $aSaveBtnAttributes)?>>
            <?=$sSaveBtnText?>
        </button>
        <?php
    }

    echo $sHtmlCenter;

    if (!empty($oItem) && $bLastModifiedEnabled) {
        echo form_hidden(
            $sLastModifiedKey,
            set_value($sLastModifiedKey, $oItem->modified ?? null),
            implode(' ', array_filter([
                $sLastModifiedId ? 'id="' . $sLastModifiedId . '"' : null,
            ]))
        );
        echo form_hidden(
            $sLastModifiedOverWriteKey,
            0,
            implode(' ', array_filter([
                $sLastModifiedOverWriteId ? 'id="' . $sLastModifiedOverWriteId . '"' : null,
            ]))
        );
    }

    if (!empty($oItem) && $bNotesEnabled && $sNotesModel) {
        ?>
        <button type="button"
                class="<?=$sNotesBtnClass?> js-admin-notes"
                data-model-name="<?=$sNotesModel?>"
                data-model-provider="<?=$sNotesProvider?>"
                data-id="<?=$oItem->id?>"
                data-show-count="true"
        >
            <?=$sNotesBtnText?>
        </button>
        <?php
    }

    echo $sHtmlRight;
    ?>
</div>
