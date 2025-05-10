<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Post;
use Illuminate\Support\Str;

class RssFeedController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->take(10)->get();

        $xml = new \SimpleXMLElement('<rss version="2.0"/>');
        $channel = $xml->addChild('channel');

        $channel->addChild('title', config('app.name'));
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Latest blog posts from ' . config('app.name'));

        foreach ($posts as $post) {
            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($post->title));
            $item->addChild('link', url('/posts/' . $post->slug));
            $item->addChild('pubDate', $post->created_at->toRfc2822String());
            $item->addChild('guid', url('/posts/' . $post->slug));

            // Use CDATA for description (to allow HTML safely)
            $description = $item->addChild('description');
            $node = dom_import_simplexml($description);
            $nohtml = Str::limit(strip_tags($post->content), 200);
            $cdata = $node->ownerDocument->createCDATASection($nohtml);
            $node->appendChild($cdata);
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
