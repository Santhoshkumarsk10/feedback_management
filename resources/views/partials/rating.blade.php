@if(is_null($value) || $value === '')
    <span class="text-muted small">—</span>
@else
    @php
        $num = (float)$value;
        $class = $num >= 4.0 ? 'high' : ($num >= 3.0 ? 'mid' : 'low');
    @endphp
    <span class="rating-pill {{ $class }}">
        <i class="bi bi-star-fill"></i>
        <span>{{ number_format($num, 1) }}</span>
    </span>
@endif
