<?php declare(strict_types=1);

namespace App\Database\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

/**
 * Every connection works in the time zone of PHP (php.ini date.timezone): the columns are timestamptz, PostgreSQL returns
 * them in the time zone of the session and compares parameters without a zone (Doctrine binds a DateTimeImmutable
 * parameter as "Y-m-d H:i:s") in it too. Without it the times would be shown in the zone of the server (UTC in Docker).
 */
final class SessionTimeZoneMiddleware implements Middleware
{
    public function wrap(Driver $driver) : Driver
    {
        return new SessionTimeZoneDriver($driver, date_default_timezone_get());
    }
}
