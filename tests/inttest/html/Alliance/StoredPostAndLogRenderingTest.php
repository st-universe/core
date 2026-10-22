<?php

declare(strict_types=1);

namespace Stu\Html\Alliance;

use Doctrine\ORM\EntityManagerInterface;
use Dom\Element;
use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Alliance\Action\CreatePost\CreatePost;
use Stu\Module\Alliance\Action\CreateTopic\CreateTopic;
use Stu\Module\Alliance\Action\EditPost\EditPost;
use Stu\Module\Alliance\View\ShowEditPost\ShowEditPost;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\UserProfileProvider;
use Stu\Module\Spacecraft\Action\AddShipLog\AddShipLog;
use Stu\Module\Spacecraft\Action\EditSpacecraftLog\EditSpacecraftLog;
use Stu\Module\Spacecraft\View\ShowScan\ShowScan;
use Stu\Module\Spacecraft\View\ShowShipCommunication\ShowShipCommunication;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\SpacecraftLog;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;
use Stu\Orm\Repository\SpacecraftLogScanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\TwigTestCase;
use Twig\Environment;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class StoredPostAndLogRenderingTest extends TwigTestCase
{
    private const string ATTACK = "Anfang\n</textarea><img src=\"x\" onerror=\"alert(1)\">Ende & \"Zitat\"";
    private const string FORMATTED = "[b]Hallo[/b] [i]Welt[/i] [u]Text[/u] [color=#123456]Farbe[/color]\nA & B \"Zitate\"";

    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        self::$testSession->setUserById(101);
    }

    #[DataProvider('postInputs')]
    public function testStoredPostIsSafeInTopicAndEditor(string $source, bool $formatted): void
    {
        $text = $formatted ? self::FORMATTED : self::ATTACK;
        $post = $this->createPost($source, $text);
        $expected = $source === 'edit' ? $text : trim(strip_tags($text));
        self::assertSame($expected, $post->getText());

        $dic = $this->getContainer();
        $member = $dic->get(UserRepositoryInterface::class)->find(102);
        $member->setAlliance($post->getBoard()->getAlliance());
        $dic->get(UserRepositoryInterface::class)->save($member);
        $dic->get(EntityManagerInterface::class)->flush();

        $context = $this->viewContext(102, ModuleEnum::ALLIANCE);
        request::setMockVars(['pid' => $post->getId()]);
        try {
            $dic->get(ShowEditPost::class)->handle($context);
            self::fail('Fremde Beiträge dürfen nicht im Editor geöffnet werden');
        } catch (AccessViolationException) {
        }
        $context = $this->viewContext(101, ModuleEnum::ALLIANCE);
        $dic->get(ShowEditPost::class)->handle($context);
        $document = $this->render('html/alliance/editallypost.twig');
        self::assertSame(0, $document->getElementsByTagName('img')->length);
        self::assertSame(0, $document->getElementsByTagName('script')->length);
        $textarea = $document->querySelector('textarea[name="text"]');
        self::assertNotNull($textarea);
        self::assertSame($expected, $textarea->textContent);

        $context = $this->viewContext(102, ModuleEnum::ALLIANCE);
        request::setMockVars(['topicid' => $post->getTopicId(), 'boardid' => $post->getBoardId()]);
        $dic->get(Topic::class)->handle($context);
        $document = $this->render('html/alliance/allianceboardtopic.twig');
        $contents = $document->querySelectorAll('td[style="vertical-align: top;"] > span');
        self::assertGreaterThan(0, $contents->length);
        $content = $contents->item($contents->length - 1);
        $this->assertSafeContent($content, $expected, $formatted);

        self::$testSession->setUserById(101);
        $this->executeAction($dic->get(EditPost::class), ['pid' => $post->getId(), 'text' => $textarea->textContent]);
        $dic->get(EntityManagerInterface::class)->refresh($post);
        self::assertSame($expected, $post->getText());
    }

    public static function postInputs(): iterable
    {
        foreach (['edit', 'create-topic', 'create-post'] as $source) {
            yield $source . '-attack' => [$source, false];
            yield $source . '-formatted' => [$source, true];
        }
    }

    #[DataProvider('logInputs')]
    public function testPublicLogIsSafeForOwnerScannerAndSavedProfile(string $source, bool $formatted): void
    {
        $dic = $this->getContainer();
        $text = $formatted ? self::FORMATTED : self::ATTACK;
        $logs = $dic->get(SpacecraftLogRepositoryInterface::class);
        $this->executeAction($dic->get(AddShipLog::class), ['id' => 42, 'log' => $source === 'add' ? $text : 'Ursprünglicher Eintrag']);
        $entries = $logs->getBySpacecraftId(42);
        self::assertCount(1, $entries);
        $log = $entries[0];
        if ($source === 'edit') {
            $this->executeAction($dic->get(EditSpacecraftLog::class), ['id' => 42, 'logid' => $log->getId(), 'log' => $text]);
        }
        $em = $dic->get(EntityManagerInterface::class);
        $em->refresh($log);
        self::assertSame($text, $log->getText());
        self::assertFalse($log->isPrivate());

        $target = $dic->get(SpacecraftRepositoryInterface::class)->find(42);
        $private = $logs->prototype()->setSpacecraft($target)->setText('Privater Testeintrag')->setDate(time())->setPrivate(true);
        $logs->save($private);
        $scanner = $dic->get(SpacecraftRepositoryInterface::class)->find(1021);
        $scanner->setLocation($target->getLocation());
        $dic->get(SpacecraftRepositoryInterface::class)->save($scanner);
        $em->flush();

        $context = $this->viewContext(101, ModuleEnum::SHIP);
        request::setMockVars(['id' => 42]);
        $dic->get(ShowShipCommunication::class)->handle($context);
        $document = $this->render('html/ship/shipcommunication.twig');
        $content = null;
        foreach ($document->querySelectorAll('#logbook button[data-log-text]') as $button) {
            if ($button->getAttribute('data-log-text') === $text) {
                $content = $button->closest('table')->querySelector('td[colspan="3"]:not(.knedit)');
            }
        }
        self::assertNotNull($content);
        $this->assertSafeContent($content, $text, $formatted);

        $context = $this->viewContext(102, ModuleEnum::SHIP);
        request::setMockVars(['id' => 1021, 'target' => 42]);
        $dic->get(ShowScan::class)->handle($context);
        $em->flush();
        $scan = $dic->get(SpacecraftLogScanRepositoryInterface::class)->findOneBy(['user' => self::$testSession->getUser(), 'spacecraft_id' => 42]);
        self::assertNotNull($scan);
        self::assertSame([$log->getId()], array_map(static fn (SpacecraftLog $entry): int => $entry->getId(), $logs->getBySpacecraftIdUntil(42, $scan->getDate(), false)));
        $document = $this->render('html/ship/showshipscan.twig');
        $content = $document->querySelector('#scanlogbook table td:not(.knedit)');
        self::assertNotNull($content);
        $this->assertSafeContent($content, $text, $formatted);
        self::assertStringNotContainsString('Privater Testeintrag', $document->getElementById('scanlogbook')->textContent);

        $context = $this->viewContext(102, ModuleEnum::USERPROFILE);
        request::setMockVars(['uid' => 101]);
        $dic->get(UserProfileProvider::class)->setTemplateVariables($context);
        $document = $this->render('html/view/userprofile.twig');
        $content = $document->querySelector('#spacecraft-log-42 > td > table td:not(.knedit)');
        self::assertNotNull($content);
        $this->assertSafeContent($content, $text, $formatted);
        self::assertStringNotContainsString('Privater Testeintrag', $document->getElementById('spacecraft-log-42')->textContent);
        $em->refresh($log);
        self::assertSame($text, $log->getText());
    }

    public static function logInputs(): iterable
    {
        foreach (['add', 'edit'] as $source) {
            yield $source . '-attack' => [$source, false];
            yield $source . '-formatted' => [$source, true];
        }
    }

    #[DataProvider('foreignPostOperations')]
    public function testForeignAllianceCannotReadOrEditPost(string $operation): void
    {
        $dic = $this->getContainer();
        $post = $this->createPost('create-topic', 'Unveränderter Beitrag');
        $context = $this->viewContext(102, ModuleEnum::ALLIANCE);
        request::setMockVars(['pid' => $post->getId(), 'topicid' => $post->getTopicId(), 'boardid' => $post->getBoardId()]);
        try {
            match ($operation) {
                'editor' => $dic->get(ShowEditPost::class)->handle($context),
                'topic' => $dic->get(Topic::class)->handle($context),
                default => $this->executeAction($dic->get(EditPost::class), ['pid' => $post->getId(), 'text' => 'Unberechtigte Änderung'])
            };
        } catch (AccessViolationException) {
            $dic->get(EntityManagerInterface::class)->refresh($post);
            self::assertSame('Unveränderter Beitrag', $post->getText());
            return;
        }
        self::fail('Allianzfremder Zugriff darf nicht möglich sein');
    }

    public static function foreignPostOperations(): iterable
    {
        yield ['editor'];
        yield ['topic'];
        yield ['edit'];
    }

    #[DataProvider('foreignLogOperations')]
    public function testForeignSpacecraftCannotBeUsedToWriteLogs(bool $edit): void
    {
        $dic = $this->getContainer();
        self::$testSession->setUserById(102);
        try {
            $this->executeAction($dic->get($edit ? EditSpacecraftLog::class : AddShipLog::class), ['id' => 42, 'logid' => 1, 'log' => self::ATTACK]);
        } catch (AccessViolationException) {
            self::assertSame([], $dic->get(SpacecraftLogRepositoryInterface::class)->getBySpacecraftId(42));
            return;
        }
        self::fail('Fremde Raumfahrzeuge dürfen nicht zum Schreiben verwendet werden');
    }

    public static function foreignLogOperations(): iterable
    {
        yield [false];
        yield [true];
    }

    #[DataProvider('invalidTokens')]
    public function testWritesRejectMissingAndForeignTokens(bool $foreign): void
    {
        $dic = $this->getContainer();
        $post = $this->createPost('create-topic', 'Unveränderter Beitrag');
        $token = new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())
            ->createSessionString($dic->get(UserRepositoryInterface::class)->find(102));
        request::setMockVars(['id' => 42, 'pid' => $post->getId(), 'text' => self::ATTACK, 'log' => self::ATTACK] + ($foreign ? ['sstr' => $token] : []));
        foreach ([EditPost::class, AddShipLog::class, EditSpacecraftLog::class] as $action) {
            self::assertFalse($dic->get(AccessCheckInterface::class)->checkUserAccess($dic->get($action), new InformationWrapper()));
        }
        $dic->get(EntityManagerInterface::class)->refresh($post);
        self::assertSame('Unveränderter Beitrag', $post->getText());
        self::assertSame([], $dic->get(SpacecraftLogRepositoryInterface::class)->getBySpacecraftId(42));
    }

    public static function invalidTokens(): iterable
    {
        yield [false];
        yield [true];
    }

    public function testNewlineFilterDoesNotTrustUnescapedInput(): void
    {
        $html = $this->getContainer()->get(Environment::class)->createTemplate('{{ text|nl2br }}')->render(['text' => self::ATTACK]);
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        self::assertSame(0, $document->getElementsByTagName('img')->length);
        self::assertStringContainsString('</textarea><img src="x" onerror="alert(1)">', $document->body->textContent);
    }

    private function createPost(string $source, string $text): AllianceBoardPost
    {
        $dic = $this->getContainer();
        $posts = $dic->get(AllianceBoardPostRepositoryInterface::class);
        $this->executeAction($dic->get(CreateTopic::class), ['boardid' => 1, 'tname' => 'Testthema', 'ttext' => $source === 'create-topic' ? $text : 'Startbeitrag']);
        $post = $posts->getRecentByBoard(1);
        self::assertInstanceOf(AllianceBoardPost::class, $post);
        $dic->get(EntityManagerInterface::class)->refresh($post);
        if ($source === 'edit') {
            $this->executeAction($dic->get(EditPost::class), ['pid' => $post->getId(), 'text' => $text]);
        } elseif ($source === 'create-post') {
            $this->executeAction($dic->get(CreatePost::class), ['boardid' => 1, 'topicid' => $post->getTopicId(), 'ttext' => $text]);
            $matches = array_values(array_filter($posts->getByTopic($post->getTopicId(), 20, 0), static fn (AllianceBoardPost $entry): bool => $entry->getText() === trim(strip_tags($text))));
            self::assertCount(1, $matches);
            $post = $matches[0];
        }
        $dic->get(EntityManagerInterface::class)->refresh($post);
        return $post;
    }

    private function executeAction(ActionControllerInterface $action, array $parameters): void
    {
        $dic = $this->getContainer();
        $token = new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())
            ->createSessionString(self::$testSession->getUser());
        request::setMockVars($parameters + ['sstr' => $token]);
        self::assertTrue($dic->get(AccessCheckInterface::class)->checkUserAccess($action, new InformationWrapper()));
        $action->handle($dic->get(ActionContextFactoryInterface::class)->createActionContext($dic->get(GameControllerInterface::class)));
        $dic->get(EntityManagerInterface::class)->flush();
    }

    private function viewContext(int $userId, ModuleEnum $module): ViewControllerContext
    {
        self::$testSession->setUserById($userId);
        $dic = $this->getContainer();
        $game = $dic->get(GameControllerInterface::class);
        $game->resetGameData();
        $page = $dic->get(TwigPageInterface::class);
        $page->resetVariables();
        $page->setVar('USER', self::$testSession->getUser());
        $page->setVar('SESSIONSTRING', 'rendering-test');
        return $dic->get(ViewContextFactoryInterface::class)->createViewContext($game, $module);
    }

    private function render(string $template): HTMLDocument
    {
        $page = $this->getContainer()->get(TwigPageInterface::class);
        $page->setTemplate($template);
        return HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
    }

    private function assertSafeContent(Element $content, string $text, bool $formatted): void
    {
        self::assertSame(0, $content->getElementsByTagName('img')->length);
        self::assertSame(0, $content->getElementsByTagName('script')->length);
        self::assertSame(1, $content->getElementsByTagName('br')->length);
        if ($formatted) {
            self::assertSame('Hallo', $content->getElementsByTagName('strong')->item(0)?->textContent);
            self::assertSame('Welt', $content->getElementsByTagName('em')->item(0)?->textContent);
            self::assertSame('Text', $content->getElementsByTagName('u')->item(0)?->textContent);
            self::assertSame('color: #123456', $content->getElementsByTagName('span')->item(0)?->getAttribute('style'));
            self::assertStringContainsString('A & B "Zitate"', $content->textContent);
        } else {
            self::assertStringContainsString($text, $content->textContent);
        }
    }
}
