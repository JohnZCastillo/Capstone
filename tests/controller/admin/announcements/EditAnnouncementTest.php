<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class EditAnnouncementTest extends TestCase
{

    public function testEditAnnouncement()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcement/edit/1');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEditNonExistingAnnouncement()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcement/edit/199');

        $response = $app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
    }

}