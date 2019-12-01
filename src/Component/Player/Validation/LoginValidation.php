<?php

declare(strict_types=1);

namespace Stu\Component\Player\Validation;

use Stu\Orm\Entity\UserInterface;

final class LoginValidation implements LoginValidationInterface
{
    private array $validators;

    /**
     * @param PlayerValidationInterface[] $validators
     */
    public function __construct(
        array $validators
    ) {
        $this->validators = $validators;
    }

    public function validate(UserInterface $user): bool
    {
        foreach ($this->validators as $validator) {
            if ($validator->validate($user) === false) {
                return false;
            }
        }
        return true;
    }
}
