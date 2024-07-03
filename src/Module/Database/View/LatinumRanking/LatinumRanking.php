<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\LatinumRanking;

use Override;
use Generator;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Returns the top10 user with the most latinum
 */
final class LatinumRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOP_LATINUM';

    public function __construct(private StorageRepositoryInterface $storageRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Die 10 Söhne des Nagus'
            ]
        ]);
        $game->setPageTitle('/ Datenbank / Die 10 Söhne des Nagus');
        $game->showMacro('html/database.xhtml/top_lat_user');

        $game->setTemplateVar('NAGUS_LIST', $this->getTop10());
    }

    /**
     * @return Generator<array{user: null|UserInterface, amount: int}>
     */
    private function getTop10(): Generator
    {
        foreach ($this->storageRepository->getLatinumTop10() as $item) {
            yield [
                'user' => $this->userRepository->find($item['user_id']),
                'amount' => $item['amount'],
            ];
        }
    }
}
