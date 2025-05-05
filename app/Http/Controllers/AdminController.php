<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        return Inertia::render('AdminDashboard'); // Adjust view name if needed
    }
}
