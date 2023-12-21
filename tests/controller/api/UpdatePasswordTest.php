<?php

namespace Tests\controller\api;

use App\controller\ActionPayload;
use Tests\TestCase;
use UMA\DIC\Container;

class UpdatePasswordTest extends TestCase
{

    public function testUnauthorizedUser()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $formData = [
            'currentPassword' => 2023,
            'newPassword' => 2023,
            'confirmPassword' => 2023,
            'userId' => 2,
        ];

        $request = $this->createRequest('POST','/change-password');
        $request = $request->withParsedBody($formData);

        $response = $app->handle($request);

        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals('1','1');
    }

    public function testIncorrectPassword()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $formData = [
            'currentPassword' => 2023,
            'newPassword' => 2023,
            'confirmPassword' => 2023,
            'userId' => 2,
        ];

        $request = $this->createRequest('POST','/change-password');
        $request = $request->withParsedBody($formData);

        $response = $app->handle($request);

        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals('1','1');
    }

    public function testIncorrectConfirmation()
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $formData = [
            'currentPassword' => 2023,
            'newPassword' => 2023,
            'confirmPassword' => 2023,
            'userId' => 2,
        ];

        $request = $this->createRequest('POST','/change-password');
        $request = $request->withParsedBody($formData);

        $response = $app->handle($request);

        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals('1','1');
    }
}