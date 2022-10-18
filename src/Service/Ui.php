<?php

namespace Nails\Admin\Service;

use Nails\Admin\Interfaces;
use Nails\Components;

class Ui
{
    protected ?array $aHeaderButtonCreateItems    = null;
    protected ?array $aHeaderButtonSearchSections = null;

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Interfaces\Ui\Header\Button\Create[]
     * @throws \Nails\Common\Exception\NailsException
     */
    public function getHeaderButtonCreateItems(): array
    {
        $this->discoverHeaderButtonCreateItems();
        return $this->aHeaderButtonCreateItems;
    }

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Interfaces\Ui\Header\Button\Search\Section[]
     */
    public function getHeaderButtonSearchSections(): array
    {
        $this->discoverHeaderButtonSearchSections();

        //  @todo (Pablo 2022-09-22) - perform search to generate count

        return $this->aHeaderButtonSearchSections;
    }

    // --------------------------------------------------------------------------

    /**
     * @return \Nails\Admin\Interfaces\Ui\Header\Button\Search\Result[]
     */
    public function getHeaderButtonSearchResults(): array
    {
        $this->discoverHeaderButtonSearchSections();

        //  @todo (Pablo 2022-09-22) - perform search

        return [];
    }


    // --------------------------------------------------------------------------

    /**
     * @throws \Nails\Common\Exception\NailsException
     */
    protected function discoverHeaderButtonCreateItems(): void
    {
        if ($this->aHeaderButtonCreateItems !== null) {
            return;
        }

        $this->aHeaderButtonCreateItems = [];

        foreach (Components::available() as $oComponent) {

            $oClasses = $oComponent
                ->findClasses('Admin\\Ui\\Header\\Button\\Create')
                ->whichImplement(Interfaces\Ui\Header\Button\Create::class)
                ->whichCanBeInstantiated();

            foreach ($oClasses as $sClass) {
                $this->aHeaderButtonCreateItems[] = new $sClass();
            }
        }

        usort(
            $this->aHeaderButtonCreateItems,
            function (Interfaces\Ui\Header\Button\Create $a, Interfaces\Ui\Header\Button\Create $b) {

                if ($a::getOrder() !== $b::getOrder()) {
                    return $a::getOrder() <=> $b::getOrder();
                }

                return $a->getLabel() <=> $b->getLabel();

            }
        );
    }

    // --------------------------------------------------------------------------

    protected function discoverHeaderButtonSearchSections(): void
    {
        if ($this->aHeaderButtonSearchSections !== null) {
            return;
        }

        $this->aHeaderButtonSearchSections = [];

        foreach (Components::available() as $oComponent) {

            $oClasses = $oComponent
                ->findClasses('Admin\\Ui\\Header\\Button\\Search\\Section')
                ->whichImplement(Interfaces\Ui\Header\Button\Search\Section::class)
                ->whichCanBeInstantiated();

            foreach ($oClasses as $sClass) {
                $this->aHeaderButtonSearchSections[] = new $sClass();
            }
        }

        usort(
            $this->aHeaderButtonSearchSections,
            function (Interfaces\Ui\Header\Button\Search\Section $a, Interfaces\Ui\Header\Button\Search\Section $b) {

                if ($a::getOrder() !== $b::getOrder()) {
                    return $a::getOrder() <=> $b::getOrder();
                }

                return $a->getLabel() <=> $b->getLabel();

            }
        );
    }
}
