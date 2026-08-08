<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Models\Entity\PmEntity;
use Nette\Localization\Translator;

/**
 * Description of UserFacade
 *
 * @author rendix2
 * @package App\Models
 */
class UserFacade
{
    public function __construct(
        private readonly Translator $translator,

        private readonly EntityManagerDecorator $em,

    ) {
    }

    public function delete(int $userId): void
    {
    }

    public function add(\App\Model\Entity\UserEntity $userEntity): void
    {
        $this->em->persist($userEntity);
        $this->em->flush();

        $user_id         = $userEntity->id;
        
        $pmEntity = new PmEntity();
        $pmEntity->setPm_user_id_from(1)
                 ->setPm_user_id_to($user_id)
                 ->setPm_subject($this->translator->translate('welcome_pm_subject'))
                 ->setPm_text(sprintf($this->translator->translate('welcome_pm_text'), $userEntity->username))
                 ->setPm_status('sent')
                 ->setPm_time_sent(time());

        $this->em->persist($pmEntity);
        $this->em->flush();
    }
}
