<?php

namespace Tests\controller\admin\payments;

use App\controller\ActionPayload;
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

        $expected = $data = [
            [
                "date" => [
                    "date" => "2023-01-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 50,
                "savePoint" => true,
                "month" => "Jan"
            ],
            [
                "date" => [
                    "date" => "2023-02-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 120,
                "savePoint" => true,
                "month" => "Feb"
            ],
            [
                "date" => [
                    "date" => "2023-03-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 100,
                "savePoint" => true,
                "month" => "Mar"
            ],
            [
                "date" => [
                    "date" => "2023-04-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => true,
                "month" => "Apr"
            ],
            [
                "date" => [
                    "date" => "2023-05-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "May"
            ],
            [
                "date" => [
                    "date" => "2023-06-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Jun"
            ],
            [
                "date" => [
                    "date" => "2023-07-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Jul"
            ],
            [
                "date" => [
                    "date" => "2023-08-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Aug"
            ],
            [
                "date" => [
                    "date" => "2023-09-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Sep"
            ],
            [
                "date" => [
                    "date" => "2023-10-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Oct"
            ],
            [
                "date" => [
                    "date" => "2023-11-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Nov"
            ],
            [
                "date" => [
                    "date" => "2023-12-01 00:00:00.000000",
                    "timezone_type" => 3,
                    "timezone" => "Europe/Berlin"
                ],
                "amount" => 22,
                "savePoint" => false,
                "month" => "Dec"
            ]
        ];


        $request = $this->createRequest('POST','/admin/payments/year-dues');
        $request = $request->withParsedBody($formData);

        $response = $app->handle($request);

        $payload = (string) $response->getBody();

        $expectedPayload = new ActionPayload(200, $expected);
        $serializedPayload = json_encode($expectedPayload, JSON_PRETTY_PRINT);


        $this->assertEquals($serializedPayload, $payload);
    }

}