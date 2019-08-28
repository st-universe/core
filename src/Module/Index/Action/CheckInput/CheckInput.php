<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\CheckInput;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use User;

final class CheckInput implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CHECK_REGVAR';

    private $checkInputRequest;

    public function __construct(
        CheckInputRequestInterface $checkInputRequest
    ) {
        $this->checkInputRequest = $checkInputRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $var = $this->checkInputRequest->getVariable();
        $value = $this->checkInputRequest->getValue();
        $state = REGISTER_STATE_NOK;
        switch ($var) {
            default:
            case 'loginname':
                if (!preg_match('=^[a-zA-Z0-9]+$=i', $value)) {
                    break;
                }
                if (strlen($value) < 6) {
                    break;
                }
                if (User::getByLogin($value)) {
                    $state = REGISTER_STATE_DUP;
                    break;
                }
                $state = REGISTER_STATE_OK;
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    break;
                }
                if (User::getByEmail($value)) {
                    $state = REGISTER_STATE_DUP;
                    break;
                }
                $state = REGISTER_STATE_OK;
                break;
        }
        echo $state;
        exit;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
