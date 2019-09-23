<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class GetNewPrivateMessages extends Action
{
    private $session;

    private $privateMessageFolderRepository;

    public function __construct(
        SessionInterface $session,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->session = $session;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $userId = $this->session->getUser()->getId();

        $pmFolder = [
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
        ];

        return $response->withData(array_map(
            function (int $specialFolderId) use ($userId): array {
                return [
                    'folder_special_id' => $specialFolderId,
                    'new_pm_amount' => $this->privateMessageFolderRepository->getByUserAndSpecial(
                        $userId,
                        $specialFolderId
                    )->getCategoryCountNew()
                ];
            },
            $pmFolder
        ));
    }
}
