<?php

namespace Tests\Feature;

use Tests\TestCase;

class FeedPageTest extends TestCase
{
    public function test_feed_page_redirects_guest_to_customer_login(): void
    {
        if (! app('router')->has('public.feed')) {
            $this->markTestSkipped('Marketplace/ecommerce frontend routes are not loaded in test environment.');
        }

        $response = $this->get('/feed');

        $response->assertStatus(302);
    }

    public function test_feed_items_endpoint_redirects_guest_to_customer_login(): void
    {
        if (! app('router')->has('public.feed.items')) {
            $this->markTestSkipped('Marketplace/ecommerce frontend routes are not loaded in test environment.');
        }

        $response = $this->get('/feed/items?page=2');

        $response->assertStatus(302);
    }
}
