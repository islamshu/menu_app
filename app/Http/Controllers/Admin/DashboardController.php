<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Platform overview for super admins.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'restaurants' => Restaurant::count(),
                'users' => User::count(),
                'plans' => Plan::count(),
            ],
        ]);
    }
}
