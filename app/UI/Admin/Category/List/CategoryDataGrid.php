<?php declare(strict_types=1);

namespace App\UI\Admin\Category\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Localization\Translator;

/**
 * The categories of the administration: edit and delete are pages (the delete one a POST form with CSRF), the grid itself
 * changes nothing.
 */
class CategoryDataGrid
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator $translator,
    ) {
    }

    public function create() : Datagrid
    {
        $grid = new Datagrid();
        $grid->setPrimaryKey('id');
        $grid->setDefaultPerPage(20);
        $grid->setTranslator($this->translator);
        $grid->setDataSource(
            $this->em->getRepository(CategoryEntity::class)->createQueryBuilder('_c'),
        );
        $grid->setDefaultSort(['sortOrder' => 'ASC']);

        $grid->addColumnText('name', 'admin-category-list.name')
            ->setSortable()
            ->setFilterText()
            ->setCondition(function (QueryBuilder $queryBuilder, string $value) : void {
                $queryBuilder->andWhere('LOWER(_c.name) LIKE :name')
                    ->setParameter('name', '%' . addcslashes(mb_strtolower($value), '%_\\') . '%');
            });
        $grid->addColumnNumber('sortOrder', 'admin-category-list.sortOrder')
            ->setSortable();
        $grid->addColumnText('active', 'admin-category-list.active')
            ->setRenderer(fn (CategoryEntity $category) : string => $this->translator->translate($category->active ? 'admin-category-list.yes' : 'admin-category-list.no'));
        $grid->addColumnNumber('forums', 'admin-category-list.forums')
            ->setRenderer(fn (CategoryEntity $category) : string => (string) $category->forums->count());

        $grid->addAction('edit', 'admin-category-list.edit', ':Admin:Category:Edit:default')
            ->setIcon('edit')
            ->setClass('btn btn-xs btn-outline-primary category-edit-link');
        $grid->addAction('delete', 'admin-category-list.delete', ':Admin:Category:Delete:default')
            ->setIcon('trash')
            ->setClass('btn btn-xs btn-outline-danger category-delete-link');

        return $grid;
    }
}
