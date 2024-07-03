<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use request;
use RuntimeException;
use Stu\Component\Game\GameEnum;
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

final class MessageProvider implements ViewComponentProviderInterface
{
    private const int PMLIMITER = 6;

    public function __construct(private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository, private PrivateMessageRepositoryInterface $privateMessageRepository, private IgnoreListRepositoryInterface $ignoreListRepository, private PrivateMessageUiFactoryInterface $privateMessageUiFactory, private ContactRepositoryInterface $contactRepository, private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
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

        if ($mark % self::PMLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $this->privateMessageRepository->getAmountByFolder($category);
        $maxpage = ceil($maxcount / self::PMLIMITER);
        $curpage = floor($mark / self::PMLIMITER);
        $pmNavigation = [];
        if ($curpage != 0) {
            $pmNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $pmNavigation[] = ["page" => "<", "mark" => ($mark - self::PMLIMITER), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $pmNavigation[] = [
                "page" => $i,
                "mark" => ($i * self::PMLIMITER - self::PMLIMITER),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $pmNavigation[] = ["page" => ">", "mark" => ($mark + self::PMLIMITER), "cssclass" => "pages"];
            $pmNavigation[] = ["page" => ">>", "mark" => $maxpage * self::PMLIMITER - self::PMLIMITER, "cssclass" => "pages"];
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
                    self::PMLIMITER
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
