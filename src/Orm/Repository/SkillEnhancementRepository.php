<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\SkillEnhancement;

/**
 * @extends EntityRepository<SkillEnhancement>
 */
final class SkillEnhancementRepository extends EntityRepository implements SkillEnhancementRepositoryInterface {}
