<?php

use Nails\Admin\Exception\HelperException;

if (!function_exists('adminHelper')) {
    /**
     * Call an adminHelper static method, the first parameter should be the method to call,
     * all following params will be passed as if been called directly.
     *
     * @return mixed
     * @throws HelperException
     */
    function adminHelper()
    {
        trigger_error(__FUNCTION__ . ' is deprecated', E_USER_DEPRECATED);

        $aArgs   = func_get_args();
        $sMethod = array_shift($aArgs);

        if (!is_string($sMethod)) {
            throw new HelperException('First parameter must be a string.', 1);
        }

        //  Attempt to call the static method
        $sMethod = '\Nails\Admin\Helper::' . $sMethod;
        return call_user_func_array($sMethod, $aArgs);
    }
}

// --------------------------------------------------------------------------

if (!function_exists('adminDynamicTable')) {
    /**
     * Generates a dynamic table
     *
     * @param string $sKey    The key to give items in the table
     * @param array  $aFields The fields to render
     * @param array  $aData   Data to populate the table with
     *
     * @return string
     */
    function adminDynamicTable($sKey, array $aFields, array $aData = [])
    {
        trigger_error(__FUNCTION__ . ' is deprecated', E_USER_DEPRECATED);
        return \Nails\Admin\Helper::dynamicTable($sKey, $aFields, $aData);
    }
}
