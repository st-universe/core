<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use PHPUnit\Framework\TestCase;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\TechnologyRecipe;

final class TechnologyRecipeValidatorTest extends TestCase
{
    public function testResearchAcceptsOnlyPositiveDurationAndValidFailureChance(): void
    {
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::NANITE_RESEARCH)->setDuration(172800)->setFailureChance(35);
        $validator = new TechnologyRecipeValidator();
        self::assertTrue($validator->isValid($recipe));
        self::assertFalse($validator->isValid($recipe->setDuration(0)));
        self::assertFalse($validator->isValid($recipe->setDuration(172800)->setFailureChance(101)));
    }

    public function testCopyRequiresSourceSystemAndActualOutput(): void
    {
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::DATASET_COPY)->setDuration(43200);
        $validator = new TechnologyRecipeValidator();
        self::assertFalse($validator->isValid($recipe));
        $recipe->setResearchType(AwayMissionResearchEnum::SENSORS)->setOutputCommodity(new Commodity())->setOutputAmount(1);
        self::assertTrue($validator->isValid($recipe));
        self::assertFalse($validator->isValid($recipe->setOutputAmount(0)));
    }

    public function testOptimizationRejectsIncompleteOrUnboundedSettings(): void
    {
        $settings = ['min_modules' => 5, 'max_modules' => 50, 'min_nanites' => 10, 'max_nanites' => 100,
            'base_success' => 20, 'max_base_success' => 50, 'extra_success' => 30, 'max_steps' => 70, 'accident_chance' => 5, 'data_amount' => 1];
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setDuration(57600)
            ->setNaniteCommodity(new Commodity())->setSettings($settings);
        $validator = new TechnologyRecipeValidator();
        self::assertTrue($validator->isValid($recipe));
        self::assertFalse($validator->isValid($recipe->setSettings(array_replace($settings, ['max_steps' => 71]))));
        self::assertFalse($validator->isValid($recipe->setSettings(array_replace($settings, ['max_modules' => 5]))));
        self::assertFalse($validator->isValid($recipe->setSettings(array_replace($settings, ['extra_success' => 51]))));
        unset($settings['min_nanites']);
        self::assertFalse($validator->isValid($recipe->setSettings($settings)));
    }
}
