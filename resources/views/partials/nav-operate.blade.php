@php($t = app(\App\Services\TierService::class))
<x-subnav :items="[['My Business', 'operate.index', 'space_dashboard'], ['Growth Finance', 'finance.index', 'account_balance_wallet'], ['Laporan Keuangan', 'finance.report', 'summarize', ! $t->allows(current_business(), 'finance.pnl')], ['Growth Inventory', 'inventory.index', 'inventory_2']]" />
