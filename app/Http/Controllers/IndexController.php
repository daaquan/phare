<?php

namespace App\Http\Controllers;

use Phare\Attributes\Route;
use Phare\Http\Request;
use Phare\Support\Facades\Inertia;

class IndexController extends Controller
{
    #[Route('/', name: 'welcome')]
    public function welcome(Request $request)
    {
        return Inertia::render('Welcome', [
            'title' => __('welcome.title'),
            'strings' => [
                'login' => __('welcome.login'),
                'dashboard' => __('welcome.dashboard'),
                'footer' => __('welcome.footer'),
            ],
            'cards' => [
                [
                    'title' => __('welcome.cards.docs.title'),
                    'description' => __('welcome.cards.docs.description'),
                    'href' => 'https://laravel.com/docs',
                    'icon' => 'docs',
                ],
                [
                    'title' => __('welcome.cards.phalcon.title'),
                    'description' => __('welcome.cards.phalcon.description'),
                    'href' => 'https://phalcon.io',
                    'icon' => 'phalcon',
                ],
                [
                    'title' => __('welcome.cards.github.title'),
                    'description' => __('welcome.cards.github.description'),
                    'href' => 'https://github.com/daaquan/framework',
                    'icon' => 'github',
                ],
            ],
        ]);
    }

    #[Route('/dashboard', middlewares: ['auth'], name: 'dashboard')]
    public function dashboard(Request $request)
    {
        return Inertia::render('Dashboard', [
            'title' => __('pages.users.title'),
            'description' => __('pages.users.description'),
            'stats' => [
                ['title' => 'Downloads', 'value' => '31K', 'desc' => 'Jan 1st - Feb 1st'],
                ['title' => 'New Users', 'value' => '4,200', 'desc' => '↗︎ 400 (22%)'],
                ['title' => 'New Registers', 'value' => '1,200', 'desc' => '↘︎ 90 (14%)'],
            ],
            'rows' => [
                ['id' => 1, 'name' => 'Cy Ganderton', 'job' => 'Quality Control Specialist', 'color' => 'Blue'],
                ['id' => 2, 'name' => 'Hart Hagerty', 'job' => 'Desktop Support Technician', 'color' => 'Purple'],
                ['id' => 3, 'name' => 'Brice Swyre', 'job' => 'Tax Accountant', 'color' => 'Red'],
            ],
        ]);
    }
}
