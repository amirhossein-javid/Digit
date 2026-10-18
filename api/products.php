<?php

declare(strict_types=1);

use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\ProductRepository;

require __DIR__ . '/_bootstrap.php';

Request::requireGet();

$search = Request::optionalString('q', 100);
$category = Request::optionalString('category', 100);
$sort = Request::enum('sort', ['default', 'price-asc', 'price-desc', 'name', 'newest'], 'default');
$page = Request::boundedInt('page', 1, 1, 100000);
$limit = Request::boundedInt('limit', 12, 1, 100);

$repository = new ProductRepository(nova_database());
$result = $repository->paginate($search, $category, $sort, $page, $limit);
$pages = $result['total'] === 0 ? 0 : (int) ceil($result['total'] / $limit);

ApiResponse::success($result['items'], [
    'page' => $page,
    'limit' => $limit,
    'total' => $result['total'],
    'pages' => $pages,
]);
