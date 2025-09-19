<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hug;

class HugController extends Controller
{
    public function index()
    {
        $hug = Hug::first();
        return response()->json(['count' => $hug->count]);
    }

    public function increment()
    {
        $hug = Hug::first();
        $hug->increment('count');
        return response()->json(['count' => $hug->count]);
    }
}
