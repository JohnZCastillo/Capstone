<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class AnnouncementTest extends TestCase
{


    public function testHomepage()
    {

        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcement');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}