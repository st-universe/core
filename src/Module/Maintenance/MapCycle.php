<?php

namespace Stu\Module\Maintenance;

use Stu\Lib\DbInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class MapCycle implements MaintenanceHandlerInterface
{
    private $db;

    private $mapRepository;

    private $userRepository;

    public function __construct(
        DbInterface $db,
        MapRepositoryInterface $mapRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->db = $db;
        $this->mapRepository = $mapRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(): void
    {
        $fieldcount = $this->mapRepository->count([]);
        $list = $this->userRepository->getByMappingType(MAPTYPE_INSERT);
        foreach ($list as $user) {
            if ($this->db->query("SELECT COUNT(*) FROM stu_user_map WHERE user_id=" . $user->getId()) >= $fieldcount) {
                $this->cycle($user);
            }
        }
    }

    private function cycle(UserInterface $user)
    {
        $user->setMapType(MAPTYPE_DELETE);
        $this->userRepository->save($user);

        $fields = $this->db->query("SELECT cx,cy,id FROM stu_map WHERE id NOT IN (SELECT map_id FROM stu_user_map WHERE user_id=" . $user->getId() . ")");
        $this->db->query("DELETE FROM stu_user_map WHERE user_id=" . $user->getId());
        while ($data = mysqli_fetch_assoc($fields)) {
            $this->db->query("INSERT INTO stu_user_map (cx,cy,user_id,map_id) VALUES ('" . $data['cx'] . "','" . $data['cy'] . "','" . $user->getId() . "','" . $data['id'] . "')");
        }

    }
}
