<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SkillEnhancement;

/**
 * @extends ObjectRepository<SkillEnhancement>
 *
 * @method SkillEnhancement[] findAll()
 */
interface SkillEnhancementRepositoryInterface extends ObjectRepository {}
