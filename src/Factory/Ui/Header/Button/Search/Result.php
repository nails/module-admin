<?php

namespace Nails\Admin\Factory\Ui\Header\Button\Search;

class Result implements \Nails\Admin\Interfaces\Ui\Header\Button\Search\Result
{
    protected string $sIconClass   = '';
    protected string $sLabel       = '';
    protected string $sDescription = '';
    protected int    $iOrder       = 0;
    protected array  $aActions     = [];

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

    public function setDescription(string $sDescription): self
    {
        $this->sDescription = $sDescription;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->sDescription;
    }

    public function setOrder(int $iOrder): self
    {
        $this->iOrder = $iOrder;
        return $this;
    }

    public function getOrder(): int
    {
        return $this->iOrder;
    }

    public function setActions(array $aActions): self
    {
        $this->aActions = $aActions;
        return $this;
    }

    public function getActions(): array
    {
        return $this->aActions;
    }
}
