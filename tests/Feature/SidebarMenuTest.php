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

        $html = $this->get('/treatment')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'MASTER DATA'), strpos($html, 'nav-header">CMS') - strpos($html, 'MASTER DATA'));

        $this->assertMatchesRegularExpression(
            '/Treatment<sup class="menu-step">1<\/sup>.*Product Type<sup class="menu-step">2<\/sup>.*Data Product<sup class="menu-step">3<\/sup>.*Product Variant<sup class="menu-step">4<\/sup>.*Vehicle.*Building.*Teknisi.*Rekening.*Data User/s',
            $section
        );
        $this->assertSame(4, substr_count($section, 'menu-step">'));
    }
}
