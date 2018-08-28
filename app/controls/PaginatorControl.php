<?php

namespace App\Controls;

use Dibi\Fluent;
use Nette\Application\UI\Control;
use Nette\Utils\Paginator;

/**
 * Class PaginatorControl
 *
 * @package App\controls\forms
 */
class PaginatorControl extends Control
{
    /**
     * items per page
     *
     * @var int $itemsPerPage
     */
    private $itemsPerPage;

    /**
     * items around
     *
     * @var int $itemsAround
     */
    private $itemsAround;

    /**
     * paginator
     *
     * @var Paginator $paginator
     */
    private $paginator;

    /**
     * count
     *
     * @var int $count
     */
    private $count;

    /**
     * data
     *
     * @var Fluent $data
     */
    private $data;

    /**
     * PaginatorControl constructor.
     *
     * @param Fluent &$data
     * @param int    $itemsPerPage
     * @param int    $itemsAround
     * @param int    $page
     *
     * @api
     */
    public function __construct(
        Fluent $data,
        $itemsPerPage,
        $itemsAround,
        $page
    ) {
        parent::__construct();

        $this->itemsPerPage = $itemsPerPage;
        $this->itemsAround  = $itemsAround;

        $this->paginator = new Paginator();
        $this->paginator->setItemsPerPage($this->itemsPerPage);
        $this->paginator->setPage($page);

        $this->data  = $data;
        $this->count = $this->data->count();
        $this->paginator->setItemCount($this->count);
        $data->limit($this->paginator->getLength())->offset($this->paginator->getOffset());
    }

    /**
     * PaginatorControl destructor.
     *
     * @api
     */
    public function __destruct()
    {
        $this->itemsPerPage = null;
        $this->itemsAround  = null;
        $this->count        = null;
        $this->paginator    = null;
        $this->data         = null;
    }

    /**
     * returns count
     *
     * @return int
     */
    final public function getCount()
    {
        return $this->count;
    }

    /**
     * returns paginator
     *
     * @return Paginator
     */
    final public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * renders paginator
     *
     * @api
     */
    final public function render()
    {
        $template = $this->template;
        $sep      = DIRECTORY_SEPARATOR;

        $template->setFile(__DIR__ . $sep . 'templates' . $sep . 'paginator' . $sep . 'paginator.latte');

        $presenter = $this->getPresenter();
        $params    = $presenter->getParameters();
        unset($params['page']);

        $template->link   = ':' . $presenter->getName() . ':' . $presenter->getAction();
        $template->params = $params;

        // some black magic to make nicer paginator
        $left                = $this->paginator->page - $this->itemsAround >= 1 ? $this->getPaginator()->page - $this->itemsAround : 1; // start
        $right               = $this->paginator->page + $this->itemsAround <= $this->getPaginator()->getPageCount() ? $this->getPaginator()->page + $this->itemsAround : $this->getPaginator()->getPageCount(); // end
        $template->arround   = $this->itemsAround;
        $template->paginator = $this->paginator;
        $template->left      = $left;
        $template->right     = $left === 1 && $this->paginator->getPageCount() > $this->itemsAround ? $this->itemsAround * 2 + 1 : $right;

        // render now!
        $template->render();
    }
}
