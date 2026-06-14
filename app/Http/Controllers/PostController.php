<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class PostController extends Controller
{
    /**
     * 投稿一覧をページネーション付きで表示する。
     */
    #[Route('/posts', methods: ['GET'], name: 'posts.index')]
    public function index(Request $request)
    {
        $perPage = 10;
        $page = max(1, (int)$request->getQuery('page', 'int', 1));

        $total = (int)Post::count();

        $items = [];
        foreach (Post::query()->orderByDesc('id')->forPage($page, $perPage)->get() as $post) {
            $items[] = [
                'id' => $post->id,
                'title' => $post->title,
                'author' => $post->user?->name ?? '—',
            ];
        }

        return Inertia::render('Posts/Index', [
            'title' => '投稿一覧',
            'posts' => [
                'data' => $items,
                'current_page' => $page,
                'last_page' => max(1, (int)ceil($total / $perPage)),
            ],
        ]);
    }
}
