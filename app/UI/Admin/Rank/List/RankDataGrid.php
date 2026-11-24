<?php declare(strict_types=1);

namespace App\UI\Admin\Rank\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;
use Contributte\Datagrid\Datagrid;
use Nette\Localization\Translator;

/**
 * The ranks of the administration: by posts (by the minimum) and special ones; edit and delete are pages.
 */
class RankDataGrid
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
        $grid->setDefaultPerPage(50);
        $grid->setTranslator($this->translator);
        $grid->setDataSource(
            $this->em->getRepository(RankEntity::class)
                ->createQueryBuilder('_r')
                ->addSelect('CASE WHEN _r.minPosts IS NULL THEN 1 ELSE 0 END AS HIDDEN special'),
        );
        $grid->setDefaultSort(['minPosts' => 'ASC']);

        $grid->addColumnText('name', 'admin-rank-list.name')
            ->setSortable();
        $grid->addColumnText('minPosts', 'admin-rank-list.minPosts')
            ->setSortable()
            ->setRenderer(fn (RankEntity $rank) : string => $rank->minPosts !== null ? (string) $rank->minPosts : $this->translator->translate('admin-rank-list.special'));
        $grid->addColumnText('image', 'admin-rank-list.image')
            ->setRenderer(fn (RankEntity $rank) : string => $this->translator->translate($rank->image !== null ? 'admin-rank-list.yes' : 'admin-rank-list.no'));
        $grid->addColumnNumber('users', 'admin-rank-list.users')
            ->setRenderer(fn (RankEntity $rank) : string => (string) $this->em->getRepository(UserEntity::class)->count(['rank' => $rank]));

        $grid->addAction('edit', 'admin-rank-list.edit', ':Admin:Rank:Edit:default')
            ->setIcon('edit')
            ->setClass('btn btn-xs btn-outline-primary rank-edit-link');
        $grid->addAction('delete', 'admin-rank-list.delete', ':Admin:Rank:Delete:default')
            ->setIcon('trash')
            ->setClass('btn btn-xs btn-outline-danger rank-delete-link');

        return $grid;
    }
}
