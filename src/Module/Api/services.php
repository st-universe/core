<?php

declare(strict_types=1);

namespace Stu\Module\Api;

use Stu\Module\Api\V1\Common\News\GetNews;
use Stu\Module\Index\Action\Login\Login;
use function DI\autowire;

return [
    GetNews::class => autowire(GetNews::class),
    Login::class => autowire(Login::class),
];