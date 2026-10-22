<?php

declare(strict_types=1);

namespace Stu\Html\Game;

use Doctrine\ORM\EntityManagerInterface;
use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Index\News\NewsItem;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Alliance\Action\CreateAlliance\CreateAlliance;
use Stu\Module\Alliance\Action\EditDetails\EditDetails;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\AllianceProvider;
use Stu\Module\Game\Lib\View\Provider\UserProfileProvider;
use Stu\Module\PlayerSetting\Action\ChangeDescription\ChangeDescription;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\News;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\TwigTestCase;

final class BbCodeImageRenderingTest extends TwigTestCase
{
    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
    }

    #[DataProvider('descriptions')]
    public function testStoredDescriptionIsSafeForOtherViewers(string $entrypoint, bool $valid): void
    {
        $dic = $this->getContainer();
        $description = $valid
            ? '[b]Testbild[/b][img]https://example.test/image.png?a=1&b=2[/img]'
            : '[img]x" onerror="alert(document.domain)[/img]';
        $game = $dic->get(GameControllerInterface::class);
        $actorId = $entrypoint === 'alliance-create' ? 103 : 101;
        self::$testSession->setUserById($actorId);
        $actionContext = $dic->get(ActionContextFactoryInterface::class)->createActionContext($game);

        request::setMockVars([
            'description' => $description,
            'name' => 'Testallianz',
            'rgb' => '',
            'acceptapp' => 1
        ]);

        if ($entrypoint === 'profile') {
            $dic->get(ChangeDescription::class)->handle($actionContext);
        } elseif ($entrypoint === 'alliance-create') {
            $dic->get(CreateAlliance::class)->handle($actionContext);
        } elseif ($entrypoint === 'alliance-edit') {
            $dic->get(EditDetails::class)->handle($actionContext);
        }

        $em = $dic->get(EntityManagerInterface::class);
        $em->flush();
        $actor = $dic->get(UserRepositoryInterface::class)->find($actorId);
        self::assertInstanceOf(User::class, $actor);
        $em->refresh($actor);
        $alliance = $entrypoint === 'profile' ? null : $actor->getAlliance();
        if ($entrypoint !== 'news') {
            if ($entrypoint === 'profile') {
                self::assertSame($description, $actor->getDescription());
            } else {
                self::assertNotNull($alliance);
                $em->refresh($alliance);
                self::assertSame($description, $alliance->getDescription());
            }
        }

        self::$testSession->setUserById(102);
        $game->resetGameData();
        $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext(
            $game,
            $entrypoint === 'profile' ? ModuleEnum::USERPROFILE : ModuleEnum::ALLIANCE
        );
        $page = $dic->get(TwigPageInterface::class);
        $page->setVar('USER', self::$testSession->getUser());

        if ($entrypoint === 'news') {
            $news = new News();
            $news->setText($description);
            $html = '<div id="description">' . new NewsItem($dic->get(ParserWithImageInterface::class), $news)->getText() . '</div>';
        } elseif ($entrypoint === 'profile') {
            request::setMockVars(['uid' => $actorId]);
            $dic->get(UserProfileProvider::class)->setTemplateVariables($context);
            $page->setTemplate('html/view/userprofile.twig');
            $html = $page->render();
        } else {
            self::assertNotSame($alliance?->getId(), self::$testSession->getUser()?->getAlliance()?->getId());
            request::setMockVars(['id' => $alliance?->getId()]);
            $dic->get(AllianceProvider::class)->setTemplateVariables($context);
            $page->setTemplate('html/view/alliance.twig');
            $html = $page->render();
        }

        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $container = $document->getElementById(str_starts_with($entrypoint, 'alliance') ? 'allianceDescription' : 'description');
        self::assertNotNull($container);
        self::assertSame(0, $container->getElementsByTagName('script')->length);
        if ($valid) {
            $image = $container->getElementsByTagName('img')->item(0);
            self::assertNotNull($image);
            self::assertSame('https://example.test/image.png?a=1&b=2', $image->getAttribute('src'));
            self::assertSame(2, $image->attributes->length);
            self::assertSame('Testbild', $container->getElementsByTagName('strong')->item(0)?->textContent);
        } else {
            self::assertSame(0, $container->getElementsByTagName('img')->length);
            self::assertStringContainsString($description, $container->textContent);
        }
    }

    public static function descriptions(): iterable
    {
        foreach (['profile', 'alliance-create', 'alliance-edit', 'news'] as $entrypoint) {
            yield $entrypoint . '-attack' => [$entrypoint, false];
            yield $entrypoint . '-valid' => [$entrypoint, true];
        }
    }
}
