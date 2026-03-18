@props(['amount', 'class' => ''])

@php
    $amountValue = (float) $amount;
@endphp

<span class="{{ $class }}">{{ format_money($amountValue, 2, '.', ',') }}</span>