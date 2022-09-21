<?php

namespace Stu\Module\Control;

use Stu\Orm\Entity\GameRequestInterface;

interface EntityManagerLoggingInterface
{
    public function beginTransaction();
    public function persist(GameRequestInterface $request);
    public function flush();
    public function commit();
}
