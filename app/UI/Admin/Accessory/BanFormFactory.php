<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use App\Model\Entity\BanEntity;
use App\Model\Entity\UserEntity;
use App\Model\Enum\BanScope;
use App\Model\Service\AdminException;
use App\Model\Service\BanAdminService;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Nette\Application\UI\Form;
use Nette\Localization\Translator;

/**
 * The form of a ban for Admin:Ban:Create and Admin:Ban:Edit: a target (username, e-mail, IP address, at least one), what it
 * blocks (the whole web by default), the reason written or chosen from the predefined ones (a written one wins), an optional end.
 */
class BanFormFactory
{
    public function __construct(
        private readonly BanAdminService $banAdmin,
        private readonly Translator $translator,
    ) {
    }

    /**
     * @param callable(BanEntity) : void $onSaved
     */
    public function create(?BanEntity $ban, UserEntity $admin, callable $onSaved) : BootstrapForm
    {
        $form = new BootstrapForm();
        BootstrapForm::switchBootstrapVersion(BootstrapVersion::V5);
        $form->setTranslator($this->translator);
        $form->addProtection('admin-forum.protection');

        $form->addText('username', 'admin-ban.form.username');
        $form->addText('email', 'admin-ban.form.email');
        $form->addText('ipAddress', 'admin-ban.form.ipAddress')
            ->setOption('description', $this->translator->translate('admin-ban.form.targetHelp'));

        $scopes = [];

        foreach (BanScope::cases() as $scope) {
            $scopes[$scope->value] = $scope->translationKey();
        }

        $form->addRadioList('scope', 'admin-ban.form.scope', $scopes)
            ->setDefaultValue(BanScope::Web->value)
            ->setRequired();
        // the texts of the reasons are not translated
        $form->addSelect('reasonPreset', $this->translator->translate('admin-ban.form.reasonPreset'), $this->banAdmin->reasonChoices())
            ->setTranslator(null)
            ->setPrompt($this->translator->translate('admin-ban.form.reasonPresetPrompt'));
        $form->addTextArea('reason', 'admin-ban.form.reason', rows: 3)
            ->setOption('description', $this->translator->translate('admin-ban.form.reasonHelp'));
        $form->addDateTime('expiresAt', 'admin-ban.form.expiresAt')
            ->setOption('description', $this->translator->translate('admin-ban.form.expiresAtHelp'));
        $form->addSubmit('send', 'admin-forum.save');

        if ($ban !== null) {
            $form->setDefaults([
                'username' => $ban->user?->username,
                'email' => $ban->email,
                'ipAddress' => $ban->ipAddress,
                'scope' => $ban->scope->value,
                'reason' => $ban->reason,
                'expiresAt' => $ban->expiresAt,
            ]);
        }

        $form->onSuccess[] = function (Form $form) use ($ban, $admin, $onSaved) : void {
            $values = $form->getValues(BanValues::class);
            $reason = trim($values->reason) !== '' ? $values->reason : (string) $values->reasonPreset;

            try {
                $saved = $this->banAdmin->saveBan($ban, $values->username, $values->email, $values->ipAddress, BanScope::from($values->scope), $reason, $values->expiresAt, $admin);
            } catch (AdminException $exception) {
                $form->addError($this->translator->translate($exception->getMessage(), $exception->parameters), false);

                return;
            }

            $onSaved($saved);
        };

        return $form;
    }
}
