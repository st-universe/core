<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

use JBBCode\Parser;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeUserName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private ChangeUserNameRequestInterface $changeUserNameRequest;

    private Parser $bbcodeParser;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ChangeUserNameRequestInterface $changeUserNameRequest,
        Parser $bbcodeParser,
        UserRepositoryInterface $userRepository
    ) {
        $this->changeUserNameRequest = $changeUserNameRequest;
        $this->bbcodeParser = $bbcodeParser;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $text = $this->changeUserNameRequest->getName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        if (strrpos(strtoupper($text), 'UMODE') || strrpos(strtoupper($text), 'U-MODE') || strrpos(strtoupper($text), 'URLAUB')) {
            $game->addInformation(_('Das Suffix UMODE wird automatisch an den Namen angehängt!'));
            return;
        }

        $value = CleanTextUtils::clearEmojis($text);
        $valueWithoutMarkup = $this->bbcodeParser->parse($value)->getAsText();

        if (mb_strlen($valueWithoutMarkup) < 6) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername muss aus mindestens 6 Zeichen bestehen')
                )
            );
            return;
        }
        if (mb_strlen($value) > 255) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername darf inklusive BBCode nur maximal 255 Zeichen lang sein')
                )
            );
            return;
        }
        if (mb_strlen($valueWithoutMarkup) > 60) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername darf nur maximal 60 Zeichen lang sein')
                )
            );
            return;
        }

        $user = $game->getUser();
        $user->setUsername($value);

        $this->userRepository->save($user);

        $game->addInformation(_('Dein Name wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
