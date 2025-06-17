<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditKn;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Communication\Action\EditKnPost\EditKnPost;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnCharacterRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class ShowEditKn implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'EDIT_KN';

    public function __construct(
        private ShowEditKnRequestInterface $showEditKnRequest,
        private KnPostRepositoryInterface $knPostRepository,
        private RpgPlotRepositoryInterface $rpgPlotRepository,
        private KnCharacterRepositoryInterface $knCharactersRepository,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->showEditKnRequest->getKnId());

        if ($post === null) {
            throw new AccessViolationException(sprintf(_('UserId %d tried to edit non-existing kn post'), $game->getUser()->getId()));
        }

        if ($post->getUserId() !== $game->getUser()->getId() && !$game->isAdmin()) {
            throw new AccessViolationException(sprintf(_('UserId %d tried to edit foreign kn post'), $game->getUser()->getId()));
        }

        $game->setViewTemplate('html/communication/editKn.twig');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        if ($post->getDate() < $this->stuTime->time() - EditKnPost::EDIT_TIME && !$game->isAdmin()) {
            $game->addInformation(sprintf(_('Die Zeit zum Editieren ist abgelaufen (%d Sekunden)'), EditKnPost::EDIT_TIME));
        } else {
            $game->appendNavigationPart(
                sprintf('comm.php?%s=1&knid=%d', self::VIEW_IDENTIFIER, $post->getId()),
                _('Beitrag bearbeiten')
            );
            $game->setPageTitle(_('Beitrag bearbeiten'));

            $characterEntities = $this->knCharactersRepository->findBy(['knPost' => $post->getId()]);
            $characterIds = array_map(fn($characterEntity): int => $characterEntity->getUserCharacter()->getId(), $characterEntities);
            $characterIdsString = implode(',', $characterIds);

            $game->setTemplateVar('CHARACTER_IDS_STRING', $characterIdsString);

            $game->setTemplateVar(
                'ACTIVE_RPG_PLOTS',
                $this->rpgPlotRepository->getActiveByUser($game->getUser()->getId())
            );
            $game->setTemplateVar('POST', $post);
        }
    }
}
