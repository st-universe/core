<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class TutorialProvider implements GuiComponentProviderInterface
{
    public function __construct(private UserTutorialRepositoryInterface $userTutorial) {}

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $tutorialSteps = [
            ['elementIds' => ['colsurface', 'submenu'], 'title' => 'Oberfläche und Kolonieinformationen', 'text' => 'Willkommen auf der Seite deiner Kolonie. Hier links siehst du die Oberfläche deiner Kolo mit allen Gebäuden bla bla bla. Hier findest du wichtige Informationen zu deiner Kolonie. Blub'],
            ['elementIds' => ['colonystorage'], 'title' => 'Kolonielager', 'text' => 'Ja und hier ist dein Lager mit allem Krams'],
            ['elementIds' => ['colmenubutton_1'], 'title' => 'Baumenü', 'text' => 'Klick mal aufs Baumenü und danach auf weiter. Dann können wir mal endlich was bauen.', 'innerUpdate' => 'switchColonySubmenu'],
            ['elementIds' => ['Baumaterialfabrik'], 'title' => 'Bau ne Baumaterialfabrik', 'text' => 'BM Fabriken sind mega dufte am anfang. Klick sie an und dann bau eine auf einem Gebäude auf deiner Oberfläche. Denk dran auf weiter zu klicken.', 'innerUpdate' => 'openBuildingInfo', 'fallbackIndex' => 2],
            ['elementIds' => ['buildinginfo'], 'title' => 'Gebäudeinformationen', 'text' => 'Hier siehst du alle Infos zu deinem Gebäude. Klick mal weiter.', 'fallbackIndex' => 2],
            ['elementIds' => ['colsurface'], 'title' => 'Bau jetzt endlich!', 'text' => 'Ja genau hier auf irgendeinem Feld das hier passend erscheint.', 'innerUpdate' => 'fieldMouseClick', 'fallbackIndex' => 2],
            ['elementIds' => ['colmenubutton_2'], 'title' => 'Infomenü', 'text' => 'Naja test']

        ];

        $tutorial = $this->userTutorial->findByUserAndModule($game->getUser(), 'colony');
        if ($tutorial != null) {

            $game->setTemplateVar(
                'TUTORIAL',
                json_encode($tutorialSteps)
            );
            $game->setTemplateVar('STEP', $tutorial->getStep());
        }
    }
}
