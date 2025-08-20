<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OrderTrendsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));
        $raw = Order::query()
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $labels = [];
        $data = [];
        foreach ($days as $day) {
            $key = $day->toDateString();
            $labels[] = $day->format('d M');
            $data[] = (int)($raw[$key] ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'data' => $data,
                    'fill' => false,
                    'tension' => 0.25,
                    'borderColor' => '#3c8dbc',
                    'backgroundColor' => '#3c8dbc',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
        ]);
    }
}