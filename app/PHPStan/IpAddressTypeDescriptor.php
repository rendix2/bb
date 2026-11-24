<?php declare(strict_types=1);

namespace App\PHPStan;

use App\Database\Types\IpAddressType;
use PHPStan\Type\Doctrine\Descriptors\DoctrineTypeDescriptor;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

/**
 * Tells phpstan-doctrine what the custom DBAL type `ip_address` (App\Database\Types\IpAddressType, INET) holds: a string on both
 * sides, so the columns of IP addresses are checked against their properties like any other string column.
 */
final class IpAddressTypeDescriptor implements DoctrineTypeDescriptor
{
    public function getType() : string
    {
        return IpAddressType::class;
    }

    public function getWritableToPropertyType() : Type
    {
        return new StringType();
    }

    public function getWritableToDatabaseType() : Type
    {
        return new StringType();
    }

    public function getDatabaseInternalType() : Type
    {
        return new StringType();
    }
}
