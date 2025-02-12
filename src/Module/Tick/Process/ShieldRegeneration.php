<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use RuntimeException;
use Stu\Component\Game\TimeConstants;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShieldRegeneration implements ProcessTickHandlerInterface
{
    public const int SHIELD_REGENERATION_TIME = TimeConstants::FIFTEEN_MINUTES_IN_SECONDS;

    /** @var array<FieldTypeEffectEnum> */
    public const array PREVENTING_FIELD_TYPE_EFFECTS = [
        FieldTypeEffectEnum::SHIELD_MALFUNCTION
    ];

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function work(): void
    {
        $time = $this->stuTime->time();
        $regenerationThreshold = $time - self::SHIELD_REGENERATION_TIME;

        foreach ($this->spacecraftRepository->getSuitableForShieldRegeneration() as $spacecraft) {

            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
            $shieldSystemData = $wrapper->getShieldSystemData();
            if ($shieldSystemData === null) {
                throw new RuntimeException('this should hot happen');
            }

            if ($shieldSystemData->getShieldRegenerationTimer() > $regenerationThreshold) {
                continue;
            }

            $fieldType = $spacecraft->getLocation()->getFieldType();
            foreach (self::PREVENTING_FIELD_TYPE_EFFECTS as $effect) {
                if ($fieldType->hasEffect($effect)) {
                    continue 2;
                }
            }

            $rate = $wrapper->getShieldRegenerationRate();
            if ($spacecraft->getShield() + $rate > $spacecraft->getMaxShield()) {
                $rate = $spacecraft->getMaxShield() - $spacecraft->getShield();
            }
            $spacecraft->setShield($spacecraft->getShield() + $rate);

            $shieldSystemData->setShieldRegenerationTimer($time)->update();

            $this->spacecraftRepository->save($spacecraft);
        }
    }
}
