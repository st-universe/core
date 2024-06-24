<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use request;
use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Component\Game\GameEnum;

final class MessageProvider implements ViewComponentProviderInterface
{
    private const PMLIMITER = 6;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private IgnoreListRepositoryInterface $ignoreListRepository;

    private ContactRepositoryInterface $contactRepository;

    private PrivateMessageUiFactoryInterface $privateMessageUiFactory;

    private ComponentLoaderInterface $componentLoader;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        IgnoreListRepositoryInterface $ignoreListRepository,
        PrivateMessageUiFactoryInterface $privateMessageUiFactory,
        ContactRepositoryInterface $contactRepository,
        ComponentLoaderInterface $componentLoader
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->contactRepository = $contactRepository;
        $this->privateMessageUiFactory = $privateMessageUiFactory;
        $this->componentLoader = $componentLoader;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $categoryId = request::indInt('pmcat');

        $mark = request::indInt('mark');

        if ($categoryId === 0) {
            $category = $this->privateMessageFolderRepository->getByUserAndSpecial(
                $userId,
                PrivateMessageFolderTypeEnum::SPECIAL_MAIN
            );
        } else {
            $category = $this->privateMessageFolderRepository->find($categoryId);
            if ($category === null || $category->getUserId() !== $userId || $category->isDeleted()) {
                $category = $this->privateMessageFolderRepository->getByUserAndSpecial(
                    $userId,
                    PrivateMessageFolderTypeEnum::SPECIAL_MAIN
                );
            }
        }

        if ($category === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($mark % static::PMLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $this->privateMessageRepository->getAmountByFolder($category);
        $maxpage = ceil($maxcount / static::PMLIMITER);
        $curpage = floor($mark / static::PMLIMITER);
        $pmNavigation = [];
        if ($curpage != 0) {
            $pmNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $pmNavigation[] = ["page" => "<", "mark" => ($mark - static::PMLIMITER), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $pmNavigation[] = [
                "page" => $i,
                "mark" => ($i * static::PMLIMITER - static::PMLIMITER),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $pmNavigation[] = ["page" => ">", "mark" => ($mark + static::PMLIMITER), "cssclass" => "pages"];
            $pmNavigation[] = ["page" => ">>", "mark" => $maxpage * static::PMLIMITER - static::PMLIMITER, "cssclass" => "pages"];
        }

        $game->appendNavigationPart(
            sprintf('%s?pmcat=%d', ModuleViewEnum::PM->getPhpPage(), $category->getId()),
            sprintf(_('Ordner: %s'), $category->getDescription())
        );

        $game->setTemplateVar('CATEGORY', $category);
        $game->setTemplateVar(
            'PM_LIST',
            array_map(
                fn (PrivateMessageInterface $message): PrivateMessageListItem => new PrivateMessageListItem(
                    $this->privateMessageRepository,
                    $this->contactRepository,
                    $this->ignoreListRepository,
                    $message,
                    $userId
                ),
                $this->privateMessageRepository->getByUserAndFolder(
                    $userId,
                    $category->getId(),
                    $mark,
                    static::PMLIMITER
                )
            )
        );
        $game->setTemplateVar('PM_NAVIGATION', $pmNavigation);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn (PrivateMessageFolderInterface $folder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($folder),
                $this->privateMessageFolderRepository->getOrderedByUser($userId)
            )
        );

        $this->componentLoader->addComponentUpdate(ComponentEnum::PM_NAVLET);
        $game->addExecuteJS("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER);
    }
}
