<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Override;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;

final class NagusComponent implements ComponentInterface
{
    public function __construct() {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $game->setTemplateVar('SHOW_DEALS', $game->getUser()->getDeals());
    }
}
