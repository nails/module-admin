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

use JetBrains\PhpStorm\Internal\TentativeType;
use Nails\Admin\Constants;
use Nails\Admin\Factory\Nav\Action;
use Nails\Common\Helper\ArrayHelper;
use Nails\Factory;

/**
 * Class Nav
 *
 * @package Nails\Admin\Factory
 */
class Nav implements \JsonSerializable
{
    /** @var string */
    protected string $sLabel;

    /** @var string */
    protected string $sIcon;

    /** @var \Nails\Admin\Factory\Nav\Action[] */
    protected array $aActions;

    /** @var string[] */
    protected array $aKeywords;

    /** @var bool */
    protected bool $bIsOpen;

    // --------------------------------------------------------------------------

    public function __construct(string $sLabel = '', string $sIcon = '', array $aActions = [], array $aKeywords = [], bool $bIsOpen = false)
    {
        $this
            ->setLabel($sLabel)
            ->setIcon($sIcon)
            ->setActions($aActions)
            ->setKeywords($aKeywords)
            ->setIsOpen($bIsOpen);
    }

    // --------------------------------------------------------------------------

    /**
     * Set the label
     *
     * @param string $sLabel
     *
     * @return $this
     */
    public function setLabel(string $sLabel = ''): self
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the icon
     *
     * @param string $sIcon
     *
     * @return $this
     */
    public function setIcon(string $sIcon): self
    {
        $this->sIcon = $sIcon;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the icon
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->sIcon;
    }

    // --------------------------------------------------------------------------

    /**
     * Set actions
     *
     * @param Action[] $aActions
     *
     * @return $this
     */
    public function setActions(array $aActions): self
    {
        $this->aActions = $aActions;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the actions
     *
     * @return \Nails\Admin\Factory\Nav\Action[]
     */
    public function getActions(bool $bSorted = true): array
    {
        if ($bSorted) {
            usort($this->aActions, function (Action $a, Action $b) {
                if ($a->getOrder() === $b->getOrder()) {
                    return $a->getLabel() <=> $b->getLabel();
                }

                return $a->getOrder() <=> $b->getOrder();
            });
        }

        return array_values($this->aActions);
    }

    // --------------------------------------------------------------------------

    /**
     * Adds a new action
     *
     * @param string|Action $mLabel    The label to give the action, or an Action object
     * @param string        $sUrl      The url this action applies to
     * @param array         $aAlerts   An array of alerts to have alongside this action
     * @param mixed         $iOrder    An optional order index, used to push menu items up and down the group
     * @param array         $aKeywords Additional search terms for the item
     *
     * @return $this
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function addAction($mLabel, string $sUrl = 'index', array $aAlerts = [], int $iOrder = null, array $aKeywords = []): self
    {
        if ($mLabel instanceof Action) {
            $this->aActions[$mLabel->getUrl()] = $mLabel;
        } else {
            $this->aActions[$sUrl] = Factory::factory('NavAction', Constants::MODULE_SLUG, $mLabel, $sUrl, $aAlerts, $iOrder, $aKeywords);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Removes an action
     *
     * @param string $sUrl The URL/key of the action to remove
     *
     * @return Nav
     */
    public function removeAction(string $sUrl): self
    {
        unset($this->aActions[$sUrl]);
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the search terms
     *
     * @param array $aKeywords
     *
     * @return $this
     */
    public function setKeywords(array $aKeywords): self
    {
        $this->aKeywords = $aKeywords;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the open state
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->bIsOpen;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the open state
     *
     * @param bool $bIsOpen
     *
     * @return $this
     */
    public function setIsOpen(bool $bIsOpen): self
    {
        $this->bIsOpen = $bIsOpen;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the search terms
     *
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->aKeywords;
    }

    // --------------------------------------------------------------------------

    public function jsonSerialize(): mixed
    {
        return (object) [
            'label'    => $this->getLabel(),
            'icon'     => $this->getIcon(),
            'actions'  => $this->getActions(),
            'keywords' => $this->getKeywords(),
            'is_open'  => $this->isOpen(),
        ];
    }
}
