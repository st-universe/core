<?php

namespace Stu\Module\Game\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStepInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;

final class TutorialProvider
{
    public function __construct(private TutorialStepRepositoryInterface $tutorialStepRepository) {}

    public function setTemplateVariables(
        ViewContext $viewContext,
        GameControllerInterface $game
    ): void {

        /** @var array<int, array{elementIds: array<string>, title: string, text: string}> */
        $tutorialSteps = [
            ['elementIds' => ['colsurface', 'submenu'], 'title' => 'Oberfläche und Kolonieinformationen', 'text' => 'Willkommen auf der Seite deiner Kolonie. Hier links siehst du die Oberfläche deiner Kolo mit allen Gebäuden bla bla bla. Hier findest du wichtige Informationen zu deiner Kolonie. Blub'],
            ['elementIds' => ['colonystorage'], 'title' => 'Kolonielager', 'text' => 'Ja und hier ist dein Lager mit allem Krams'],
            ['elementIds' => ['colmenubutton_1'], 'title' => 'Baumenü', 'text' => 'Klick mal aufs Baumenü und danach auf weiter. Dann können wir mal endlich was bauen.', 'innerUpdate' => 'switchColonySubmenu'],
            ['elementIds' => ['Baumaterialfabrik'], 'title' => 'Bau ne Baumaterialfabrik', 'text' => 'BM Fabriken sind mega dufte am anfang. Klick sie an und dann bau eine auf einem Gebäude auf deiner Oberfläche. Denk dran auf weiter zu klicken.', 'innerUpdate' => 'openBuildingInfo', 'fallbackIndex' => 2],
            ['elementIds' => ['buildinginfo'], 'title' => 'Gebäudeinformationen', 'text' => 'Hier siehst du alle Infos zu deinem Gebäude. Klick mal weiter.', 'fallbackIndex' => 2],
            ['elementIds' => ['colsurface'], 'title' => 'Bau jetzt endlich!', 'text' => 'Ja genau hier auf irgendeinem Feld das hier passend erscheint.', 'innerUpdate' => 'fieldMouseClick', 'fallbackIndex' => 2],
            ['elementIds' => ['colmenubutton_2'], 'title' => 'Infomenü', 'text' => 'Naja test']
        ];

        $user = $game->getUser();
        if ($user->getTutorials()->isEmpty()) {
            return;
        }

        $tutorialSteps = $this->tutorialStepRepository->findByUserAndViewContext(
            $game->getUser(),
            $viewContext
        );

        $currentStep = current($tutorialSteps);
        if (!$currentStep) {
            return;
        }

        $payloadArray = array_map(fn(TutorialStepInterface $tutorialStep) => $tutorialStep->getPayload(), $tutorialSteps);

        $game->addExecuteJS(sprintf(
            "updateTutorialStep('%s', %d);",
            json_encode($payloadArray),
            $currentStep->getId()
        ), GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
