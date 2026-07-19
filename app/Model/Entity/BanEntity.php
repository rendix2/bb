<?php declare(strict_types=1);

namespace App\Model\Entity;


use App\Database\Types\IpAddressType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'ban')]
class BanEntity
{

    #[Id()]
    #[GeneratedValue()]
    #[Column(type: Types::BIGINT)]
    public string $id;


    #[Column(type: Types::STRING, length: 512)]
    public string $email;

    #[Column(type: IpAddressType::NAME, nullable: false)]
    public string $ipAddress;

    #[Column(type: Types::STRING, length: 512)]
    public string $username;

}