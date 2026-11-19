<?php declare(strict_types=1);

namespace App\UI\Forum\Category\View;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use Nette\Application\UI\Presenter;

/**
 * A category: its subcategories and its forums (active ones only).
 */
class ViewPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $uuid) : void
    {
        $category = $this->em->getRepository(CategoryEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($category === null || !$category->active) {
            $this->error('Category was not found.');
        }

        $this->template->category = $category;
        $this->template->subcategories = $this->em->getRepository(CategoryEntity::class)
            ->findBy(['parent' => $category, 'active' => true], ['sortOrder' => 'ASC']);
        $this->template->forums = $this->em->getRepository(ForumEntity::class)
            ->findBy(['category' => $category, 'parent' => null, 'active' => true], ['sortOrder' => 'ASC']);
    }

}
