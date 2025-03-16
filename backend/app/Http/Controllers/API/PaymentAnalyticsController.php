<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentAnalyticsController extends Controller
{
    public function getPaymentStats()
    {
        $stats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'successful_payments' => Payment::where('status', 'completed')->count(),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'refunded_amount' => Payment::where('status', 'refunded')->sum('amount'),
            'payment_method_distribution' => $this->getPaymentMethodDistribution(),
            'daily_revenue' => $this->getDailyRevenue(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'refund_analysis' => $this->getRefundAnalysis(),
            'failure_reasons' => $this->getFailureReasons()
        ];

        return response()->json($stats);
    }

    protected function getPaymentMethodDistribution()
    {
        return Payment::where('status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_method')
            ->get();
    }

    protected function getDailyRevenue()
    {
        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    protected function getMonthlyRevenue()
    {
        return Payment::where('status', 'completed')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('AVG(amount) as average_transaction')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
    }

    protected function getRefundAnalysis()
    {
        return Payment::where('status', 'refunded')
            ->select(
                'refund_reason',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(EXTRACT(EPOCH FROM (refund_date - created_at))/3600) as avg_time_to_refund')
            )
            ->groupBy('refund_reason')
            ->get();
    }

    protected function getFailureReasons()
    {
        $totalFailedCount = Payment::where('status', 'failed')->count();

        return Payment::where('status', 'failed')
            ->select(
                'failure_reason',
                DB::raw('COUNT(*) as count'),
                DB::raw('(COUNT(*) * 100.0 / ' . ($totalFailedCount ?: 1) . ') as percentage')
            )
            ->groupBy('failure_reason')
            ->get();
    }

    public function getPaymentMethodTrends()
    {
        $rawTrends = Payment::where('status', 'completed')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('month', 'payment_method')
            ->orderBy('month')
            ->get();

        // Restructure the data to match the expected format
        $months = $rawTrends->pluck('month')->unique()->values();
        $formattedTrends = [];

        foreach ($months as $month) {
            $monthData = [
                'date' => $month,
                'stripe' => 0,
                'mpesa' => 0,
                'paypal' => 0
            ];

            foreach ($rawTrends->where('month', $month) as $trend) {
                $method = strtolower($trend->payment_method);
                if (isset($monthData[$method])) {
                    $monthData[$method] = $trend->count;
                }
            }

            $formattedTrends[] = $monthData;
        }

        return response()->json(['trends' => $formattedTrends]);
    }

    public function getFailureAnalysis(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $analysis = Payment::where('status', 'failed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'failure_reason',
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(amount) as average_amount')
            )
            ->groupBy('failure_reason', 'payment_method')
            ->get();

        return response()->json(['analysis' => $analysis]);
    }
}
