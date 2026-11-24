<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\GroupEntity;
use App\Model\Service\AdminException;
use App\Model\Service\ForumAccessAdminService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of the name of a group for Admin:Group:Create and Admin:Group:Edit.
 */
class GroupFormFactory
{
    public function __construct(
        private readonly ForumAccessAdminService $access,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(GroupEntity) : void $onSaved
     */
    public function create(?GroupEntity $group, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('name', 'admin-access.groupName')
            ->setRequired('admin-access.error.groupName')
            ->setMaxLength(255)
            ->setDefaultValue($group?->name);
        $form->addSubmit('send', 'admin-forum.save');

        $form->onSuccess[] = function (Form $form) use ($group, $onSaved) : void {
            try {
                $saved = $this->access->saveGroup($group, (string) $form->getValues('array')['name']);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
