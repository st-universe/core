<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Event;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

/**
 * Describes the war declaration event
 */
class WarDeclaredEvent
{
    public function __construct(private Alliance $alliance, private Alliance $counterpart, private User $responsibleUser) {}

    /**
     * Returns the alliance which declared war
     */
    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    /**
     * Returns the alliance war was declared on
     */
    public function getCounterpart(): Alliance
    {
        return $this->counterpart;
    }

    /**
     * Returns the user which actually declared war
     */
    public function getResponsibleUser(): User
    {
        return $this->responsibleUser;
    }
}
