<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\View\ShowWriteQuickPmResponse\ShowWriteQuickPmResponse;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class WritePm implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_WRITE_PM';

    public function __construct(
        private WritePmRequestInterface $writePmRequest,
        private IgnoreListRepositoryInterface $ignoreListRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $text = $this->writePmRequest->getText();
        $recipientId = $this->writePmRequest->getRecipientId();
        $userId = $game->getUser()->getId();

        $recipient = $this->userRepository->find($recipientId);
        if ($recipient === null) {
            $this->finish($game, false, "Dieser Siedler existiert nicht");
            return;
        }
        if ($recipient->getId() === $userId) {
            $this->finish($game, false, "Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($this->ignoreListRepository->exists($recipient->getId(), $userId)) {
            $this->finish($game, false, "Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $this->finish($game, false, "Der Text ist zu kurz");
            return;
        }

        $this->privateMessageSender->send($userId, $recipient->getId(), $text, PrivateMessageFolderTypeEnum::SPECIAL_MAIN);

        $this->finish($game, true, _('Die Nachricht wurde abgeschickt'));
    }

    private function finish(GameControllerInterface $game, bool $success, string $message): void
    {
        $game->getInfo()->addInformation($message);

        if ($this->isQuickPm()) {
            $game->setTemplateVar('QUICKPM_SUCCESS', $success);
            $game->setTemplateVar('QUICKPM_MESSAGE', $message);
            $game->setView(ShowWriteQuickPmResponse::VIEW_IDENTIFIER);
            return;
        }

        if ($success) {
            $game->setView(ModuleEnum::PM);
        }
    }

    private function isQuickPm(): bool
    {
        return request::has('quickPm');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
