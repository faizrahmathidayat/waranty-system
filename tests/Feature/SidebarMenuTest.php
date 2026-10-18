<?php

namespace Tests\Feature;

use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_menu_lists_numbered_setup_steps_first(): void
    {
        $this->actingAs(Login::factory()->create());

        $html = $this->get('/product-type')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'MASTER DATA'), strpos($html, 'nav-header">CMS') - strpos($html, 'MASTER DATA'));

        $this->assertMatchesRegularExpression(
            '/Product<sup class="menu-step">1<\/sup>.*Product Type<sup class="menu-step">2<\/sup>.*Kegelapan<sup class="menu-step">3<\/sup>.*Vehicle.*Building.*Teknisi.*Rekening.*Data User/s',
            $section
        );
        $this->assertStringNotContainsString('Treatment', $section);
        $this->assertSame(3, substr_count($section, 'menu-step">'));
    }
}
