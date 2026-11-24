<?php declare(strict_types=1);

namespace App\UI\Forum\Search;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserEntity;
use App\Model\Service\SearchService;
use App\Model\Service\TopicPaginationService;
use App\Model\Service\UserListService;
use App\Settings\ForumSettings;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Localization\Translator;
use Nette\Utils\Paginator;

/**
 * The search (slice 4h): posts (title and text) and topics (name) by the PostgreSQL full-text (SearchService, the most relevant
 * first), only from the forums the viewer may read; users (by a part of the username) only for logged-in users (the list of
 * users is not public, decision of 2026-11-19). A GET form, the address /forum.search/?q=…&type=posts|topics|users&page=N.
 */
class SearchPresenter extends Presenter
{
    public const string TypePosts = 'posts';

    public const string TypeTopics = 'topics';

    public const string TypeUsers = 'users';

    private const array Types = [self::TypePosts, self::TypeTopics, self::TypeUsers];

    private ?UserEntity $viewer = null;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly SearchService $searchService,
        private readonly UserListService $userListService,
        private readonly TopicPaginationService $topicPagination,
        private readonly ForumSettings $forumSettings,
        private readonly Translator $translator,
    )
    {
        parent::__construct();
    }

    public function actionDefault(string $q = '', string $type = self::TypePosts, int $page = 1) : void
    {
        if (!in_array($type, self::Types, true)) {
            $this->redirect('this', ['type' => self::TypePosts, 'page' => 1]);
        }

        if ($this->getUser()->isLoggedIn()) {
            $this->viewer = $this->em->find(UserEntity::class, $this->getUser()->getId());
        }

        // the users are not public: a guest goes to the login and comes back
        if ($type === self::TypeUsers && $this->viewer === null) {
            $this->redirect(':Web:User:Login:default', ['backlink' => $this->storeRequest()]);
        }

        $this['searchForm']->setDefaults(['query' => $q, 'in' => $type]);
    }

    public function renderDefault(string $q = '', string $type = self::TypePosts, int $page = 1) : void
    {
        $q = trim($q);
        $paginator = new Paginator();
        $paginator->setItemsPerPage(max(1, (int) $this->forumSettings->get()['pagination']['itemsPerPage']));
        $paginator->setPage($page);

        $result = match (true) {
            $q === '' => ['count' => 0, 'items' => []],
            $type === self::TypeTopics => $this->searchService->topics($this->viewer, $q, $paginator->getOffset(), $paginator->getLength()),
            $type === self::TypeUsers => $this->userListService->page($this->viewer, $q, UserListService::SortUsername, false, $paginator->getOffset(), $paginator->getLength()),
            default => $this->searchService->posts($this->viewer, $q, $paginator->getOffset(), $paginator->getLength()),
        };
        $paginator->setItemCount($result['count']);

        $pages = [];

        if ($type === self::TypePosts) {
            // the page of the topic where each post is (the link goes right to it)
            foreach ($result['items'] as $post) {
                $pages[$post->id] = $this->topicPagination->pageOf($post);
            }
        }

        $this->getTemplate()->q = $q;
        $this->getTemplate()->type = $type;
        $this->getTemplate()->count = $result['count'];
        $this->getTemplate()->items = $result['items'];
        $this->getTemplate()->pages = $pages;
        $this->getTemplate()->paginator = $paginator;
    }

    /**
     * A GET form (changes nothing): redirects to the address with q and type from the first page.
     */
    protected function createComponentSearchForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setMethod(Form::Get);
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->setAttribute('class', 'forum-search-form');

        $form->addText('query', 'forum-search.query')
            ->setMaxLength(255)
            ->setRequired('forum-search.required');

        $types = [self::TypePosts => 'forum-search.type.posts', self::TypeTopics => 'forum-search.type.topics'];

        if ($this->getUser()->isLoggedIn()) {
            $types[self::TypeUsers] = 'forum-search.type.users';
        }

        $form->addRadioList('in', 'forum-search.in', $types)
            ->setDefaultValue(self::TypePosts);

        $form->addSubmit('send', 'forum-search.search')
            ->setHtmlAttribute('class', 'btn btn-primary btn-sm');

        $form->onSuccess[] = function (Form $form) : void {
            $values = $form->getValues('array');
            $this->redirect('this', ['q' => trim((string) $values['query']), 'type' => (string) ($values['in'] ?? self::TypePosts), 'page' => 1]);
        };

        return $form;
    }
}
