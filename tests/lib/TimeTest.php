<?php

namespace Tests\lib;

use App\controller\ActionPayload;
use App\lib\Time;
use Tests\TestCase;
use UMA\DIC\Container;

class TimeTest extends TestCase
{
    public function testTrue()
    {
        $valid = Time::isValidFormat('Y-m','2023-02');
        $this->assertTrue($valid);
    }

    public function testFalse()
    {
        $valid = Time::isValidFormat('Y-m','2023-02-01');
        $this->assertFalse($valid);
    }

    public function testRandom()
    {
        $random = Time::isValidFormat('Y-m','safsjafjlsajfkl');
        $this->assertFalse($random);

    }

}