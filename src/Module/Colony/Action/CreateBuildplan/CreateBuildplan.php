<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateBuildplan;

use Doctrine\ORM\EntityManagerInterface;
use Override;

use request;
use RuntimeException;
use Stu\Component\Ship\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreen;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class CreateBuildplan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILDPLAN_SAVE';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ShipRumpRepositoryInterface $shipRumpRepository,
        private EntityManagerInterface $entityManager,
        private ShipCrewCalculatorInterface $shipCrewCalculator,
        private BuildplanSignatureCreationInterface $buildplanSignatureCreation,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    private function exitOnError(GameControllerInterface $game): void
    {
        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $rump = $this->shipRumpRepository->find(request::indInt('rump'));
        if ($rump === null) {
            $this->loggerUtil->log('A');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('B');

        if (!array_key_exists($rump->getId(), $this->shipRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation(sprintf(
                'userId %d tried to create a buildplan with rump %s, but has not researched the rump',
                $userId,
                $rump->getName()
            ));
        }

        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());

        /** @var array<int, ModuleInterface> */
        $modules = [];

        $error = false;
        foreach (ShipModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {

            $value = $moduleType->value;
            $this->loggerUtil->log(sprintf('%d', $value));
            $module = request::postArray('mod_' . $value);
            if (
                $moduleType != ShipModuleTypeEnum::SPECIAL
                && $moduleLevels->{'getModuleMandatory' . $value}()
                && count($module) == 0
            ) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    $moduleType->getDescription()
                );
                $this->loggerUtil->log('C');
                $this->exitOnError($game);
                $error = true;
            }
            if ($moduleType->isSpecialSystemType()) {
                $specialCount = 0;
                foreach ($module as $id) {
                    $specialMod = $this->moduleRepository->find((int) $id);

                    if ($specialMod === null) {
                        continue;
                    }

                    $modules[$id] = $specialMod;
                    $specialCount++;
                }

                if ($specialCount > $rump->getSpecialSlots()) {
                    $game->addInformation(_('Mehr Spezial-Module als der Rumpf gestattet'));
                    $this->loggerUtil->log('D');
                    $this->exitOnError($game);
                    $error = true;
                }
                continue;
            }
            if (count($module) == 0 || current($module) == 0) {
                continue;
            }
            $mod = null;
            if (current($module) > 0) {
                $moduleId = (int) current($module);
                $mod = $this->moduleRepository->find($moduleId);
                if ($mod === null) {
                    throw new RuntimeException(sprintf('moduleId %d does not exist', $moduleId));
                }
            } elseif (!$moduleLevels->{'getModuleLevel' . $value}()) {
                $this->exitOnError($game);
                return;
            }
            if ($mod !== null) {
                $modules[current($module)] = $mod;
            }
        }

        if ($error) {
            return;
        }

        $this->loggerUtil->log('E');

        $crewUsage = $this->shipCrewCalculator->getCrewUsage($modules, $rump, $user);
        if ($crewUsage > $this->shipCrewCalculator->getMaxCrewCountByRump($rump)) {
            $game->addInformation(_('Crew-Maximum wurde überschritten'));
            $this->loggerUtil->log('F');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('G');
        $signature = $this->buildplanSignatureCreation->createSignature($modules, $crewUsage);

        $plannameFromRequest = request::indString('buildplanname');
        if (
            $plannameFromRequest !== false
            && $plannameFromRequest !== ''
            && $plannameFromRequest !== 'Bauplanname'
        ) {
            $planname = CleanTextUtils::clearEmojis($plannameFromRequest);
            $nameWithoutUnicode = CleanTextUtils::clearUnicode($planname);
            if ($planname !== $nameWithoutUnicode) {
                $game->addInformation(_('Der Name enthält ungültigen Unicode'));
                $this->exitOnError($game);
                return;
            }

            if (mb_strlen($planname) > 255) {
                $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
                $this->exitOnError($game);
                return;
            }
        } else {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
        }
        if ($this->shipBuildplanRepository->findByUserAndName($userId, $planname) !== null) {
            $game->addInformation(_('Ein Bauplan mit diesem Namen existiert bereits'));
            $this->exitOnError($game);
            return;
        }
        $existingPlan = $this->shipBuildplanRepository->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);
        if ($existingPlan !== null) {
            $game->addInformation(_('Ein Bauplan mit dieser Konfiguration existiert bereits: %s'));
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('H');
        $game->addInformationf(
            _('Lege neuen Bauplan an: %s'),
            $planname
        );
        $plan = $this->shipBuildplanRepository->prototype();
        $plan->setUser($user);
        $plan->setRump($rump);
        $plan->setName($planname);
        $plan->setSignature($signature);
        $plan->setBuildtime($rump->getBuildtime());
        $plan->setCrew($crewUsage);

        $this->shipBuildplanRepository->save($plan);
        $this->entityManager->flush();

        $this->loggerUtil->log('I');


        foreach ($modules as $module) {
            $buildplanModule = $this->buildplanModuleRepository->prototype();
            $buildplanModule->setModuleType($module->getType());
            $buildplanModule->setBuildplan($plan);
            $buildplanModule->setModule($module);
            $buildplanModule->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($module->getSpecials()));

            $this->buildplanModuleRepository->save($buildplanModule);

            $plan->getModules()->set($module->getId(), $buildplanModule);
        }

        $this->loggerUtil->log('J');

        $game->setView(ShowModuleScreenBuildplan::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::BUILDPLAN, $plan->getId());

        $this->loggerUtil->log('K');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
