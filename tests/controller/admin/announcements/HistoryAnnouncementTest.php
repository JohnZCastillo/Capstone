<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class HistoryAnnouncementTest extends TestCase
{

    public function testEditAnnouncement()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/announcement/edit/history/1');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}