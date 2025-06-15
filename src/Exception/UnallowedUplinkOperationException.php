<?php

declare(strict_types=1);

namespace Stu\Exception;

use Stu\Module\Control\Router\FallbackRouteException;

final class UnallowedUplinkOperationException extends FallbackRouteException {}
