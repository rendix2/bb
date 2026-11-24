<?php declare(strict_types=1);

namespace Tests\Integration\Schema;

use App\Model\Enum\BanScope;
use App\Model\Enum\ReportStatus;
use Contributte\Translation\Translator;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Tests\Support\IntegrationTester;

/**
 * What the database guards besides the Doctrine mapping (the postUp() of the create migrations, DBAL does not introspect
 * it, so SchemaInSyncCest cannot see it): times with a time zone, the case-insensitive uniqueness of the username and the
 * email, CHECK constraints. Every test runs in a transaction that is rolled back.
 */
class SchemaConstraintsCest
{
    public function _before(IntegrationTester $I) : void
    {
        $this->connection($I)->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        $this->connection($I)->rollBack();
        $I->em()->clear();
    }

    /**
     * A time without a zone is right only while PHP and the database run in the same zone.
     */
    public function timesHaveATimeZone(IntegrationTester $I) : void
    {
        /** @var list<string> $columns */
        $columns = $this->connection($I)->createQueryBuilder()
            ->select("table_name || '.' || column_name")
            ->from('information_schema.columns')
            ->where("table_schema = 'public'")
            ->andWhere("data_type = 'timestamp without time zone'")
            // the table of doctrine/migrations has no entity
            ->andWhere("table_name != 'migrations'")
            ->orderBy('1')
            ->fetchFirstColumn();

        $I->assertSame([], $columns, 'These columns are timestamp without time zone, use Types::DATETIMETZ_IMMUTABLE.');
    }

    /**
     * SessionTimeZoneMiddleware: the session works in the zone of PHP, so a time is read in it and a parameter without
     * a zone ("Y-m-d H:i:s", how Doctrine binds DateTimeImmutable) means the same instant as in PHP.
     */
    public function theSessionUsesTheTimeZoneOfPhp(IntegrationTester $I) : void
    {
        $connection = $this->connection($I);
        $I->assertSame(date_default_timezone_get(), $connection->fetchOne('SHOW TIME ZONE'));

        $now = new DateTimeImmutable('2026-07-01 12:00:00');
        $utc = $connection->createQueryBuilder()
            ->select("to_char(CAST(:time AS timestamptz) AT TIME ZONE 'UTC', 'YYYY-MM-DD HH24:MI:SS')")
            ->setParameter('time', $now->format('Y-m-d H:i:s'))
            ->fetchOne();

        $I->assertSame($now->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), $utc);
    }

    public function theUsernameIsUniqueRegardlessOfTheCase(IntegrationTester $I) : void
    {
        $first = $I->createUser('Petr');
        $second = $I->createUser('Pavel');

        $this->expectViolation($I, UniqueConstraintViolationException::class, fn () => $this->updateUser($I, $second->id, 'username', mb_strtoupper($first->username)));
        $this->expectViolation($I, UniqueConstraintViolationException::class, fn () => $this->updateUser($I, $second->id, 'email', mb_strtoupper($first->email)));
    }

    public function checkConstraintsRefuseNegativeCounts(IntegrationTester $I) : void
    {
        $connection = $this->connection($I);

        $this->expectViolation($I, DriverException::class, fn () => $connection->createQueryBuilder()
            ->update('topic')
            ->set('view_count', '-1')
            ->executeStatement());
        $this->expectViolation($I, DriverException::class, fn () => $connection->createQueryBuilder()
            ->update('rank')
            ->set('min_posts', '-1')
            ->executeStatement());
    }

    /**
     * The CHECK constraints of enum columns list exactly the cases of their enum, every case has a translation.
     */
    public function theEnumChecksFollowTheEnums(IntegrationTester $I) : void
    {
        $translator = $I->grabService(Translator::class);

        foreach (['ch__report__status' => ReportStatus::cases(), 'ch__ban__scope' => BanScope::cases()] as $constraint => $enumCases) {
            $definition = (string) $this->connection($I)->createQueryBuilder()
                ->select('pg_get_constraintdef(oid)')
                ->from('pg_constraint')
                ->where('conname = :name')
                ->setParameter('name', $constraint)
                ->fetchOne();
            preg_match_all("~'(\\w+)'~", $definition, $matches);
            $values = $matches[1];
            sort($values);

            $cases = array_map(static fn (ReportStatus|BanScope $case) : string => $case->value, $enumCases);
            sort($cases);

            $I->assertSame($cases, $values, "The CHECK constraint $constraint ($definition) does not list the cases of its enum.");

            foreach (['cs', 'en'] as $locale) {
                foreach ($enumCases as $case) {
                    $I->assertNotSame($case->translationKey(), $translator->translate($case->translationKey(), [], null, $locale), "Missing $locale translation of {$case->translationKey()}.");
                }
            }
        }
    }

    private function updateUser(IntegrationTester $I, int $id, string $column, string $value) : void
    {
        $this->connection($I)->createQueryBuilder()
            ->update('users')
            ->set($column, ':value')
            ->where('id = :id')
            ->setParameter('value', $value)
            ->setParameter('id', $id)
            ->executeStatement();
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    private function expectViolation(IntegrationTester $I, string $exception, callable $statement) : void
    {
        // a savepoint: the failed statement must not abort the transaction of the test
        $connection = $this->connection($I);
        $connection->beginTransaction();

        try {
            $statement();
            $I->fail("The database accepted it, $exception expected.");
        } catch (DriverException $e) {
            $I->assertInstanceOf($exception, $e);
        } finally {
            $connection->rollBack();
        }
    }

    private function connection(IntegrationTester $I) : Connection
    {
        return $I->em()->getConnection();
    }
}
