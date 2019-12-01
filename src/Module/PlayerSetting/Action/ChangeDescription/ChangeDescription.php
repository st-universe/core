<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeDescription;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeDescription implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_DESCRIPTION';

    private ChangeDescriptionRequestInterface $changeDescriptionRequest;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ChangeDescriptionRequestInterface $changeDescriptionRequest,
        UserRepositoryInterface $userRepository
    ) {
        $this->changeDescriptionRequest = $changeDescriptionRequest;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = $this->changeDescriptionRequest->getDescription();

        $user = $game->getUser();

        $user->setDescription($value);

        $this->userRepository->save($user);

        $game->addInformation(_('Deine Beschreibung wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
