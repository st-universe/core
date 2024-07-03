<?php

declare(strict_types=1);

namespace Stu\Lib\Information;

class InformationWrapper implements InformationInterface
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

    public function addInformation(?string $information): InformationInterface
    {
        if ($information !== null) {
            $this->informations[] = $information;
        }

        return $this;
    }

    public function addInformationf(string $information, ...$args): InformationInterface
    {
        $this->addInformation(vsprintf(
            $information,
            $args
        ));

        return $this;
    }

    /**
     * @param array<string> $informations
     */
    public function addInformationArray(array $informations, bool $isHead = false): InformationWrapper
    {
        if ($isHead) {
            $this->informations = array_merge($informations, $this->informations);
        } else {
            $this->informations = array_merge($this->informations, $informations);
        }

        return $this;
    }

    public function addInformationWrapper(?InformationWrapper $informations, bool $isHead = false): InformationWrapper
    {
        if ($informations !== null) {
            $this->addInformationArray($informations->getInformations(), $isHead);
        }

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getInformations(): array
    {
        return $this->informations;
    }

    public function getInformationsAsString(): string
    {
        return implode(PHP_EOL, $this->getInformations());
    }

    public function dumpTo(InformationInterface $informations): void
    {
        array_walk(
            $this->informations,
            function (string $info) use ($informations): void {
                $informations->addInformation($info);
            }
        );
    }

    public function isEmpty(): bool
    {
        return $this->getInformations() === [];
    }
}
