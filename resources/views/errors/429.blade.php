<x-error-page code="429" title="Terlalu banyak percobaan">
    Demi keamanan akun, coba lagi
    @if ($seconds = $exception->getHeaders()['Retry-After'] ?? null)
        dalam {{ $seconds }} detik.
    @else
        beberapa saat lagi.
    @endif
</x-error-page>
