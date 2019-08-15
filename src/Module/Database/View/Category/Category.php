<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category;

use DatabaseCategory;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Category implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_CATEGORY';

    private $categoryRequest;

    public function __construct(
        CategoryRequestInterface $categoryRequest
    )
    {
        $this->categoryRequest = $categoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $category = new DatabaseCategory($this->categoryRequest->getCategoryId());

        $category_description = $category->getDescription();

        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1&cat=%d',
                static::VIEW_IDENTIFIER,
                $category->getId(),
            ),
            sprintf(
                _('Datenbank: %s'),
                $category_description
            )
        );
        $game->setPageTitle(
            sprintf(
                _('/ Datenbank: %s'),
                $category_description
            )
        );
        $game->setTemplateFile('html/databasecategory.xhtml');
        $game->setTemplateVar('CATEGORY', $category);
    }
}