<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class PostAnnouncementTest extends TestCase
{

    public function testPost()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST','/admin/announcement/post/1');

        $response = $app->handle($request);

        $this->assertEquals(303, $response->getStatusCode());

    }

}