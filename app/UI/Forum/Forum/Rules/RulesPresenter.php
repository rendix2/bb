<?php declare(strict_types=1);

namespace App\UI\Forum\Forum\Rules;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use Nette\Application\UI\Presenter;

/**
 * The rules of a forum (active forum in an active category only).
 */
class RulesPresenter extends Presenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
    ) {
        parent::__construct();
    }

    public function renderDefault(string $uuid) : void
    {
        $forum = $this->em->getRepository(ForumEntity::class)->findOneBy(['uuid' => $uuid]);

        if ($forum === null || !$forum->active || ($forum->category !== null && !$forum->category->active)) {
            $this->error('Forum was not found.');
        }

        $this->template->forum = $forum;
    }

}
