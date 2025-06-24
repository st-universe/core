<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreatorInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class CreateTholianWeb implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_WEB';

    /** @param SpacecraftCreatorInterface<SpacecraftWrapperInterface> $spacecraftCreator */
    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private InteractionCheckerInterface $interactionChecker,
        private ActivatorDeactivatorHelperInterface $helper,
        private TholianWebRepositoryInterface $tholianWebRepository,
        private TholianWebUtilInterface $tholianWebUtil,
        private SpacecraftCreatorInterface $spacecraftCreator,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuTime $stuTime,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //TODO other web spinners in fleet should join

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

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
        if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::THOLIAN_WEB, $game)) {
            return;
        }
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::WEB_SPINNING);

        //create web
        /** @var TholianWebInterface */
        $web = $this->spacecraftCreator->createBy($userId, 9, 1840, null)
            ->setLocation($ship->getLocation())
            ->finishConfiguration()
            ->get();

        //link spacecrafts to web
        foreach ($possibleCatches as $target) {
            $target->setHoldingWeb($web);
            $this->spacecraftRepository->save($target);
            $web->getCapturedSpacecrafts()->add($target);

            //notify target owner
            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    'In Sektor %s wird ein Energienetz um die %s errichtet',
                    $target->getSectorString(),
                    $target->getName()
                ),
                $target->getType()->getMessageFolderType()
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

    private function tryToCatch(ShipInterface $ship, int $targetId, GameControllerInterface $game): ?SpacecraftInterface
    {
        $target = $this->spacecraftRepository->find($targetId);

        if ($target === null || $target->isCloaked()) {
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
