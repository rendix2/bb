<?php declare(strict_types=1);

namespace App\UI\Admin\Accessory;

use DateTimeImmutable;

final class BanValues
{
    public string $username;

    public string $email;

    public string $ipAddress;

    public string $scope;

    public ?string $reasonPreset;

    public string $reason;

    public ?DateTimeImmutable $expiresAt;
}
