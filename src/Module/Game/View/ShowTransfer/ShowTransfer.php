<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowTransfer;

use Override;
use request;
use RuntimeException;
use Stu\Config\Init;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Transfer\CommodityTransferInterface;
use Stu\Lib\Transfer\Strategy\TransferStrategyInterface;
use Stu\Lib\Transfer\TransferEntityNotFoundException;
use Stu\Lib\Transfer\TransferInformation;
use Stu\Lib\Transfer\TransferInformationFactoryInterface;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRANSFER';

    public function __construct(
        private CommodityTransferInterface $commodityTransfer,
        private TransferInformationFactoryInterface $transferInformationFactory,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $id = request::getIntFatal('id');
        $isUnload = request::getIntFatal('is_unload') === 1;
        $transferType = TransferTypeEnum::from(request::getIntFatal('transfer_type'));

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        try {
            $transferInformation = $this->transferInformationFactory->createTransferInformation(
                $id,
                TransferEntityTypeEnum::from(request::getStringFatal('source_type')),
                request::getIntFatal('target'),
                TransferEntityTypeEnum::from(request::getStringFatal('target_type')),
                $transferType,
                $isUnload,
                $user,
                false
            );
        } catch (TransferEntityNotFoundException) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation('Das Ziel konnte nicht gefunden werden');
            return;
        }

        $this->setPageTitle($transferInformation, $game);

        $source = $transferInformation->getSource();
        $target = $transferInformation->getTarget();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($source)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game->getInfo())) {
            $game->setMacroInAjaxWindow('');
            return;
        }

        $game->setMacroInAjaxWindow('html/transfer/showTransfer.twig');

        $game->setTemplateVar('SOURCE', $source);
        $game->setTemplateVar('TARGET', $target);
        $game->setTemplateVar('OWNS_TARGET', $transferInformation->getTargetWrapper()->getUser() === $user);
        $game->setTemplateVar('TRANSFER_INFO', $transferInformation);

        $strategy = $this->getTransferStrategy($transferType);
        $strategy->setTemplateVariables(
            $isUnload,
            $transferInformation->getSourceWrapper(),
            $transferInformation->getTargetWrapper(),
            $game
        );
    }


    private function setPageTitle(
        TransferInformation $transferInformation,
        GameControllerInterface $game
    ): void {
        $game->setPageTitle(sprintf(
            '%s %s %s %s',
            $transferInformation->getTransferType()->getGoodName(),
            $transferInformation->isUnload() ? 'zu' : 'von',
            $transferInformation->getTargetType()->getName(),
            $this->commodityTransfer->isDockTransfer($transferInformation->getSource(), $transferInformation->getTarget()) ? 'transferieren' : 'beamen'
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
}
