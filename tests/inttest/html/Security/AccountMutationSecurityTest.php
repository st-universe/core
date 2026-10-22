<?php

declare(strict_types=1);

namespace Stu\Html\Security;

use Doctrine\ORM\EntityManagerInterface;
use Dom\HTMLDocument;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Noodlehaus\Parser\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ReflectionClass;
use request;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\RedirectionException;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\View\Provider\PlayerSettingsProvider;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\EmailNotificationSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\IgnoreList;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserSettingRepositoryInterface;
use Stu\TwigTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AccountMutationSecurityTest extends TwigTestCase
{
    private const string ORIGINAL_AVATAR = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private string $directory;
    private int $contactId;
    private int $ignoreId;
    private int $logoutCount = 0;
    private int $emailCount = 0;

    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        self::$testSession->setUserById(101);
        request::setMockVars(null);
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $this->directory = sys_get_temp_dir() . '/stu-account-security-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
        $image = imagecreatetruecolor(150, 150);
        imagepng($image, $this->directory . '/' . self::ORIGINAL_AVATAR . '.png');
        imagepng($image, $this->directory . '/upload.png');
        $dic = $this->getContainer();
        $notification = $this->mock(EmailNotificationSenderInterface::class);
        $notification->shouldReceive('sendNotification')->zeroOrMoreTimes()->andReturnUsing(function (): void {
            $this->emailCount++;
        });
        $dic->setAdditionalService(EmailNotificationSenderInterface::class, $notification);
        $user = self::$testSession->getUser();
        $user->setDescription('Ursprüngliche Beschreibung');
        $user->setVacationMode(false);
        $user->setVacationRequestDate(0);
        $changer = $dic->get(ChangeUserSettingInterface::class);
        $changer->change($user, UserSettingEnum::SHOW_ONLINE_STATUS, '1');
        $changer->change($user, UserSettingEnum::SAVE_LOGIN, '1');
        $changer->change($user, UserSettingEnum::RGB_CODE, '#123456');
        $changer->change($user, UserSettingEnum::AVATAR, self::ORIGINAL_AVATAR);
        $ranks = $dic->get(UserCrewRankRepositoryInterface::class);
        $ranks->save($ranks->prototype()->setUser($user)->setRank(CrewSkillLevelEnum::CADET)->setName('Anwärter'));
        $contacts = $dic->get(ContactRepositoryInterface::class);
        $contact = $contacts->prototype()->setUser($user)
            ->setRecipient($dic->get(UserRepositoryInterface::class)->find(103))
            ->setMode(ContactListModeEnum::NEUTRAL)->setComment('Ursprünglicher Kommentar');
        $contacts->save($contact);
        $dic->get(EntityManagerInterface::class)->flush();
        $this->contactId = $contact->getId();
        $ignores = $dic->get(IgnoreListRepositoryInterface::class);
        $ignore = $ignores->prototype()->setUser($user)
            ->setRecipient($dic->get(UserRepositoryInterface::class)->find(103));
        $ignores->save($ignore);
        $em = $dic->get(EntityManagerInterface::class);
        $em->persist(new PrivateMessageFolder()
            ->setUser($dic->get(UserRepositoryInterface::class)->find(103))
            ->setSpecial(PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM));
        $first = new TutorialStep();
        $first->setModule(ModuleEnum::OPTIONS);
        $first->setTitle('Erster Testschritt');
        $second = new TutorialStep();
        $second->setModule(ModuleEnum::PM);
        $second->setTitle('Zweiter Testschritt');
        $em->persist($first);
        $em->persist($second);
        $em->persist(new UserTutorial()->setUser($user)->setTutorialStep($second));
        $em->flush();
        $this->ignoreId = $ignore->getId();
    }

    #[\Override]
    public function tearDown(): void
    {
        foreach (glob($this->directory . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        unset($_SERVER['REQUEST_METHOD']);
        request::setMockVars(null);
        parent::tearDown();
    }

    public static function actions(): iterable
    {
        foreach ([
            'PlayerSetting' => ['ChangeUserName', 'ChangeDescription', 'ChangeSettings', 'ChangeCrewRankNames', 'ActivateVacation', 'CreateTutorials', 'DeleteTutorials', 'ChangeRgbCode', 'ChangeAvatar'],
            'Message' => ['AddContact', 'SwitchContactMode', 'EditContactComment', 'DeleteContacts', 'IgnoreUser', 'DeleteIgnores'],
            'Alliance' => ['SetTopicSticky', 'UnsetTopicSticky']
        ] as $module => $names) {
            foreach ($names as $name) {
                yield $name => [sprintf('Stu\\Module\\%s\\Action\\%s\\%s', $module, $name, $name)];
            }
        }
    }

    #[DataProvider('actions')]
    public function testForgedRequestsAreRejectedAndAuthorizedActionStillWorks(string $class): void
    {
        $em = $this->getContainer()->get(EntityManagerInterface::class);
        if (str_ends_with($class, '\\SetTopicSticky')) {
            $em->find(AllianceBoardTopic::class, 1)->setSticky(false);
            $em->flush();
        }
        $parameters = $this->parameters($class);
        $before = $this->state();
        foreach ([['GET', 'missing'], ['GET', 'own'], ['POST', 'missing'], ['POST', 'wrong'], ['POST', 'foreign'], ['POST', 'consumed']] as [$method, $kind]) {
            $request = $parameters;
            if ($kind !== 'missing') {
                $request['sstr'] = $this->token($kind === 'foreign' ? 102 : 101);
                if ($kind === 'wrong') {
                    $request['sstr'] = 'ungueltiger-token';
                } elseif ($kind === 'consumed') {
                    self::assertTrue($this->getContainer()->get(SessionStringRepositoryInterface::class)->isValid($request['sstr'], 101));
                }
            }
            self::assertFalse($this->dispatch($class, $method, $request));
            self::assertSame($before, $this->state(), $class . ' ' . $method . ' ' . $kind);
        }
        $parameters['sstr'] = $this->token(101);
        $redirected = $this->dispatch($class, 'POST', $parameters);
        self::assertSame(str_ends_with($class, '\\ActivateVacation'), $redirected);
        $this->assertAuthorizedEffect($class, $before);
        $after = $this->state();
        self::assertFalse($this->dispatch($class, 'POST', $parameters));
        self::assertSame($after, $this->state());
    }

    public function testFormsAndContactControlsSupplyTokens(): void
    {
        $dic = $this->getContainer();
        $page = $dic->get(TwigPageInterface::class);
        $token = $this->token(101);
        $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($dic->get(GameControllerInterface::class), ModuleEnum::OPTIONS);
        $dic->get(PlayerSettingsProvider::class)->setTemplateVariables($context);
        $page->setVar('SESSIONSTRING', $token);
        $page->setTemplate('html/view/options.twig');
        $document = HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
        foreach (['B_CHANGE_NAME', 'B_CHANGE_DESCRIPTION', 'B_CHANGE_SETTINGS', 'B_CHANGE_CREW_RANK_NAMES', 'B_ACTIVATE_VACATION', 'B_CREATE_TUTORIALS', 'B_DELETE_TUTORIALS', 'B_CHANGE_USER_RGB', 'B_CHANGE_AVATAR'] as $action) {
            $form = $document->querySelector('[name="' . $action . '"]')->closest('form');
            self::assertSame('post', $form->getAttribute('method'));
            self::assertSame($token, $form->querySelector('[name="sstr"]')?->getAttribute('value'));
        }
        $page->setVar('IGNORE_LIST', []);
        $page->setVar('REMOTE_IGNORES', []);
        $page->setTemplate('html/communication/ignorelist.twig');
        $document = HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
        self::assertSame($token, $document->querySelector('form [name="sstr"]')?->getAttribute('value'));
        $page->setVar('div', 'contacttest');
        $page->setVar('userId', 102);
        $page->setVar('divname', 'contacttest');
        $page->setVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
        $page->setVar('contact', $dic->get(ContactRepositoryInterface::class)->find($this->contactId));
        foreach (['contactButton', 'ignoreButton', 'contactModeSwitch'] as $template) {
            $page->setTemplate('html/user/' . $template . '.twig');
            $document = HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
            foreach ($document->querySelectorAll('[onclick]') as $control) {
                self::assertStringContainsString("'" . $token . "'", $control->getAttribute('onclick'));
            }
        }
        $topic = $dic->get(EntityManagerInterface::class)->find(AllianceBoardTopic::class, 1);
        foreach ([false, true] as $sticky) {
            $topic->setSticky($sticky);
            $dic->get(EntityManagerInterface::class)->flush();
            $page->setVar('TOPIC', $topic);
            $page->setTemplate('html/alliance/alliancetopicsettings.twig');
            $document = HTMLDocument::createFromString($page->render(), LIBXML_NOERROR);
            $action = $sticky ? 'B_UNSET_STICKY' : 'B_SET_STICKY';
            $button = $document->querySelector('[name="' . $action . '"]');
            self::assertNotNull($button);
            $form = $button->closest('form');
            self::assertSame('post', $form->getAttribute('method'));
            self::assertSame($token, $form->querySelector('[name="sstr"]')?->getAttribute('value'));
        }
    }

    public function testIntentionallyEmptyRankAndSettingValuesStillReset(): void
    {
        $ranks = 'Stu\\Module\\PlayerSetting\\Action\\ChangeCrewRankNames\\ChangeCrewRankNames';
        $this->dispatch($ranks, 'POST', ['crew_rank_names' => [CrewSkillLevelEnum::CADET->value => ''], 'sstr' => $this->token(101)]);
        self::assertSame('', $this->state()['rank']);
        $settings = 'Stu\\Module\\PlayerSetting\\Action\\ChangeSettings\\ChangeSettings';
        $this->dispatch($settings, 'POST', ['show_online_status' => '1', 'sstr' => $this->token(101)]);
        self::assertSame('1', $this->state()['settings']['show_online_status']);
        $this->dispatch($settings, 'POST', ['sstr' => $this->token(101)]);
        self::assertNull($this->state()['settings']['show_online_status']);
    }

    public function testValidTokenDoesNotBypassOwnershipAndEligibilityChecks(): void
    {
        $dic = $this->getContainer();
        $user = $dic->get(UserRepositoryInterface::class)->find(102);
        $contacts = $dic->get(ContactRepositoryInterface::class);
        $foreignContact = $contacts->prototype()->setUser($user)->setRecipient(self::$testSession->getUser());
        $contacts->save($foreignContact);
        $ignores = $dic->get(IgnoreListRepositoryInterface::class);
        $foreignIgnore = $ignores->prototype()->setUser($user)->setRecipient(self::$testSession->getUser());
        $ignores->save($foreignIgnore);
        $em = $dic->get(EntityManagerInterface::class);
        self::$testSession->getUser()->setAlliance(null);
        self::$testSession->getUser()->setVacationRequestDate(time());
        $em->flush();
        $before = $this->state();
        foreach (self::actions() as [$class]) {
            $parameters = match (new ReflectionClass($class)->getShortName()) {
                'AddContact' => ['recid' => 101, 'clmode' => 1],
                'SwitchContactMode' => ['cid' => $foreignContact->getId(), 'clmode' => 2],
                'EditContactComment' => ['edit_contact' => $foreignContact->getId(), 'comment_' . $foreignContact->getId() => 'Fremder Kommentar'],
                'DeleteContacts' => ['deletion_mark' => (string) $foreignContact->getId()],
                'IgnoreUser' => ['recid' => 101],
                'DeleteIgnores' => ['deletion_mark' => (string) $foreignIgnore->getId()],
                'SetTopicSticky', 'UnsetTopicSticky' => ['topicid' => 1],
                'ActivateVacation' => [],
                default => null
            };
            if ($parameters === null) {
                continue;
            }
            $parameters['sstr'] = $this->token(101);
            try {
                self::assertFalse($this->dispatch($class, 'POST', $parameters));
            } catch (AccessViolationException) {
                self::assertContains(new ReflectionClass($class)->getShortName(), ['SetTopicSticky', 'UnsetTopicSticky']);
            }
            self::assertSame($before, $this->state(), $class);
        }
    }

    private function parameters(string $class): array
    {
        return match (new ReflectionClass($class)->getShortName()) {
            'ChangeUserName' => ['uname' => 'Lokaler Testname'],
            'ChangeDescription' => ['description' => 'Neue Testbeschreibung'],
            'ChangeCrewRankNames' => ['crew_rank_names' => [CrewSkillLevelEnum::CADET->value => 'Kadett']],
            'ChangeRgbCode' => ['rgb_code' => '#abcdef'],
            'AddContact' => ['recid' => '102', 'clmode' => 1, 'cldiv' => 'testcontact'],
            'SwitchContactMode' => ['cid' => $this->contactId, 'clmode' => 1, 'cldiv' => 'testcontact'],
            'EditContactComment' => ['edit_contact' => $this->contactId, 'comment_' . $this->contactId => 'Neuer Kommentar'],
            'DeleteContacts' => ['deletion_mark' => (string) $this->contactId],
            'IgnoreUser' => ['recid' => 102],
            'DeleteIgnores' => ['deletion_mark' => (string) $this->ignoreId],
            'SetTopicSticky', 'UnsetTopicSticky' => ['topicid' => 1, 'boardid' => 1],
            default => []
        };
    }

    private function token(int $userId): string
    {
        $dic = $this->getContainer();
        return new SessionStringFactory($dic->get(SessionStringRepositoryInterface::class), new StuTime())
            ->createSessionString($dic->get(UserRepositoryInterface::class)->find($userId));
    }

    private function dispatch(string $class, string $method, array $parameters): bool
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $parameters[$class::ACTION_IDENTIFIER] = '1';
        $_GET = $method === 'GET' ? $parameters : [];
        $_POST = $method === 'POST' ? $parameters : [];
        $_FILES = ['avatar' => ['name' => 'upload.png', 'error' => UPLOAD_ERR_OK, 'type' => 'image/png', 'size' => filesize($this->directory . '/upload.png'), 'tmp_name' => $this->directory . '/upload.png']];
        $dic = $this->getContainer();
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()->getParameters() as $parameter) {
            $type = $parameter->getType()->getName();
            if (str_ends_with($type, 'RequestInterface')) {
                $requestClass = substr($type, 0, -strlen('Interface'));
                $arguments[] = new $requestClass();
            } elseif ($type === SessionInterface::class) {
                $session = $this->mock(SessionInterface::class);
                $session->shouldReceive('logout')->zeroOrMoreTimes()->andReturnUsing(function (): void {
                    $this->logoutCount++;
                });
                $arguments[] = $session;
            } elseif ($type === PngUpload::class) {
                $upload = $this->mock(PngUpload::class)->makePartial();
                $upload->shouldReceive('readUploaded')->andReturnUsing(fn (string $path, int $error, int $bytes, int $width, int $height, int $pixels) => $upload->readFile($path, $bytes, $width, $height, $pixels));
                $arguments[] = $upload;
            } elseif ($type === ConfigInterface::class) {
                $arguments[] = new Config(json_encode(['game' => ['webroot' => '', 'user_avatar_path' => $this->directory]]), new Json(), true);
            } else {
                $arguments[] = $dic->get($type);
            }
        }
        $action = new $class(...$arguments);
        $module = $this->getModuleOfController($action, []);
        $discovery = $this->mock(ControllerDiscoveryInterface::class);
        $discovery->shouldReceive('getControllers')->once()->with($module, false)->andReturn([$class::ACTION_IDENTIFIER => $action]);
        $data = new GameData();
        $gameRequest = new GameRequest();
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('getGameData')->andReturn($data);
        $game->shouldReceive('getInfo')->andReturn($data->gameInformations);
        $game->shouldReceive('getUser')->andReturn(self::$testSession->getUser());
        try {
            new CallbackExecution($discovery, $dic->get(AccessCheckInterface::class), $dic->get(ActionContextFactoryInterface::class), new StuTime(), $dic->get(EntityManagerInterface::class))->execute($module, $game);
        } catch (RedirectionException) {
            $dic->get(EntityManagerInterface::class)->flush();
            return true;
        }
        self::assertSame($class::ACTION_IDENTIFIER, $gameRequest->getAction());
        return false;
    }

    private function state(): array
    {
        $dic = $this->getContainer();
        $em = $dic->get(EntityManagerInterface::class);
        $user = self::$testSession->getUser();
        $em->refresh($user);
        $settings = [];
        foreach (UserSettingEnum::cases() as $setting) {
            $entry = $dic->get(UserSettingRepositoryInterface::class)->findOneBy(['user' => $user, 'setting' => $setting]);
            if ($entry !== null) {
                $em->refresh($entry);
            }
            $settings[$setting->value] = $entry?->getValue();
        }
        $contacts = [];
        foreach ($em->getRepository(Contact::class)->findAll() as $contact) {
            $em->refresh($contact);
            $contacts[$contact->getId()] = [$contact->getUserId(), $contact->getRecipientId(), $contact->getMode()->value, $contact->getComment()];
        }
        $ignores = [];
        foreach ($em->getRepository(IgnoreList::class)->findAll() as $ignore) {
            $em->refresh($ignore);
            $ignores[$ignore->getId()] = [$ignore->getUserId(), $ignore->getRecipientId()];
        }
        $tutorials = [];
        foreach ($em->getRepository(UserTutorial::class)->findBy(['user' => $user]) as $entry) {
            $tutorials[] = $entry->getTutorialStep()->getId();
            $em->detach($entry);
        }
        sort($tutorials);
        $topic = $em->find(AllianceBoardTopic::class, 1);
        $em->refresh($topic);
        $files = [];
        foreach (glob($this->directory . '/*') as $file) {
            $files[basename($file)] = hash_file('sha256', $file);
        }
        return [
            'name' => $user->getName(), 'description' => $user->getDescription(),
            'vacation' => $user->isVacationMode(), 'vacationDate' => $user->getVacationRequestDate(),
            'settings' => $settings,
            'rank' => $dic->get(UserCrewRankRepositoryInterface::class)->getCustomRankName($user, CrewSkillLevelEnum::CADET),
            'contacts' => $contacts, 'ignores' => $ignores, 'tutorials' => $tutorials, 'sticky' => $topic->getSticky(),
            'messages' => $em->getRepository(PrivateMessage::class)->count([]),
            'emails' => $this->emailCount, 'logouts' => $this->logoutCount, 'files' => $files
        ];
    }

    private function assertAuthorizedEffect(string $class, array $before): void
    {
        $after = $this->state();
        self::assertNotSame($before, $after);
        match (new ReflectionClass($class)->getShortName()) {
            'ChangeUserName' => self::assertSame('Lokaler Testname', $after['name']),
            'ChangeDescription' => self::assertSame('Neue Testbeschreibung', $after['description']),
            'ChangeSettings' => self::assertNull($after['settings']['show_online_status']),
            'ChangeCrewRankNames' => self::assertSame('Kadett', $after['rank']),
            'ActivateVacation' => self::assertSame([true, 1], [$after['vacation'], $after['logouts']]),
            'CreateTutorials' => self::assertCount(2, $after['tutorials']),
            'DeleteTutorials' => self::assertSame([], $after['tutorials']),
            'ChangeRgbCode' => self::assertSame('#abcdef', $after['settings']['rgb_code']),
            'ChangeAvatar' => self::assertNotSame(self::ORIGINAL_AVATAR, $after['settings']['avatar']),
            'AddContact' => self::assertSame(ContactListModeEnum::FRIEND, $this->getContainer()->get(ContactRepositoryInterface::class)->getByUserAndOpponent(101, 102)->getMode()),
            'SwitchContactMode' => self::assertSame(1, $after['contacts'][$this->contactId][2]),
            'EditContactComment' => self::assertSame('Neuer Kommentar', $after['contacts'][$this->contactId][3]),
            'DeleteContacts' => self::assertArrayNotHasKey($this->contactId, $after['contacts']),
            'IgnoreUser' => self::assertTrue($this->getContainer()->get(IgnoreListRepositoryInterface::class)->exists(101, 102)),
            'DeleteIgnores' => self::assertArrayNotHasKey($this->ignoreId, $after['ignores']),
            'SetTopicSticky' => self::assertTrue($after['sticky']),
            'UnsetTopicSticky' => self::assertFalse($after['sticky'])
        };
        if (in_array(new ReflectionClass($class)->getShortName(), ['AddContact', 'SwitchContactMode', 'DeleteContacts'], true)) {
            self::assertGreaterThan($before['messages'], $after['messages']);
        }
        if (str_ends_with($class, '\\ChangeAvatar')) {
            self::assertFileDoesNotExist($this->directory . '/' . self::ORIGINAL_AVATAR . '.png');
            $file = $this->directory . '/' . $after['settings']['avatar'] . '.png';
            self::assertFileExists($file);
            self::assertSame([150, 150], array_slice(getimagesize($file), 0, 2));
        }
    }
}
