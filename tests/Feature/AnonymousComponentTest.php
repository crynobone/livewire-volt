<?php

use Laravel\Folio\Folio;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Volt\FragmentMap;
use Livewire\Volt\Volt;

beforeEach(function () {
    Volt::mount([
        __DIR__.'/resources/views/functional-api-pages',
        __DIR__.'/resources/views/functional-api',
    ]);

    $this->app['config']->set('database.default', 'testbench');

    $this->app['config']->set('database.connections.testbench', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    $this->artisan('migrate', [
        '--database' => 'testbench',
        '--path' => 'migrations',
    ]);

    $this->artisan('migrate', [
        '--database' => 'testbench',
        '--path' => __DIR__.'/resources/migrations',
        '--realpath' => true,
    ]);
});

it('may be tested', function () {
    Volt::test('fragment-component', ['name' => 'Taylor'])
        ->assertSee('Hello Taylor');

    Volt::test('first-fragment-component', ['name' => 'Taylor'])
        ->assertSee('First - Hello Taylor');

    Volt::test('second-fragment-component', ['name' => 'Taylor'])
        ->assertSee('Second - Hello Taylor');
});

it('may be lazy', function () {
    Folio::route(__DIR__.'/resources/views/functional-api-pages');

    Volt::test('named-lazy-fragment-component')
        ->assertSee('Hello From Named Lazy');

    Volt::test('named-eager-fragment-component')
        ->assertSee('Hello From Named Eager');

    $this->get('page-with-lazy-fragment')
        ->assertDontSee('Hello From Named Lazy')
        ->assertDontSee('Hello From Lazy')
        ->assertDontSee('Hello From Named Lazy On Load')
        ->assertDontSee('Hello From Lazy On Load')
        ->assertSee('Hello From Eager')
        ->assertSee('Hello From Non Named Eager')
        ->assertSee('Hello From Named Eager');
});

it('throws component not found exception when component does not exist', function () {
    Volt::test('non-existent-component', ['name' => 'Taylor'])->dd()
        ->assertSee('Second - Hello Taylor');
})->throws(
    ComponentNotFoundException::class,
    'Unable to find component: [non-existent-component]'
);

it('throws component not found exception when the component alias was tampered', function () {
    Volt::test('fragment-component', ['name' => 'Taylor'])
        ->assertSee('Hello Taylor');

    FragmentMap::add('fragment-component', 'volt-anonymous-fragment-another-base64-hash');

    Volt::test('fragment-component', ['name' => 'Taylor'])
        ->assertSee('Hello Taylor');
})->throws(
    ComponentNotFoundException::class,
    'Unable to find component'
);
