<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\TopicSettings;

use AccessViolation;
use AllianceTopic;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class TopicSettings implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOPIC_SETTINGS';

    private $topicSettingsRequest;

    public function __construct(
        TopicSettingsRequestInterface $topicSettingsRequest
    ) {
        $this->topicSettingsRequest = $topicSettingsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $topicId = $this->topicSettingsRequest->getTopicId();

        $topic = new AllianceTopic($topicId);
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Thema bearbeiten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/alliancemacros.xhtml/topic_settings');
        $game->setTemplateVar('TOPIC', $topic);
    }
}
