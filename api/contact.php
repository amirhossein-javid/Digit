<?php

declare(strict_types=1);

use Nova\Config\Environment;
use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\ContactMessageRepository;
use Nova\Service\ContactMessageValidator;

require __DIR__ . '/_bootstrap.php';

Request::requirePost();
Request::requireSameOrigin();
$message = (new ContactMessageValidator())->validate(Request::jsonBody(8192));
$address = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$clientHash = hash_hmac('sha256', $address, Environment::require('APP_KEY'));
$id = (new ContactMessageRepository(nova_database()))->create($message, $clientHash);

ApiResponse::success(['messageId' => $id], [], 201);
