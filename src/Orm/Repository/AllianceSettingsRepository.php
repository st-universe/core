<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Alliance\AllianceSettingsEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceSettings;

/**
 * @extends EntityRepository<AllianceSettings>
 */
final class AllianceSettingsRepository extends EntityRepository implements AllianceSettingsRepositoryInterface
{
    #[\Override]
    public function prototype(): AllianceSettings
    {
        return new AllianceSettings();
    }

    #[\Override]
    public function save(AllianceSettings $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(AllianceSettings $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[\Override]
    public function findByAllianceAndSetting(Alliance $alliance, AllianceSettingsEnum $setting): ?AllianceSettings
    {
        return $this->findOneBy([
            'alliance' => $alliance,
            'setting' => $setting->value
        ]);
    }
}
