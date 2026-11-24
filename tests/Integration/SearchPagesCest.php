<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\SearchService;
use Tests\Support\IntegrationTester;

/**
 * The search (slice 4h, PostgreSQL full-text): posts and topics only from the forums the viewer may read, the most relevant first,
 * pages of 25; users only for logged-in users. Every test runs in a transaction that is rolled back (the GIN indexes see the rows
 * of the own transaction).
 */
class SearchPagesCest
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

    public function aGuestFindsPostsOnlyFromReadableForumsTheMostRelevantFirst(IntegrationTester $I) : void
    {
        $word = $this->word();
        $author = $I->createUser('Author', role: 'Normal');
        $visible = $this->createTopic($I, $author, 'Visible topic', true, true);
        $closedForum = $this->createTopic($I, $author, 'Closed forum topic', false, true);
        $closedCategory = $this->createTopic($I, $author, 'Closed category topic', true, false);
        $relevant = $this->addPost($I, $visible, $author, $word . ' ' . $word . ' ' . $word . ' relevant');
        // newer (a higher id), but less relevant: the relevance sorts, not the date
        $lessRelevant = $this->addPost($I, $visible, $author, 'Only once ' . $word . ' among many other words of a long post about something else');
        $this->addPost($I, $closedForum, $author, 'Hidden ' . $word);
        $this->addPost($I, $closedCategory, $author, 'Hidden ' . $word);
        $this->addPost($I, $visible, $author, 'Nothing to find here');

        $I->amOnPage('/forum.index/');
        $I->click('.search-link');
        $I->submitForm('.forum-search-form', ['query' => mb_strtoupper($word), 'in' => 'posts']);
        $I->seeInCurrentUrl('q=');
        $I->seeNumberOfElements('.forum-search-post', 2);
        $I->see('relevant', '(//div[contains(@class, "forum-search-post")])[1]');
        $I->see('Only once', '(//div[contains(@class, "forum-search-post")])[2]');
        $I->dontSee('Hidden');
        $I->seeElement('.forum-search-post a[href*="' . $visible->uuid->toString() . '"][href*="#post-' . $relevant->id . '"]');
        $I->assertGreaterThan($relevant->id, $lessRelevant->id);

        // the title of a post is searched too; a query with operators of the user is never a syntax error
        $titled = $this->addPost($I, $visible, $author, 'A text without it');
        $titled->title = 'Title ' . $word . 'x';
        $I->em()->flush();
        $I->amOnPage('/forum.search/?q=' . urlencode($word . 'x') . '&type=posts');
        $I->seeNumberOfElements('.forum-search-post', 1);
        $I->amOnPage('/forum.search/?q=' . urlencode('"' . $word . ' or -(&!:*') . '&type=posts');
        $I->seeResponseCodeIs(200);
    }

    public function topicsAreFoundByTheirNameOnlyFromReadableForums(IntegrationTester $I) : void
    {
        $word = $this->word();
        $author = $I->createUser('Author', role: 'Normal');
        $found = $this->createTopic($I, $author, 'Topic ' . $word, true, true);
        $this->createTopic($I, $author, 'Hidden ' . $word, false, true);
        $this->createTopic($I, $author, 'Hidden too ' . $word, true, false);

        $I->amOnPage('/forum.search/?q=' . $word . '&type=topics');
        $I->seeNumberOfElements('.forum-search-topic-row', 1);
        $I->see($found->name, '.forum-search-topic');
        $I->dontSee('Hidden');
    }

    public function usersOnlyForLoggedInUsers(IntegrationTester $I) : void
    {
        $tag = 'Hledany' . random_int(100000, 999999);
        $found = $I->createUser('Found', role: 'Normal');
        $found->username = $tag . 'Kůň';
        $I->em()->flush();
        $viewer = $I->createUser('Viewer', role: 'Normal');

        $I->amOnPage('/forum.search/');
        $I->dontSeeElement('input[name="in"][value="users"]');
        $I->amOnPage('/forum.search/?q=' . strtolower($tag) . '&type=users');
        $I->seeElement('#frm-loginForm');
        $I->submitForm('#frm-loginForm', ['username' => $viewer->username, 'password' => IntegrationTester::PASSWORD]);
        $I->seeNumberOfElements('.forum-search-user', 1);
        $I->see($found->username, '.forum-search-username');
        $I->seeElement('input[name="in"][value="users"]');
    }

    public function theResultsHavePagesOf25(IntegrationTester $I) : void
    {
        $word = $this->word();
        $author = $I->createUser('Author', role: 'Normal');
        $topic = $this->createTopic($I, $author, 'Paged topic', true, true);

        foreach (range(1, 26) as $number) {
            $this->addPost($I, $topic, $author, 'Post ' . $number . ' ' . $word);
        }

        $I->amOnPage('/forum.search/?q=' . $word);
        $I->see('26', '.forum-search-count');
        $I->seeNumberOfElements('.forum-search-post', 25);
        $I->click('2', '.pagination');
        $I->seeNumberOfElements('.forum-search-post', 1);

        // the service itself: nothing without a query, an unknown type of the address goes to the posts
        $I->assertSame(0, $I->grabService(SearchService::class)->posts(null, '  ', 0, 25)['count']);
        $I->amOnPage('/forum.search/?q=' . $word . '&type=passwords');
        $I->seeNumberOfElements('.forum-search-post', 25);
    }

    /**
     * The SQL of TS_MATCH is the expression of the GIN indexes (postUp() of the migrations of post and topic): else PostgreSQL
     * reads the whole table. EXPLAIN with sequential scans discouraged must name the index.
     */
    public function theSearchUsesTheGinIndexes(IntegrationTester $I) : void
    {
        $em = $I->em();
        $connection = $em->getConnection();
        $connection->executeStatement('SET LOCAL enable_seqscan = off');
        $queries = [
            'k__post__search' => $em->getRepository(PostEntity::class)->createQueryBuilder('_p')->select('_p.id')
                ->where('TS_MATCH(' . SearchService::PostDocument . ', :query) = true'),
            'k__topic__search' => $em->getRepository(TopicEntity::class)->createQueryBuilder('_t')->select('_t.id')
                ->where('TS_MATCH(' . SearchService::TopicDocument . ', :query) = true'),
        ];

        foreach ($queries as $index => $qb) {
            $plan = implode("\n", $connection->fetchFirstColumn('EXPLAIN ' . $qb->getQuery()->getSQL(), ['word']));
            $I->assertStringContainsString($index, $plan, 'The search does not use the index ' . $index . ".\n" . $plan);
        }
    }

    private function word() : string
    {
        return 'slovo' . random_int(100000, 999999);
    }

    private function createTopic(IntegrationTester $I, UserEntity $author, string $name, bool $forumActive, bool $categoryActive) : TopicEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Search category';
        $category->sortOrder = 1000;
        $category->active = $categoryActive;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Search forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = $forumActive;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = $name;
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);
        $em->flush();

        return $topic;
    }

    private function addPost(IntegrationTester $I, TopicEntity $topic, UserEntity $author, string $text) : PostEntity
    {
        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = $text;
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $I->em()->persist($post);
        $I->em()->flush();

        return $post;
    }
}
