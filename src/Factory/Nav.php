<?php

/**
 * This class is used for building navGroups in admin
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Factory
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Factory;

/**
 * Class Nav
 *
 * @package Nails\Admin\Factory
 */
class Nav
{
    /**
     * The Nav gorup's label
     *
     * @var string
     */
    protected $label;

    /**
     * The Nav group's icon
     *
     * @var string
     */
    protected $icon;

    /**
     * The Nav group's actions
     *
     * @var array
     */
    protected $actions;

    /** @var string[] */
    protected $aSearchTerms = [];

    // --------------------------------------------------------------------------

    /**
     * Construct Nav
     *
     * @param string $label The label to give the navGroup
     * @param string $icon  The icon to give the navGroup
     */
    public function __construct($label = '', $icon = '')
    {
        $this->setLabel($label);
        $this->setIcon($icon);
        $this->actions = [];
    }

    // --------------------------------------------------------------------------

    /**
     * Set the navGroup's label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label = '')
    {
        $this->label = $label;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the navGroup's aSearchTerms
     *
     * @param array $aSearchTerms
     *
     * @return $this
     */
    public function setSearchTerms(array $aSearchTerms)
    {
        $this->aSearchTerms = $aSearchTerms;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's aSearchTerms
     *
     * @return string[]
     */
    public function getSearchTerms(): array
    {
        return $this->aSearchTerms;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the navGroup's icon
     *
     * @param string $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the navGroup's actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new action to the navGroup. An action is menu item essentially.
     *
     * @param string $label        The label to give the action
     * @param string $url          The url this action applies to
     * @param array  $alerts       An array of alerts to have along side this action
     * @param mixed  $order        An optional order index, used to push menu items up and down the group
     * @param array  $aSearchTerms Additional search terms for the item
     *
     * @return $this
     */
    public function addAction($label, $url = 'index', $alerts = [], $order = null, array $aSearchTerms = [])
    {
        $this->actions[$url]              = new \stdClass();
        $this->actions[$url]->label       = $label;
        $this->actions[$url]->searchTerms = array_merge([$this->getLabel()], $this->getSearchTerms(), $aSearchTerms);
        $this->actions[$url]->alerts      = !is_array($alerts) ? [$alerts] : $alerts;
        $this->actions[$url]->order       = $order;

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Removes a action
     *
     * @param string $url The URL/key of the action to remove
     *
     * @return Nav
     */
    public function removeAction($url)
    {
        unset($this->actions[$url]);

        return $this;
    }
}
