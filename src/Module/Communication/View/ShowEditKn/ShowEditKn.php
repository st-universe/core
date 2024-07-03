<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowEditKn;

use Stu\Exception\AccessViolation;
use Stu\Module\Communication\Action\EditKnPost\EditKnPost;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\KnCharactersRepositoryInterface;

final class ShowEditKn implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'EDIT_KN';

    private ShowEditKnRequestInterface $showEditKnRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private KnCharactersRepositoryInterface $knCharactersRepository;

    public function __construct(
        ShowEditKnRequestInterface $showEditKnRequest,
        KnPostRepositoryInterface $knPostRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        KnCharactersRepositoryInterface $knCharactersRepository
    ) {
        $this->showEditKnRequest = $showEditKnRequest;
        $this->knPostRepository = $knPostRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->knCharactersRepository = $knCharactersRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->showEditKnRequest->getPostId());

        if ($post === null) {
            throw new AccessViolation(sprintf(_('UserId %d tried to edit non-existing kn post'), $game->getUser()->getId()));
        }

        if ($post->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation(sprintf(_('UserId %d tried to edit foreign kn post'), $game->getUser()->getId()));
        }

        $game->setTemplateFile('html/editkn.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        if ($post->getDate() < time() - EditKnPost::EDIT_TIME) {
            $game->addInformation(sprintf(_('Die Zeit zum Editieren ist abgelaufen (%d Sekunden)'), EditKnPost::EDIT_TIME));
        } else {
            $game->appendNavigationPart(
                sprintf('comm.php?%s=1&knid=%d', static::VIEW_IDENTIFIER, $post->getId()),
                _('Beitrag bearbeiten')
            );
            $game->setPageTitle(_('Beitrag bearbeiten'));

            $characterEntities = $this->knCharactersRepository->findBy(['knPost' => $post->getId()]);
            $characterIds = array_map(function ($characterEntity): int {
                return $characterEntity->getUserCharacters()->getId();
            }, $characterEntities);
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
