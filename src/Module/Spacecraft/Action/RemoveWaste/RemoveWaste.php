<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RemoveWaste;

use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class RemoveWaste implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_WASTE_SPACECRAFT';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spaceCraftLoader
     */
    public function __construct(
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private SpacecraftLoaderInterface $spaceCraftLoader,
        private NPCLogRepositoryInterface $npcLogRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $spacecraft = $this->spaceCraftLoader->getByIdAndUser(request::indInt('id'), $userId);

        $commodities = request::postArray('commodity');

        $reason = request::postString('reason');

        if ($commodities === []) {
            $context->getInfo()->addInformation(_('Es wurden keine Waren ausgewählt'));
            return;
        }


        if ($context->getUser()->isNpc() && ($reason === '' || $reason == null)) {
            $context->getInfo()->addInformation("Grund fehlt");
            return;
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

            if ($stor && $count > $stor->getAmount()) {
                $count = $stor->getAmount();
            }

            $this->storageManager->lowerStorage($spacecraft, $commodity, $count);
            $wasted[] = sprintf('%d %s', $count, $commodity->getName());
        }
        $context->getInfo()->addInformation(_('Die folgenden Waren wurden entsorgt:'));
        foreach ($wasted as $msg) {
            $context->getInfo()->addInformation($msg);
        }

        if ($context->getUser()->isNpc()) {
            $this->createEntry(
                sprintf(
                    '%s (%d) hat auf dem Spacecraft %s (%d) %s entsorgt. Grund: %s',
                    $context->getUser()->getName(),
                    $context->getUser()->getId(),
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
        $entry->setAdminView(false);

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
