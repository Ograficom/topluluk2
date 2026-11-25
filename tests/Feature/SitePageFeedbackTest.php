<?php

namespace Tests\Feature;

use App\Models\SitePage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePageFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_vote_change_vote_and_submit_feedback_without_duplicate(): void
    {
        $page = SitePage::query()->create(['title' => 'Test sayfası', 'slug' => 'feedback-test', 'is_published' => true]);
        $url = route('pages.feedback.store', $page->slug);

        $this->getJson($url)->assertOk()->assertJson(['like' => 0, 'dislike' => 0, 'vote' => null]);
        $this->postJson($url, ['action' => 'vote', 'vote' => 'like'])
            ->assertOk()->assertJson(['like' => 1, 'dislike' => 0, 'vote' => 'like']);
        $this->postJson($url, ['action' => 'vote', 'vote' => 'like'])
            ->assertOk()->assertJson(['like' => 1]);
        $this->postJson($url, ['action' => 'feedback', 'vote' => 'dislike', 'email' => 'test@example.com', 'message' => 'Bir öneri'])
            ->assertOk()->assertExactJson(['like' => 0, 'dislike' => 1, 'vote' => 'dislike']);
        $this->assertDatabaseCount('site_page_feedback', 1);
        $this->assertDatabaseHas('site_page_feedback', ['site_page_id' => $page->id, 'vote' => 'dislike', 'message' => 'Bir öneri']);
        $this->postJson($url, ['action' => 'vote', 'vote' => 'like'])->assertOk();
        $this->assertDatabaseHas('site_page_feedback', ['site_page_id' => $page->id, 'vote' => 'like', 'message' => 'Bir öneri']);
    }

    public function test_page_uses_original_theme_popup(): void
    {
        $page = SitePage::query()->create(['title' => 'Popup', 'slug' => 'feedback-popup', 'is_published' => true]);
        $this->get(route('pages.show', $page->slug))
            ->assertOk()
            ->assertSee('data-modal-btn="site-page-feedback"', false)
            ->assertSee('data-modal="site-page-feedback"', false)
            ->assertSee('form="site-feedback-form"', false);
    }

    public function test_unpublished_page_and_invalid_feedback_are_rejected(): void
    {
        $page = SitePage::query()->create(['title' => 'Taslak', 'slug' => 'feedback-draft', 'is_published' => false]);
        $this->postJson(route('pages.feedback.store', $page->slug), ['action' => 'vote', 'vote' => 'like'])->assertNotFound();

        $page->update(['is_published' => true]);
        $this->postJson(route('pages.feedback.store', $page->slug), ['action' => 'feedback', 'vote' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors(['vote', 'message']);
    }
}
