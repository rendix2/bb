<?php declare(strict_types=1);

namespace App\UI\Admin\Group\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\GroupEntity;
use App\Model\Entity\UserGroupMemberEntity;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Localization\Translator;

/**
 * The groups of the administration: edit and delete are pages, the grid changes nothing.
 */
class GroupDataGrid
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
        $grid->setDataSource($this->em->getRepository(GroupEntity::class)->createQueryBuilder('_g'));
        $grid->setDefaultSort(['name' => 'ASC']);

        $grid->addColumnText('name', 'admin-group-list.name')
            ->setSortable()
            ->setFilterText()
            ->setCondition(function (QueryBuilder $queryBuilder, string $value) : void {
                $queryBuilder->andWhere('LOWER(_g.name) LIKE :name')
                    ->setParameter('name', '%' . addcslashes(mb_strtolower($value), '%_\\') . '%');
            });
        $grid->addColumnNumber('members', 'admin-group-list.members')
            ->setRenderer(fn (GroupEntity $group) : string => (string) $this->em->getRepository(UserGroupMemberEntity::class)->count(['group' => $group]));

        $grid->addAction('edit', 'admin-group-list.edit', ':Admin:Group:Edit:default')
            ->setIcon('edit')
            ->setClass('btn btn-xs btn-outline-primary group-edit-link');
        $grid->addAction('delete', 'admin-group-list.delete', ':Admin:Group:Delete:default')
            ->setIcon('trash')
            ->setClass('btn btn-xs btn-outline-danger group-delete-link');

        return $grid;
    }
}
