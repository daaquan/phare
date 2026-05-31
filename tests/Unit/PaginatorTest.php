<?php

use Phare\Pagination\LengthAwarePaginator;

/*
 * LengthAwarePaginator is a pure (DB-free) value object, so its
 * Laravel-parity behaviour can be verified directly here.
 */

test('paginator computes totals, last page and window', function () {
    $items = [['id' => 1], ['id' => 2], ['id' => 3]];

    $paginator = new LengthAwarePaginator($items, total: 25, perPage: 10, currentPage: 2, options: ['path' => '/posts']);

    expect($paginator->total())->toBe(25)
        ->and($paginator->currentPage())->toBe(2)
        ->and($paginator->lastPage())->toBe(3)
        ->and($paginator->firstItem())->toBe(11)
        ->and($paginator->lastItem())->toBe(20)
        ->and($paginator->hasMorePages())->toBeTrue();
});

test('paginator renders previous/next links for the current page', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], total: 25, perPage: 10, currentPage: 2, options: ['path' => '/posts']);

    $html = $paginator->links();

    expect($html)->toContain('Previous')
        ->and($html)->toContain('Next')
        ->and($html)->toContain('/posts?page=1')
        ->and($html)->toContain('/posts?page=3');
});

test('paginator on the last page has no more pages', function () {
    $paginator = new LengthAwarePaginator([['id' => 1]], total: 25, perPage: 10, currentPage: 3, options: ['path' => '/posts']);

    expect($paginator->hasMorePages())->toBeFalse()
        ->and($paginator->lastItem())->toBe(25);
});
