@props(['amount', 'showBoth' => true, 'class' => '', 'currency' => 'USD'])

@php
    $isVenezuela = is_venezuela_company();
    $rate = $isVenezuela ? \App\Models\ExchangeRate::getLatestRate('USD') : null;
    $amountValue = is_numeric($amount) ? (float) $amount : 0;
    $config = get_regional_config();
    $currencySymbol = $config['currency_symbol'] ?? '$';
    $decimalSep = $config['decimal_separator'] ?? '.';
    $thousandSep = $config['thousand_separator'] ?? ',';
    $decimals = $config['decimals'] ?? 2;
@endphp

<span class="{{ $class }}">
    @if($isVenezuela && $showBoth && $rate)
        {{-- Para Venezuela: USD como principal + Bs como conversión --}}
        <span class="text-success fw-bold">{{ format_money($amountValue, 2, '.', ',') }}</span>
        <small class="text-muted d-block">
            Bs. {{ number_format($amountValue * $rate, 2, ',', '.') }}
        </small>
    @else
        {{-- Para otros países: usar símbolo y formato regional --}}
        {{ format_money($amountValue, $decimals, $decimalSep, $thousandSep) }}
    @endif
</span>