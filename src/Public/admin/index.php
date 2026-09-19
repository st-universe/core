<?php

declare(strict_types=1);

use Stu\Component\Game\ModuleEnum;
use Stu\Config\GameRunner;

require_once __DIR__ . '/../../../vendor/autoload.php';

GameRunner::runModule(ModuleEnum::ADMIN);
