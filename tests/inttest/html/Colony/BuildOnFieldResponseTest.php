<?php

declare(strict_types=1);

namespace Stu\Html\Colony;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Colony\Action\BuildOnField\BuildOnField;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\AccessCheck;
use Stu\Module\Control\Component\Action\ActionContextFactory;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ContextFactory;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\Component\TutorialProvider;
use Stu\Module\Control\Component\View\ViewContextFactory;
use Stu\Module\Control\Component\ViewExecution;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameData;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Control\JavascriptExecution;
use Stu\Module\Control\JavascriptExecutionInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\TwigTestCase;

final class BuildOnFieldResponseTest extends TwigTestCase
{
    private CallbackExecution $callbackExecution;
    private ViewExecution $viewExecution;
    private SessionStringFactory $sessionStringFactory;
    private User $user;

    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
        $session = self::$testSession;
        self::assertNotNull($session);
        $session->setUserById(101);
        $user = $session->getUser();
        self::assertNotNull($user);
        $this->user = $user;

        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $stuTime = new StuTime();
        $sessionStringRepository = $container->get(SessionStringRepositoryInterface::class);
        $this->sessionStringFactory = new SessionStringFactory($sessionStringRepository, $stuTime);
        $twigPage = $container->get(TwigPageInterface::class);
        $javascriptExecution = $container->get(JavascriptExecutionInterface::class);
        $contextFactory = new ContextFactory($twigPage, $javascriptExecution, $this->sessionStringFactory);
        $viewContextFactory = new ViewContextFactory(
            $contextFactory,
            $twigPage,
            $container->get(GameSetupInterface::class),
            $javascriptExecution,
            $this->sessionStringFactory
        );
        $accessCheck = new AccessCheck(
            $sessionStringRepository,
            $container->get(StuConfigInterface::class),
            $container->get(GameUserRoleCheckerInterface::class),
            $session
        );
        $controllerDiscovery = $container->get(ControllerDiscoveryInterface::class);
        $this->callbackExecution = new CallbackExecution(
            $controllerDiscovery,
            $accessCheck,
            new ActionContextFactory($contextFactory),
            $stuTime,
            $entityManager
        );
        $this->viewExecution = new ViewExecution(
            $controllerDiscovery,
            $accessCheck,
            $viewContextFactory,
            $container->get(TutorialProvider::class),
            $stuTime,
            $entityManager
        );
    }

    public function testRepeatedRejectedBuildsAndConsumedSessionStringKeepInformationResponse(): void
    {
        $container = $this->getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $field = $container->get(PlanetFieldRepositoryInterface::class)->find(34);
        self::assertNotNull($field);
        $colony = $field->getHost();
        self::assertInstanceOf(Colony::class, $colony);
        $building = $container->get(BuildingRepositoryInterface::class)->find(12345);
        self::assertNotNull($building);
        $building->setEpsCost(100);
        $fieldType = new PlanetFieldTypeBuilding()->setFieldTypeId($field->getFieldType());
        $entityManager->getClassMetadata(PlanetFieldTypeBuilding::class)->setFieldValue($fieldType, 'building', $building);
        $building->getBuildableFields()->set($field->getFieldType(), $fieldType);
        $entityManager->persist($fieldType);
        $entityManager->flush();

        $initialSessionString = $this->sessionStringFactory->createSessionString($this->user);
        $firstResponse = $this->renderBuildAttempt($initialSessionString);
        self::assertStringContainsString('Zum Bau wird 100 Energie benötigt - Vorhanden ist nur 96', $firstResponse);
        $secondSessionString = $this->getSessionStringFromResponse($firstResponse);
        self::assertNotSame($initialSessionString, $secondSessionString);

        $secondResponse = $this->renderBuildAttempt($secondSessionString);
        self::assertStringContainsString('Zum Bau wird 100 Energie benötigt - Vorhanden ist nur 96', $secondResponse);
        self::assertNotSame($secondSessionString, $this->getSessionStringFromResponse($secondResponse));

        $replayedResponse = $this->renderBuildAttempt($initialSessionString);
        self::assertStringNotContainsString('Zum Bau wird', $replayedResponse);
        $recoveredSessionString = $this->getSessionStringFromResponse($replayedResponse);
        self::assertNotSame($initialSessionString, $recoveredSessionString);

        $recoveredResponse = $this->renderBuildAttempt($recoveredSessionString);
        self::assertStringContainsString('Zum Bau wird 100 Energie benötigt - Vorhanden ist nur 96', $recoveredResponse);
        $entityManager->refresh($field);
        self::assertNull($field->getBuilding());
        self::assertSame(96, $colony->getChangeable()->getEps());
    }

    private function renderBuildAttempt(string $sessionString): string
    {
        $container = $this->getContainer();
        JavascriptExecution::reset();
        $container->get(TwigPageInterface::class)->resetVariables();
        request::setMockVars([
            BuildOnField::ACTION_IDENTIFIER => '1',
            ShowInformation::VIEW_IDENTIFIER => '1',
            'fid' => '34',
            'buildingid' => '12345',
            'sstr' => $sessionString
        ]);

        $gameData = new GameData();
        $gameRequest = new GameRequest();
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getGameData')->andReturn($gameData);
        $game->shouldReceive('getGameRequest')->andReturn($gameRequest);
        $game->shouldReceive('getInfo')->andReturn($gameData->gameInformations);
        $game->shouldReceive('getUser')->andReturn($this->user);
        $game->shouldReceive('hasUser')->andReturnTrue();

        $this->callbackExecution->execute(ModuleEnum::COLONY, $game);
        $this->viewExecution->execute(ModuleEnum::COLONY, $game);

        self::assertSame([], $gameRequest->getErrors());
        self::assertSame(ShowInformation::VIEW_IDENTIFIER, $gameRequest->getView());
        self::assertSame('html/systeminformationAndJsBeforeAfterRender.twig', $gameData->macro);
        self::assertSame([], $gameData->siteNavigation);

        return $container->get(GameTwigRendererInterface::class)->render($game, $game->getUser());
    }

    private function getSessionStringFromResponse(string $response): string
    {
        self::assertSame(1, preg_match("/sstr = '([a-f0-9]{30})';/", $response, $matches));

        return $matches[1];
    }
}
