<?php declare(strict_types=1);

namespace App\Database\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use PDO;
use SensitiveParameter;

/**
 * Sets the time zone of the session right after connecting (SessionTimeZoneMiddleware).
 */
final class SessionTimeZoneDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $driver,
        private readonly string $timeZone,
    ) {
        parent::__construct($driver);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function connect(#[SensitiveParameter] array $params) : Connection
    {
        $connection = parent::connect($params);
        // straight to PDO, past the middleware of the Tracy panel (nettrine wraps it inside this one): the panel is static
        // and keeps the stack of every query from app/, with this frame it would keep the connection alive after close()
        // (Integration tests ran out of max_connections)
        $native = $connection->getNativeConnection();
        if ($native instanceof PDO) {
            $native->exec('SET TIME ZONE ' . $native->quote($this->timeZone));
        } else {
            $connection->exec('SET TIME ZONE ' . $connection->quote($this->timeZone));
        }

        return $connection;
    }
}
