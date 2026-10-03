<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeEmail implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_EMAIL';

    public function __construct(private ChangeEmailRequestInterface $changeEmailRequest, private UserRepositoryInterface $userRepository, private BlockedUserRepositoryInterface $blockedUserRepository, private StuHashInterface $stuHash) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $value = trim($this->changeEmailRequest->getEmailAddress());
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $context->getInfo()->addInformation(_('Die E-Mail-Adresse ist ungültig'));
            return;
        }

        if ($this->userRepository->getByEmail($value) !== null) {
            $context->getInfo()->addInformation(_('Die E-Mail-Adresse wird bereits verwendet'));
            return;
        }
        if ($this->blockedUserRepository->getByEmailHash($this->stuHash->hash($value)) !== null) {
            $context->getInfo()->addInformation(_('Die E-Mail-Adresse ist blockiert'));
            return;
        }

        $user = $context->getUser();

        $user->getRegistration()->setEmail($value);

        $this->userRepository->save($user);

        $context->getInfo()->addInformation(_('Deine E-Mail-Adresse wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
