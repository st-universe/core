<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\NewsRepositoryInterface;

final class PostNews implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_POST_NEWS';

    public function __construct(
        private NewsRepositoryInterface $newsRepository,
        private readonly StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin[/color][/b]'));
            return;
        }

        $subject = request::postString('subject');
        $text = request::postString('text');
        $isChangelog = request::postInt('is_changelog');
        $refs = request::postString('refs');

        if ($subject === false || $text === false || $subject === '' || $text === '') {
            $game->getInfo()->addInformation(_('Bitte fülle alle Felder aus'));
            return;
        }


        $news = $this->newsRepository->prototype();
        $news->setSubject($subject);
        $news->setText($text);
        $news->setDate($this->stuTime->time());
        $news->setRefs($refs === false ? '' : $refs);
        $news->setChangelog($isChangelog === 1);

        $this->newsRepository->save($news);

        $game->getInfo()->addInformation(_('Die News wurde gespeichert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
