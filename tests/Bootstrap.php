<?php

declare(strict_types=1);

namespace Stu;

use Mockery;

require_once __DIR__ . '/../vendor/autoload.php';

Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
