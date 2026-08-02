<?php declare(strict_types=1);

namespace App\UI\Forum\Forum\Rules;

use App\Controls\BreadCrumbControl;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ForumRepository;
use App\services\BreadcrumbService;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;

class RulesPresenter extends Presenter
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly ForumRepository    $forumRepository,

        private readonly BreadcrumbService $breadcrumbService,

        private readonly Translator $translator,
    )
    {
    }

    public function renderDefault(int $category_id, int $forum_id): void
    {
        $categoryEntity = $this->categoryRepository
            ->findOneBy(
                [
                    'id' => $category_id
                ]
            );

        if ($categoryEntity === null) {
            $this->error('Category was not found.');
        }

        if ($categoryEntity->active === false) {
            $this->error('Category is not active.');
        }

        $forumEntity = $this->forumRepository
            ->findOneBy(
                [
                    'id' => $forum_id,
                ]
            );

        if ($forumEntity === null) {
            $this->error('Forum not found');
        }

        if ($forumEntity->rules === null) {
            $this->flashMessage('No forum rules.', 'warning');
        }

        $this->template->forumEntity = $forumEntity;
    }

    protected function createComponentBreadCrumbRules(): BreadCrumbControl
    {
        $breadCrumb = array_merge(
            [['link' => 'Index:default', 'text' => 'menu_index']],
            $this->breadcrumbService->getCategoryBreadCrumb((int) $this->getParameter('category_id')),
            $this->breadcrumbService->getForumBreadCrumb((int) $this->getParameter('forum_id')),
            [['link' => 'Forum:rules', 'text' => 'forum_rules', 'params' => [$this->getParameter('category_id'), $this->getParameter('forum_id')]]]
        );

        return new BreadCrumbControl($breadCrumb, $this->translator);
    }

}