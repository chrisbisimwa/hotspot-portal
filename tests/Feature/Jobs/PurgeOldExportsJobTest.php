<?php

declare(strict_types=1);

use App\Jobs\PurgeOldExportsJob;
use App\Models\Export;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('purges old exports', function () {
    Storage::fake('local');
    
    // Create old export
    $oldExport = Export::factory()->create([
        'created_at' => now()->subDays(10),
        'file_path' => 'exports/old_file.csv',
    ]);
    
    // Create new export
    $newExport = Export::factory()->create([
        'created_at' => now()->subDays(3),
        'file_path' => 'exports/new_file.csv',
    ]);
    
    // Create the files
    Storage::disk('local')->put('exports/old_file.csv', 'old content');
    Storage::disk('local')->put('exports/new_file.csv', 'new content');
    
    $job = new PurgeOldExportsJob();
    $job->handle();
    
    // Old export should be deleted
    expect(Export::find($oldExport->id))->toBeNull()
        ->and(Storage::disk('local')->exists('exports/old_file.csv'))->toBeFalse();
    
    // New export should remain
    expect(Export::find($newExport->id))->not->toBeNull()
        ->and(Storage::disk('local')->exists('exports/new_file.csv'))->toBeTrue();
});