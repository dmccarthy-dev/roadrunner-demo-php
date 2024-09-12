<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;

use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;


$worker = Worker::create();

$factory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

$logger = new \Roadrunnerdemo\Php\Log\Logger();
$purchaseHistoryClient = new \Roadrunnerdemo\Php\Clients\PurchaseHistoryClient();

while (true) {
    try {
        $request = $psr7->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        $psr7->respond(new Response(400));
        continue;
    }

    try {
        $reqId = $request->getAttribute("attr_request_id", "");

        $logger->INFO($reqId, "Processing Request");

        $headers = [
            'Request-Id'=>$reqId,
            'Content-Type'=>'application/json'
        ];

        $history = includeHistory($request) ?
            $purchaseHistoryClient->getPurchaseHistory("b1302b2a-795e-4faa-a730-a278e218b72b"):
            [];

        $body = [
            'id' => "b1302b2a-795e-4faa-a730-a278e218b72b",
            'name' => 'Jane Smith',
            'email' => 'jsmith@example.com',
            'purchase_history' => $history
        ];

        $psr7->respond(new Response(
            200,
            $headers,
            json_encode($body)));

    } catch (\Throwable $e) {
        $psr7->respond(new Response(500, [], $e->getMessage()));
        $psr7->getWorker()->error((string)$e);
    }
}

function includeHistory(\Psr\Http\Message\ServerRequestInterface $request ): bool
{
    $params = $request->getQueryParams();
    if (array_key_exists('include-history', $params )){
        return $request->getQueryParams()['include-history'] === 'true';
    }
    return false;
}