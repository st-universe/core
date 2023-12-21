<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowSignatures;

use request;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowSignatures implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'ADMIN_SHOW_SIGNATURES';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private PanelLayerCreationInterface $panelLayerCreation;

    private LayerRepositoryInterface $layerRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        PanelLayerCreationInterface $panelLayerCreation,
        LayerRepositoryInterface $layerRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->panelLayerCreation = $panelLayerCreation;
        $this->layerRepository = $layerRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
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
            $game->addInformation(sprintf('layerId %d existiert nicht', $layerId));
            return;
        }

        if ($shipId !== 0) {
            $game->addInformation(_('Aktion noch nicht möglich für Einzelschiff'));
            return;
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
            $userId,
            $allyId,
            $this->loggerUtilFactory->getLoggerUtil()
        ));
    }
}
