<?php

namespace Tests\controller\admin\announcements;

use Tests\TestCase;

class MakeAnnouncementTest extends TestCase
{

    public function testMakeAnnouncement()
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST', '/admin/announcement/post');

        $request = $request->withParsedBody([
            'title' => 'Hello world',
            'content' => 'hi john'
        ]);

        $response = $app->handle($request);

        $this->assertEquals(303, $response->getStatusCode());
    }

}