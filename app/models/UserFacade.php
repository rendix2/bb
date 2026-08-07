<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Repository\PostRepository;
use App\Model\Repository\ThankRepository;
use App\Model\Repository\TopicWatchRepository;
use App\Model\Repository\UserRepository;
use App\Models\Entity\PmEntity;
use App\services\AvatarService;
use App\Services\TranslatorFactory;

/**
 * Description of UserFacade
 *
 * @author rendix2
 * @package App\Models
 */
class UserFacade
{
    /**
     * @var PmManager $pmManager
     */
    private PmManager $pmManager;

    private TranslatorFactory $translatorFactory;

    public function __construct(
        TranslatorFactory   $translatorFactory,

        private readonly EntityManagerDecorator $em,

    ) {
        $this->translatorFactory    = $translatorFactory;
    }

    public function delete(int $userId): void
    {
    }

    public function add(\App\Model\Entity\UserEntity $userEntity): void
    {
        $this->em->persist($userEntity);
        $this->em->flush();

        $user_id         = $userEntity->id;
        $forumTranslator = $this->translatorFactory->getForumTranslator();
        
        $pmEntity = new PmEntity();
        $pmEntity->setPm_user_id_from(1)
                 ->setPm_user_id_to($user_id)
                 ->setPm_subject($forumTranslator->translate('welcome_pm_subject'))
                 ->setPm_text(sprintf($forumTranslator->translate('welcome_pm_text'), $userEntity->username))
                 ->setPm_status('sent')
                 ->setPm_time_sent(time());

        $this->pmManager->add($pmEntity->getArrayHash());
    }
}
