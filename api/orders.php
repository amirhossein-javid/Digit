<?php

declare(strict_types=1);

use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\OrderRepository;
use Nova\Service\OrderValidator;

require __DIR__ . '/_bootstrap.php';

Request::requirePost();
$payload = Request::jsonBody();
$validated = (new OrderValidator())->validate($payload, Request::header('Idempotency-Key'));
$result = (new OrderRepository(nova_database()))->create($validated);

ApiResponse::success($result, [], $result['duplicate'] ? 200 : 201);
