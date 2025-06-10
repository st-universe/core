<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Category;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactoryInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class Category implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CATEGORY';

    public function __construct(private CategoryRequestInterface $categoryRequest, private DatabaseCategoryRepositoryInterface $databaseCategoryRepository, private DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory, private LayerRepositoryInterface $layerRepository) {}

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
                self::VIEW_IDENTIFIER,
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
        //only layers, that are known by user
        $layers = $this->layerRepository->getKnownByUser($game->getUser());

        if ($layers === []) {
            return;
        }

        $layerId = request::getInt('layerid');
        if ($layerId === 0) {
            $layer = current($layers);
        } else {
            if (!array_key_exists($layerId, $layers)) {
                throw new SanityCheckException('user tried to access unknown layer');
            }

            $layer = $layers[$layerId];
        }
        $game->setTemplateVar('LAYERID', $layer->getId());


        $game->setViewTemplate('html/database/databasecategory.twig');
        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar(
            'CATEGORY',
            $this->databaseCategoryWrapperFactory->createDatabaseCategoryWrapper($category, $game->getUser(), $layer->getId())
        );
    }
}
