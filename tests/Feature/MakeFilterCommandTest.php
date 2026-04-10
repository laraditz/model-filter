<?php

declare(strict_types=1);

// Clean up any generated files after each test
afterEach(function (): void {
    $dir = app_path('Filters');
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($dir);
    }
});

// ── Happy path ────────────────────────────────────────────────────────────────

it('generates a filter file at app/Filters/{Name}.php', function (): void {
    $this->artisan('make:filter', ['name' => 'TestFilter'])
        ->assertSuccessful();

    expect(file_exists(app_path('Filters/TestFilter.php')))->toBeTrue();
});

it('generated file contains correct namespace and class name', function (): void {
    $this->artisan('make:filter', ['name' => 'PostFilter'])
        ->assertSuccessful();

    $content = file_get_contents(app_path('Filters/PostFilter.php'));

    expect($content)
        ->toContain("namespace App\\Filters;")
        ->toContain('class PostFilter extends Filter');
});

it('creates the Filters directory when it does not exist', function (): void {
    expect(is_dir(app_path('Filters')))->toBeFalse();

    $this->artisan('make:filter', ['name' => 'TestFilter'])
        ->assertSuccessful();

    expect(is_dir(app_path('Filters')))->toBeTrue();
});

// ── --force flag ──────────────────────────────────────────────────────────────

it('errors when filter already exists without --force', function (): void {
    mkdir(app_path('Filters'), 0755, true);
    file_put_contents(app_path('Filters/TestFilter.php'), '<?php // existing');

    $this->artisan('make:filter', ['name' => 'TestFilter'])
        ->assertFailed();
});

it('overwrites existing file when --force is passed', function (): void {
    mkdir(app_path('Filters'), 0755, true);
    file_put_contents(app_path('Filters/TestFilter.php'), '<?php // old content');

    $this->artisan('make:filter', ['name' => 'TestFilter', '--force' => true])
        ->assertSuccessful();

    $content = file_get_contents(app_path('Filters/TestFilter.php'));

    expect($content)
        ->not->toContain('// old content')
        ->toContain('class TestFilter extends Filter');
});

// ── Input normalization ───────────────────────────────────────────────────────

it('strips .php suffix from name', function (): void {
    $this->artisan('make:filter', ['name' => 'TestFilter.php'])
        ->assertSuccessful();

    expect(file_exists(app_path('Filters/TestFilter.php')))->toBeTrue();

    $content = file_get_contents(app_path('Filters/TestFilter.php'));
    expect($content)->toContain('class TestFilter extends Filter');
});

// ── Path separator rejection ──────────────────────────────────────────────────

it('errors when name contains a forward slash', function (): void {
    $this->artisan('make:filter', ['name' => 'Admin/TestFilter'])
        ->assertFailed();
});

it('errors when name contains a backslash', function (): void {
    // Single-quoted 'Admin\\TestFilter' is one backslash in the string
    $this->artisan('make:filter', ['name' => 'Admin\\TestFilter'])
        ->assertFailed();
});
