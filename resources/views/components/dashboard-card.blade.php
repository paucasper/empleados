@props([
    'title',
    'subtitle' => null,
    'count' => null,
    'icon' => '📄',
    'href' => '#',
])

<a href="{{ $href }}"
   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            @if($subtitle)
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                    {{ $subtitle }}
                </p>
            @endif

            <h3 class="mt-2 text-lg font-semibold text-gray-900">
                {{ $title }}
            </h3>

            @if(!is_null($count))
                <p class="mt-4 text-3xl font-bold text-green-700">
                    {{ $count }}
                </p>
            @endif
        </div>

        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-100 text-2xl">
            {{ $icon }}
        </div>
    </div>
</a>