<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action\CreateNPCQuest;

use Override;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\AwardRepositoryInterface;
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
        private RpgPlotRepositoryInterface $rpgPlotRepository,
        private AwardRepositoryInterface $awardRepository
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowNPCQuests::VIEW_IDENTIFIER);

        $user = $context->getUser();

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
            $context->getInfo()->addInformation('Titel muss ausgefüllt werden');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($text)) {
            $context->getInfo()->addInformation('Beschreibung muss ausgefüllt werden');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($startDate) || empty($startTime)) {
            $context->getInfo()->addInformation('Startdatum und -zeit müssen ausgefüllt werden');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if (empty($applicationEndDate) || empty($applicationEndTime)) {
            $context->getInfo()->addInformation('Anmeldeschluss-Datum und -zeit müssen ausgefüllt werden');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        $startTimestamp = $this->parseDateTime($startDate, $startTime);
        $applicationEndTimestamp = $this->parseDateTime($applicationEndDate, $applicationEndTime);

        if ($startTimestamp === null) {
            $context->getInfo()->addInformation('Ungültiges Startdatum oder -zeit. Format: TT.MM.JJJJ und HH:MM');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp === null) {
            $context->getInfo()->addInformation('Ungültiges Anmeldeschluss-Datum oder -zeit. Format: TT.MM.JJJJ und HH:MM');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($startTimestamp <= time()) {
            $context->getInfo()->addInformation('Das Startdatum muss in der Zukunft liegen');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp <= time()) {
            $context->getInfo()->addInformation('Der Anmeldeschluss muss in der Zukunft liegen');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($applicationEndTimestamp >= $startTimestamp) {
            $context->getInfo()->addInformation('Der Anmeldeschluss muss vor dem Start liegen');
            $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
            return;
        }

        if ($plotId > 0) {
            $plot = $this->rpgPlotRepository->find($plotId);
            if ($plot === null) {
                $context->getInfo()->addInformation('Der angegebene Plot existiert nicht');
                $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
                return;
            }
            if ($plot->getUserId() !== $user->getId()) {
                $context->getInfo()->addInformation('Du bist nicht der Ersteller dieses Plots');
                $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
                return;
            }
        }

        $selectedAward = null;
        if ($awardId > 0) {
            $selectedAward = $this->awardRepository->find($awardId);
            if ($selectedAward === null) {
                $context->getInfo()->addInformation('Der angegebene Award existiert nicht');
                $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
                return;
            }

            if ($selectedAward->getIsNpc() !== true) {
                $context->getInfo()->addInformation('Es können nur NPC-Awards als Quest-Belohnung verwendet werden');
                $this->setFormData($context, $title, $text, $startDate, $startTime, $applicationEndDate, $applicationEndTime, $prestige, $awardId, $applicantMax, $plotId, $approvalRequired, $factionIds, $secretFactionIds, $commodities, $spacecrafts);
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
        $quest->setTitle($title);
        $quest->setText($text);
        $quest->setTime(time());
        $quest->setStart($startTimestamp);
        $quest->setApplicationEnd($applicationEndTimestamp);

        if ($prestige > 0) {
            $quest->setPrestige($prestige);
        }

        if ($selectedAward !== null) {
            $quest->setAwardId($selectedAward->getId());
            $quest->setAward($selectedAward);
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

        if ($validatedFactions !== []) {
            $quest->setFactions($validatedFactions);
        }

        if ($validatedCommodities !== []) {
            $quest->setCommodityReward($validatedCommodities);
        }

        if ($validatedSpacecrafts !== []) {
            $quest->setSpacecrafts($validatedSpacecrafts);
        }

        if ($validatedSecretFactions !== []) {
            $quest->setSecret($validatedSecretFactions);
        }

        $this->npcQuestRepository->save($quest);

        $context->getInfo()->addInformation('Die Quest wurde erfolgreich erstellt');
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
        $playableFactionIds = array_map(fn (Faction $faction): int => $faction->getId(), $playableFactions);

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
        ActionControllerContext $context,
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
        $context->setTemplateVar('FORM_TITLE', $title);
        $context->setTemplateVar('FORM_TEXT', $text);
        $context->setTemplateVar('FORM_START_DATE', $startDate);
        $context->setTemplateVar('FORM_START_TIME', $startTime);
        $context->setTemplateVar('FORM_APPLICATION_END_DATE', $applicationEndDate);
        $context->setTemplateVar('FORM_APPLICATION_END_TIME', $applicationEndTime);
        $context->setTemplateVar('FORM_PRESTIGE', $prestige > 0 ? $prestige : '');
        $context->setTemplateVar('FORM_AWARD_ID', $awardId > 0 ? $awardId : '');
        $context->setTemplateVar('FORM_APPLICANT_MAX', $applicantMax > 0 ? $applicantMax : '');
        $context->setTemplateVar('FORM_PLOT_ID', $plotId > 0 ? $plotId : '');
        $context->setTemplateVar('FORM_APPROVAL_REQUIRED', $approvalRequired);
        $context->setTemplateVar('FORM_SELECTED_FACTIONS', $factionIds);
        $context->setTemplateVar('FORM_SELECTED_SECRET_FACTIONS', $secretFactionIds);
        $context->setTemplateVar('FORM_COMMODITIES', $commodities);
        $context->setTemplateVar('FORM_SPACECRAFTS', $spacecrafts);
        $context->setTemplateVar('QUEST_CREATOR_OPEN', true);
    }
}
