<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

use JBBCode\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\StuBbCodeDefinitionSet;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class ChangeUserNameTest extends StuTestCase
{
    private ?string $requestMethod = null;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        parent::tearDown();
    }

    public function testWhitespaceNormalizationCannotTurnAcceptedUsernameIntoAttributeSyntax(): void
    {
        $request = $this->mock(ChangeUserNameRequestInterface::class);
        $request->shouldReceive('getName')->once()->andReturn("[COLOR=red\"\tonmouseover=\"alert(1)]Testspieler[/COLOR]");
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getInfo')->once()->andReturn(new InformationWrapper());
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeDefinitionSet());

        new ChangeUserName($request, $parser, $this->mock(UserRepositoryInterface::class))->handle($context);
    }

    #[DataProvider('validNames')]
    public function testValidNamesRetainFormattingAfterNormalization(string $input, string $expected): void
    {
        $request = $this->mock(ChangeUserNameRequestInterface::class);
        $request->shouldReceive('getName')->once()->andReturn($input);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getInfo')->once()->andReturn(new InformationWrapper());
        $user = new User();
        $context->shouldReceive('getUser')->once()->andReturn($user);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('save')->with($user)->once();
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new StuBbCodeDefinitionSet());

        new ChangeUserName($request, $parser, $users)->handle($context);

        self::assertSame($expected, $user->getName());
    }

    public static function validNames(): iterable
    {
        yield ['[COLOR=#12AbEF][b]Testspieler[/b][/COLOR]', '[COLOR=#12AbEF][b]Testspieler[/b][/COLOR]'];
        yield ["Test\t spieler", 'Test spieler'];
    }
}
