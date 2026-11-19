<?php declare(strict_types=1);

namespace App\Database\Fixtures;

use App\Model\Entity\RankEntity;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Ranks by the post count and one special rank (given by the admin, see UserFixture).
 */
class RankFixture implements FixtureInterface, OrderedFixtureInterface
{
    public const string SpecialRankName = 'Administrátor';

    public function load(ObjectManager $manager) : void
    {
        $ranks = [
            ['Nováček', 0],
            ['Člen', 10],
            ['Pokročilý', 50],
            ['Veterán', 200],
            [self::SpecialRankName, null],
        ];

        foreach ($ranks as $i => [$name, $minPosts]) {
            $rankEntity = new RankEntity();
            $rankEntity->name = $name;
            $rankEntity->minPosts = $minPosts;
            $rankEntity->isSpecial = $minPosts === null;
            $rankEntity->fileName = 'rank-' . ($i + 1) . '.png';

            $manager->persist($rankEntity);
        }

        $manager->flush();
    }

    public function getOrder() : int
    {
        return 5;
    }

}
