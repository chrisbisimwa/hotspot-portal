<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\Services\ExportService;
use App\Http\Controllers\Controller;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDownloadController extends Controller
{
    public function __construct(
        private ExportService $exportService
    ) {}

    /**
     * Download an export file
     */
    public function download(Request $request, Export $export): BinaryFileResponse
    {
        // Check if user can access this export
        if (!$this->exportService->canUserAccessExport($export, $request->user())) {
            abort(403, 'You do not have permission to download this export.');
        }

        // Check if export is completed
        if (!$export->isCompleted()) {
            abort(404, 'Export is not ready for download.');
        }

        // Check if file exists
        if (!$export->file_path) {
            abort(404, 'Export file not found.');
        }

        $disk = Storage::disk(config('exports.storage_disk', 'local'));
        
        if (!$disk->exists($export->file_path)) {
            abort(404, 'Export file not found on storage.');
        }

        $filename = $export->getDownloadFilename();
        $filePath = $disk->path($export->file_path);

        return response()->download($filePath, $filename, [
            'Content-Type' => $this->getContentType($export->format),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Get content type for export format
     */
    private function getContentType(string $format): string
    {
        return match ($format) {
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}