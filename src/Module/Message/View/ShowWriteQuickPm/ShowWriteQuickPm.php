<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowWriteQuickPm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WRITE_QUICKPM';

    private ShowWriteQuickPmRequestInterface $showWriteQuickPmRequest;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ShowWriteQuickPmRequestInterface $showWriteQuickPmRequest,
        UserRepositoryInterface $userRepository
    ) {
        $this->showWriteQuickPmRequest = $showWriteQuickPmRequest;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/write_quick_pm');
        $game->setPageTitle(_('Neue private Nachricht'));

        $game->setTemplateVar(
            'RECIPIENT',
            $this->userRepository->find($this->showWriteQuickPmRequest->getRecipientId())
        );
    }
}
