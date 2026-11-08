<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class TechnologyCatalog
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private SpecialResearchRepositoryInterface $foundations,
        private ResearchedRepositoryInterface $researched,
        private TechnologyRecipeValidator $validator
    ) {}

    /** @return list<TechnologyRecipe> */
    public function getRecipes(): array
    {
        return array_values(array_filter($this->repository->getRecipes(), fn (TechnologyRecipe $recipe): bool => $this->validator->isValid($recipe)));
    }

    /** @return array<string, SpecialResearch> */
    public function getFoundations(User $user): array
    {
        $completed = [];
        foreach ($this->foundations->getByUser($user) as $research) {
            if ($research->getStatus() === SpecialResearchStatusEnum::COMPLETED) {
                $completed[$research->getResearchType()->value] = $research;
            }
        }
        return $completed;
    }

    public function getSystem(Module $module): ?AwayMissionResearchEnum
    {
        return [
            SpacecraftModuleTypeEnum::SHIELDS->value => AwayMissionResearchEnum::SHIELDS,
            SpacecraftModuleTypeEnum::EPS->value => AwayMissionResearchEnum::EPS,
            SpacecraftModuleTypeEnum::IMPULSEDRIVE->value => AwayMissionResearchEnum::IMPULSE_DRIVE,
            SpacecraftModuleTypeEnum::REACTOR->value => AwayMissionResearchEnum::REACTOR,
            SpacecraftModuleTypeEnum::COMPUTER->value => AwayMissionResearchEnum::COMPUTER,
            SpacecraftModuleTypeEnum::PHASER->value => AwayMissionResearchEnum::ENERGY_WEAPONS,
            SpacecraftModuleTypeEnum::SENSOR->value => AwayMissionResearchEnum::SENSORS,
            SpacecraftModuleTypeEnum::WARPDRIVE->value => AwayMissionResearchEnum::WARP_DRIVE
        ][$module->getType()->value] ?? null;
    }

    /** @param array<string, SpecialResearch>|null $foundations */
    public function supportsOptimization(User $user, Module $module, ?array $foundations = null): bool
    {
        $system = $this->getSystem($module);
        if ($system === null || $module->getLevel() < 1 || $module->getLevel() > 6 || $module->getisNpc() === true) {
            return false;
        }
        $faction = $module->getFaction();
        if ($faction !== null && $faction->getId() !== $user->getFactionId()) {
            return false;
        }
        $foundations ??= $this->getFoundations($user);
        return isset($foundations[$system->value]);
    }

    /** @param array<string, SpecialResearch>|null $foundations */
    public function canOptimize(User $user, Module $module, ?array $foundations = null): bool
    {
        if (!$this->supportsOptimization($user, $module, $foundations)) {
            return false;
        }
        $researchId = $module->getResearchId();
        return $researchId === null || $researchId === 0 || $this->researched->hasUserFinishedResearch($user, [$researchId]);
    }

    public function canUse(User $user, TechnologyRecipe $recipe): bool
    {
        if (!$this->validator->isValid($recipe)) {
            return false;
        }
        $foundations = $this->getFoundations($user);
        return match ($recipe->getKind()) {
            TechnologyRecipeKindEnum::NANITE_RESEARCH => $foundations !== [] && !$this->repository->hasNaniteFabrication($user),
            TechnologyRecipeKindEnum::DATASET_COPY => isset($foundations[$recipe->getResearchType()->value ?? '']),
            TechnologyRecipeKindEnum::NANITE_PRODUCTION,
            TechnologyRecipeKindEnum::MODULE_OPTIMIZATION => $this->repository->hasNaniteFabrication($user)
        };
    }
}
