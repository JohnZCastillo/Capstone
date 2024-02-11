<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class CreateAnnouncementTest extends TestCase
{


    public function testCreateAnnouncement()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcement');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}