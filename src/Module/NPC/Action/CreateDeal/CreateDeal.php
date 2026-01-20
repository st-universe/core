<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\CreateDeal;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Entity\Deals;

final class CreateDeal implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_DEAL';

    public function __construct(
        private CommodityRepositoryInterface $commodityRepository,
        private NPCLogRepositoryInterface $npcLogRepository,
        private DealsRepositoryInterface $dealsRepository,
        private FactionRepositoryInterface $factionRepository,
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $user = $game->getUser();

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $dealType = (int)request::postInt('deal_type');
        $dealCount = request::postInt('deal_count');
        $factionId = (int)request::postInt('faction_restriction');
        $reason = request::postString('reason');
        $startDate = request::postString('start_date');
        $startTime = request::postString('start_time');
        $endDate = request::postString('end_date');
        $endTime = request::postString('end_time');
        $wantCommodityId = (int)request::postInt('want_commodity_id');
        $wantCommodityAmount = request::postInt('want_commodity_amount');
        $wantPrestige = request::postInt('want_prestige');

        $giveCommodityId = (int)request::postInt('give_commodity_id');
        $giveCommodityAmount = request::postInt('give_commodity_amount');
        $giveBuildplanId = (int)request::postInt('give_buildplan_id');
        $giveType = (int)request::postInt('give_type');

        if ($game->isNPC() && $reason === '') {
            $game->getInfo()->addInformation("Grund fehlt");
            return;
        }

        if (empty($startDate) || empty($startTime) || empty($endDate) || empty($endTime)) {
            $game->getInfo()->addInformation("Start- oder Endzeit fehlt");
            return;
        }

        $startTimestamp = $this->parseDateTime($startDate, $startTime);
        $endTimestamp = $this->parseDateTime($endDate, $endTime);

        if ($startTimestamp === false || $endTimestamp === false) {
            $game->getInfo()->addInformation("Ungültiges Datum oder Zeitformat. Bitte verwende TT.MM.JJJJ und HH:MM");
            return;
        }

        if ($startTimestamp >= $endTimestamp) {
            $game->getInfo()->addInformation("Endzeit muss nach Startzeit liegen");
            return;
        }

        if ($startTimestamp <= time()) {
            $game->getInfo()->addInformation("Startzeit muss in der Zukunft liegen");
            return;
        }

        $isDeal = $dealType === 1;
        if ($isDeal && $dealCount < 0) {
            $game->getInfo()->addInformation("Bei einem Deal darf die Anzahl nicht negativ sein");
            return;
        }

        $hasWantCommodity = $wantCommodityId > 0 && $wantCommodityAmount > 0;
        $hasWantPrestige = $wantPrestige > 0;

        if ($hasWantCommodity && $hasWantPrestige) {
            $game->getInfo()->addInformation("Es kann entweder eine Ware oder Prestige verlangt werden, nicht beides");
            return;
        }

        if (!$hasWantCommodity && !$hasWantPrestige) {
            $game->getInfo()->addInformation("Es muss entweder eine Ware oder Prestige verlangt werden");
            return;
        }

        $hasGiveCommodity = $giveCommodityId > 0 && $giveCommodityAmount > 0;
        $hasGiveBuildplan = $giveBuildplanId > 0;

        if ($hasGiveCommodity && $hasGiveBuildplan) {
            $game->getInfo()->addInformation("Es kann entweder eine Ware oder ein Schiff/Bauplan angeboten werden, nicht beides");
            return;
        }

        if (!$hasGiveCommodity && !$hasGiveBuildplan) {
            $game->getInfo()->addInformation("Es muss entweder eine Ware oder ein Schiff/Bauplan angeboten werden");
            return;
        }

        $wantedCommodity = null;
        if ($hasWantCommodity) {
            $wantedCommodity = $this->commodityRepository->find($wantCommodityId);
            if ($wantedCommodity === null) {
                $game->getInfo()->addInformation("Ungültige verlangte Ware mit ID: " . $wantCommodityId);
                return;
            }
        }

        $giveCommodity = null;
        if ($hasGiveCommodity) {
            $giveCommodity = $this->commodityRepository->find($giveCommodityId);
            if ($giveCommodity === null) {
                $game->getInfo()->addInformation("Ungültige angebotene Ware mit ID: " . $giveCommodityId);
                return;
            }
        }

        $buildplan = null;
        if ($hasGiveBuildplan) {
            $buildplan = $this->buildplanRepository->find($giveBuildplanId);
            if ($buildplan === null) {
                $game->getInfo()->addInformation("Ungültiger Bauplan mit ID: " . $giveBuildplanId);
                return;
            }
        }

        $faction = null;
        if ($factionId > 0) {
            $faction = $this->factionRepository->find($factionId);
            if ($faction === null) {
                $game->getInfo()->addInformation("Ungültige Fraktion mit ID: " . $factionId);
                return;
            }
        }


        $deal = $this->dealsRepository->prototype();
        $deal->setAuction($dealType === 2);

        if ($isDeal && $dealCount > 0) {
            $deal->setAmount($dealCount);
        } else {
            $deal->setAmount(null);
        }

        if ($faction !== null) {
            $deal->setFaction($faction);
        }

        if ($hasWantCommodity) {
            $deal->setWantedCommodity($wantedCommodity);
            $deal->setWantCommodityAmount($wantCommodityAmount);
        } elseif ($hasWantPrestige) {
            $deal->setwantPrestige($wantPrestige);
        }

        $deal->setShip(false);

        if ($hasGiveCommodity) {
            $deal->setGiveCommodity($giveCommodity);
            $deal->setGiveCommodityAmount($giveCommodityAmount);
        }
        if ($hasGiveBuildplan) {
            $deal->setBuildplan($buildplan);
            if ($giveType === 1) {
                $deal->setShip(true);
            }
        }

        $deal->setStart($startTimestamp);
        $deal->setEnd($endTimestamp);

        $this->dealsRepository->save($deal);

        $reasonStr = is_string($reason) ? $reason : '';


        $text = $this->createLogText($user->getName(), $deal, $reasonStr);

        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($text, $user->getId());
        }

        $game->getInfo()->addInformation("Deal erfolgreich erstellt");
    }

    private function parseDateTime(string $date, string $time): int|false
    {
        $dateTime = \DateTime::createFromFormat('d.m.Y H:i', $date . ' ' . $time);

        if ($dateTime === false) {
            return false;
        }

        return $dateTime->getTimestamp();
    }

    private function createLogText(string $userName, Deals $deal, string $reason): string
    {
        $dealType = $deal->getAuction() ? "Auktion" : "Deal";
        $faction = $deal->getFaction() ? " für die Fraktion " . $deal->getFaction()->getName() : "";

        $wantText = "";
        if ($deal->getWantedCommodity() !== null) {
            $wantText = sprintf(
                "%d %s",
                $deal->getWantCommodityAmount(),
                $deal->getWantedCommodity()->getName()
            );
        } elseif ($deal->getWantPrestige() !== null) {
            $wantText = $deal->getWantPrestige() . " Prestige";
        }

        $giveText = "";
        if ($deal->getGiveCommodity() !== null) {
            $giveText = sprintf(
                "%d %s",
                $deal->getGiveCommodityAmount(),
                $deal->getGiveCommodity()->getName()
            );
        } elseif ($deal->getBuildplanId() !== null) {
            $type = $deal->getShip() ? "Schiff" : "Bauplan";
            $buildplanName = $deal->getBuildplan() ? $deal->getBuildplan()->getName() : "ID:" . $deal->getBuildplanId();
            $giveText = $type . " " . $buildplanName;
        }

        $amount = $deal->getAmount() !== null ? " (Anzahl: " . $deal->getAmount() . ")" : "";

        $startDate = date("d.m.Y H:i", $deal->getStart());
        $endDate = date("d.m.Y H:i", $deal->getEnd());

        return sprintf(
            '%s hat folgenden %s%s erstellt%s: Verlangt wird: %s, Angeboten wird: %s, Start: %s, Ende: %s. Grund: %s',
            $userName,
            $dealType,
            $faction,
            $amount,
            $wantText,
            $giveText,
            $startDate,
            $endDate,
            $reason
        );
    }

    private function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
