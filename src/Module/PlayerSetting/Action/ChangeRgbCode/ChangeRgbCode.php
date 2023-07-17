<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeRgbCode;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeRgbCode implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_USER_RGB';

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = request::postStringFatal('rgb');
        if (strlen($value) != 7) {
            $game->addInformation(_('Der RGB-Code muss sieben Zeichen lang sein, z.B. #11ff67'));
            return;
        }

        if (!$this->validHex($value)) {
            $game->addInformation(_('Der RGB-Code ist ungültig!'));
            return;
        }

        $user = $game->getUser();

        $user->setRgbCode($value);

        $this->userRepository->save($user);

        $game->addInformation(_('Dein RGB-Code wurde geändert'));
    }

    private function validHex(string $hex): int|bool
    {
        return preg_match('/^#?(([a-f0-9]{3}){1,2})$/i', $hex);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
