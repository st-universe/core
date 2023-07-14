<?php

declare(strict_types=1);

namespace Stu\Lib;

//TODO use everywhere instead of string array
class InformationWrapper
{
    /** @var array<string> */
    private array $informations;

    /**
     * @param array<string> $informations
     */
    public function __construct(array $informations = [])
    {
        $this->informations = $informations;
    }

    public function addInformation(?string $information): InformationWrapper
    {
        if ($information !== null) {
            $this->informations[] = $information;
        }

        return $this;
    }

    /**
     * @param array<string> $informations
     */
    public function addInformationMerge(array $informations): InformationWrapper
    {
        $this->informations = array_merge($this->informations, $informations);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getInformations(): array
    {
        return $this->informations;
    }
}
