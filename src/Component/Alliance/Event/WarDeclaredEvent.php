<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Describes the war declaration event
 */
class WarDeclaredEvent
{
    public function __construct(private AllianceInterface $alliance, private AllianceInterface $counterpart, private UserInterface $responsibleUser)
    {
    }

    /**
     * Returns the alliance which declared war
     */
    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    /**
     * Returns the alliance war was declared on
     */
    public function getCounterpart(): AllianceInterface
    {
        return $this->counterpart;
    }

    /**
     * Returns the user which actually declared war
     */
    public function getResponsibleUser(): UserInterface
    {
        return $this->responsibleUser;
    }
}
