<?php

declare(strict_types=1);

namespace Stu;

use Stu\Component\Faction\FactionEnum;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Orm\Repository\FactionRepositoryInterface;

class TestUser extends AbstractTestData
{
    public function insertTestData(): Object
    {
        $factionRepository = $this->dic->get(FactionRepositoryInterface::class);
        $playerCreator = $this->dic->get(LocalPlayerCreator::class);

        $user = $playerCreator->createPlayer(
            'testuser',
            'test@stu.de',
            $factionRepository->find(FactionEnum::FACTION_FEDERATION->value),
            'password'
        );

        return $user;
    }
}
