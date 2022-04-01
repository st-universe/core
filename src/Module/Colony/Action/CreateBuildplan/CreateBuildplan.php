<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateBuildplan;

use request;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreen;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class CreateBuildplan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILDPLAN_SAVE';

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    private function exitOnError(GameControllerInterface $game): void
    {
        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $rump = $this->shipRumpRepository->find((int) request::indInt('rump'));
        if ($rump === null) {
            $this->loggerUtil->log('A');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('B');

        $modules = array();
        $sigmod = array();
        $crew_usage = $rump->getBaseCrew();
        for ($i = 1; $i <= ShipModuleTypeEnum::MODULE_TYPE_COUNT; $i++) {
            $this->loggerUtil->log(sprintf('%d', $i));
            $module = request::postArray('mod_' . $i);
            if (
                $i != ShipModuleTypeEnum::MODULE_TYPE_SPECIAL
                && $rump->getModuleLevels()->{'getModuleMandatory' . $i}() == ShipModuleTypeEnum::MODULE_MANDATORY
                && count($module) == 0
            ) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    ModuleTypeDescriptionMapper::getDescription($i)
                );
                $this->loggerUtil->log('C');
                $this->exitOnError($game);
                return;
            }
            if ($i === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $specialCount = 0;
                foreach ($module as $id) {
                    /** @var ModuleInterface[] $modules */
                    $specialMod = $this->moduleRepository->find((int) $id);
                    $crew_usage += $specialMod->getCrew();
                    $modules[$id] = $specialMod;
                    $sigmod[$id] = $id;
                    $specialCount++;
                }

                if ($specialCount > $rump->getSpecialSlots()) {
                    $game->addInformation(_('Mehr Spezial-Module als der Rumpf gestattet'));
                    $this->loggerUtil->log('D');
                    $this->exitOnError($game);
                    return;
                }
                continue;
            }
            if (count($module) == 0 || current($module) == 0) {
                $sigmod[$i] = 0;
                continue;
            }
            if (current($module) > 0) {
                /** @var ModuleInterface $mod */
                $mod = $this->moduleRepository->find((int) current($module));
                if ($mod->getLevel() > $rump->getModuleLevel()) {
                    $crew_usage += $mod->getCrew() + 1;
                } else {
                    $crew_usage += $mod->getCrew();
                }
            } else {
                if (!$rump->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    $this->exitOnError($game);
                    return;
                }
            }
            $modules[current($module)] = $mod;
            $sigmod[$i] = $mod->getId();
        }
        $this->loggerUtil->log('E');
        if ($crew_usage > $rump->getMaxCrewCount()) {
            $game->addInformation(_('Crew-Maximum wurde überschritten'));
            $this->loggerUtil->log('F');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('G');
        $signature = ShipBuildplan::createSignature($sigmod, $crew_usage);
        if (
            request::has('buildplanname')
            && request::indString('buildplanname') != ''
            && request::indString('buildplanname') != 'Bauplanname'
        ) {
            $planname = request::indString('buildplanname');
        } else {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
        }
        $this->loggerUtil->log('H');
        $game->addInformationf(
            _('Lege neuen Bauplan an: %s'),
            $planname
        );
        $plan = $this->shipBuildplanRepository->prototype();
        $plan->setUser($game->getUser());
        $plan->setRump($rump);
        $plan->setName($planname);
        $plan->setSignature($signature);
        $plan->setBuildtime($rump->getBuildtime());
        $plan->setCrew($crew_usage);

        $this->shipBuildplanRepository->save($plan);
        $this->entityManager->flush();

        $this->loggerUtil->log('I');

        foreach ($modules as $obj) {
            $mod = $this->buildplanModuleRepository->prototype();
            $mod->setModuleType((int) $obj->getType());
            $mod->setBuildplanId((int) $plan->getId());
            $mod->setModule($obj);
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($obj->getSpecials()));

            $this->buildplanModuleRepository->save($mod);
        }

        $this->loggerUtil->log('J');

        $game->setView(ShowModuleScreenBuildplan::VIEW_IDENTIFIER);
        request::setVar('planid', $plan->getId());

        $this->loggerUtil->log('K');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
