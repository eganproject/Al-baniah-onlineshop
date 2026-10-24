{{--
    Ikon info kecil berisi penjelasan isian (tooltip). Hover/fokus di desktop, ketuk di HP.
    <x-tip title="Harga coret" text="Untuk apa isian ini." :tips="['Saran 1', 'Saran 2']" />
    Di komponen x-form.* cukup pakai prop `tip` (+ `tips`), judulnya otomatis = label.
    Isi bebas juga bisa lewat slot: <x-tip title="...">...</x-tip>
--}}
@props([
    'text' => null,
    'tips' => [],
    'title' => null,
])
@php
    $tips = array_values(array_filter((array) $tips));
    $id = 'tip-'.\Illuminate\Support\Str::random(8);
@endphp
<span x-data="tip" {{ $attributes->class('relative inline-flex shrink-0 align-middle') }}
      @keydown.escape.window="escape()">
    <button type="button" x-ref="trigger"
            class="group/tip relative grid size-[18px] place-items-center rounded-full text-stone-400 transition hover:text-brand-600 focus-visible:text-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400 before:absolute before:-inset-2.5 before:content-['']"
            :class="open && '!text-brand-600'"
            aria-label="Info{{ $title ? ': '.$title : '' }}" aria-describedby="{{ $id }}" :aria-expanded="open"
            @pointerenter="enter($event)" @pointerleave="leave($event)"
            @click.prevent.stop="toggle()" @focus="focus($event)" @blur="blur()">
        <x-icon name="question" class="size-[18px]" />
    </button>

    <template x-teleport="body">
        <div x-ref="panel" id="{{ $id }}" role="tooltip" x-show="open" x-cloak
             x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             :style="{ top: pos.top + 'px', left: pos.left + 'px' }"
             @pointerenter="enter($event)" @pointerleave="leave($event)"
             class="tip-panel">
            <span class="tip-arrow" :class="placement === 'top' ? '-bottom-[5px]' : '-top-[5px]'" :style="{ left: pos.arrow + 'px' }" aria-hidden="true"></span>
            @if ($title)
                <p class="tip-title">{{ $title }}</p>
            @endif
            @if ($text)
                <p>{{ $text }}</p>
            @endif
            {{ $slot }}
            @if ($tips)
                <div class="tip-tips">
                    <p class="tip-label">Tips agar maksimal</p>
                    <ul>
                        @foreach ($tips as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </template>
</span>
