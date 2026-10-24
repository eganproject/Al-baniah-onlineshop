{{--
    Pembungkus isian: label, kontrol (slot), petunjuk, dan pesan error.
    <x-form.field label="Nama" for="name" name="name" hint="..." optional>
        <input id="name" name="name" class="input">
    </x-form.field>
    `name` dipakai untuk mencari error (boleh "variants[0][price]" atau "variants.0.price").
    `tip` (+ `tips` = daftar saran) menambah ikon info bertooltip di samping label (<x-tip>).
--}}
@props([
    'label' => null,
    'for' => null,
    'name' => null,
    'hint' => null,
    'tip' => null,
    'tips' => [],
    'optional' => false,
    'bag' => 'default',
])
@php
    $errorKey = \App\Support\FormField::errorKey($name ?? $for);
@endphp
<div {{ $attributes }}>
    @if ($label || isset($labelSlot))
        @if ($tip || $tips) <div class="label-row"> @endif
        <label @if ($for) for="{{ $for }}" @endif class="label">
            {{ $label }}{{ $labelSlot ?? '' }}
            @if ($optional)
                <span class="font-normal text-stone-400">({{ is_string($optional) ? $optional : 'opsional' }})</span>
            @endif
        </label>
        @if ($tip || $tips)
            <x-tip :title="$label" :text="$tip" :tips="$tips" />
            </div>
        @endif
    @endif
    {{ $slot }}
    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif
    {{ $after ?? '' }}
    @if ($errorKey)
        <x-input-error :for="$errorKey" :bag="$bag" />
    @endif
</div>
