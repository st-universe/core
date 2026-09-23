<?php

declare(strict_types=1);

namespace Stu\Module\Control\Component;

use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;

interface ControllerContext {

    public function getCurrentRound(): GameTurn;
    
    public function setTemplateVar(string $key, mixed $variable): void;

    public function getUser(): User;

}
