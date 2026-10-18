<?php

declare(strict_types=1);

use Nova\Http\ApiResponse;
use Nova\Http\Request;
use Nova\Repository\ProductRepository;

require __DIR__ . '/_bootstrap.php';

Request::requireGet();
$id = Request::positiveInt('id', true);

$repository = new ProductRepository(nova_database());
$product = $repository->find($id);

if ($product === null) {
    ApiResponse::error('product_not_found', 'محصول موردنظر پیدا نشد.', 404);
}

ApiResponse::success($product);
