<?php

namespace App\Models;

use dibi;
use Dibi\Fluent;
use Dibi\Row;
use Dibi\Connection;
use Nette\Caching\IStorage;
use Zebra_Mptt;

/**
 * Description of ForumManager
 *
 * @author rendix2
 */
class ForumsManager extends Crud\CrudManager
{    
    /**
     * @var Zebra_Mptt $mptt
     */
    private $mptt;

    public function __construct(Connection $dibi, IStorage $storage)
    {
        parent::__construct($dibi, $storage);
        
        $this->mptt = new Zebra_Mptt(
            $dibi,
            $this->getTable(),
            $this->getPrimaryKey(),
            'forum_name',
            'forum_left',
            'forum_right',
            'forum_parent_id'
        );        
    }
    
    /**
     * 
     * @return Zebra_Mptt
     */
    public function getMptt()
    {
        return $this->mptt;
    }

    /**
     * @param int $category_id
     *
     * @return Fluent
     */
    public function getByCategory($category_id)
    {
        return $this->getAllFluent()
            ->where('[forum_category_id] = %i', $category_id);
    }

    /**
     * @param int $forum_id
     *
     * @return array
     */
    public function getByParent($forum_id)
    {
        return $this->getAllFluent()
            ->where('[forum_parent_id] = %i', $forum_id)
            ->fetchAll();
    }

    /**
     * @param int $forum_id
     *
     * @return Row|false
     */
    public function getParentForumsByForumId($forum_id)
    {
        return $this->dibi
            ->select('f2.*')
            ->from($this->getTable())
            ->as('f1')
            ->innerJoin($this->getTable())
            ->as('f2')
            ->on('[f1.forum_parent_id] = [f2.forum_id]')
            ->where('[f1.forum_id] = %i', $forum_id)
            ->fetch();
    }
    
    /**
     * @param int $category_id
     *
     * @return Row[]
     */
    public function getForumsFirstLevel($category_id)
    {
        return $this->getAllFluent()
            ->where('[forum_category_id] = %i', $category_id)
            ->where('[forum_active] = %i', 1)
            ->where('[forum_parent_id] = %i', 0)
            ->orderBy('forum_order', dibi::ASC)
            ->fetchAll();
    }

    /**
     * @param iterable $forums
     * @param int      $forum_parent_id
     *
     * @return array
     */
    public function createForums($forums, $forum_parent_id)
    {
        $result = [];

        foreach ($forums as $forum) {
            if ($forum->forum_parent_id === $forum_parent_id) {
                $result[$forum->forum_id]           = $forum;
                $result[$forum->forum_id]['childs'] = $this->createForums(
                    $forums,
                    $forum->forum_id
                );
            }
        }

        return $result;
    }
    
    /**
     *
     */
    public function move()
    {
    }
    
    public function getBreadCrumb($forum_id)
    {
        $forums = $this->mptt->getBreadCrumb($forum_id);
        
        $bcForum = [];
        
        foreach ($forums as $forum) {
            $tmp = [];
            $tmp['link']   = 'Forum:default';
            $tmp['params'] = ['forum_id' => $forum->forum_id];
            $tmp['text']   = $forum->forum_name;
            $tmp['t']      = 0;
            
            $bcForum[] = $tmp;
        }    
        
        return $bcForum;
    }
}
