<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\Transfer;

use Override;
use request;
use RuntimeException;
use Stu\Config\Init;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferInformationFactoryInterface;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;

final class Transfer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRANSFER';

    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private TransferInformationFactoryInterface $transferInformationFactory,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $isUnload = request::postIntFatal('is_unload') === 1;
        $transferType = TransferTypeEnum::from(request::postIntFatal('transfer_type'));

        $transferInformation = $this->transferInformationFactory->createTransferInformation(
            request::postIntFatal('id'),
            TransferEntityTypeEnum::from(request::postStringFatal('source_type')),
            request::postIntFatal('target'),
            TransferEntityTypeEnum::from(request::postStringFatal('target_type')),
            $transferType,
            $isUnload
        );

        $source = $transferInformation->getSource();
        $target = $transferInformation->getTarget();

        $game->setView($source->getTransferEntityType()->getViewIdentifier());

        if (!$transferInformation->getSourceWrapper()->canTransfer($game)) {
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
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game)) {
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

        $this->privateMessageSender->send(
            $transferInformation->getSourceWrapper()->getUser()->getId(),
            $transferInformation->getTargetWrapper()->getUser()->getId(),
            $informations->getInformationsAsString(),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            $target->getHref()
        );

        $game->addInformationWrapper($informations);
    }

    private function sanityCheck(TransferInformation $transferInformation): void
    {
        if (!in_array(
            $transferInformation->getSourceType(),
            $transferInformation->getTargetType()->getAllowedTransferSources()
        )) {
            throw new SanityCheckException(sprintf('unallowed transfer source!'));
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
