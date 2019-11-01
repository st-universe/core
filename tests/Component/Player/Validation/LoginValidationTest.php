<?php

declare(strict_types=1);

namespace Stu\Component\Player\Validation;

use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class LoginValidationTest extends StuTestCase
{

    /**
     * @var MockInterface|null|PlayerValidationInterface
     */
    private $playerValidator;

    /**
     * @var LoginValidation|null
     */
    private $loginValidator;

    public function setUp(): void
    {
        $this->playerValidator = $this->mock(PlayerValidationInterface::class);

        $this->loginValidator = new LoginValidation([$this->playerValidator]);
    }

    public function testValidateReturnsFalseIfValidatorFails(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->playerValidator->shouldReceive('validate')
            ->with($user)
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->loginValidator->validate($user)
        );
    }

    public function testValidateReturnsTrueOnSuccessfulValidation(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->playerValidator->shouldReceive('validate')
            ->with($user)
            ->once()
            ->andReturnTrue();

        $this->assertTrue(
            $this->loginValidator->validate($user)
        );
    }
}
