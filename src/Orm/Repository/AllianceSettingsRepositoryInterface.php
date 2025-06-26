<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\Alliance;

/**
 * @extends ObjectRepository<AllianceSettings>
 *
 * @method null|AllianceSettings find(integer $id)
 * @method AllianceSettings[] findAll()
 */
interface AllianceSettingsRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceSettings;

    public function save(AllianceSettings $post): void;

    public function delete(AllianceSettings $post): void;

    public function findByAllianceAndSetting(Alliance $alliance, string $setting): ?AllianceSettings;
}
