<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\Transfer;

use Override;
use request;
use RuntimeException;
use Stu\Config\Init;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferEntityNotFoundException;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferInformationFactoryInterface;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\TargetLink;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;


final class Transfer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER';

    public const int INACTIV_TIME = 60 * 60 * 48; // 48 hours

    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private TransferInformationFactoryInterface $transferInformationFactory,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
        private NPCLogRepositoryInterface $npcLogRepository,
        private MapRepositoryInterface $mapRepository,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $isUnload = request::postIntFatal('is_unload') === 1;
        $transferType = TransferTypeEnum::from(request::postIntFatal('transfer_type'));

        try {
            $transferInformation = $this->transferInformationFactory->createTransferInformation(
                request::postIntFatal('id'),
                TransferEntityTypeEnum::from(request::postStringFatal('source_type')),
                request::postIntFatal('target'),
                TransferEntityTypeEnum::from(request::postStringFatal('target_type')),
                $transferType,
                $isUnload,
                $game->getUser(),
                true
            );
        } catch (TransferEntityNotFoundException) {
            $game->getInfo()->addInformation('Das Ziel konnte nicht gefunden werden');
            return;
        }

        $source = $transferInformation->getSource();
        $target = $transferInformation->getTarget();

        $game->setView($source->getTransferEntityType()->getViewIdentifier());

        if (!$transferInformation->getSourceWrapper()->canTransfer($game->getInfo())) {
            return;
        }

        $this->sanityCheck($transferInformation);

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($source)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_TARGET_NO_VACATION,
                InteractionCheckType::EXPECT_TARGET_DOCKED_OR_NO_ION_STORM,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game->getInfo())) {
            return;
        }

        $target = $transferInformation->getTarget();
        $strategy = $this->getTransferStrategy($transferType);

        $informations = new InformationWrapper();

        $strategy->transfer(
            $isUnload,
            $transferInformation->getSourceWrapper(),
            $transferInformation->getTargetWrapper(),
            $informations
        );

        if ($transferInformation->getTargetType() === TransferEntityTypeEnum::COLONY && !$isUnload && !$this->playerRelationDeterminator->isFriend($target->getUser(), $source->getUser())) {
            $targetEntity = $transferInformation->getTargetWrapper()->get();
            if ($targetEntity instanceof Colony) {
                $targetUser = $target->getUser();
                $sourceUser = $source->getUser();
                if ($targetUser !== null && $sourceUser !== null && $this->mapRepository->isAdminRegionUserRegion($target->getLocation()->getId(), $targetUser->getFactionId())) {
                    $userstring = $sourceUser->getName() . '(' . $sourceUser->getId() . ') -> ' . $targetUser->getName() . '(' . $targetUser->getId() . ')';
                    if ($targetUser->getLastaction() < time() - self::INACTIV_TIME) {
                        $lastactivestring = ' | Lastaction: ' . date('d.m.Y H:i:s', $targetUser->getLastaction());
                    } else {
                        $lastactivestring = '';
                    }
                    $text = $informations->getInformationsAsString() . ' | ' . $userstring . ' | ' . $target->getLocation()->getSectorString() . $lastactivestring;

                    $this->createEntry(
                        $text,
                        $sourceUser->getId(),
                        $targetUser->getFactionId()
                    );
                }
            }
        }



        $this->privateMessageSender->send(
            $transferInformation->getSourceWrapper()->getUser()->getId(),
            $transferInformation->getTargetWrapper()->getUser()->getId(),
            $informations->getInformationsAsString(),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            $target
        );

        if ($target->getUser() === $source->getUser()) {
            $game->setTargetLink(new TargetLink(
                $target->getHref(),
                sprintf('Zu Ziel-%s wechseln', $target->getTransferEntityType()->getName())
            ));
        }

        $game->getInfo()->addInformationWrapper($informations);
    }

    private function sanityCheck(TransferInformation $transferInformation): void
    {
        if (!in_array(
            $transferInformation->getSourceType(),
            $transferInformation->getTargetType()->getAllowedTransferSources()
        )) {
            throw new SanityCheckException('unallowed transfer source!');
        }

        switch ($transferInformation->getTransferType()) {
            case TransferTypeEnum::COMMODITIES:
                if ($transferInformation->isCommodityTransferPossible(false)) {
                    return;
                }
                break;
            case TransferTypeEnum::CREW:
                if ($transferInformation->isCrewTransferPossible(false)) {
                    return;
                }
                break;
            case TransferTypeEnum::TORPEDOS:
                if ($transferInformation->isTorpedoTransferPossible(false)) {
                    return;
                }
                break;
        }

        throw new SanityCheckException(sprintf(
            'userId %d tried to transfer %s %s targetId %d (%d), but it is not possible',
            $transferInformation->getSourceWrapper()->getUser()->getId(),
            $transferInformation->getTransferType()->getGoodName(),
            $transferInformation->isUnload() ? 'to' : 'from',
            $transferInformation->getTarget()->getId(),
            $transferInformation->getTargetType()->value
        ));
    }

    private function getTransferStrategy(TransferTypeEnum $transferType): TransferStrategyInterface
    {
        $transferStrategy = Init::getContainer()->getDefinedImplementationsOf(TransferStrategyInterface::class)->get($transferType->value);
        if ($transferStrategy === null) {
            throw new RuntimeException(sprintf('transfer strategy with typeValue %d does not exist', $transferType->value));
        }

        return $transferStrategy;
    }

    private function createEntry(
        string $text,
        int $UserId,
        int $factionId
    ): void {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($UserId);
        $entry->setDate(time());
        $entry->setFactionId($factionId);

        $this->npcLogRepository->save($entry);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
