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
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
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
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, refunded_at)) as avg_time_to_refund')
            )
            ->groupBy('refund_reason')
            ->get();
    }

    protected function getFailureReasons()
    {
        return Payment::where('status', 'failed')
            ->select(
                'failure_reason',
                DB::raw('COUNT(*) as count'),
                DB::raw('(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM payments WHERE status = "failed")) as percentage')
            )
            ->groupBy('failure_reason')
            ->get();
    }

    public function getPaymentMethodTrends()
    {
        $trends = Payment::where('status', 'completed')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                'payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('month', 'payment_method')
            ->orderBy('month')
            ->get()
            ->groupBy('payment_method');

        return response()->json(['trends' => $trends]);
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
