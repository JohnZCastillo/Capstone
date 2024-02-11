<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class ArchiveAnnouncementTest extends TestCase
{

    public function testHomepage()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST','/admin/announcement/archive/1');

        $response = $app->handle($request);

        $this->assertEquals(303, $response->getStatusCode());

    }

    public function testNotExist()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST','/admin/announcement/archive/999');

        $response = $app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());

    }

}