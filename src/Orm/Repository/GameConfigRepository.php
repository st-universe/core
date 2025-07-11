<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\GameConfig;

/**
 * @extends EntityRepository<GameConfig>
 */
final class GameConfigRepository extends EntityRepository implements GameConfigRepositoryInterface
{
    #[Override]
    public function save(GameConfig $item): void
    {
        $em = $this->getEntityManager();

        $em->persist($item);
        $em->flush();
    }

    #[Override]
    public function getByOption(int $optionId): ?GameConfig
    {
        return $this->findOneBy([
            'option' => $optionId
        ]);
    }

    #[Override]
    public function updateGameState(int $state, Connection $connection): void
    {
        $connection->update(
            GameConfig::TABLE_NAME,
            [
                'value' => $state
            ],
            [
                'option' => GameEnum::CONFIG_GAMESTATE
            ],
            [
                'value' => ParameterType::INTEGER
            ]
        );
    }
}
