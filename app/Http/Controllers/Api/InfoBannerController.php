<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InfoBanner;
use Illuminate\Http\Request;

class InfoBannerController extends Controller
{
    // Fetch current banner
    public function index()
    {
        $banner = InfoBanner::first();
        return response()->json($banner);
    }

    // Update banner (message or visibility)
    public function update(Request $request)
    {
        $banner = InfoBanner::first();
            if (!$banner) {
                $banner = InfoBanner::create([
                    'message' => '',
                    'is_visible' => false  // <- hide by default
                ]);
            }


        $banner->update([
            'message' => $request->input('message', $banner->message),
            'is_visible' => $request->input('is_visible', $banner->is_visible),
        ]);

        return response()->json($banner);
    }
}
