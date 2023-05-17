<?php

$oData   = !empty($data) ? $data : [];
$bHeader = !empty($header);

if (!empty($bHeader)) {

    $aColumnsOut = [];
    foreach ($oData->list_fields() as $sColumn) {
        $aColumnsOut[] = '"' . str_replace('"', '""', $sColumn) . '"';
    }

    echo $aColumnsOut ? implode(',', $aColumnsOut) . "\n" : '';
}

// --------------------------------------------------------------------------

//  Now do the data dance
while ($oRow = $oData->unbuffered_row()) {

    $sCsvRow = '';

    foreach ($oRow as $mValue) {

        //  Sanitize
        if (!is_string($mValue) && !is_numeric($mValue) && !is_null($mValue)) {
            $mValue = json_encode($mValue, JSON_PRETTY_PRINT);
        }

        $mValue = str_replace('"', '""', (string) $mValue);
        $mValue = trim(preg_replace("/\r\n|\r|\n/", ' ', $mValue));

        $sCsvRow .= '"' . $mValue . '",';
    }

    echo substr($sCsvRow, 0, -1) . "\n";
}
