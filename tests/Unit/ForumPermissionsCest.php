<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Entity\ForumPermissions;
use Tests\Support\UnitTester;

/**
 * The flags of a forum, a group or a user only add up (ForumPermissions::union()): each flag set on either side is set in the
 * union, none other. union() names every flag explicitly, so a flag added to FLAGS without it would be lost silently.
 */
class ForumPermissionsCest
{
    public function theFlagsAreTheProperties(UnitTester $I) : void
    {
        $I->assertSame(ForumPermissions::FLAGS, array_keys(get_object_vars(new ForumPermissions())));
    }

    public function theUnionKeepsEveryFlagOfBothSides(UnitTester $I) : void
    {
        foreach (ForumPermissions::FLAGS as $flag) {
            $one = new ForumPermissions();
            $one->setFlags([$flag => true]);
            $empty = new ForumPermissions();

            $expected = array_fill_keys(ForumPermissions::FLAGS, false);
            $expected[$flag] = true;

            $I->assertSame($expected, get_object_vars($one->union($empty)), $flag . ' from the left side');
            $I->assertSame($expected, get_object_vars($empty->union($one)), $flag . ' from the right side');
        }
    }

    public function theUnionOfNothingIsEmpty(UnitTester $I) : void
    {
        $I->assertTrue((new ForumPermissions())->union(new ForumPermissions())->isEmpty());
    }
}
