<?php

namespace Tests\controller\announcements;

use Tests\TestCase;

class AccountTest extends TestCase
{
    public function testPage()
    {

        $app = $this->getAppInstance();

        $request = $this->createRequest('GET','/admin/account');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}