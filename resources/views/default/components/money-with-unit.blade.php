@php
    $base_class = 'rounded-7xl bg-foreground/10 p-2 text-center text-foreground/50 min-w-8';
@endphp
<span {{ $attributes->withoutTwMergeClasses()->twMerge($base_class, $attributes->get('class')) }}>
    {{ $value > 1000 ? floatval($value / 1000) . 'k' : $value }}
</span>
