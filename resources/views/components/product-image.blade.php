@props(['product', 'class' => 'aspect-square', 'eager' => false, 'bg' => 'bg-sand-100'])
<div {{ $attributes->merge(['class' => 'relative overflow-hidden '.$bg.' '.$class]) }}>
    @if ($url = $product->imageUrl())
        <img src="{{ $url }}" alt="{{ $product->name }}" @unless ($eager) loading="lazy" @endunless decoding="async"
             class="size-full object-cover transition duration-700 ease-out group-hover:scale-[1.03]">
    @else
        {{-- Placeholder bila produk belum punya foto --}}
        <div class="@container grid size-full place-items-center bg-sand-100 p-[8%]">
            <div class="flex w-full flex-col items-center gap-2 text-center">
                <x-logo variant="mark" mono class="h-auto w-[34%] max-w-16 text-stone-300" aria-hidden="true" />
                <span class="max-w-[12rem] text-[11px] font-medium text-stone-400 line-clamp-2 @max-[9rem]:hidden">{{ $product->name }}</span>
            </div>
        </div>
    @endif
</div>
