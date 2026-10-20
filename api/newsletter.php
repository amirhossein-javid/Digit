<?php

declare(strict_types=1);

use Nova\Config\Environment;
use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\NewsletterRepository;
use Nova\Service\NewsletterValidator;

require __DIR__ . '/_bootstrap.php';

Request::requirePost();
Request::requireSameOrigin();
$payload = Request::jsonBody(2048);
$email = (new NewsletterValidator())->validate($payload['email'] ?? null);
$address = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$clientHash = hash_hmac('sha256', $address, Environment::require('APP_KEY'));
$result = (new NewsletterRepository(nova_database()))->subscribe($email, $clientHash);

ApiResponse::success($result, [], $result['duplicate'] ? 200 : 201);
