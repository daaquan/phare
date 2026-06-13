<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Pagination\LengthAwarePaginator;

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

        $total = (int)Post::count();

        $items = [];
        foreach (Post::query()->orderByDesc('id')->forPage($page, $perPage)->get() as $post) {
            $items[] = $post;
        }

        $posts = new LengthAwarePaginator($items, $total, $perPage, $page, ['path' => '/posts']);

        return view('posts/index')
            ->with('title', 'Posts')
            ->with('posts', $posts);
    }
}
