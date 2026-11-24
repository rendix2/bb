<?php declare(strict_types=1);

namespace App\UI\Admin\Language\List;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\LanguageAdminService;
use App\UI\Admin\AdminBasePresenter;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\MenuControl\UI\MenuComponent;
use Contributte\MenuControl\UI\MenuComponentFactory;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The languages of the interface (slice 7d): the list, adding a locale of the whitelist with complete translations (never
 * renaming), the locales that still miss translations.
 */
class ListPresenter extends AdminBasePresenter
{
    public function __construct(
        private readonly EntityManagerDecorator $em,
        private readonly LanguageAdminService $languageAdmin,
        private readonly MenuComponentFactory $menuFactory,
        private readonly Translator $translator,
    ) {
        parent::__construct();
    }

    public function renderDefault() : void
    {
        $languages = $this->em->getRepository(LanguageEntity::class)->findBy([], ['name' => 'ASC']);
        $users = [];

        foreach ($languages as $language) {
            $users[$language->id] = $this->em->getRepository(UserEntity::class)->count(['language' => $language]);
        }

        $this->template->languages = $languages;
        $this->template->users = $users;
        $this->template->incomplete = array_filter($this->languageAdmin->candidates(), static fn (array $candidate) : bool => $candidate['missing'] !== []);
    }

    protected function createComponentMenu() : MenuComponent
    {
        return $this->menuFactory->create('admin');
    }

    protected function createComponentAddForm() : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $choices = [];

        foreach ($this->languageAdmin->candidates() as $locale => $candidate) {
            if ($candidate['missing'] === []) {
                $choices[$locale] = $candidate['name'] . ' (' . $locale . ')';
            }
        }

        // the names of the languages are not translated
        $form->addSelect('locale', $this->translator->translate('admin-language-list.locale'), $choices)
            ->setTranslator(null)
            ->setPrompt($this->translator->translate($choices === [] ? 'admin-language-list.noneAvailable' : 'admin-language-list.choose'))
            ->setRequired($this->translator->translate('admin-language-list.choose'));
        $form->addSubmit('add', 'admin-language-list.add');

        $form->onSuccess[] = function (Form $form) : void {
            try {
                $language = $this->languageAdmin->add((string) $form->getValues('array')['locale']);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $this->adminFlash($this->translator->translate('admin-language-list.added', ['name' => $language->name]));
            $this->redirect('this');
        };

        return $form;
    }
}
