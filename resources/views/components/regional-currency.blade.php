@props(['amount', 'class' => ''])

@php
    $amountValue = (float) $amount;
    $config = get_regional_config();
    $currencySymbol = $config['currency_symbol'] ?? '$';
    $decimalSep = $config['decimal_separator'] ?? '.';
    $thousandSep = $config['thousand_separator'] ?? ',';
    $decimals = $config['decimals'] ?? 2;
@endphp

<span class="{{ $class }}">{{ $currencySymbol }}{{ format_money($amountValue, $decimals, $decimalSep, $thousandSep) }}</span>