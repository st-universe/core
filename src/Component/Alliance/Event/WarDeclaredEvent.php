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
    private AllianceInterface $alliance;

    private AllianceInterface $counterpart;

    private UserInterface $responsibleUser;

    public function __construct(
        AllianceInterface $alliance,
        AllianceInterface $counterpart,
        UserInterface $responsibleUser
    ) {
        $this->alliance = $alliance;
        $this->counterpart = $counterpart;
        $this->responsibleUser = $responsibleUser;
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