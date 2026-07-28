<?php

namespace App\Models;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Repository\PostRepository;
use App\Models\Entity\PmEntity;
use App\Models\Entity\UserEntity;
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
     * @var SessionManager $sessionsManager
     */
    private SessionManager $sessionsManager;

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
     * @var PostManager $postsManager $postsManager
     */
    private PostManager $postsManager;
    
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

    /**
     * UserFacade constructor.
     *
     * @param PostManager        $postsManager
     * @param PostsHistoryManager $postsHistoryManager
     * @param PostFacade          $postFacade
     * @param Mails2UsersManager  $mails2UsersManager
     * @param ModeratorManager   $moderatorsManager
     * @param ReportManager      $reportsManager
     * @param SessionManager     $sessionsManager
     * @param Users2ForumsManager $users2ForumsManager
     * @param User2GroupManager $users2GroupsManager
     * @param UsersManager        $usersManager
     * @param TranslatorFactory   $translatorFactory
     * @param PmFacade            $pmFacade
     */
    public function __construct(
        PostManager        $postsManager,
        PostsHistoryManager $postsHistoryManager,
        PostFacade          $postFacade,
        Mails2UsersManager  $mails2UsersManager,
        ModeratorManager   $moderatorsManager,
        ReportManager      $reportsManager,
        SessionManager     $sessionsManager,
        Users2ForumsManager $users2ForumsManager,
        User2GroupManager $users2GroupsManager,
        //Users2SessionsManager $users2SessionManager,
        UsersManager        $usersManager,
        TranslatorFactory   $translatorFactory,
        PmFacade            $pmFacade,
        private readonly PostRepository $postRepository,
        private readonly EntityManagerDecorator $em
    ) {
        $this->usersManager         = $usersManager;
        $this->postsHistoryManager  = $postsHistoryManager;
        $this->postFacade           = $postFacade;
        //$this->users2SessionManager = $users2SessionManager;
        $this->mails2UsersManager   = $mails2UsersManager;
        $this->moderatorsManager    = $moderatorsManager;
        $this->reportsManager       = $reportsManager;
        $this->sessionsManager      = $sessionsManager;
        $this->users2ForumsManager  = $users2ForumsManager;
        $this->users2GroupsManager  = $users2GroupsManager;
        $this->translatorFactory    = $translatorFactory;
        $this->pmFacade             = $pmFacade;
    }

    public function delete(int $userId): void
    {
        $userEntity = $this->em
            ->getRepository(\App\Model\Entity\UserEntity::class)
            ->findOneBy(
                [
                    'id' => $userId,
                ]
            );
        
        if ($userEntity && $userEntity->user_avatar) {
            $this->usersManager->removeAvatarFile($userEntity->user_avatar);
        }

        $posts = $this->postRepository->findByUser($userId);
                
        foreach ($posts as $post) {
            $this->postFacade->delete($post->topic, $post);
        }

        $topicsWatches = $this->em
            ->getRepository(TopicWatchEntity::class)
            ->findBy(
                [
                    'user' => $userEntity,
                ]
            );

        foreach ($topicsWatches as $topicsWatch) {
            $this->em->remove($topicsWatch);
            $this->em->flush();
        }

        //$this->users2SessionManager->deleteByLeft($item_id);
        $this->mails2UsersManager->deleteByRight($userId);
        $this->moderatorsManager->deleteByLeft($userId);
        $this->pmFacade->delete($userId);
        $this->reportsManager->deleteByUser($userId);
        $this->sessionsManager->deleteByUser($userId);

        $thanks = $this->em
            ->getRepository(ThankEntity::class)
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
