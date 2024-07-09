<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;

final class Category implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CATEGORY';

    public function __construct(private CategoryRequestInterface $categoryRequest, private DatabaseCategoryRepositoryInterface $databaseCategoryRepository, private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $category_id = $this->categoryRequest->getCategoryId();

        /**
         * @var DatabaseCategoryInterface $category
         */
        $category = $this->databaseCategoryRepository->find($category_id);

        $category_description = $category->getDescription();

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d',
                static::VIEW_IDENTIFIER,
                $category_id
            ),
            $category_description
        );
        $game->setPageTitle(
            sprintf(
                _('/ Datenbank: %s'),
                $category_description
            )
        );
        $game->setViewTemplate('html/database/databasecategory.twig');
        $game->setTemplateVar(
            'CATEGORY',
            $this->databaseCategoryTalFactory->createDatabaseCategoryTal($category, $game->getUser())
        );
    }
}
