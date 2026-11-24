<?php declare(strict_types=1);

namespace App\UI\Web\User\List;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PostRepository;
use App\Model\Service\UserListService;
use App\Model\Service\UserRankService;
use App\Settings\ForumSettings;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Utils\Paginator;

/**
 * The list of users (slice 4g): only for logged-in users (decision of 2026-11-19; a guest goes to the login and comes back), paginated,
 * sorted by the username, the registration or the post count, filtered by a part of the username (case-insensitive). Public data
 * only: username, avatar, rank, registration, post count (never the name and the surname).
 */
class ListPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly UserListService $userListService,
        private readonly PostRepository $postRepository,
        private readonly UserRankService $userRankService,
        private readonly ForumSettings $forumSettings,
    )
    {
        parent::__construct($em);
    }

    public function actionDefault(int $page = 1, string $sort = UserListService::SortUsername, string $order = 'asc', string $filter = '') : void
    {
        $this->loadViewer();

        if (!in_array($sort, UserListService::Sorts, true) || !in_array($order, ['asc', 'desc'], true)) {
            $this->redirect('this', ['sort' => UserListService::SortUsername, 'order' => 'asc', 'page' => 1]);
        }

        $this['filterForm']->setDefaults(['username' => $filter]);
    }

    public function renderDefault(int $page = 1, string $sort = UserListService::SortUsername, string $order = 'asc', string $filter = '') : void
    {
        $paginator = new Paginator();
        $paginator->setItemsPerPage(max(1, (int) $this->forumSettings->get()['pagination']['itemsPerPage']));
        $paginator->setPage($page);

        $result = $this->userListService->page($this->viewer, trim($filter), $sort, $order === 'desc', $paginator->getOffset(), $paginator->getLength());
        $paginator->setItemCount($result['count']);

        // the rank follows all posts of the user (as on the profile and at his posts in a topic)
        $allPosts = $this->postRepository->countByUsers(array_map(static fn (array $item) : int => $item['user']->id, $result['items']));
        $ranks = [];

        foreach ($result['items'] as $item) {
            $ranks[$item['user']->id] = $this->userRankService->rankOf($item['user'], $allPosts[$item['user']->id] ?? 0);
        }

        $this->getTemplate()->items = $result['items'];
        $this->getTemplate()->ranks = $ranks;
        $this->getTemplate()->paginator = $paginator;
        $this->getTemplate()->sort = $sort;
        $this->getTemplate()->order = $order;
        $this->getTemplate()->filter = $filter;
    }

    /**
     * The filter by the username: a GET form (changes nothing), redirects to the list with the parameter filter from the first page.
     */
    protected function createComponentFilterForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setMethod(Form::Get);
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->setAttribute('class', 'user-list-filter');

        $form->addText('username', 'web-user-list.filter')
            ->setMaxLength(255);

        $form->addSubmit('send', 'web-user-list.search')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary btn-sm');

        $form->onSuccess[] = function (Form $form) : void {
            $this->redirect('this', ['filter' => trim((string) $form->getValues('array')['username']), 'page' => 1]);
        };

        return $form;
    }
}
