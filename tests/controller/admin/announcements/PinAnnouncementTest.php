<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class PinAnnouncementTest extends TestCase
{

    public function testPin()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST', '/admin/announcement/pin/1');

        $response = $app->handle($request);

        $this->assertEquals(303, $response->getStatusCode());
    }

}