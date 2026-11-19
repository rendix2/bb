<?php declare(strict_types=1);

namespace App\UI\Forum\Accessory;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\ForumEntity;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * "Jump to a forum": a GET form choosing an active forum of an active category, it redirects to Forum:Forum:View and
 * changes nothing (no CSRF protection needed).
 */
class ForumJumpFormFactory
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly Translator $translator,
    ) {
    }

    public function create(?ForumEntity $current = null) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);

        $form->setMethod(Form::Get);
        $form->setTranslator($this->translator);
        $form->getElementPrototype()->setAttribute('class', 'forum-jump');

        // forum names are not translated, so the select has no translator and gets its texts translated here
        $form->addSelect('forum', $this->translator->translate('forum-jump.label'), $this->choices())
            ->setTranslator(null)
            ->setPrompt($this->translator->translate('forum-jump.prompt'))
            ->setRequired($this->translator->translate('forum-jump.required'))
            ->setDefaultValue($current?->uuid->toString());

        $form->addSubmit('jump', 'forum-jump.go')
            ->setHtmlAttribute('class', 'btn btn-outline-secondary btn-sm');

        $form->onSuccess[] = function (Form $form) : void {
            $form->getPresenter()?->redirect(':Forum:Forum:View:default', ['uuid' => (string) $form->getValues('array')['forum']]);
        };

        return $form;
    }

    /**
     * Active forums of active categories, grouped by the category (optgroups).
     *
     * @return array<string, array<string, string>> category name => forum uuid => forum name
     */
    private function choices() : array
    {
        /** @var list<ForumEntity> $forums */
        $forums = $this->em->getRepository(ForumEntity::class)
            ->createQueryBuilder('_f')
            ->addSelect('_c')
            ->join('_f.category', '_c')
            ->where('_f.active = true')
            ->andWhere('_c.active = true')
            ->orderBy('_c.sortOrder', 'ASC')
            ->addOrderBy('_c.id', 'ASC')
            ->addOrderBy('_f.sortOrder', 'ASC')
            ->addOrderBy('_f.id', 'ASC')
            ->getQuery()
            ->getResult();

        $choices = [];

        foreach ($forums as $forum) {
            $choices[$forum->category?->name ?? ''][$forum->uuid->toString()] = $forum->name;
        }

        return $choices;
    }
}
