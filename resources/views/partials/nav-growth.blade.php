@php($t = app(\App\Services\TierService::class))
<x-subnav :items="[
    ['Ringkasan', 'growth.index', 'dashboard'],
    ['Growth Score', 'growth.score', 'speed'],
    ['AI Assistant', 'assistant', 'auto_awesome'],
    ['Growth Ledger', 'growth.ledger', 'receipt_long'],
    ['Before vs After', 'growth.before-after', 'compare_arrows', ! $t->allows(current_business(), 'before_after')],
    ['AI Review', 'growth.review', 'rate_review'],
]" />
