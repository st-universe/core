<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Entity\SkillEnhancementInterface;

/**
 * @extends ObjectRepository<SkillEnhancement>
 *
 * @method array<SkillEnhancementInterface> findAll()
 */
interface SkillEnhancementRepositoryInterface extends ObjectRepository {}
