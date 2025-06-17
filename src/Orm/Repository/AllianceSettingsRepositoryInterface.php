<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\AllianceSettingsInterface;
use Stu\Orm\Entity\AllianceInterface;

/**
 * @extends ObjectRepository<AllianceSettings>
 *
 * @method null|AllianceSettingsInterface find(integer $id)
 * @method AllianceSettingsInterface[] findAll()
 */
interface AllianceSettingsRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceSettingsInterface;

    public function save(AllianceSettingsInterface $post): void;

    public function delete(AllianceSettingsInterface $post): void;

    public function findByAllianceAndSetting(AllianceInterface $alliance, string $setting): ?AllianceSettingsInterface;
}
