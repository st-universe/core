<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider\Message;

use request;
use RuntimeException;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ClassicStyleProvider implements ViewComponentProviderInterface
{
    private const int PMLIMITER = 6;

    public function __construct(
        private readonly PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        private readonly PrivateMessageRepositoryInterface $privateMessageRepository,
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly PagingFactory $pagingFactory
    ) {}

    #[\Override]
    public function setTemplateVariables(ViewControllerContext $game): void
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

        if ($mark % self::PMLIMITER !== 0 || $mark < 0) {
            $mark = 0;
        }

        $game->appendNavigationPart(
            sprintf('%s?pmcat=%d', ModuleEnum::PM->getPhpPage(), $category->getId()),
            sprintf(_('Ordner: %s'), $category->getDescription())
        );

        $game->setTemplateVar('CATEGORY', $category);
        $game->setTemplateVar(
            'PM_LIST',
            array_map(
                fn (PrivateMessage $message): PrivateMessageListItem => new PrivateMessageListItem(
                    $this->privateMessageRepository,
                    $this->contactRepository,
                    $this->userSettingsProvider,
                    $message,
                    $game->getUser()
                ),
                $this->privateMessageRepository->getByUserAndFolder(
                    $userId,
                    $category->getId(),
                    $mark,
                    self::PMLIMITER
                )
            )
        );
        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $this->privateMessageRepository->getAmountByFolder($category),
            self::PMLIMITER,
            $mark,
            sprintf('?pmcat=%d', $category->getId())
        ));
    }
}
