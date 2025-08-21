<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderTrendsController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info('OrderTrendsController invoked', [
            'user_id' => optional($request->user())->id,
            'path' => $request->path()
        ]);

        // Sécurité : si pas d'user ou pas rôle admin => renvoie 403 (pas 404)
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            Log::warning('OrderTrendsController unauthorized', ['user_id'=>optional($request->user())->id]);
            abort(403);
        }

        $days = (int) $request->query('days', 7);
        $days = max(1, min(30, $days));
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        $raw = Order::query()
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', $start)
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $data = [];
        $cursor = $start->copy();
        for ($i=0; $i<$days; $i++) {
            $date = $cursor->toDateString();
            $labels[] = $date;
            $data[] = (int) ($raw[$date]->c ?? 0);
            $cursor->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Commandes', 'data' => $data]
            ],
            'total' => array_sum($data),
            'range_days' => $days,
        ]);
    }
}