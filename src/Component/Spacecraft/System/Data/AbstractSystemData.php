<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Module\Template\StatusBarInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

abstract class AbstractSystemData
{
    protected Spacecraft $spacecraft;

    public function __construct(
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private StatusBarFactoryInterface $statusBarFactory
    ) {}

    public function setSpacecraft(Spacecraft $spacecraft): void
    {
        $this->spacecraft = $spacecraft;
    }

    abstract public function getSystemType(): SpacecraftSystemTypeEnum;

    /**
     * updates the system metadata for this specific ship system
     */
    public function update(): void
    {
        $system = $this->spacecraft->getSpacecraftSystem($this->getSystemType());
        $system->setData(json_encode($this, JSON_THROW_ON_ERROR));
        $this->shipSystemRepository->save($system);
    }

    protected function getStatusBar(string $label, int $value, int $maxValue, string $color): StatusBarInterface
    {
        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor($color)
            ->setLabel($label)
            ->setMaxValue($maxValue)
            ->setValue($value);
    }
}
