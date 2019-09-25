<?php

namespace Stu\Module\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class MapCycle implements MaintenanceHandlerInterface
{
    private $mapRepository;

    private $userRepository;

    private $userMapRepository;

    private $entityManager;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        UserRepositoryInterface $userRepository,
        UserMapRepositoryInterface $userMapRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->mapRepository = $mapRepository;
        $this->userRepository = $userRepository;
        $this->userMapRepository = $userMapRepository;
        $this->entityManager = $entityManager;
    }

    public function handle(): void
    {
        $fieldcount = $this->mapRepository->count([]);
        $list = $this->userRepository->getByMappingType(MAPTYPE_INSERT);
        foreach ($list as $user) {
            if ($this->userMapRepository->getAmountByUser($user->getId()) >= $fieldcount) {
                $this->cycle($user);
            }
        }
    }

    private function cycle(UserInterface $user)
    {
        $user->setMapType(MAPTYPE_DELETE);

        $this->userRepository->save($user);

        $userId = $user->getId();

        $connection = $this->entityManager->getConnection();

        $result = $connection->query(
            sprintf(
                'SELECT cx,cy,id FROM stu_map WHERE id NOT IN (SELECT map_id FROM stu_user_map WHERE user_id = %d)',
                $user->getId()
            )
        );

        $this->userMapRepository->truncateByUser($userId);

        while ($data = $connection->fetchAssoc($result)) {
            $connection->query(
                sprintf(
                    "INSERT INTO stu_user_map (cx,cy,user_id,map_id) VALUES (%d,%d,%d,%d)",
                    $data['cx'],
                    $data['cy'],
                    $user->getId(),
                    $data['id']
                )
            );
        }

    }
}
