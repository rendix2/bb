<?php declare(strict_types=1);

namespace App\UI\Web\User\Watches;

use App\Database\EntityManagerDecorator;
use App\Forms\IconBootstrapForm;
use App\Model\Entity\TopicEntity;
use App\Model\Service\TopicWatchService;
use App\UI\Web\User\Accessory\ProfilePresenter;
use Nette\Application\UI\Multiplier;

/**
 * My watched topics (decision of 2026-11-20): only the own list (no uuid in the address, the watches of others are private); each
 * can be stopped (POST + CSRF). A topic the user may not read any more is shown as "inaccessible" without its name or forum.
 */
class WatchesPresenter extends ProfilePresenter
{
    public function __construct(
        EntityManagerDecorator $em,
        private readonly TopicWatchService $topicWatchService,
    )
    {
        parent::__construct($em);
    }

    public function actionDefault() : void
    {
        $this->loadViewer();
    }

    public function renderDefault() : void
    {
        $this->getTemplate()->watches = $this->topicWatchService->watchedBy($this->viewer);
    }

    /**
     * Stop watching one topic: one form per topic id.
     */
    protected function createComponentUnwatchForm() : Multiplier
    {
        return new Multiplier(function (string $topicId) : IconBootstrapForm {
            $form = $this->buttonForm('web-user-watches.unwatch', 'fas fa-eye-slash', 'btn btn-outline-danger btn-sm');
            $form->onSuccess[] = function () use ($topicId) : void {
                $topic = $this->em->find(TopicEntity::class, (int) $topicId);

                if ($topic === null) {
                    $this->error('Topic not found');
                }

                $this->topicWatchService->unwatch($this->viewer, $topic);
                $this->flash('web-user-watches.unwatched');
                $this->redirect('this');
            };

            return $form;
        });
    }
}
