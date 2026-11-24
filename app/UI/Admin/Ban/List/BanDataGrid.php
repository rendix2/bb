<?php declare(strict_types=1);

namespace App\UI\Admin\Ban\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\BanEntity;
use Contributte\Datagrid\Datagrid;
use DateTimeImmutable;
use Nette\Localization\Translator;

/**
 * The bans of the administration, the newest first: edit and lift are pages, the grid changes nothing.
 */
class BanDataGrid
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator $translator,
    ) {
    }

    public function create() : Datagrid
    {
        $now = new DateTimeImmutable();
        $grid = new Datagrid();
        $grid->setPrimaryKey('id');
        $grid->setDefaultPerPage(20);
        $grid->setTranslator($this->translator);
        $grid->setDataSource(
            $this->em->getRepository(BanEntity::class)
                ->createQueryBuilder('_b')
                ->select('_b', '_u')
                ->leftJoin('_b.user', '_u'),
        );
        $grid->setDefaultSort(['createdAt' => 'DESC']);

        $grid->addColumnText('target', 'admin-ban-list.target')
            ->setRenderer(fn (BanEntity $ban) : string => implode(', ', array_filter([$ban->user?->username, $ban->email, $ban->ipAddress], static fn (?string $part) : bool => $part !== null)));
        $grid->addColumnText('scope', 'admin-ban-list.scope')
            ->setRenderer(fn (BanEntity $ban) : string => $this->translator->translate($ban->scope->translationKey()));
        $grid->addColumnText('reason', 'admin-ban-list.reason');
        $grid->addColumnDateTime('expiresAt', 'admin-ban-list.expiresAt')
            ->setFormat('j. n. Y H:i')
            ->setReplacement(['' => $this->translator->translate('admin-ban-list.forever')]);
        $grid->addColumnText('state', 'admin-ban-list.state')
            ->setRenderer(fn (BanEntity $ban) : string => $this->translator->translate($ban->isActive($now) ? 'admin-ban-list.active' : 'admin-ban-list.expired'));
        $grid->addColumnDateTime('createdAt', 'admin-ban-list.createdAt')
            ->setFormat('j. n. Y H:i')
            ->setSortable();

        $grid->addAction('edit', 'admin-ban-list.edit', ':Admin:Ban:Edit:default')
            ->setIcon('edit')
            ->setClass('btn btn-xs btn-outline-primary ban-edit-link');
        $grid->addAction('delete', 'admin-ban-list.delete', ':Admin:Ban:Delete:default')
            ->setIcon('trash')
            ->setClass('btn btn-xs btn-outline-danger ban-delete-link');

        return $grid;
    }
}
