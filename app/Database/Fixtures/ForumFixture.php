<?php declare(strict_types=1);

namespace App\Database\Fixtures;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Repository\UserRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Nette\DI\Container;
use Nette\Security\Passwords;
use Nettrine\Fixtures\Fixture\ContainerAwareInterface;

class ForumFixture implements FixtureInterface, OrderedFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('cs_CZ'); // Máme rádi české texty

        // 1. Získáme uživatele (předpokládáme, že UserFixture už proběhla)
        $users = $manager->getRepository(UserEntity::class)->findAll();
        $testUser = $users[0]; // Tvůj TestUsername

        for ($c = 1; $c <= 3; $c++) {
            $category = new CategoryEntity();
            $category->name = "Kategorie " . $faker->word;
            $category->active = true;
            $category->sortOrder = $c;

            $manager->persist($category);

            // 3. Do každé kategorie dáme pár fór
            for ($f = 1; $f <= 4; $f++) {
                $forum = new ForumEntity();
                $forum->name = "Fórum " . $faker->sentence(2);
                $forum->category = $category;
                $forum->active = true;
                $forum->sortOrder = $f;
                //$forum->topicCount = 0;


                $manager->persist($forum);

                for ($t = 1; $t <= 10; $t++) {
                    $topic = new TopicEntity();
                    $topic->name = $faker->sentence(5);
                    $topic->category = $category;
                    $topic->forum = $forum;
                    $topic->user = $faker->randomElement($users);

                    $firstPost = new PostEntity();
                    $firstPost->user = $topic->user;
                    $firstPost->category = $category;
                    $firstPost->forum = $forum;
                    $firstPost->topic = $topic;
                    $firstPost->title = $topic->name;
                    $firstPost->text = $faker->paragraphs(3, true);
                    $firstPost->addIpAddress = $faker->ipv4;

                    $manager->persist($firstPost);

                    $topic->firstPost = $firstPost;
                    $topic->lastPost = $firstPost;

                    $manager->persist($topic);

                    $replyCount = rand(5, 25);

                    for ($r = 1; $r <= $replyCount; $r++) {
                        $reply = new PostEntity();
                        $reply->user = $faker->randomElement($users);
                        $reply->category = $category;
                        $reply->forum = $forum;
                        $reply->topic = $topic;
                        $reply->text = $faker->paragraph(rand(1, 5));
                        $reply->addIpAddress = $faker->ipv4;

                        $manager->persist($reply);

                        $topic->lastPost = $reply;
                    }
                }
            }
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 20;
    }
}