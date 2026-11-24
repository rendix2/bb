<?php declare(strict_types=1);

namespace App\UI\Admin\Forum\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Localization\Translator;

/**
 * The forums of the administration (also subforums and inactive ones): edit and delete are pages (the delete one a POST form
 * with CSRF), the grid itself changes nothing.
 */
class ForumDataGrid
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
            $this->em->getRepository(ForumEntity::class)
                ->createQueryBuilder('_f')
                ->select('_f', '_c', '_p')
                ->leftJoin('_f.category', '_c')
                ->leftJoin('_f.parent', '_p'),
        );
        $grid->setDefaultSort(['sortOrder' => 'ASC']);

        $grid->addColumnText('name', 'admin-forum-list.name')
            ->setSortable()
            ->setFilterText()
            ->setCondition(function (QueryBuilder $queryBuilder, string $value) : void {
                $queryBuilder->andWhere('LOWER(_f.name) LIKE :name')
                    ->setParameter('name', '%' . addcslashes(mb_strtolower($value), '%_\\') . '%');
            });
        $grid->addColumnText('place', 'admin-forum-list.place')
            ->setRenderer(fn (ForumEntity $forum) : string => $forum->parent !== null
                ? $this->translator->translate('admin-forum-list.subforumOf', ['forum' => $forum->parent->name])
                : ($forum->category->name ?? ''));
        $grid->addColumnNumber('sortOrder', 'admin-forum-list.sortOrder')
            ->setSortable();
        $grid->addColumnText('active', 'admin-forum-list.active')
            ->setRenderer(fn (ForumEntity $forum) : string => $this->translator->translate($forum->active ? 'admin-forum-list.yes' : 'admin-forum-list.no'));
        $grid->addColumnNumber('topics', 'admin-forum-list.topics')
            ->setRenderer(fn (ForumEntity $forum) : string => (string) $forum->topics->count());

        $grid->addAction('edit', 'admin-forum-list.edit', ':Admin:Forum:Edit:default')
            ->setIcon('edit')
            ->setClass('btn btn-xs btn-outline-primary forum-edit-link');
        $grid->addAction('access', 'admin-forum-list.access', ':Admin:Forum:Access:default')
            ->setIcon('user-shield')
            ->setClass('btn btn-xs btn-outline-secondary forum-access-link');
        $grid->addAction('delete', 'admin-forum-list.delete', ':Admin:Forum:Delete:default')
            ->setIcon('trash')
            ->setClass('btn btn-xs btn-outline-danger forum-delete-link');

        return $grid;
    }
}
