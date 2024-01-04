<?php

namespace Tests\controller\announcements;

use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    public function testHomepage()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcements');

        $response = $app->handle($request);

        var_dump($_SESSION);

        $this->assertEquals(200, $response->getStatusCode());
    }

}