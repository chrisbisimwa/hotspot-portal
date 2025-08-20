<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class LogExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $this->authorize('viewAdminLogs'); // À définir ou remplacer par middleware/role

        $levels = (array) $request->get('levels', []);
        $action = $request->get('action');
        $actorId = $request->get('actorId');
        $loggableType = $request->get('loggableType');
        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');
        $search = $request->get('search');
        $includeDeleted = (bool)$request->get('includeDeleted', false);
        $onlyDeleted = (bool)$request->get('onlyDeleted', false);

        $maxRows = 5000;

        $query = Log::query()
            ->when($levels, fn($q) => $q->whereIn('level', $levels))
            ->when($action, fn($q) => $q->where('action', $action))
            ->when($actorId, fn($q) => $q->where('actor_id', $actorId))
            ->when($loggableType, fn($q) => $q->where('loggable_type', $loggableType))
            ->when($dateFrom, fn($q) => $q->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay()))
            ->when($dateTo, fn($q) => $q->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay()))
            ->when($search, function($q) use ($search) {
                $term = '%'.$search.'%';
                $q->where(function($inner) use ($term) {
                    $inner->where('message','like',$term)
                          ->orWhere('action','like',$term);
                });
            });

        if ($onlyDeleted) {
            $query->onlyTrashed();
        } elseif ($includeDeleted) {
            $query->withTrashed();
        }

        $filename = 'logs_export_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function() use ($query, $maxRows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id','created_at','level','action','message','actor_id','loggable_type',
                'loggable_id','ip_address','user_agent','deleted_at','context_json'
            ]);

            $count = 0;
            $query->orderBy('id','desc')
                ->chunk(500, function($chunk) use (&$count, $maxRows, $out) {
                    foreach ($chunk as $log) {
                        if ($count >= $maxRows) {
                            return false; // Stop chunking when maxRows is reached
                        }
                        fputcsv($out, [
                            $log->id,
                            $log->created_at,
                            $log->level,
                            $log->action,
                            mb_substr($log->message ?? '',0,500),
                            $log->actor_id,
                            $log->loggable_type,
                            $log->loggable_id,
                            $log->ip_address,
                            $log->user_agent,
                            $log->deleted_at,
                            json_encode($log->context, JSON_UNESCAPED_UNICODE),
                        ]);
                        $count++;
                    }
                });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}