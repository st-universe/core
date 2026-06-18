<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Doctrine\ORM\EntityManagerInterface;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowBuildplanCreator\ShowBuildplanCreator;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateBuildplan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_BUILDPLAN';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private UserRepositoryInterface $userRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private BuildplanSignatureCreationInterface $buildplanSignatureCreation,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBuildplanCreator::VIEW_IDENTIFIER);
        $userId = request::postIntFatal('userId');
        $rumpId = request::postIntFatal('rumpId');
        $buildplanCount = $this->determineBuildplanCount($game);
        if ($buildplanCount === false) {
            return;
        }

        $modInput = request::postArray('mod');
        $moduleList = [];

        foreach ($modInput as $moduleId) {
            if (is_numeric($moduleId) && (int)$moduleId > 0) {
                $moduleList[] = (int)$moduleId;
            }
        }

        $specialModInput = request::postArray('special_mod');
        $moduleSpecialList = [];

        foreach ($specialModInput as $moduleId) {
            if (is_numeric($moduleId) && (int)$moduleId > 0) {
                $moduleSpecialList[] = (int)$moduleId;
            }
        }

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $rump = $this->spacecraftRumpRepository->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException(sprintf('rumpId %d does not exist!', $rumpId));
        }

        $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump($rump);
        if ($mod_level === null) {
            throw new RuntimeException(sprintf('No module levels found for rump %d', $rump->getId()));
        }

        if (count($moduleList) < $mod_level->getMandatoryModulesCount()) {
            $game->getInfo()->addInformation('Nicht alle benötigten Module wurden ausgewählt');
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException(sprintf('userId %d does not exist', $userId));
        }

        $moduleIds = array_merge($moduleList, $moduleSpecialList);
        $modules = [];

        foreach ($moduleIds as $moduleId) {
            $module = $this->moduleRepository->find($moduleId);
            if ($module === null) {
                throw new RuntimeException(sprintf('moduleId %d does not exist', $moduleId));
            }

            $modules[$moduleId] = $module;
        }

        $crewInput = request::postString('crew_input');
        if ($crewInput !== false && $crewInput !== '') {
            if (!ctype_digit($crewInput)) {
                $game->getInfo()->addInformation('Benötigte Crew muss leer sein oder eine Zahl größer/gleich 0 enthalten');
                return;
            }

            $crewUsage = (int)$crewInput;
        } else {
            $crewUsage = $this->shipCrewCalculator->getCrewUsage($modules, $rump, $user);
        }

        $signature = $this->buildplanSignatureCreation->createSignatureByModuleIds(
            $moduleIds,
            $crewUsage
        );

        $plan = $this->buildplanRepository->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);

        if ($plan === null) {
            $planname = sprintf(
                'Bauplan %s %s',
                $rump->getName(),
                date('d.m.Y H:i')
            );

            $plan = $this->buildplanRepository->prototype();
            $plan->setUser($user);
            $plan->setRump($rump);
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($rump->getBuildtime());
            $plan->setCrew($crewUsage);
            $plan->setNpcGift(true);
            $plan->setCount($buildplanCount);

            $this->buildplanRepository->save($plan);
            $this->entityManager->flush();

            foreach ($moduleList as $moduleId) {
                $module = $modules[$moduleId];

                $mod = $this->buildplanModuleRepository->prototype();
                $mod->setModuleType($module->getType());
                $mod->setBuildplan($plan);
                $mod->setModule($module);

                $this->buildplanModuleRepository->save($mod);
            }

            foreach ($moduleSpecialList as $moduleId) {
                $module = $modules[$moduleId];

                $mod = $this->buildplanModuleRepository->prototype();
                $mod->setModuleType($module->getType());
                $mod->setBuildplan($plan);
                $mod->setModule($module);
                $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($module->getSpecials()));

                $this->buildplanModuleRepository->save($mod);
            }

            $this->entityManager->flush();

            $moduleNames = [];
            foreach ($modules as $module) {
                $moduleNames[] = $module->getName();
            }

            $reason = request::postString('reason');

            if ($reason === '') {
                $game->getInfo()->addInformation("Grund fehlt");
                return;
            }

            $logText = sprintf(
                '%s hat für Spieler %s (%s) einen Bauplan erstellt. Rumpf: %s, Module: %s, Crew: %d, Baubar: %s, Grund: %s',
                $game->getUser()->getName(),
                $user->getName(),
                $user->getId(),
                $rump->getName(),
                implode(', ', $moduleNames),
                $plan->getCrew(),
                $buildplanCount === null ? 'unlimitiert' : sprintf('%d mal', $buildplanCount),
                $reason
            );

            if ($game->getUser()->isNpc()) {
                $this->createLogEntry($logText, $game->getUser()->getId());
            }

            $game->getInfo()->addInformation('Bauplan wurde erstellt');
        } else {
            $game->getInfo()->addInformation('Bauplan existiert bereits');
        }
    }

    public function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());
        $entry->setAdminView(false);

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function determineBuildplanCount(GameControllerInterface $game): int|false|null
    {
        $buildplanCount = request::postString('buildplan_count');
        if ($buildplanCount === false || $buildplanCount === '') {
            return null;
        }

        if (!ctype_digit($buildplanCount) || (int)$buildplanCount <= 0) {
            $game->getInfo()->addInformation('X mal baubar muss leer sein oder eine Zahl größer 0 enthalten');
            return false;
        }

        return (int)$buildplanCount;
    }
}
