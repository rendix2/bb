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
    
    /**
     * @var User2GroupManager $users2GroupsManager
     */
    private User2GroupManager $users2GroupsManager;

    /**
     * @var Users2ForumsManager $users2ForumsManager
     */
    private Users2ForumsManager $users2ForumsManager;

    /**
     * @var ReportManager $reportsManager
     */
    private ReportManager $reportsManager;

    /**
     * @var ModeratorManager $moderatorsManager
     */
    private ModeratorManager $moderatorsManager;

    /**
     * @var Mails2UsersManager $mails2UsersManager
     */
    private Mails2UsersManager $mails2UsersManager;
    
    /**
     * @var PostFacade $postFacade
     */
    private PostFacade $postFacade;
    
    /**
     * @var UsersManager $usersManager
     */
    private UsersManager $usersManager;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     *
     */
    private PostsHistoryManager $postsHistoryManager;

    /**
     * @var TranslatorFactory $translatorFactory
     */
    private TranslatorFactory $translatorFactory;
    
    /**
     *
     * @var PmFacade $pmFacade
     */
    private PmFacade $pmFacade;

    public function __construct(
        PostsHistoryManager $postsHistoryManager,
        PostFacade          $postFacade,
        Mails2UsersManager  $mails2UsersManager,
        ModeratorManager   $moderatorsManager,
        ReportManager      $reportsManager,
        Users2ForumsManager $users2ForumsManager,
        User2GroupManager $users2GroupsManager,
        UsersManager        $usersManager,
        TranslatorFactory   $translatorFactory,
        PmFacade            $pmFacade,
        private readonly PostRepository       $postRepository,
        private readonly UserRepository       $userRepository,
        private readonly ThankRepository      $thankRepository,
        private readonly TopicWatchRepository $topicWatchRepository,

        private readonly EntityManagerDecorator $em,

        private readonly AvatarService $avatarService,
    ) {
        $this->usersManager         = $usersManager;
        $this->postsHistoryManager  = $postsHistoryManager;
        $this->postFacade           = $postFacade;
        $this->mails2UsersManager   = $mails2UsersManager;
        $this->moderatorsManager    = $moderatorsManager;
        $this->reportsManager       = $reportsManager;
        $this->users2ForumsManager  = $users2ForumsManager;
        $this->users2GroupsManager  = $users2GroupsManager;
        $this->translatorFactory    = $translatorFactory;
        $this->pmFacade             = $pmFacade;
    }

    public function delete(int $userId): void
    {
        $userEntity = $this->userRepository
            ->findOneBy(
                [
                    'id' => $userId,
                ]
            );
        
        if ($userEntity && $userEntity->user_avatar) {
            $this->avatarService->removeAvatarFile($userEntity->user_avatar);
        }

        $posts = $this->postRepository->findByUser($userEntity);
                
        foreach ($posts as $post) {
            $this->postFacade->delete($post->topic, $post);
        }

        $topicsWatches = $this->topicWatchRepository
            ->findBy(
                [
                    'user' => $userEntity,
                ]
            );

        foreach ($topicsWatches as $topicsWatch) {
            $this->em->remove($topicsWatch);
        }

        $this->em->flush();

        //$this->users2SessionManager->deleteByLeft($item_id);
        $this->mails2UsersManager->deleteByRight($userId);
        $this->moderatorsManager->deleteByLeft($userId);
        $this->pmFacade->delete($userId);
        $this->reportsManager->deleteByUser($userId);


        foreach ($userEntity->sessions as $session) {
            $this->em->remove($session);
        }

        $this->em->flush();

        $thanks = $this->thankRepository
            ->findBy(
                [
                    'user' => $userEntity,
                ]
            );

        foreach ($thanks as $thank) {
            $this->em->remove($thank);
            $this->em->flush();
        }

        $this->users2ForumsManager->deleteByLeft($userId);
        $this->users2GroupsManager->deleteByLeft($userId);
        $this->postsHistoryManager->deleteByUser($userId);
    
        $this->em->remove($userEntity);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Entity\UserEntity $user
     *
     * @return int
     */
    public function add(\App\Model\Entity\UserEntity $userEntity)
    {
        $user_id         = $this->usersManager->add($userEntity->getArrayHash());
        $forumTranslator = $this->translatorFactory->getForumTranslator();
        
        $pmEntity = new PmEntity();
        $pmEntity->setPm_user_id_from(1)
                 ->setPm_user_id_to($user_id)
                 ->setPm_subject($forumTranslator->translate('welcome_pm_subject'))
                 ->setPm_text(sprintf($forumTranslator->translate('welcome_pm_text'), $userEntity->username))
                 ->setPm_status('sent')
                 ->setPm_time_sent(time());

        $this->pmManager->add($pmEntity->getArrayHash());

        return $user_id;
    }
}
