<?php

declare(strict_types=1);

namespace Stu\Html\PlayerSetting;

use Doctrine\ORM\EntityManagerInterface;
use Dom\HTMLDocument;
use Hackzilla\PasswordGenerator\Generator\PasswordGeneratorInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Mail\MailFactory;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\PlayerSettingsProvider;
use Stu\Module\Index\Action\ResetPassword\ResetPassword;
use Stu\Module\Index\Action\ResetPassword\ResetPasswordRequest;
use Stu\Module\Index\Action\SendPassword\SendPassword;
use Stu\Module\Index\Action\SendPassword\SendPasswordRequest;
use Stu\Module\PlayerSetting\Action\ChangeEmail\ChangeEmail;
use Stu\Module\PlayerSetting\Action\ChangeEmail\ChangeEmailRequest;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\TwigTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class ChangeEmailSecurityTest extends TwigTestCase
{
    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        self::$testSession->setUserById(101);
        $user = self::$testSession->getUser();
        $user->getRegistration()->setEmail('original@example.test');
        $this->getContainer()->get(UserRepositoryInterface::class)->save($user);
        $this->getContainer()->get(EntityManagerInterface::class)->flush();
        request::setMockVars(null);
        $_GET = [];
        $_POST = [];
    }

    #[\Override]
    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        unset($_SERVER['REQUEST_METHOD']);
        request::setMockVars(null);
        parent::tearDown();
    }

    #[DataProvider('forgedRequests')]
    public function testForgedRequestCannotRedirectRecovery(string $method, string $tokenKind): void
    {
        $token = match ($tokenKind) {
            'own' => $this->createToken(101),
            'foreign' => $this->createToken(102),
            'consumed' => $this->consumeToken(),
            'wrong' => 'ungueltiges-token',
            default => ''
        };
        $parameters = ['B_CHANGE_EMAIL' => '1', 'email' => 'attacker@example.test'];
        if ($tokenKind !== 'missing') {
            $parameters['sstr'] = $token;
        }
        $this->dispatch($method, $method === 'GET' ? $parameters : [], $method === 'GET' ? [] : $parameters);

        $this->assertStoredEmail('original@example.test');
        self::assertNull($this->getContainer()->get(UserRepositoryInterface::class)->getByEmail('attacker@example.test'));
        $this->assertRecoveryUsesAddress('original@example.test');
    }

    public static function forgedRequests(): iterable
    {
        yield 'GET without token' => ['GET', 'missing'];
        yield 'GET with valid token' => ['GET', 'own'];
        yield 'POST without token' => ['POST', 'missing'];
        yield 'POST with empty token' => ['POST', 'empty'];
        yield 'POST with wrong token' => ['POST', 'wrong'];
        yield 'POST with foreign token' => ['POST', 'foreign'];
        yield 'POST with consumed token' => ['POST', 'consumed'];
        yield 'HEAD with valid token' => ['HEAD', 'own'];
        yield 'PUT with valid token' => ['PUT', 'own'];
    }

    #[DataProvider('authorizedUsers')]
    public function testAuthorizedPostChangesEmailAndRecovery(int $userId): void
    {
        self::$testSession->setUserById($userId);
        $token = $this->createToken($userId);
        $this->dispatch('POST', [], [
            'B_CHANGE_EMAIL' => '1',
            'email' => '  replacement@example.test  ',
            'sstr' => $token
        ]);

        $this->assertStoredEmail('replacement@example.test');
        $this->assertRecoveryUsesAddress('replacement@example.test');

        $this->dispatch('POST', [], [
            'B_CHANGE_EMAIL' => '1',
            'email' => 'replayed@example.test',
            'sstr' => $token
        ]);
        $this->assertStoredEmail('replacement@example.test');
    }

    public static function authorizedUsers(): iterable
    {
        yield 'player' => [101];
        yield 'administrator' => [102];
    }

    #[DataProvider('invalidAddresses')]
    public function testAddressValidationRemainsEffective(string $address, string $information): void
    {
        $dic = $this->getContainer();
        $blocked = $dic->get(BlockedUserRepositoryInterface::class);
        $blocked->save($blocked->prototype()->setId(99999)->setEmailHash($dic->get(StuHashInterface::class)->hash('blocked@example.test')));
        $dic->get(EntityManagerInterface::class)->flush();
        $info = $this->dispatch('POST', [], [
            'B_CHANGE_EMAIL' => '1',
            'email' => $address,
            'sstr' => $this->createToken(101)
        ]);

        $this->assertStoredEmail('original@example.test');
        self::assertSame($information, $info);
    }

    public static function invalidAddresses(): iterable
    {
        yield 'invalid' => ['keine-adresse', 'Die E-Mail-Adresse ist ungültig'];
        yield 'used' => ['user103@stuniverse.de', 'Die E-Mail-Adresse wird bereits verwendet'];
        yield 'blocked' => ['blocked@example.test', 'Die E-Mail-Adresse ist blockiert'];
    }

    public function testPostDoesNotReadEmailFromQuery(): void
    {
        $this->dispatch('POST', ['B_CHANGE_EMAIL' => '1', 'email' => 'query@example.test'], ['sstr' => $this->createToken(101)]);
        $this->assertStoredEmail('original@example.test');
    }

    public function testSettingsFormSubmitsUsableToken(): void
    {
        $dic = $this->getContainer();
        $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($dic->get(GameControllerInterface::class), ModuleEnum::OPTIONS);
        $dic->get(PlayerSettingsProvider::class)->setTemplateVariables($context);
        $token = $this->createToken(101);
        $page = $dic->get(TwigPageInterface::class);
        $page->setVar('SESSIONSTRING', $token);
        $page->setTemplate('html/view/options.twig');
        $document = HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
        $form = $document->querySelector('input[name="B_CHANGE_EMAIL"]')->closest('form');
        self::assertSame('post', $form->getAttribute('method'));
        self::assertSame('options.php', $form->getAttribute('action'));
        $tokenInput = $form->querySelector('input[name="sstr"]');
        self::assertNotNull($tokenInput);
        self::assertSame('hidden', $tokenInput->getAttribute('type'));
        self::assertSame($token, $tokenInput->getAttribute('value'));
        $this->dispatch('POST', [], [
            'B_CHANGE_EMAIL' => '1',
            'email' => 'form@example.test',
            'sstr' => $tokenInput->getAttribute('value')
        ]);
        $this->assertStoredEmail('form@example.test');
    }

    private function createToken(int $userId): string
    {
        $dic = $this->getContainer();
        return new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())
            ->createSessionString($dic->get(UserRepositoryInterface::class)->find($userId));
    }

    private function consumeToken(): string
    {
        $token = $this->createToken(101);
        self::assertTrue($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($token, 101));
        return $token;
    }

    private function dispatch(string $method, array $query, array $body): string
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_GET = $query;
        $_POST = $body;
        $dic = $this->getContainer();
        $action = new ChangeEmail(
            new ChangeEmailRequest(),
            $dic->get(UserRepositoryInterface::class),
            $dic->get(BlockedUserRepositoryInterface::class),
            $dic->get(StuHashInterface::class)
        );
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->once()->with(ModuleEnum::OPTIONS, false)->andReturn([ChangeEmail::ACTION_IDENTIFIER => $action]);
        $gameData = new GameData();
        $gameRequest = new GameRequest();
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('getGameData')->andReturn($gameData);
        $game->shouldReceive('getInfo')->andReturn($gameData->gameInformations);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        new CallbackExecution(
            $discovery,
            $dic->get(AccessCheckInterface::class),
            $dic->get(ActionContextFactoryInterface::class),
            new StuTime(),
            $dic->get(EntityManagerInterface::class)
        )->execute(ModuleEnum::OPTIONS, $game);
        self::assertSame(ChangeEmail::ACTION_IDENTIFIER, $gameRequest->getAction());
        return $gameData->gameInformations->getInformationsAsString();
    }

    private function assertStoredEmail(string $email): void
    {
        $registration = self::$testSession->getUser()->getRegistration();
        $this->getContainer()->get(EntityManagerInterface::class)->refresh($registration);
        self::assertSame($email, $registration->getEmail());
    }

    private function assertRecoveryUsesAddress(string $email): void
    {
        $dic = $this->getContainer();
        $messages = [];
        $mailer = $this->mock(MailerInterface::class);
        $mailer->shouldReceive('send')->twice()->andReturnUsing(static function (Email $message) use (&$messages): void {
            $messages[] = $message;
        });
        $mailFactory = new MailFactory($mailer, $dic->get(StuConfigInterface::class));
        $context = $dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [];
        $_POST = ['B_SEND_PASSWORD' => '1', 'emailaddress' => $email];
        new SendPassword(
            new SendPasswordRequest(),
            $dic->get(UserRepositoryInterface::class),
            $mailFactory,
            $dic->get(ConfigInterface::class)
        )->handle($context);
        $dic->get(EntityManagerInterface::class)->flush();
        self::assertCount(1, $messages);
        self::assertSame($email, $messages[0]->getTo()[0]->getAddress());
        self::assertSame(1, preg_match('/SHOW_RESET_PASSWORD=1&TOKEN=([^\s]+)/', $messages[0]->getTextBody(), $matches));
        $_POST = ['B_RESET_PASSWORD' => '1', 'TOKEN' => $matches[1]];
        new ResetPassword(
            new ResetPasswordRequest(),
            $mailFactory,
            $dic->get(ConfigInterface::class),
            $dic->get(UserRepositoryInterface::class),
            $dic->get(PasswordGeneratorInterface::class)
        )->handle($context);
        $dic->get(EntityManagerInterface::class)->flush();
        self::assertCount(2, $messages);
        self::assertSame($email, $messages[1]->getTo()[0]->getAddress());
        self::assertSame(1, preg_match('/einloggen: (\S+)/', $messages[1]->getTextBody(), $password));
        $registration = self::$testSession->getUser()->getRegistration();
        $dic->get(EntityManagerInterface::class)->refresh($registration);
        self::assertTrue(password_verify($password[1], $registration->getPassword()));
        self::assertSame('', $registration->getPasswordToken());
    }
}
