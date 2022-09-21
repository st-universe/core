<?php

namespace Stu\Module\Control;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerCreatorInterface
{
    public function create(): EntityManagerInterface;
}
