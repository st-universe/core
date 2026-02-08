<?php

declare(strict_types=1);

namespace Stu\Module\Control\Exception;

use Stu\Module\Control\Router\FallbackRouteException;

/**
 * Is thrown when a requested item (e.g. user profile) was not found
 */
final class ItemNotFoundException extends FallbackRouteException {}
