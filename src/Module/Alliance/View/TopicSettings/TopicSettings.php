<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\TopicSettings;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class TopicSettings implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const VIEW_IDENTIFIER = 'SHOW_TOPIC_SETTINGS';

    private TopicSettingsRequestInterface $topicSettingsRequest;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    public function __construct(
        TopicSettingsRequestInterface $topicSettingsRequest,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->topicSettingsRequest = $topicSettingsRequest;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $topicId = $this->topicSettingsRequest->getTopicId();

        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null || $topic->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Thema bearbeiten'));
        $game->setMacroInAjaxWindow('html/alliancemacros.xhtml/topic_settings');
        $game->setTemplateVar('TOPIC', $topic);
    }
}
