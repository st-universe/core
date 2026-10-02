<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeDescription;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeDescription implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_DESCRIPTION';

    public function __construct(private ChangeDescriptionRequestInterface $changeDescriptionRequest, private UserRepositoryInterface $userRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $value = $this->changeDescriptionRequest->getDescription();

        $user = $context->getUser();

        $user->setDescription($value);

        $this->userRepository->save($user);

        $context->getInfo()->addInformation(_('Deine Beschreibung wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
