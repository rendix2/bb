<?php

use App\Bootstrap;
use Doctrine\ORM\EntityManagerInterface;

require __DIR__ . '/../../vendor/autoload.php';

// RobotLoader of the app rebuilds its cache when PHPStan asks it for an unknown name; PHPStan traps reading files at that
// moment, so the Finder the rebuild needs must be loaded beforehand (otherwise "Class Nette\Utils\Finder not found")
class_exists(Nette\Utils\Finder::class);

return (new Bootstrap())->boot('console')->createContainer()
    ->getByType(EntityManagerInterface::class);