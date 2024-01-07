<?php

namespace Tests\controller\admin\payments;

use App\lib\Login;
use Tests\TestCase;
use UMA\DIC\Container;

class YearlyDueTest extends TestCase
{
    public function testAction()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $formData = [
            'dueYear' => 2023,
        ];

        $request = $this->createRequest('GET','/admin/payments');

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

}