<?php

namespace Nails\Admin\Factory\Nav;

use JetBrains\PhpStorm\Internal\TentativeType;

/**
 * Class Action
 *
 * @package Nails\Admin\Factory\Nav
 */
class Action implements \JsonSerializable
{
    /** @var string */
    protected $sLabel;

    /** @var string */
    protected $sUrl;

    /** @var \Nails\Admin\Factory\Nav\Action[] */
    protected $aAlerts = [];

    /** @var int|null */
    protected $iOrder;

    /** @var string[] */
    protected $aKeywords = [];

    // --------------------------------------------------------------------------

    /**
     * @param string   $sLabel
     * @param string   $sUrl
     * @param array    $aAlerts
     * @param int|null $iOrder
     * @param array    $aKeywords
     */
    public function __construct(string $sLabel, string $sUrl, array $aAlerts = [], int $iOrder = null, array $aKeywords = [])
    {
        $this
            ->setLabel($sLabel)
            ->setUrl($sUrl)
            ->setAlerts($aAlerts)
            ->setOrder($iOrder)
            ->setKeywords($aKeywords);
    }

    // --------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->sLabel;
    }

    // --------------------------------------------------------------------------

    /**
     * @param string $sLabel
     */
    public function setLabel(string $sLabel): self
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->sUrl;
    }

    // --------------------------------------------------------------------------

    /**
     * @param string $sUrl
     *
     * @return $this
     */
    public function setUrl(string $sUrl): self
    {
        $this->sUrl = $sUrl;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @param string $sGroupUrl
     *
     * @return $this
     */
    public function compileUrl(string $sGroupUrl): self
    {
        $sCompiledUrl = siteUrl(
            sprintf(
                '%s/%s',
                $sGroupUrl,
                $this->getUrl()
            )
        );

        $sCompiledUrl = preg_replace('/\/index$/', '', $sCompiledUrl);

        $this->setUrl($sCompiledUrl);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Factory\Nav\Action[]
     */
    public function getAlerts(): array
    {
        return $this->aAlerts;
    }

    // --------------------------------------------------------------------------

    /**
     * @param \Nails\Admin\Factory\Nav\Action[]
     *
     * @return $this$aAlerts
     */
    public function setAlerts(array $aAlerts): self
    {
        $this->aAlerts = $aAlerts;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->iOrder;
    }

    // --------------------------------------------------------------------------

    /**
     * @param int|null $iOrder
     *
     * @return $this
     */
    public function setOrder(?int $iOrder): self
    {
        $this->iOrder = $iOrder;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->aKeywords;
    }

    // --------------------------------------------------------------------------

    /**
     * @param string[] $aKeywords
     *
     * @return $this
     */
    public function setKeywords(array $aKeywords): self
    {
        $this->aKeywords = $aKeywords;
        return $this;
    }

    // --------------------------------------------------------------------------

    public function jsonSerialize()
    {
        return (object) [
            'label'    => $this->getLabel(),
            'url'      => $this->getUrl(),
            'alerts'   => $this->getAlerts(),
            'order'    => $this->getOrder(),
            'keywords' => $this->getKeywords(),
        ];
    }
}
