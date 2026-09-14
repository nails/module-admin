<?php

/**
 * A single admin breadcrumb crumb
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Factory
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Factory;

/**
 * Class Breadcrumb
 *
 * @package Nails\Admin\Factory
 */
class Breadcrumb
{
    protected string  $sLabel;
    protected ?string $sUrl;

    // --------------------------------------------------------------------------

    public function __construct(string $sLabel = '', ?string $sUrl = null)
    {
        $this
            ->setLabel($sLabel)
            ->setUrl($sUrl);
    }

    // --------------------------------------------------------------------------

    /**
     * Set the crumb label
     *
     * @param string $sLabel The label shown in the trail
     *
     * @return $this
     */
    public function setLabel(string $sLabel): self
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the crumb label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the optional URL; null means the crumb is not a link
     *
     * @param string|null $sUrl The URL to link to
     *
     * @return $this
     */
    public function setUrl(?string $sUrl): self
    {
        $this->sUrl = $sUrl !== null && $sUrl !== ''
            ? $sUrl
            : null;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the crumb URL, if any
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->sUrl;
    }

    // --------------------------------------------------------------------------

    /**
     * Whether this crumb should be rendered as a link
     *
     * @return bool
     */
    public function hasUrl(): bool
    {
        return $this->sUrl !== null;
    }
}
