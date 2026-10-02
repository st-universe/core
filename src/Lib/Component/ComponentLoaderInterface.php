<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

interface ComponentLoaderInterface
{
    /**
     * Adds the execute javascript after render.
     */
    public function loadComponentUpdates(): void;

    public function loadRegisteredComponents(User $user, TemplateInterface $template): void;

    public function registerStubbedComponent(ComponentEnumInterface $componentEnum): ComponentLoaderInterface;

    public function resetStubbedComponents(): void;
}
