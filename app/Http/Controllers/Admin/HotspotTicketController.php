<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HotspotUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class HotspotTicketController extends Controller
{
    /**
     * GET /admin/hotspot-users/{hotspotUser}/ticket.pdf
     */
    public function single(Request $request, HotspotUser $hotspotUser)
    {
        $this->authorizeAdmin();

        $users = [$hotspotUser];
        $title = "Ticket Hotspot {$hotspotUser->username}";

        $pdf = Pdf::loadView('hotspot.pdf.tickets', [
            'users' => $users,
            'title' => $title,
            'batch_ref' => $hotspotUser->batch_ref,
            'generated_at' => now(),
            'single' => true,
        ])->setPaper('a4');

        return $pdf->stream("ticket-{$hotspotUser->username}.pdf");
    }

    /**
     * GET /admin/hotspot-users/tickets.pdf?ids=1,2,3
     */
    public function batch(Request $request)
    {
        $this->authorizeAdmin();

        $ids = collect(explode(',', (string)$request->query('ids')))
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->take(1000) // sécurité
            ->all();

        if (empty($ids)) {
            abort(400, 'No IDs provided.');
        }

        $users = HotspotUser::with('userProfile')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            abort(404, 'No users found.');
        }

        $batchRef = $request->query('batch') ?? $users->first()->batch_ref;
        $title = $batchRef ? "Tickets Batch {$batchRef}" : 'Tickets Hotspot';

        $pdf = Pdf::loadView('hotspot.pdf.tickets', [
            'users' => $users,
            'title' => $title,
            'batch_ref' => $batchRef,
            'generated_at' => now(),
            'single' => false,
        ])->setPaper('a4');

        return $pdf->download(
            ($batchRef ? "hotspot-tickets-{$batchRef}" : 'hotspot-tickets').'.pdf'
        );
    }

    private function authorizeAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }
}