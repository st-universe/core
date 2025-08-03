<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraft;

use InvalidArgumentException;
use Override;
use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Config\Init;
use Stu\Lib\Map\NavPanel\NavPanel;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Lib\Trait\SpacecraftShuttleSpaceTrait;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactoryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Spacecraft\Lib\Ui\ShipUiFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowSpacecraft implements ViewControllerInterface, ViewWithTutorialInterface
{
    use SpacecraftShuttleSpaceTrait;
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFT';

    private ViewContext $viewContext;

    private LoggerUtilInterface $loggerUtil;

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader
     */
    public function __construct(
        private readonly SpacecraftLoaderInterface $spacecraftLoader,
        private readonly UserLayerRepositoryInterface $userLayerRepository,
        private readonly AnomalyRepositoryInterface $anomalyRepository,
        private readonly DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory,
        private readonly NbsUtilityInterface $nbsUtility,
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly ShipUiFactoryInterface $shipUiFactory,
        private readonly SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private readonly ColonizationCheckerInterface $colonizationChecker,
        private readonly SessionStorageInterface $sessionStorage,
        private LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->viewContext = new ViewContext(ModuleEnum::SHIP, self::VIEW_IDENTIFIER);
        $this->loggerUtil = $this->loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;
        $spacecraftId = request::indInt('id');

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $spacecraftId,
            $userId,
            true,
            false
        );
        $spacecraft = $wrapper->get();

        $spacecraftTypeShowStrategy = Init::getContainer()->getDefinedImplementationsOf(SpacecraftTypeShowStragegyInterface::class)->get($spacecraft->getType()->value);
        if ($spacecraftTypeShowStrategy === null) {
            throw new InvalidArgumentException('this should not happen');
        }

        $this->viewContext = $spacecraftTypeShowStrategy
            ->appendNavigationPart($game)
            ->setTemplateVariables($spacecraftId, $game)
            ->getViewContext();

        $tachyonFresh = $game->getViewContext(ViewContextTypeEnum::TACHYON_SCAN_JUST_HAPPENED) ?? false;
        $tachyonActive = $tachyonFresh;

        // check if tachyon scan still active
        if (!$tachyonActive) {
            $tachyonActive = $this->nbsUtility->isTachyonActive($spacecraft);
        }

        $rump = $spacecraft->getRump();

        $colony = $this->getColony($spacecraft);
        $canColonize = false;
        if ($colony !== null) {
            if ($rump->hasSpecialAbility(SpacecraftRump::SPECIAL_ABILITY_COLONIZE)) {
                $canColonize = $this->colonizationChecker->canColonize($user, $colony);
            }
            $ownsCurrentColony = $colony->getUser() === $user;
        }

        //Forschungseintrag erstellen, damit System-Link optional erstellt werden kann
        $starSystem = $spacecraft->getSystem() ?? $spacecraft->isOverSystem();
        if ($starSystem !== null && $starSystem->getDatabaseEntry() !== null) {
            $starSystemEntryTal = $this->databaseCategoryWrapperFactory->createDatabaseCategoryEntryWrapper($starSystem->getDatabaseEntry(), $user);
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starSystemEntryTal);
        }

        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', self::VIEW_IDENTIFIER, $spacecraft->getId()),
            $spacecraft->getName()
        );

        $game->setViewTemplate('html/spacecraft/spacecraft.twig');
        $game->setTemplateVar('WRAPPER', $wrapper);

        if ($spacecraft->getLss()) {

            $this->createUserLayerIfNecessary($user, $spacecraft);

            $game->setTemplateVar('VISUAL_NAV_PANEL', $this->shipUiFactory->createVisualNavPanel(
                $wrapper,
                $game->getUser(),
                $this->loggerUtilFactory->getLoggerUtil(),
                $tachyonFresh
            ));
        }
        if ($spacecraft->canMove()) {
            $game->setTemplateVar('NAV_PANEL', new NavPanel($spacecraft));
        }

        $this->nbsUtility->setNbsTemplateVars($spacecraft, $game, $this->sessionStorage, $tachyonActive);

        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CAN_COLONIZE', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);
        $game->setTemplateVar('CLOSEST_ANOMALY_DISTANCE', $this->anomalyRepository->getClosestAnomalyDistance($wrapper));
        $game->setTemplateVar('HAS_FREE_SHUTTLE_SPACE', $this->hasFreeShuttleSpace($spacecraft));
        $game->setTemplateVar('STORED_SHUTTLE_COUNT', $this->getStoredShuttleCount($spacecraft));
        if ($game->getUser()->getState() === UserStateEnum::COLONIZATION_SHIP) {
            $game->setTemplateVar('CLOSEST_COLONIZABLE_DISTANCE', $this->colonyRepository->getClosestColonizableColonyDistance($wrapper));
        }

        $userLayers = $user->getUserLayers();
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)) {
            $game->setTemplateVar('USER_LAYERS', $userLayers);
        }

        $layer = $spacecraft->getLayer();
        if ($layer !== null && $userLayers->containsKey($layer->getId())) {
            $game->setTemplateVar('IS_MAP_BUTTON_VISIBLE', true);
        }

        $crewObj = $this->shipCrewCalculator->getCrewObj($rump);

        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $crewObj === null
                ? null
                : $this->shipCrewCalculator->getMaxCrewCountByShip($spacecraft)
        );

        $game->addExecuteJS(sprintf("setSpacecraftIdAndSstr(%d, '%s');", $spacecraft->getId(), $game->getSessionString()));
        $this->addWarpcoreSplitJavascript($wrapper, $game);

        $this->loggerUtil->log(sprintf('ShowShip.handle-end, timestamp: %F', microtime(true)));
    }

    private function createUserLayerIfNecessary(User $user, Spacecraft $spacecraft): void
    {
        $layer = $spacecraft->getLayer();
        if ($layer === null) {
            return;
        }

        if ($spacecraft->getMap() === null) {
            return;
        }

        if ($this->hasSeen($user, $layer)) {
            return;
        }

        $userLayer = $this->userLayerRepository->prototype();
        $userLayer->setLayer($layer);
        $userLayer->setUser($user);
        $this->userLayerRepository->save($userLayer);

        $user->getUserLayers()->set($layer->getId(), $userLayer);
    }

    private function addWarpcoreSplitJavascript(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $reactor = $wrapper->getReactorWrapper();
        $warpDriveSystem = $wrapper->getWarpDriveSystemData();
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $warpDriveSystem !== null
            && $epsSystem !== null
            && $reactor !== null
        ) {
            $ship = $wrapper->get();

            $game->addExecuteJS(sprintf(
                'setReactorSplitConstants(%d,%d,%d,%d,%d,%d);',
                $reactor->getOutputCappedByLoad(),
                $wrapper->getEpsUsage(),
                $ship->getRump()->getFlightEcost(),
                $epsSystem->getMaxEps() - $epsSystem->getEps(),
                $warpDriveSystem->getWarpDrive(),
                $warpDriveSystem->getMaxWarpDrive()
            ), JavascriptExecutionTypeEnum::AFTER_RENDER);
            $game->addExecuteJS(sprintf(
                'updateReactorValues(%d);',
                $warpDriveSystem->getWarpDriveSplit(),
            ), JavascriptExecutionTypeEnum::AFTER_RENDER);
        }
    }

    private function getColony(Spacecraft $spacecraft): ?Colony
    {
        if ($spacecraft->getStarsystemMap() === null) {
            return null;
        }

        return $spacecraft->getStarsystemMap()->getColony();
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return $this->viewContext;
    }
}
