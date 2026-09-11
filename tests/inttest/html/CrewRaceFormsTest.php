<?php

declare(strict_types=1);

namespace Stu\Html;

use DOMDocument;
use DOMXPath;
use ReflectionProperty;
use Stu\Module\Database\View\ShowCrewRaceModeration\CrewRaceModerationEntry;
use Stu\Orm\Entity\CrewRace;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class CrewRaceFormsTest extends StuTestCase
{
    private function twig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../../src'), ['strict_variables' => true]);
        $twig->addFilter(new TwigFilter('bbcode', static fn (string $value): string => $value));
        return $twig;
    }

    private function race(int $id): CrewRace
    {
        $race = new CrewRace()->setCreatorUserId(42)->setDescription('Test')->setGfxPath('TEST')->setMaleRatio(50);
        new ReflectionProperty(CrewRace::class, 'id')->setValue($race, $id);
        return $race;
    }

    public function testRejectedRaceCanBeEditedAtCreationLimitAndReasonIsEscaped(): void
    {
        $race = $this->race(5)->setAcceptedUserId(7)->setRejectionReason('<script>alert(1)</script>');
        $html = $this->twig()->render('html/user/crewRaceManagement.twig', [
            'OWN_CREW_RACES' => [$race], 'PLAYABLE_FACTIONS' => [], 'OWN_FACTION_ID' => 1,
            'CAN_CREATE_CREW_RACE' => false, 'CREW_RACE_CREATION_REMAINING' => 0,
            'EDIT_CREW_RACE' => $race, 'SESSIONSTRING' => 'session',
            'FORM_CREW_RACE_NAME' => 'Test', 'FORM_CREW_RACE_DEFINE' => 'TEST',
            'FORM_CREW_RACE_MALE_RATIO' => 0, 'FORM_CREW_RACE_CHANCE' => 70,
            'FORM_CREW_RACE_SHARED' => false, 'FORM_CREW_RACE_CIVIL' => true,
            'FORM_CREW_RACE_FACTION_IDS' => []
        ]);
        $document = new DOMDocument();
        @$document->loadHTML($html);
        $xpath = new DOMXPath($document);
        self::assertSame(1, $xpath->query('//form[@id="crew-race-form"]//input[@name="crew_race_id" and @value="5"]')->length);
        self::assertSame(1, $xpath->query('//input[@name="B_RESUBMIT_CREW_RACE"]')->length);
        self::assertSame(0, $xpath->query('//a[@href]')->length);
        self::assertSame(12, $xpath->query('//form[@id="crew-race-form"]//input[@type="file"]')->length);
        self::assertSame(1, $xpath->query('//input[@name="crew_race_male_ratio" and @value="0"]')->length);
        self::assertStringContainsString('Erneut einreichen', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function testNonAdminModeratorSeesAllStatesButCanOnlyEditPendingRaces(): void
    {
        $pending = $this->race(1);
        $accepted = $this->race(2)->setAccepted(true)->setAcceptedUserId(7);
        $rejected = $this->race(3)->setAcceptedUserId(7)->setRejectionReason('Bitte Rahmen korrigieren');
        $entry = static fn (CrewRace $race): CrewRaceModerationEntry => new CrewRaceModerationEntry($race, null, []);
        $html = $this->twig()->render('html/database/crewRaceModeration.twig', [
            'PENDING_CREW_RACES' => [$entry($pending)], 'ACCEPTED_CREW_RACES' => [$entry($accepted)],
            'REJECTED_CREW_RACES' => [$entry($rejected)], 'IS_ADMIN' => false,
            'PLAYABLE_FACTIONS' => [], 'SESSIONSTRING' => 'session'
        ]);
        $document = new DOMDocument();
        @$document->loadHTML($html);
        $xpath = new DOMXPath($document);
        self::assertSame(3, $xpath->query('//article')->length);
        self::assertSame(1, $xpath->query('//textarea[@name="rejection_reason"]')->length);
        self::assertSame(1, $xpath->query('//input[@name="B_UPDATE_CREW_RACE_MODERATION"]')->length);
        self::assertSame(0, $xpath->query('//input[@name="crew_race_civil"]')->length);
        self::assertStringContainsString('Bitte Rahmen korrigieren', $html);
    }
}
