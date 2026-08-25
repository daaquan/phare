<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class PostController extends Controller
{
    /**
     * Render the paginated post list.
     */
    #[Route('/posts', methods: ['GET'], name: 'posts.index')]
    public function index(Request $request)
    {
        $perPage = 10;
        $page = max(1, (int)$request->getQuery('page', 'int', 1));

        $posts = Post::query()->orderByDesc('id')->paginate($perPage, $page);

        $items = [];
        foreach ($posts->items() as $post) {
            $items[] = [
                'id' => $post->id,
                'title' => $post->title,
                'author' => $post->user?->name ?? '—',
            ];
        }

        return Inertia::render('Posts/Index', [
            'title' => 'Posts',
            'posts' => [
                'data' => $items,
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }
}
