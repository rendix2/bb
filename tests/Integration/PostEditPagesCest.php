<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\IntegrationTester;

/**
 * Editing a post (slice 3e) through the pages: the edit link only at posts the user may edit, the form, the mark
 * "edited", 403 for others. Every test runs in a transaction that is rolled back.
 */
class PostEditPagesCest
{
    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function theAuthorEditsHisPost(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('#post-' . $post->id . ' .post-edit-link');
        $I->seeInField('text', 'The original text');
        $I->submitForm('#frm-editPostForm', ['title' => '', 'text' => 'The **edited** text']);

        $I->seeInCurrentUrl('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeInSource('<strong>edited</strong>');
        $I->see('upraveno', '#post-' . $post->id . ' .post-edited');
    }

    public function anotherUserHasNoEditLinkAndTheEditPageIsForbidden(IntegrationTester $I) : void
    {
        [$topic, $post] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $other = $I->createUser('Other', role: 'Normal');
        $I->login($other->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#post-' . $post->id . ' .post-edit-link');
        $I->seePageIsRefused('/forum.post.edit/' . $post->uuid->toString(), 403);
    }

    /**
     * @return array{TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Edit category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Edit forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Edit topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->category = $category;
        $post->forum = $forum;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'The original text';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $post];
    }

}
