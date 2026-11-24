<?php declare(strict_types=1);

namespace App\UI\Admin\User\Forum;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\RankEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\UserAvatarService;
use App\Model\Service\UserForumAdminService;
use App\Model\Service\UserProfileService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * bb: the forum side of a user (slice 7c2) next to the account of web-project (Admin:User:Edit, which only links here, so it
 * stays comparable with web-project): language, signature, special rank, deleting the avatar; his posts, topics, groups,
 * moderated forums, own grants and bans.
 */
class ForumPresenter extends AdminBasePresenter
{
    private UserEntity $userEntity;

    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly UserForumAdminService $userForumAdmin,
        private readonly UserProfileService $userProfile,
        private readonly UserAvatarService $userAvatar,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function actionDefault(string $uuid) : void
    {
        $this->userEntity = $this->em->getRepository(UserEntity::class)->findOneBy(['uuid' => $uuid]) ?? $this->error('User was not found.');
    }

    public function renderDefault() : void
    {
        $admin = $this->admin();
        $this->template->userEntity = $this->userEntity;
        $this->template->counts = $this->userProfile->counts($this->userEntity, $admin);
        $this->template->moderatedForums = $this->userProfile->moderatedForums($this->userEntity, $admin);
        $this->template->groups = $this->userForumAdmin->groups($this->userEntity);
        $this->template->grants = $this->userForumAdmin->grants($this->userEntity);
        $this->template->bans = $this->userForumAdmin->bans($this->userEntity);
        $this->template->now = new \DateTimeImmutable();
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentForumForm() : BootstrapForm
    {
        $form = $this->form();
        $languages = [];

        foreach ($this->em->getRepository(LanguageEntity::class)->findBy([], ['name' => 'ASC']) as $language) {
            $languages[$language->id] = $language->name;
        }

        $ranks = [];

        /** @var list<RankEntity> $allRanks */
        $allRanks = $this->em->createQueryBuilder()->select('_r')->from(RankEntity::class, '_r')->where('_r.minPosts IS NULL')->orderBy('_r.name', 'ASC')->getQuery()->getResult();

        foreach ($allRanks as $rank) {
            $ranks[$rank->id] = $rank->name;
        }

        $maxLength = $this->userForumAdmin->signatureMaxLength();
        // the names of the languages and ranks are not translated
        $form->addSelect('language', $this->translator->translate('admin-user-forum.form.language'), $languages)
            ->setTranslator(null)
            ->setRequired()
            ->setDefaultValue($this->userEntity->language->id);
        $form->addTextArea('signature', 'admin-user-forum.form.signature', rows: 3)
            ->setMaxLength($maxLength)
            ->setOption('description', $this->translator->translate('admin-user-forum.form.signatureHelp', ['maxChars' => $maxLength]))
            ->setDefaultValue($this->userEntity->signature);
        $form->addSelect('rank', $this->translator->translate('admin-user-forum.form.rank'), $ranks)
            ->setTranslator(null)
            ->setPrompt($this->translator->translate('admin-user-forum.form.rankByPosts'))
            ->setDefaultValue($this->userEntity->rank?->id);
        $form->addSubmit('send', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) : void {
            /** @var array{language: int, signature: string, rank: ?int} $values */
            $values = $form->getValues('array');
            $language = $this->em->find(LanguageEntity::class, $values['language']) ?? $this->error('Language was not found.');
            $rank = $values['rank'] !== null ? $this->em->find(RankEntity::class, $values['rank']) : null;

            try {
                $this->userForumAdmin->save($this->userEntity, $language, $values['signature'], $rank);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-user-forum.saved', ['username' => $this->userEntity->username]));
            $this->redirect('this');
        };

        return $form;
    }

    protected function createComponentDeleteAvatarForm() : BootstrapForm
    {
        $form = $this->form();
        $form->addSubmit('delete', 'admin-user-forum.avatar.delete')
            ->setHtmlAttribute('class', 'btn btn-outline-danger btn-sm');

        $form->onSuccess[] = function () : void {
            $this->userAvatar->delete($this->userEntity);
            $this->adminFlash($this->translator->translate('admin-user-forum.avatar.deleted', ['username' => $this->userEntity->username]));
            $this->redirect('this');
        };

        return $form;
    }

    private function form() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        return $form;
    }
}
