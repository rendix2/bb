<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\RankEntity;
use App\Model\Service\AdminException;
use App\Model\Service\RankAdminService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Localization\Translator;

/**
 * The form of a rank for Admin:Rank:Create and Admin:Rank:Edit: an empty minimum of posts makes a special rank.
 */
class RankFormFactory
{
    public function __construct(
        private readonly RankAdminService $rankAdmin,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(RankEntity) : void $onSaved
     */
    public function create(?RankEntity $rank, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('name', 'admin-rank.form.name')
            ->setRequired('admin-rank.error.name')
            ->setMaxLength(255)
            ->setDefaultValue($rank?->name);
        $minPosts = $form->addInteger('minPosts', 'admin-rank.form.minPosts')
            ->setNullable()
            ->setOption('description', $this->translator->translate('admin-rank.form.minPostsHelp'))
            ->setDefaultValue($rank?->minPosts);
        $minPosts->addCondition(Form::Filled)
            ->addRule(Form::Min, 'admin-rank.error.minPosts', 0);
        $form->addUpload('image', 'admin-rank.form.image')
            ->setOption('description', $this->translator->translate('admin-rank.form.imageHelp'));

        if ($rank?->image !== null) {
            $form->addCheckbox('deleteImage', 'admin-rank.form.deleteImage');
        }

        $form->addSubmit('send', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) use ($rank, $onSaved) : void {
            /** @var array{name: string, minPosts: ?int, image: FileUpload, deleteImage?: bool} $values */
            $values = $form->getValues('array');

            try {
                $saved = $this->rankAdmin->save($rank, $values['name'], $values['minPosts'], $values['image'], $values['deleteImage'] ?? false);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
