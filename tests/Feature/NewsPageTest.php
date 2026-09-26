<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsPageTest extends TestCase
{
    use RefreshDatabase;

    private function article(string $title, int $daysAgo): News
    {
        return News::create([
            'image' => 'news/x.jpg', 'title' => $title,
            'content' => 'Konten ' . $title, 'published_date' => now()->subDays($daysAgo),
        ]);
    }

    public function test_empty_state_has_no_featured_card(): void
    {
        $this->get('/news')->assertOk()
            ->assertSee('Belum ada berita')
            ->assertDontSee('>Terbaru<', false);
    }

    public function test_latest_article_is_featured_and_excluded_from_the_grid(): void
    {
        $latest = $this->article('Artikel Terbaru', 0);
        $older1 = $this->article('Artikel Lama Satu', 1);
        $older2 = $this->article('Artikel Lama Dua', 2);

        $response = $this->get('/news')->assertOk();
        $response->assertSeeInOrder(['featured-news', 'Artikel Terbaru', 'Terbaru']);

        // Artikel terbaru tidak diulang sebagai kartu biasa di grid (heading h5 grid).
        $response->assertDontSee('<h5 class="news-title">Artikel Terbaru</h5>', false);
        $response->assertSee('Artikel Lama Satu')->assertSee('Artikel Lama Dua');
    }

    public function test_only_page_one_shows_a_featured_card(): void
    {
        foreach (range(1, 7) as $i) {
            $this->article("Artikel $i", $i);
        }

        $this->get('/news')->assertSee('>Terbaru<', false);
        $this->get('/news?page=2')->assertOk()->assertDontSee('>Terbaru<', false);
    }
}
