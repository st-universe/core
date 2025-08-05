<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use Override;
use request;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowSignatures implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'ADMIN_SHOW_SIGNATURES';

    public function __construct(private FlightSignatureRepositoryInterface $flightSignatureRepository, private LoggerUtilFactoryInterface $loggerUtilFactory, private PanelLayerCreationInterface $panelLayerCreation, private LayerRepositoryInterface $layerRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $layerId = request::postIntFatal('layerid');
        $shipId = request::postInt('shipid');
        $userId = request::postInt('userid');
        $allyId = request::postInt('allyid');

        $game->setTemplateFile('html/admin/signatureScan.twig');

        $signatureRange = [];

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            $game->getInfo()->addInformation(sprintf('layerId %d existiert nicht', $layerId));
            return;
        }

        if ($shipId !== 0) {
            $signatureRange = $this->flightSignatureRepository->getSignatureRangeForShip($shipId);
        } elseif ($userId !== 0) {
            $signatureRange = $this->flightSignatureRepository->getSignatureRangeForUser($userId);
        } elseif ($allyId !== 0) {
            $signatureRange = $this->flightSignatureRepository->getSignatureRangeForAlly($allyId);
        } else {
            $signatureRange = $this->flightSignatureRepository->getSignatureRange();
        }

        if ($signatureRange === []) {
            return;
        }

        $game->setTemplateVar('VISUAL_PANEL', new SignaturePanel(
            current($signatureRange),
            $this->panelLayerCreation,
            $layer,
            $shipId,
            $userId,
            $allyId,
            $this->loggerUtilFactory->getLoggerUtil()
        ));
    }
}
