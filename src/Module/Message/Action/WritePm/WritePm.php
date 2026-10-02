<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\WritePm;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\Lib\QuickPmCrewExperienceInterface;
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
        private UserRepositoryInterface $userRepository,
        private QuickPmCrewExperienceInterface $quickPmCrewExperience
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $text = $this->writePmRequest->getText();
        $recipientId = $this->writePmRequest->getRecipientId();
        $user = $context->getUser();
        $userId = $user->getId();

        $recipient = $this->userRepository->find($recipientId);
        if ($recipient === null) {
            $this->finish($context, false, "Dieser Siedler existiert nicht");
            return;
        }
        if ($recipient->getId() === $userId) {
            $this->finish($context, false, "Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($this->ignoreListRepository->exists($recipient->getId(), $userId)) {
            $this->finish($context, false, "Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $this->finish($context, false, "Der Text ist zu kurz");
            return;
        }

        $this->privateMessageSender->send($userId, $recipient->getId(), $text, PrivateMessageFolderTypeEnum::SPECIAL_MAIN);

        if ($this->isQuickPm()) {
            $this->quickPmCrewExperience->awardExperience(
                $user,
                $recipient->getId(),
                $this->writePmRequest->getQuickPmSourceId(),
                $this->writePmRequest->getQuickPmSourceType(),
                $this->writePmRequest->getQuickPmTargetId(),
                $this->writePmRequest->getQuickPmTargetType()
            );
        }

        $this->finish($context, true, _('Die Nachricht wurde abgeschickt'));
    }

    private function finish(ActionControllerContext $context, bool $success, string $message): void
    {
        $context->getInfo()->addInformation($message);

        if ($this->isQuickPm()) {
            $context->setTemplateVar('QUICKPM_SUCCESS', $success);
            $context->setTemplateVar('QUICKPM_MESSAGE', $message);
            $context->setView(ShowWriteQuickPmResponse::VIEW_IDENTIFIER);
            return;
        }

        if ($success) {
            $context->setView(ModuleEnum::PM);
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
