<?php

namespace Stu\Orm\Entity;

interface AnomalyTypeInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getLifespanInTicks(): int;

    public function getTemplate(): string;
}
