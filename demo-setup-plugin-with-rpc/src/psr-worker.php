<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;

use Spiral\Goridge\Relay;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\Http\PSR7Worker;


$worker = Worker::create();

$factory = new Psr17Factory();

$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

$logger = new \Roadrunnerdemo\Php\Log\Logger();

$rpc = new RPC(
    Relay::create('tcp://127.0.0.1:6001')
);

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

        $resp1 = $rpc->call(
            "plugin_with_rpc.GetMessage",
            ""
        );
        $resp2 = $rpc->call(
            "plugin_with_rpc.Add",
            ["num1"=>10, "num2"=>20]
        );

        $psr7->respond(new Response(
            200,
            ['Request-Id'=>$reqId],
            $resp1  . ' -> '. $resp2["result"]));
    } catch (\Throwable $e) {
        $psr7->respond(new Response(500, [], $e->getMessage()));
        $psr7->getWorker()->error((string)$e);
    }
}