<?php

namespace Stu\Component\Spacecraft\Trait;

trait SpacecraftHrefTrait
{
    use SpacecraftTrait;

    public function getHref(): string
    {
        $self = $this->getThis();
        $moduleView = $self->getType()->getModuleView();
        if ($moduleView === null) {
            return '';
        }

        return sprintf(
            '%s?%s=1&id=%d',
            $moduleView->getPhpPage(),
            $self->getType()->getViewIdentifier(),
            $self->getId()
        );
    }
}
