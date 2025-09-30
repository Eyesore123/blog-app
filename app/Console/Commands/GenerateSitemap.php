<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use Illuminate\Support\Facades\Http;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml in public folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add homepage
        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        // Privacy Policy
        $sitemap->add(
            Url::create('/privacy-policy')
                ->setPriority(0.5)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
        );

        // Archives pages (by year)
        $years = Post::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        foreach ($years as $year) {
            $sitemap->add(
                Url::create("/archives/{$year}")
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            );
        }

        // Posts (using slug if present, fallback to id)
        $posts = Post::where('published', true)->get();

        foreach ($posts as $post) {
            $slugOrId = $post->slug ?: $post->id;

            $sitemap->add(
                Url::create("/posts/{$slugOrId}")
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            );
        }

        // Pagination for main posts listing
        $postsPerPage = 10;
        $totalPages = (int) ceil($posts->count() / $postsPerPage);

        for ($i = 1; $i <= $totalPages; $i++) {
            $sitemap->add(
                Url::create("/page/{$i}")
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }

        // Save the sitemap to /public
        $sitemapPath = public_path('sitemap.xml');
        $sitemap->writeToFile($sitemapPath);

        $this->info("✅ Sitemap generated at {$sitemapPath}");

        // Optional: Ping Google
        if ($this->option('ping-google')) {
            $this->pingGoogle();
        }

        return 0;
    }

    protected function pingGoogle()
    {
        $sitemapUrl = url('sitemap.xml');
        $this->info("Pinging Google for {$sitemapUrl}...");

        try {
            $response = Http::get('https://www.google.com/ping', [
                'sitemap' => $sitemapUrl,
            ]);

            if ($response->successful()) {
                $this->info('✅ Google has been pinged successfully!');
            } else {
                $this->warn('⚠️ Google ping returned status: ' . $response->status());
            }
        } catch (\Throwable $e) {
            $this->error('❌ Failed to ping Google: ' . $e->getMessage());
        }
    }
}
