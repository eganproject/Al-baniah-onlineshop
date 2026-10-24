@props(['product', 'class' => 'aspect-square', 'eager' => false, 'bg' => 'bg-sand-100'])
<div {{ $attributes->merge(['class' => 'relative overflow-hidden '.$bg.' '.$class]) }}>
    @if ($url = $product->imageUrl())
        <img src="{{ $url }}" alt="{{ $product->name }}" @unless ($eager) loading="lazy" @endunless decoding="async"
             class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.04]">
    @else
        {{-- Placeholder bila produk belum punya foto --}}
        <div class="relative grid size-full place-items-center overflow-hidden bg-gradient-to-br from-brand-50 via-sand-50 to-sand-100 p-4">
            <span class="absolute -right-1/4 -bottom-1/4 aspect-square w-3/4 rounded-full border-[14px] border-brand-200/50" aria-hidden="true"></span>
            <div class="relative flex flex-col items-center gap-2 text-center">
                <x-logo variant="mark" class="h-9 opacity-60" aria-hidden="true" />
                <span class="max-w-[12rem] text-[11px] font-medium text-stone-400 line-clamp-2">{{ $product->name }}</span>
            </div>
        </div>
    @endif
</div>
