<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeCrewRankNames;

use request;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ChangeCrewRankNames implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_CREW_RANK_NAMES';

    public function __construct(private UserCrewRankRepositoryInterface $userCrewRankRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $rankNames = request::postArray('crew_rank_names');

        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $name = (string) ($rankNames[$rank->value] ?? '');
            if ($name !== '' && !$this->isValidName($name)) {
                $game->getInfo()->addInformation(_('Rangnamen dürfen nur Buchstaben, einzelne Leerzeichen, Apostrophe und Akzentzeichen enthalten'));
                return;
            }
        }

        $user = $game->getUser();
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $name = (string) ($rankNames[$rank->value] ?? '');
            $rankEntry = $this->userCrewRankRepository->getByUserAndRank($user, $rank);
            if ($name === '') {
                if ($rankEntry !== null) {
                    $this->userCrewRankRepository->delete($rankEntry);
                }
                continue;
            }

            if ($rankEntry === null) {
                $rankEntry = $this->userCrewRankRepository->prototype()
                    ->setUser($user)
                    ->setRank($rank);
            }

            $rankEntry->setName($name);
            $this->userCrewRankRepository->save($rankEntry);
        }

        $game->getInfo()->addInformation(_('Die Crew-Ränge wurden aktualisiert'));
    }

    private function isValidName(string $name): bool
    {
        return mb_strlen($name) <= 64
            && preg_match("/^(?!.*['\\x60]{2})(?!.* (?!\\p{L}))\\p{L}(?:[\\p{L}'\\x60 ]*\\p{L})?$/u", $name) === 1;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
