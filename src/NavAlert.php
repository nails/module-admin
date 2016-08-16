<?php

/**
 * An alert which is shown in the admin sidebar
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    NavAlert
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin;

class NavAlert
{
    protected $sValue;
    protected $sSeverity;
    protected $sLabel;

    // --------------------------------------------------------------------------

    /**
     * Constructs the alert with defaults
     * @param string $sValue    The value, what's shown to the user
     * @param string $sSeverity The severity of the alert
     * @param string $sLabel    What's shown when the alert is moused-over
     */
    public function __construct($sValue = '', $sSeverity = '', $sLabel = '')
    {
        $this->setValue($sValue);
        $this->setSeverity($sSeverity);
        $this->setLabel($sLabel);
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the value
     * @param  string $sValue The value for the indicator
     * @return $this
     */
    public function setValue($sValue)
    {
        $this->sValue = $sValue;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current value
     * @return string
     */
    public function getValue()
    {
        return $this->sValue;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the severity
     * @param  string $sSeverity The severity for the indicator [info|danger|success|warning]
     * @return $this
     */
    public function setSeverity($sSeverity)
    {
        $this->sSeverity = $sSeverity;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current severity
     * @return string
     */
    public function getSeverity()
    {
        return $this->sSeverity;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the label
     * @param  string $sLabel The label for the indicator
     * @return $this
     */
    public function setLabel($sLabel)
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current label
     * @return string
     */
    public function getLabel()
    {
        return $this->sLabel;
    }
}
