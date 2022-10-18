<?php

$aData   = !empty($data) ? $data : [];
$bHeader = !empty($header);

//  Determine the field titles if we can
if (!empty($bHeader)) {
    $aFirstRow   = (array) reset($aData);
    $aColumns    = array_keys($aFirstRow);
    $aColumnsOut = [];

    foreach ($aColumns as $sColumn) {
        $aColumnsOut[] = '"' . str_replace('"', '""', $sColumn) . '"';
    }

    echo $aColumnsOut ? implode(',', $aColumnsOut) . PHP_EOL : '';
}

// --------------------------------------------------------------------------

//  Now do the data dance
for ($i = $bHeader ? 1 : 0; $i < count($aData); $i++) {

    $sCsvRow = '';

    foreach ($aData[$i] as $mValue) {

        //  Sanitize
        if (!is_string($mValue) && !is_numeric($mValue) && !is_null($mValue)) {
            $mValue = json_encode($mValue, JSON_PRETTY_PRINT);
        }

        $mValue = str_replace('"', '""', $mValue);
        $mValue = trim(preg_replace("/\r\n|\r|\n/", ' ', $mValue));

        //  Add to the csvRow
        $sCsvRow .= '"' . $mValue . '",';
    }

    echo substr($sCsvRow, 0, -1) . PHP_EOL;
}
