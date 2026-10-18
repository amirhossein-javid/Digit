<?php

declare(strict_types=1);

use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\CategoryRepository;

require __DIR__ . '/_bootstrap.php';

Request::requireGet();

$repository = new CategoryRepository(nova_database());
ApiResponse::success($repository->all());
