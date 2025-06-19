<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\AllianceSettingsInterface;
use Stu\Orm\Entity\AllianceInterface;

/**
 * @extends EntityRepository<AllianceSettings>
 */
final class AllianceSettingsRepository extends EntityRepository implements AllianceSettingsRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceSettingsInterface
    {
        return new AllianceSettings();
    }

    #[Override]
    public function save(AllianceSettingsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(AllianceSettingsInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function findByAllianceAndSetting(AllianceInterface $alliance, string $setting): ?AllianceSettingsInterface
    {
        return $this->findOneBy([
            'alliance' => $alliance,
            'setting' => $setting
        ]);
    }
}
