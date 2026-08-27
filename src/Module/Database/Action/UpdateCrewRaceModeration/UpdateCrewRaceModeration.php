<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action\UpdateCrewRaceModeration;

use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\View\ShowCrewRaceModeration\ShowCrewRaceModeration;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class UpdateCrewRaceModeration implements ActionControllerInterface, AccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPDATE_CREW_RACE_MODERATION';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly ConfigInterface $config
    ) {}

    #[\Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::CREW_RACE_MODERATION;
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceModeration::VIEW_IDENTIFIER);

        $crewRace = $this->crewRaceRepository->find(request::postInt('crew_race_id'));
        if ($crewRace === null || !$crewRace->isCustom()) {
            $game->getInfo()->addInformation(_('Die Crew-Rasse kann nicht geändert werden'));
            return;
        }

        $isAdmin = $game->isAdmin();
        if (!$isAdmin && (!$this->isPending($crewRace))) {
            $game->getInfo()->addInformation(_('Diese Crew-Rasse kann nur vor der Entscheidung geändert werden'));
            return;
        }

        $description = trim((string)request::postString('crew_race_name'));
        if (!CrewRaceInput::isValidDescription($description)) {
            $game->getInfo()->addInformation(_('Der Name muss mit einem Großbuchstaben beginnen und darf nur Buchstaben, einzelne Leerzeichen sowie einzelne Apostrophe oder Backticks enthalten'));
            return;
        }

        $define = CrewRaceInput::normalizeDefine((string)request::postString('crew_race_define'));
        if (!CrewRaceInput::isValidDefine($define)) {
            $game->getInfo()->addInformation(_('Die Grafikdefinition darf nur Großbuchstaben und einzelne Unterstriche enthalten'));
            return;
        }

        $existing = $this->crewRaceRepository->getByGfxPath($define);
        if ($existing !== null && $existing->getId() !== $crewRace->getId()) {
            $game->getInfo()->addInformation(_('Eine Crew-Rasse mit dieser Grafikdefinition existiert bereits'));
            return;
        }

        if ($isAdmin && !$this->updateAdminValues($crewRace, $game)) {
            return;
        }

        if ($define !== $crewRace->getGfxPath() && !$this->renameGraphicsDirectory($crewRace->getGfxPath(), $define, $game)) {
            return;
        }

        $crewRace
            ->setDescription($description)
            ->setGfxPath($define);

        $this->crewRaceRepository->save($crewRace);
        $game->getInfo()->addInformation(_('Die Crew-Rasse wurde aktualisiert'));
    }

    private function isPending(CrewRace $crewRace): bool
    {
        return !$crewRace->isAccepted() && $crewRace->getAcceptedUserId() === null;
    }

    private function updateAdminValues(CrewRace $crewRace, GameControllerInterface $game): bool
    {
        $maleRatio = filter_var(request::postString('crew_race_male_ratio'), FILTER_VALIDATE_INT);
        if ($maleRatio === false || $maleRatio < 0 || $maleRatio > 100) {
            $game->getInfo()->addInformation(_('Das Männerverhältnis muss eine Zahl zwischen 0 und 100 sein'));
            return false;
        }

        $chance = filter_var(request::postString('crew_race_chance'), FILTER_VALIDATE_INT);
        if ($chance === false || $chance < 1 || $chance > 100) {
            $game->getInfo()->addInformation(_('Die Zufallsrate muss eine Zahl zwischen 1 und 100 sein'));
            return false;
        }

        $factionIds = [];
        $playableFactionIds = array_map(
            static fn (Faction $faction): int => $faction->getId(),
            $this->factionRepository->getByChooseable(true)
        );
        foreach (request::postArray('crew_race_factions') as $factionId) {
            $factionId = (int)$factionId;
            if (in_array($factionId, $playableFactionIds, true)) {
                $factionIds[] = $factionId;
            }
        }
        if ($factionIds === []) {
            $game->getInfo()->addInformation(_('Es muss mindestens eine spielbare Fraktion gewählt werden'));
            return false;
        }

        $crewRace
            ->setMaleRatio((int)$maleRatio)
            ->setChance((int)$chance)
            ->setShared(request::postString('crew_race_shared') === '1')
            ->setCivil(request::postString('crew_race_civil') === '1')
            ->setFactionIds($factionIds);

        return true;
    }

    private function renameGraphicsDirectory(string $currentDefine, string $newDefine, GameControllerInterface $game): bool
    {
        $baseDirectory = rtrim((string)$this->config->get('game.webroot'), '/\\')
            . DIRECTORY_SEPARATOR
            . trim((string)$this->config->get('game.user_avatar_path'), '/\\')
            . DIRECTORY_SEPARATOR
            . 'crew';
        $currentDirectory = $baseDirectory . DIRECTORY_SEPARATOR . $currentDefine;
        $newDirectory = $baseDirectory . DIRECTORY_SEPARATOR . $newDefine;

        if (!is_dir($currentDirectory)) {
            $game->getInfo()->addInformation(_('Das bisherige Grafikverzeichnis wurde nicht gefunden'));
            return false;
        }
        if (file_exists($newDirectory)) {
            $game->getInfo()->addInformation(_('Das Zielverzeichnis für die Grafikdefinition existiert bereits'));
            return false;
        }
        if (!rename($currentDirectory, $newDirectory)) {
            $game->getInfo()->addInformation(_('Das Grafikverzeichnis konnte nicht umbenannt werden'));
            return false;
        }

        return true;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
