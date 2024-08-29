<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use RuntimeException;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class CreateTholianWeb implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_WEB';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private InteractionCheckerInterface $interactionChecker, private ActivatorDeactivatorHelperInterface $helper, private TholianWebRepositoryInterface $tholianWebRepository, private TholianWebUtilInterface $tholianWebUtil, private ShipCreatorInterface $shipCreator, private PrivateMessageSenderInterface $privateMessageSender, private StuTime $stuTime, private ShipStateChangerInterface $shipStateChanger, private EntityManagerInterface $entityManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //TODO other web spinners in fleet should join

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $emitter = $wrapper->getWebEmitterSystemData();

        if ($emitter === null || $emitter->getWebUnderConstruction() !== null || $emitter->getOwnedTholianWeb() !== null) {
            throw new SanityCheckException('emitter = null or already spinning', self::ACTION_IDENTIFIER);
        }

        if ($this->tholianWebRepository->getWebAtLocation($ship) !== null) {
            throw new SanityCheckException('already existing web on location', self::ACTION_IDENTIFIER);
        }

        if ($ship->isWarped()) {
            $game->addInformation("Aktion nicht möglich, Schiff befindet sich im Warp");
            return;
        }

        $chosenShipIds = request::postArray('chosen');

        $possibleCatches = [];
        foreach ($chosenShipIds as $targetId) {
            $target = $this->tryToCatch($ship, (int)$targetId, $game);
            if ($target !== null) {
                $possibleCatches[] = $target;
            }
        }

        if ($possibleCatches === []) {
            $game->addInformation("Es konnten keine Ziele erfasst werden");
            return;
        }

        // activate system
        if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, $game)) {
            return;
        }
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_WEB_SPINNING);

        //create web ship
        $webShip = $this->shipCreator->createBy($userId, 9, 1840)
            ->setLocation($ship->getLocation())
            ->finishConfiguration()
            ->get();

        $webShip->setSpacecraftType(SpacecraftTypeEnum::SPACECRAFT_TYPE_OTHER);
        $this->shipRepository->save($webShip);

        //create web entity
        $web = $this->tholianWebRepository->prototype();
        $web->setWebShip($webShip);
        $this->tholianWebRepository->save($web);

        //link ships to web
        foreach ($possibleCatches as $target) {
            $target->setHoldingWeb($web);
            $this->shipRepository->save($target);
            $web->getCapturedShips()->add($target);

            //notify target owner
            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    'In Sektor %s wird ein Energienetz um die %s errichtet',
                    $target->getSectorString(),
                    $target->getName()
                ),
                $target->isBase() ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
        }

        $this->entityManager->flush();

        $emitter
            ->setWebUnderConstructionId($web->getId())
            ->setOwnedWebId($web->getId())->update();

        $finishedTime = $this->tholianWebUtil->updateWebFinishTime($web);
        if ($finishedTime === null) {
            throw new RuntimeException('this should not happen');
        }

        $game->addInformationf(
            "Es wird ein Energienetz um %d Ziele gespannt, Fertigstellung: %s",
            count($possibleCatches),
            $this->stuTime->transformToStuDateTime($finishedTime)
        );
    }

    private function tryToCatch(ShipInterface $ship, int $targetId, GameControllerInterface $game): ?ShipInterface
    {
        $target = $this->shipRepository->find($targetId);

        if ($target === null || $target->getCloakState()) {
            return null;
        }

        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            $game->addInformationf(_('%s: Ziel nicht gefunden'), $target->getName());
            return null;
        }
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformationf(_('%s: Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'), $target->getName());
            return null;
        }

        return $target;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
