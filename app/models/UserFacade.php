<?php

namespace App\Models;

/**
 * Description of UserFacade
 *
 * @author rendix2
 */
class UserFacade
{
    /**
     * @var PmManager $pmManager
     */
    private $pmManager;
    
    /**
     * @var Users2GroupsManager $users2GroupsManager
     */
    private $users2GroupsManager;

    /**
     * @var Users2ForumsManager $users2ForumsManager
     */
    private $users2ForumsManager;

    /**
     * @var ThanksManager $thanksManager
     */
    private $thanksManager;

    /**
     * @var SessionsManager $sessionsManager
     */
    private $sessionsManager;

    /**
     * @var ReportsManager $reportsManager
     */
    private $reportsManager;

    /**
     * @var ModeratorsManager $moderatorsManager
     */
    private $moderatorsManager;

    /**
     * @var Mails2UsersManager $mails2UsersManager
     */
    private $mails2UsersManager;

    /**
     * @var PostsManager $postsManager $postsManager
     */
    private $postsManager;
    
    /**
     * @var PostFacade $postFacade
     */
    private $postFacade;
    
    /**
     * @var TopicWatchManager $topicWatchManager
     */
    private $topicWatchManager;
    
    /**
     * @var Users2SessionsManager $users2SessionManager
     */
    private $users2SessionManager;
    
    /**
     * @var UsersManager $usersManager
     */
    private $usersManager;
    
    /**
     *
     * @var PostsHistoryManager $postsHistoryManager
     *
     */
    private $postsHistoryManager;

    /**
     *
     * @param PmManager           $pmManager
     * @param PostsManager        $postsManager
     * @param PostsHistoryManager $postsHistoryManager
     * @param PostFacade          $postFacade
     * @param Mails2UsersManager  $mails2UsersManager
     * @param ModeratorsManager   $moderatorsManager
     * @param ReportsManager      $reportsManager
     * @param SessionsManager     $sessionsManager
     * @param ThanksManager       $thanksManager
     * @param TopicWatchManager   $topicWatchManager
     * @param Users2ForumsManager $users2ForumsManager
     * @param Users2GroupsManager $users2GroupsManager
     * @param UsersManager        $usersManager
     */
    public function __construct(
        PmManager $pmManager,
        PostsManager $postsManager,
        PostsHistoryManager $postsHistoryManager,
        PostFacade $postFacade,
        Mails2UsersManager $mails2UsersManager,
        ModeratorsManager $moderatorsManager,
        PmManager $pmManager,
        ReportsManager $reportsManager,
        SessionsManager $sessionsManager,
        ThanksManager $thanksManager,
        TopicWatchManager $topicWatchManager,
        Users2ForumsManager $users2ForumsManager,
        Users2GroupsManager $users2GroupsManager,
        //Users2SessionsManager $users2SessionManager,
        UsersManager $usersManager
    ) {
        $this->usersManager         = $usersManager;
        $this->postsManager         = $postsManager;
        $this->postsHistoryManager  = $postsHistoryManager;
        $this->postFacade           = $postFacade;
        $this->topicWatchManager    = $topicWatchManager;
        //$this->users2SessionManager = $users2SessionManager;
        $this->mails2UsersManager   = $mails2UsersManager;
        $this->moderatorsManager    = $moderatorsManager;
        $this->pmManager            = $pmManager;
        $this->reportsManager       = $reportsManager;
        $this->sessionsManager      = $sessionsManager;
        $this->thanksManager        = $thanksManager;
        $this->users2ForumsManager  = $users2ForumsManager;
        $this->users2GroupsManager  = $users2GroupsManager;
    }

    /**
     *
     * @param int $item_id user_id
     *
     * @return bool
     */
    public function delete($item_id)
    {
        $user = $this->usersManager->getById($item_id);
        
        if ($user && $user->user_avatar) {
            $this->usersManager->removeAvatarFile($user->user_avatar);
        }

        $posts = $this->postsManager->getByUser($item_id)->fetchAll();
                
        foreach ($posts as $post) {
            $this->postFacade->delete($post->post_id);
        }
        
        $this->topicWatchManager->deleteByRight($item_id);
        //$this->users2SessionManager->deleteByLeft($item_id);
        $this->mails2UsersManager->deleteByRight($item_id);
        $this->moderatorsManager->deleteByLeft($item_id);
        //$this->pmManager->delete($item_id);
        $this->reportsManager->deleteByUser($item_id);
        $this->sessionsManager->deleteByUser($item_id);
        $this->thanksManager->deleteByUser($item_id);
        $this->users2ForumsManager->deleteByLeft($item_id);
        $this->users2GroupsManager->deleteByLeft($item_id);
        $this->postsHistoryManager->deleteByUser($item_id);
    
        return $this->usersManager->delete($item_id);
    }
    
    public function add(\Nette\Utils\ArrayHash $item_data)
    {
        $user_id = $this->usersManager->add($item_data);
        
        $this->pmManager->add($item_data);
    }
}
