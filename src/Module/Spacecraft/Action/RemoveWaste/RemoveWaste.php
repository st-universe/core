<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RemoveWaste;

use Override;
use request;;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class RemoveWaste implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_WASTE';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spaceCraftLoader
     */
    public function __construct(
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private SpacecraftLoaderInterface $spaceCraftLoader,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $spacecraft = $this->spaceCraftLoader->getByIdAndUser(request::getIntFatal('id'), $userId);

        $commodities = request::postArray('commodity');

        $reason = request::postString('reason');

        if (!$commodities) {
            $game->addInformation(_('Es wurden keine Waren ausgewählt'));
            return;
        }


        if ($game->getUser()->isNpc()) {

            if ($reason === '' || $reason == null) {
                $game->addInformation("Grund fehlt");
                return;
            }
        }

        $storage = $spacecraft->getStorage();

        $wasted = [];
        foreach ($commodities as $commodityId => $count) {
            if (!$storage->containsKey((int)$commodityId)) {
                continue;
            }
            $count = (int)$count;

            if ($count < 1) {
                continue;
            }

            $commodity = $this->commodityRepository->find((int)$commodityId);

            if ($commodity === null) {
                continue;
            }

            $stor = $storage->get((int)$commodityId);

            if ($stor) {
                if ($count > $stor->getAmount()) {
                    $count = $stor->getAmount();
                }
            }

            $this->storageManager->lowerStorage($spacecraft, $commodity, $count);
            $wasted[] = sprintf('%d %s', $count, $commodity->getName());
        }
        $game->addInformation(_('Die folgenden Waren wurden entsorgt:'));
        foreach ($wasted as $msg) {
            $game->addInformation($msg);
        }

        if ($game->getUser()->isNpc()) {
            $this->createEntry(
                sprintf(
                    '%s (%d) hat auf dem Spacecraft %s (%d) %s entsorgt. Grund: %s',
                    $game->getUser()->getName(),
                    $game->getUser()->getId(),
                    $spacecraft->getName(),
                    $spacecraft->getId(),
                    implode(', ', $wasted),
                    $reason

                ),
                $userId
            );
        }
    }

    private function createEntry(
        string $text,
        int $UserId
    ): void {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($UserId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
