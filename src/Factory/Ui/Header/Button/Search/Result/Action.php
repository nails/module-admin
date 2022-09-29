<?php

namespace Nails\Admin\Factory\Ui\Header\Button\Search\Result;

class Action implements \Nails\Admin\Interfaces\Ui\Header\Button\Search\Result\Action
{

    protected string $sIconClass = '';
    protected string $sLabel     = '';
    protected string $sUrl       = '';

    public function setIconClass(string $sIconClass): self
    {
        $this->sIconClass = $sIconClass;
        return $this;
    }

    public function getIconClass(): string
    {
        return $this->sIconClass;
    }

    public function setLabel(string $sLabel): self
    {
        $this->sLabel = $sLabel;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->sLabel;
    }

    public function setUrl(string $sUrl): self
    {
        $this->sUrl = $sUrl;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->sUrl;
    }
}
