@php($t = app(\App\Services\TierService::class))
<x-subnav :items="[['Rekomendasi', 'solutions.index', 'recommend'], ['Activation Center', 'solutions.activation', 'toggle_on'], ['MyAds Campaigns', 'campaigns.index', 'campaign', ! $t->allows(current_business(), 'campaigns')]]" />
