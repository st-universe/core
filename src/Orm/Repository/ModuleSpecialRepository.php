<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ModuleSpecial;

/**
 * @extends EntityRepository<ModuleSpecial>
 */
final class ModuleSpecialRepository extends EntityRepository implements ModuleSpecialRepositoryInterface
{
    #[\Override]
    public function getAllForRumpCreator(): array
    {
        return array_map(
            fn (array $special): array => [
                'id' => (int) $special['id'],
                'module_id' => (int) $special['module_id'],
                'special_id' => (int) $special['special_id']
            ],
            $this->getEntityManager()->getConnection()->fetchAllAssociative(
                'SELECT id, module_id, special_id FROM stu_modules_specials ORDER BY id'
            )
        );
    }

    #[\Override]
    public function getByModule(int $moduleId): array
    {
        return $this->findBy([
            'module_id' => $moduleId
        ]);
    }
}
