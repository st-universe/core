<?php

declare(strict_types=1);

namespace Stu\Module\Control\Exception;

use Exception;

/**
 * Is thrown when a requested item (e.g. user profile) was not found
 */
final class ItemNotFoundException extends Exception
{

}