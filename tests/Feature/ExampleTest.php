<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_home_page_displays_the_available_module_links(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/');

        $response
            ->assertOk()
            ->assertSee('Productos')
            ->assertSee('Proveedores')
            ->assertSee('Insumos')
            ->assertSee('Recetas')
            ->assertSee('Salas')
            ->assertSee('Retail')
            ->assertSee('href="/productos"', false)
            ->assertSee('href="/proveedores"', false)
            ->assertSee('href="/insumos"', false);
    }
}
