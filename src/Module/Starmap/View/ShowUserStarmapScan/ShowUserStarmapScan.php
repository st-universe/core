<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapScan;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Realtime\StarmapRealtimeTokenFactory;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Control\ViewControllerInterface;

final class ShowUserStarmapScan implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_USER_STARMAP_SCAN';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private StarmapRealtimeTokenFactory $tokenFactory
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $result = $game->getViewContextMetadata(ViewContextMetadataTypeEnum::STARMAP_SCAN_RESULT);
        if (!is_string($result)) {
            $result = json_encode(['success' => false, 'message' => 'Scan nicht möglich. Bitte erneut versuchen'], JSON_THROW_ON_ERROR);
        }

        $scan = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($scan) && ($scan['success'] ?? false) === true) {
            $scan['trackingToken'] = $this->tokenFactory->create(
                $game->getUser()->getId(),
                (int) $scan['layerId'],
                60,
                [
                    'purpose' => 'starmapScan',
                    'connectionId' => request::postString('connectionid'),
                    'encryptedScan' => $this->tokenFactory->encryptScan($result)
                ]
            );
            foreach ($scan['spacecrafts'] as &$spacecraft) {
                $spacecraft['id'] = -random_int(1, 2 ** 48 - 1);
            }
            unset($spacecraft);
            $result = json_encode($scan, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $sessionString = $game->getSessionString();
        $this->entityManager->flush();
        $this->entityManager->commit();

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo '{"sessionString":' . json_encode($sessionString, JSON_THROW_ON_ERROR) . ',"result":' . $result . '}';
        exit;
    }
}
