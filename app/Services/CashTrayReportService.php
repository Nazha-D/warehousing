<?php

namespace App\Services;

use App\Models\PosCashTray;

class CashTrayReportService
{
    public function generate(PosCashTray $tray)
    {
        if ($tray->status !== 'closed') {
            throw new \Exception('Cash tray must be closed first.');
        }

        $tray->load([
            'payments.cashingMethod',
            'payments.invoice',
            'payments.invoice.lines',
            'balances.currency',
        ]);
        $defaultCurrency=auth()->user()->company->currencies()->where('is_default',true)->first();
        $payments = $tray->payments;
        $balance  = $tray->balances()->where('currency_id',$defaultCurrency->id)->first(); // عندكم عملة وحدة

        /*
        |--------------------------------------------------------------------------
        | Totals From Invoices
        |--------------------------------------------------------------------------
        */

        $invoices = $payments->pluck('invoice')->unique('id');

        $salesInvoices  = $invoices->where('type', 'SALE');

        $refundInvoices = $invoices->where('type', 'REFUND');

        $grossSales = $salesInvoices->sum('grand_total');
        $refunds    = $refundInvoices->sum('grand_total');
        $netSales   = $grossSales - $refunds;

        /*
        |--------------------------------------------------------------------------
        | Payments Breakdown
        |--------------------------------------------------------------------------
        */

        $paymentsBreakdown = $payments
            ->groupBy('cashing_method_id')
            ->map(function ($rows) {
                $method = $rows->first()->cashingMethod;

                return [
                    'method_id'   => $method->id,
                    'method_name' => $method->name,
                    'is_cash'     => $method->title=='cash',
                    'total'       => $rows->sum('amount'),
                ];
            })
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Cash Summary (Official Numbers)
        |--------------------------------------------------------------------------
        */

        $cashSummary = [
            'opening'   => $balance->opening_amount,
            'expected'  => $balance->expected_amount,
            'declared'  => $balance->declared_closing_amount,
            'difference'=> $balance->difference,
            'short'     => $balance->difference < 0 ? $balance->difference : 0,
            'over'      => $balance->difference > 0 ? $balance->difference : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | Sales By Item
        |--------------------------------------------------------------------------
        */

        $salesLines = $salesInvoices
            ->flatMap(fn($invoice) => $invoice->lines);

        $salesByItem = $salesLines
            ->groupBy('item_id')
            ->map(function ($rows) {
                return [
                    'item_id'   => $rows->first()->item_id,
                    'item_name' => $rows->first()->item->item_name,
                    'qty'       => $rows->sum('quantity'),
                    'total'     => $rows->sum('line_total'),
                ];
            })
            ->values()
            ->toArray();

        return [
            'meta' => [
                'tray_number'  => $tray->tray_number,
                'session_number'=>$tray->session->session_number,
                'session_started_on'=>$tray->session->session_number,
                'session_ended_on'=>$tray->session->session_number,
                'opened_at'    => $tray->opened_at,
                'closed_at'    => $tray->closed_at,
                'cashier_name' => optional($tray->cashier)->name,
                'rate'=>$tray->payments()->first()->exchange_rate,
            ],

            'totals' => [
                'invoice_count' => $invoices->count(),
                'gross_sales'   => $grossSales,
                'refunds'       => $refunds,
                'net_sales'     => $netSales,
            ],

            'payments'      => $paymentsBreakdown,
            'cash_summary'  => $cashSummary,
            'sales_by_item' => $salesByItem,
        ];
    }
}
