<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class CommodityCheat implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COMMODITY_CHEAT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader, private StorageManagerInterface $storageManager, private CommodityRepositoryInterface $commodityRepository, private NPCLogRepositoryInterface $npcLogRepository, private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $context->getUser();
        $text = '';
        $colony = null;
        $spacecraft = null;
        if (!request::getVarByMethod(request::postvars(), 'spacecraftid') && !request::getVarByMethod(request::postvars(), 'colonyid')) {
            $context->getInfo()->addInformation("Es wurde weder Spacecraft noch Kolonie ausgewählt");
            return;
        }

        $spacecraftId = request::postInt('spacecraftid');
        $colonyId = request::postInt('colonyid');
        $reason = request::postString('reason');
        $commodities = request::postArray('commodities');

        if ($spacecraftId !== 0 && $colonyId !== 0) {
            $context->getInfo()->addInformation("Es dürfen nicht Spacecraft und Kolonie gleichzeitig ausgewählt sein");
            return;
        }

        if ($spacecraftId === 0 && $colonyId === 0) {
            $context->getInfo()->addInformation("Es wurde weder Spacecraft noch Kolonie ausgewählt");
            return;
        }

        if ($context->getUser()->isNpc() && $reason === '') {
            $context->getInfo()->addInformation("Grund fehlt");
            return;
        }

        if ($commodities === []) {
            $context->getInfo()->addInformation("Keine Waren angegeben");
            return;
        }

        if ($spacecraftId !== 0) {
            $wrapper = $this->spacecraftLoader->find($spacecraftId);

            if ($wrapper === null) {
                throw new SpacecraftDoesNotExistException(_('Spacecraft does not exist!'));
            }
            $spacecraft = $wrapper->get();
        }

        if ($colonyId !== 0) {
            $colony = $this->colonyRepository->find($colonyId);
            if ($colony === null) {
                $context->getInfo()->addInformation("Kolonie existiert nicht");
                return;
            }
        }

        $validatedCommodities = [];
        $commodityList = [];

        foreach ($commodities as $commodityData) {
            if (!isset($commodityData['id']) || !isset($commodityData['amount'])) {
                $context->getInfo()->addInformation("Ungültige Wareneingabe");
                return;
            }

            $commodityId = (int)$commodityData['id'];
            $amount = (int)$commodityData['amount'];

            if ($commodityId <= 0) {
                continue;
            }

            if ($amount < 1) {
                $context->getInfo()->addInformation("Anzahl muss größer als 0 sein");
                return;
            }

            $commodity = $this->commodityRepository->find($commodityId);

            if ($commodity === null) {
                $context->getInfo()->addInformation("Ungültige Ware mit ID: " . $commodityId);
                return;
            }

            $validatedCommodities[] = [
                'commodity' => $commodity,
                'amount' => $amount
            ];

            $commodityList[] = sprintf('%d %s', $amount, $commodity->getName());
        }

        if ($validatedCommodities === []) {
            $context->getInfo()->addInformation("Keine gültigen Waren ausgewählt");
            return;
        }

        $commodityListString = implode(', ', $commodityList);

        foreach ($validatedCommodities as $validatedCommodity) {
            if ($spacecraft !== null) {
                $this->storageManager->upperStorage(
                    $spacecraft,
                    $validatedCommodity['commodity'],
                    $validatedCommodity['amount']
                );
            }
            if ($colony !== null) {
                $this->storageManager->upperStorage(
                    $colony,
                    $validatedCommodity['commodity'],
                    $validatedCommodity['amount']
                );
            }
        }

        if ($spacecraft !== null) {
            $text = sprintf(
                '%s hat dem Spacecraft %s (%d) von Spieler %s (%d) folgende Waren hinzugefügt: %s. Grund: %s',
                $user->getName(),
                $spacecraft->getName(),
                $spacecraft->getId(),
                $spacecraft->getUser()->getName(),
                $spacecraft->getUser()->getId(),
                $commodityListString,
                $reason
            );
        }
        if ($colony !== null) {
            $text = sprintf(
                '%s hat der Kolonie %s (%d) von Spieler %s (%d) folgende Waren hinzugefügt: %s. Grund: %s',
                $user->getName(),
                $colony->getName(),
                $colony->getId(),
                $colony->getUser()->getName(),
                $colony->getUser()->getId(),
                $commodityListString,
                $reason
            );
        }

        if ($context->getUser()->isNpc()) {
            $this->createEntry($text, $user->getId());
        }
        $context->getInfo()->addInformation("Waren hinzugefügt");
    }

    private function createEntry(
        string $text,
        int $UserId
    ): void {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($UserId);
        $entry->setDate(time());
        $entry->setAdminView(false);

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
