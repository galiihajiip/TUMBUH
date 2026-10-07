@php($t = app(\App\Services\TierService::class))
@php($b = current_business())
<x-subnav :items="[
    ['Ringkasan', 'scale.index', 'rocket_launch'],
    ['Growth Passport', 'scale.passport', 'badge'],
    ['Expansion Readiness', 'scale.readiness', 'speed', ! $t->allows($b, 'expansion.basic')],
    ['MSIGHT', 'scale.msight', 'query_stats', ! $t->allows($b, 'msight')],
    ['SiteSense', 'scale.sitesense', 'location_on', ! $t->allows($b, 'sitesense')],
    ['DCE Pathway', 'scale.dce', 'school'],
]" />
