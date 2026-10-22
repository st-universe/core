<?php

declare(strict_types=1);

namespace Stu\Html\Message;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Communication\Action\AdminDeleteKnPost\AdminDeleteKnPost;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\Message\MessageProvider;
use Stu\Module\Game\Lib\View\Provider\Message\MessengerStyleProvider;
use Stu\Module\Message\Action\WritePm\WritePm;
use Stu\Module\Message\View\ShowWritePm\ShowWritePm;
use Stu\Module\Spacecraft\Action\SendBroadcast\SendBroadcast;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\TwigTestCase;
use Twig\Environment;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PrivateMessageHtmlSafetyTest extends TwigTestCase
{
    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        self::$testSession->setUserById(101);
    }

    #[DataProvider('messageSources')]
    public function testStoredMessageRendersSafely(string $source, bool $formatted): void
    {
        $text = $formatted
            ? "[b]Hallo[/b] [i]Welt[/i] [u]Text[/u] [color=#123456]Farbe[/color]\nA & B \"Zitate\""
            : '<img src="x" onerror="alert(1)">';
        $message = $this->createMessage($source, $text);
        self::assertStringContainsString($text, $message->getText());

        foreach ($source === 'moderation' ? ['inbox'] : ['inbox', 'conversation', 'preview'] as $view) {
            $html = $this->renderMessage($message, $view);
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $content = $document->getElementById('message' . $message->getId());
            self::assertNotNull($content, $source . ': ' . $view);
            self::assertSame(0, $content->getElementsByTagName('img')->length);
            self::assertSame(0, $content->getElementsByTagName('script')->length);
            if ($formatted) {
                $messageHtml = $document->saveHtml($content);
                self::assertStringContainsString('<strong>Hallo</strong>', $messageHtml);
                self::assertStringContainsString('<em>Welt</em>', $messageHtml);
                self::assertStringContainsString('<u>Text</u>', $messageHtml);
                self::assertStringContainsString('<span style="color: #123456">Farbe</span>', $messageHtml);
                self::assertGreaterThan(0, $content->getElementsByTagName('br')->length);
                self::assertStringContainsString('A & B "Zitate"', $content->textContent);
            } else {
                self::assertStringContainsString($text, $content->textContent);
            }
        }
    }

    public static function messageSources(): iterable
    {
        foreach (['broadcast-colony', 'broadcast-station', 'moderation', 'stored'] as $source) {
            yield $source . '-html' => [$source, false];
            yield $source . '-bbcode' => [$source, true];
        }
        yield 'normal-private-message' => ['private', true];
    }

    #[DataProvider('filterInputs')]
    public function testSharedFilterEscapesRawHtml(string $input, string $expected): void
    {
        self::assertSame(
            $expected,
            $this->getContainer()->get(Environment::class)->createTemplate('{{ text|bbcode }}')->render(['text' => $input])
        );
    }

    public static function filterInputs(): iterable
    {
        yield ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'];
        yield ['[b]<svg onload=alert(1)>[/b]', '<strong>&lt;svg onload=alert(1)&gt;</strong>'];
        yield ['[b]A & B "C"[/b]', '<strong>A &amp; B &quot;C&quot;</strong>'];
        yield ['[color=#123456]Text[/color]', '<span style="color: #123456">Text</span>'];
        yield ['[color=#123456" onmouseover="alert(1)]Text[/color]', '[color=#123456&quot; onmouseover=&quot;alert(1)]Text[/color]'];
    }

    public function testHtmlRenderingDoesNotChangePlainTextConversion(): void
    {
        $twig = $this->getContainer()->get(Environment::class);
        $text = '[b]A & B "C"[/b]';
        self::assertSame(
            '<strong>A &amp; B &quot;C&quot;</strong>|A & B "C"',
            $twig->createTemplate('{{ text|bbcode }}|{{ text|bbcode2txt|raw }}')->render(['text' => $text])
        );
    }

    public function testBroadcastRejectsForeignSpacecraft(): void
    {
        request::setMockVars(['id' => 1021, 'text' => 'Testnachricht']);
        $dic = $this->getContainer();
        $repository = $dic->get(PrivateMessageRepositoryInterface::class);
        $recipient = $dic->get(UserRepositoryInterface::class)->find(102);
        $before = count($repository->getByReceiver($recipient));
        try {
            $dic->get(SendBroadcast::class)->handle(
                $dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class))
            );
        } catch (AccessViolationException) {
            self::assertCount($before, $repository->getByReceiver($recipient));
            return;
        }
        self::fail('Ein fremdes Schiff darf nicht zum Senden verwendet werden');
    }

    #[DataProvider('invalidTokens')]
    public function testBroadcastRejectsInvalidActionToken(?string $token): void
    {
        request::setMockVars(['id' => 42, 'text' => 'Testnachricht'] + ($token === null ? [] : ['sstr' => $token]));
        $dic = $this->getContainer();
        self::assertFalse($dic->get(AccessCheckInterface::class)->checkUserAccess($dic->get(SendBroadcast::class), new InformationWrapper()));
    }

    public static function invalidTokens(): iterable
    {
        yield [null];
        yield [''];
        yield ['ungueltiger-token'];
    }

    public function testBroadcastWithoutRecipientsDoesNotCreateMessages(): void
    {
        $dic = $this->getContainer();
        $repository = $dic->get(PrivateMessageRepositoryInterface::class);
        $user = self::$testSession->getUser();
        $before = count($repository->getBySender($user));
        request::setMockVars(['id' => 42, 'text' => 'Testnachricht']);
        $dic->get(SendBroadcast::class)->handle(
            $dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class))
        );
        $dic->get(EntityManagerInterface::class)->flush();
        self::assertCount($before, $repository->getBySender($user));
    }

    private function createMessage(string $source, string $text): PrivateMessage
    {
        $dic = $this->getContainer();
        $em = $dic->get(EntityManagerInterface::class);
        $messages = $dic->get(PrivateMessageRepositoryInterface::class);
        if ($source === 'stored') {
            $message = $messages->find(1000);
            self::assertInstanceOf(PrivateMessage::class, $message);
            $message->setText($text)->setDate(1732214228);
            $messages->save($message, true);
            $em->refresh($message);
            return $message;
        }

        $recipientId = $source === 'moderation' ? 101 : 102;
        self::$testSession->setUserById($source === 'moderation' ? 102 : 101);
        if ($source === 'broadcast-colony') {
            $colony = $dic->get(ColonyRepositoryInterface::class)->find(42);
            $colony->setUser($dic->get(UserRepositoryInterface::class)->find(102));
            $dic->get(ColonyRepositoryInterface::class)->save($colony);
        } elseif ($source === 'broadcast-station') {
            $station = $dic->get(SpacecraftRepositoryInterface::class)->find(43);
            self::assertInstanceOf(Spacecraft::class, $station);
            $station->setUser($dic->get(UserRepositoryInterface::class)->find(102));
            $dic->get(SpacecraftRepositoryInterface::class)->save($station);
        }
        $em->flush();

        $tokens = $dic->get(SessionStringRepositoryInterface::class);
        $tokens->save($tokens->prototype()
            ->setUserId(self::$testSession->getUser()->getId())
            ->setSessionString('message-rendering-token')
            ->setDate(new DateTime()));
        request::setMockVars([
            'id' => $source === 'broadcast-colony' ? 77 : 42,
            'text' => $text,
            'knid' => 42,
            'reason' => $text,
            'recipient' => $recipientId,
            'sstr' => 'message-rendering-token'
        ]);
        $action = match ($source) {
            'moderation' => $dic->get(AdminDeleteKnPost::class),
            'private' => $dic->get(WritePm::class),
            default => $dic->get(SendBroadcast::class)
        };
        self::assertTrue($dic->get(AccessCheckInterface::class)->checkUserAccess($action, new InformationWrapper()));
        $action->handle($dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class)));
        $em->flush();
        $recipient = $dic->get(UserRepositoryInterface::class)->find($recipientId);
        $matches = array_values(array_filter($messages->getByReceiver($recipient), static fn (PrivateMessage $pm): bool => str_contains($pm->getText(), $text)));
        self::assertCount(1, $matches);
        $em->refresh($matches[0]);
        return $matches[0];
    }

    private function renderMessage(PrivateMessage $message, string $view): string
    {
        $dic = $this->getContainer();
        self::$testSession->setUserById($message->getRecipientId());
        $game = $dic->get(GameControllerInterface::class);
        $game->resetGameData();
        $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($game, ModuleEnum::PM);
        $page = $dic->get(TwigPageInterface::class);
        $page->resetVariables();
        $page->setVar('USER', self::$testSession->getUser());
        if ($view === 'inbox') {
            request::setMockVars(['pmcat' => $message->getCategory()->getId()]);
            $dic->get(MessageProvider::class)->setTemplateVariables($context);
            $template = 'html/view/pmCategory.twig';
        } elseif ($view === 'conversation') {
            request::setMockVars(['reply' => $message->getId(), 'recipient' => $message->getSenderId()]);
            $dic->get(ShowWritePm::class)->handle($context);
            $template = 'html/message/writePm.twig';
        } else {
            request::setMockVars([]);
            $dic->get(MessengerStyleProvider::class)->setTemplateVariables($context);
            $template = 'html/communication/conversations.twig';
        }
        $page->setTemplate($template);
        return $page->render();
    }
}
