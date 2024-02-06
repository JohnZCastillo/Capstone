<?php

namespace App\fixtures;

use Doctrine\Common\DataFixtures\Loader;

$loader = new Loader();

$loader->addFixture(new AdminFixture());

return $loader;
