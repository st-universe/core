<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\CreateNPCQuest;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCQuestRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class CreateNPCQuest implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_NPC_QUEST';

    public function __construct(
        private NPCQuestRepositoryInterface $npcQuestRepository,
        private FactionRepositoryInterface $factionRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private RpgPlotRepositoryInterface $rpgPlotRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowNPCQuests::VIEW_IDENTIFIER);

        $user = $game->getUser();

        $title = trim(request::postString('title') ?: '');
        $text = trim(request::postString('text') ?: '');
        $prestige = request::postInt('prestige');
        $awardId = request::postInt('award_id');
        $applicantMax = request::postInt('applicant_max');
        $plotId = request::postInt('plot_id');
        $approvalRequired = request::has('approval_required');

        $startDate = trim(request::postString('start_date') ?: '');
        $startTime = trim(request::postString('start_time') ?: '');
        $applicationEndDate = trim(request::postString('application_end_date') ?: '');
        $applicationEndTime = trim(request::postString('application_end_time') ?: '');

        $factionIds = request::postArray('factions');
        $secretFactionIds = request::postArray('secret_factions');
        $commodities = request::postArray('commodities');
        $spacecrafts = request::postArray('spacecrafts');


        if (empty($title)) {
            $game->getInfo()->addInformation('Titel muss ausgefüllt werden');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($text)) {
            $game->getInfo()->addInformation('Beschreibung muss ausgefüllt werden');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($startDate) || empty($startTime)) {
            $game->getInfo()->addInformation('Startdatum und -zeit müssen ausgefüllt werden');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($applicationEndDate) || empty($applicationEndTime)) {
            $game->getInfo()->addInformation('Anmeldeschluss-Datum und -zeit müssen ausgefüllt werden');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        $startTimestamp = $this->parseDateTime($startDate, $startTime);
        $applicationEndTimestamp = $this->parseDateTime($applicationEndDate, $applicationEndTime);

        if ($startTimestamp === null) {
            $game->getInfo()->addInformation('Ungültiges Startdatum oder -zeit. Format: TT.MM.JJJJ und HH:MM');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp === null) {
            $game->getInfo()->addInformation('Ungültiges Anmeldeschluss-Datum oder -zeit. Format: TT.MM.JJJJ und HH:MM');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($startTimestamp <= time()) {
            $game->getInfo()->addInformation('Das Startdatum muss in der Zukunft liegen');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp <= time()) {
            $game->getInfo()->addInformation('Der Anmeldeschluss muss in der Zukunft liegen');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp >= $startTimestamp) {
            $game->getInfo()->addInformation('Der Anmeldeschluss muss vor dem Start liegen');
            $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot === null) {
                $game->getInfo()->addInformation('Der angegebene Plot existiert nicht');
                $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
                return;
            }
            if ($plot->getUserId() !== $user->getId()) {
                $game->getInfo()->addInformation('Du bist nicht der Ersteller dieses Plots');
                $this->setFormData($game, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
                return;
            }
        }

        $validatedFactions = $this->validateFactions($factionIds);
        $validatedSecretFactions = $this->validateFactions($secretFactionIds);
        $validatedCommodities = $this->validateCommodities($commodities);
        $validatedSpacecrafts = $this->validateSpacecrafts($spacecrafts);

        $quest = $this->npcQuestRepository->prototype();
        $quest->setUserId($user->getId());
        $quest->setUser($user);
        $quest->setUser($user);
        $quest->setTitle($title);
        $quest->setText($text);
        $quest->setTime(time());
        $quest->setStart($startTimestamp);
        $quest->setApplicationEnd($applicationEndTimestamp);

        if ($prestige > 0) {
            $quest->setPrestige($prestige);
        }

        if ($awardId > 0) {
            $quest->setAwardId($awardId);
        }

        if ($applicantMax > 0) {
            $quest->setApplicantMax($applicantMax);
        }

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot !== null && $plot->getUserId() === $user->getId()) {
                $quest->setPlotId($plotId);
                $quest->setPlot($plot);
            }
        }

        $quest->setApprovalRequired($approvalRequired);

        if (!empty($validatedFactions)) {
            $quest->setFactions($validatedFactions);
        }

        if (!empty($validatedCommodities)) {
            $quest->setCommodityReward($validatedCommodities);
        }

        if (!empty($validatedSpacecrafts)) {
            $quest->setSpacecrafts($validatedSpacecrafts);
        }

        if (!empty($validatedSecretFactions)) {
            $quest->setSecret($validatedSecretFactions);
        }

        $this->npcQuestRepository->save($quest);

        $game->getInfo()->addInformation('Die Quest wurde erfolgreich erstellt');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function parseDateTime(string $date, string $time): ?int
    {
        $date = trim($date);
        $time = trim($time);

        if (empty($date) || empty($time)) {
            return null;
        }

        $datePattern = '/^(\d{2})\.(\d{2})\.(\d{4})$/';
        $timePattern = '/^(\d{2}):(\d{2})$/';

        if (!preg_match($datePattern, $date, $dateMatches)) {
            return null;
        }

        if (!preg_match($timePattern, $time, $timeMatches)) {
            return null;
        }

        $day = (int)$dateMatches[1];
        $month = (int)$dateMatches[2];
        $year = (int)$dateMatches[3];
        $hour = (int)$timeMatches[1];
        $minute = (int)$timeMatches[2];

        if (!checkdate($month, $day, $year)) {
            return null;
        }

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }

        $timestamp = mktime($hour, $minute, 0, $month, $day, $year);
        return $timestamp !== false ? $timestamp : null;
    }

    /**
     * @param array<mixed> $factionIds
     * @return array<int>
     */
    private function validateFactions(array $factionIds): array
    {
        $validFactions = [];
        $playableFactions = $this->factionRepository->getByChooseable(true);
        $playableFactionIds = array_map(fn ($faction) => $faction->getId(), $playableFactions);

        foreach ($factionIds as $factionId) {
            $id = (int)$factionId;
            if ($id > 0 && in_array($id, $playableFactionIds)) {
                $validFactions[] = $id;
            }
        }

        return array_unique($validFactions);
    }

    /**
     * @param array<mixed> $commodities
     * @return array<int, int>
     */
    private function validateCommodities(array $commodities): array
    {
        $validCommodities = [];
        $allCommodities = $this->commodityRepository->getAll();

        foreach ($commodities as $commodity) {
            if (!is_array($commodity) || !isset($commodity['id']) || !isset($commodity['amount'])) {
                continue;
            }

            $id = (int)$commodity['id'];
            $amount = (int)$commodity['amount'];

            if ($id > 0 && $amount > 0 && isset($allCommodities[$id])) {
                if (isset($validCommodities[$id])) {
                    $validCommodities[$id] += $amount;
                } else {
                    $validCommodities[$id] = $amount;
                }
            }
        }

        return $validCommodities;
    }

    /**
     * @param array<mixed> $spacecrafts
     * @return array<int, int>
     */
    private function validateSpacecrafts(array $spacecrafts): array
    {
        $validSpacecrafts = [];

        foreach ($spacecrafts as $spacecraft) {
            if (!is_array($spacecraft) || !isset($spacecraft['id']) || !isset($spacecraft['amount'])) {
                continue;
            }

            $id = (int)$spacecraft['id'];
            $amount = (int)$spacecraft['amount'];

            if ($id > 0 && $amount > 0) {
                if (isset($validSpacecrafts[$id])) {
                    $validSpacecrafts[$id] += $amount;
                } else {
                    $validSpacecrafts[$id] = $amount;
                }
            }
        }

        return $validSpacecrafts;
    }

    /**
     * @param array<mixed> $factionIds
     * @param array<mixed> $secretFactionIds
     * @param array<mixed> $commodities
     * @param array<mixed> $spacecrafts
     */
    private function setFormData(
        GameControllerInterface $game,
        string $title,
        string $text,
        string $startDate,
        string $startTime,
        string $applicationEndDate,
        string $applicationEndTime,
        int $prestige,
        int $awardId,
        int $applicantMax,
        int $plotId,
        bool $approvalRequired,
        array $factionIds,
        array $secretFactionIds,
        array $commodities,
        array $spacecrafts
    ): void {
        $game->setTemplateVar('FORM_TITLE', $title);
        $game->setTemplateVar('FORM_TEXT', $text);
        $game->setTemplateVar('FORM_START_DATE', $startDate);
        $game->setTemplateVar('FORM_START_TIME', $startTime);
        $game->setTemplateVar('FORM_APPLICATION_END_DATE', $applicationEndDate);
        $game->setTemplateVar('FORM_APPLICATION_END_TIME', $applicationEndTime);
        $game->setTemplateVar('FORM_PRESTIGE', $prestige > 0 ? $prestige : '');
        $game->setTemplateVar('FORM_AWARD_ID', $awardId > 0 ? $awardId : '');
        $game->setTemplateVar('FORM_APPLICANT_MAX', $applicantMax > 0 ? $applicantMax : '');
        $game->setTemplateVar('FORM_PLOT_ID', $plotId > 0 ? $plotId : '');
        $game->setTemplateVar('FORM_APPROVAL_REQUIRED', $approvalRequired);
        $game->setTemplateVar('FORM_SELECTED_FACTIONS', $factionIds);
        $game->setTemplateVar('FORM_SELECTED_SECRET_FACTIONS', $secretFactionIds);
        $game->setTemplateVar('FORM_COMMODITIES', $commodities);
        $game->setTemplateVar('FORM_SPACECRAFTS', $spacecrafts);
        $game->setTemplateVar('QUEST_CREATOR_OPEN', true);
    }
}
