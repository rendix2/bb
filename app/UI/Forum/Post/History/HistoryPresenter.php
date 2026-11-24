<?php declare(strict_types=1);

namespace App\UI\Forum\Post\History;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostHistoryService;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;

/**
 * The edit history of a post: the current version and the previous ones with who replaced them and when. Only for the
 * author of the post, the moderators of its forum and the admin (PostHistoryService::mayView()).
 */
class HistoryPresenter extends Presenter
{
    private PostEntity $post;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly PostHistoryService $postHistory,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $post = $this->em->getRepository(PostEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($post === null || !$post->topic->forum->active || !$post->topic->category->active) {
            $this->error('Post was not found.');
        }

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Web:User:Login:default');
        }

        $user = $this->em->find(UserEntity::class, $this->getUser()->getId());

        if (!$this->postHistory->mayView($user, $post)) {
            $this->error('You may not see the history of this post.', IResponse::S403_Forbidden);
        }

        $this->post = $post;
    }

    public function renderDefault() : void
    {
        $this->template->post = $this->post;
        $this->template->versions = $this->postHistory->versions($this->post);
    }
}
