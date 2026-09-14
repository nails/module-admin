<?php

/**
 * The request-scoped admin breadcrumb trail
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Service
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Service;

use Nails\Admin\Factory;

/**
 * Class Breadcrumb
 *
 * @package Nails\Admin\Service
 */
class Breadcrumb
{
    /** @var Factory\Breadcrumb[] */
    protected array $aItems = [];

    // --------------------------------------------------------------------------

    /**
     * Append a crumb to the trail
     *
     * @param string|Factory\Breadcrumb $mCrumb The label, or a crumb instance
     * @param string|null               $sUrl   Optional URL when $mCrumb is a label
     *
     * @return $this
     */
    public function add(string|Factory\Breadcrumb $mCrumb, ?string $sUrl = null): self
    {
        $this->aItems[] = $this->normaliseCrumb($mCrumb, $sUrl);
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Insert a crumb at the start of the trail
     *
     * @param string|Factory\Breadcrumb $mCrumb The label, or a crumb instance
     * @param string|null               $sUrl   Optional URL when $mCrumb is a label
     *
     * @return $this
     */
    public function prepend(string|Factory\Breadcrumb $mCrumb, ?string $sUrl = null): self
    {
        array_unshift($this->aItems, $this->normaliseCrumb($mCrumb, $sUrl));
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Remove all crumbs from the trail
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->aItems = [];
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the crumbs in the trail
     *
     * @return Factory\Breadcrumb[]
     */
    public function getItems(): array
    {
        return $this->aItems;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the crumb labels, for document titles
     *
     * @return string[]
     */
    public function getLabels(): array
    {
        return array_map(
            static fn (Factory\Breadcrumb $oCrumb): string => $oCrumb->getLabel(),
            $this->aItems
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Whether the trail has no crumbs
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->aItems);
    }

    // --------------------------------------------------------------------------

    /**
     * Normalise a label or crumb into a Breadcrumb instance
     *
     * @param string|Factory\Breadcrumb $mCrumb The label, or a crumb instance
     * @param string|null               $sUrl   Optional URL when $mCrumb is a label
     *
     * @return Factory\Breadcrumb
     */
    protected function normaliseCrumb(string|Factory\Breadcrumb $mCrumb, ?string $sUrl = null): Factory\Breadcrumb
    {
        if ($mCrumb instanceof Factory\Breadcrumb) {
            return $mCrumb;
        }

        return new Factory\Breadcrumb($mCrumb, $sUrl);
    }
}
