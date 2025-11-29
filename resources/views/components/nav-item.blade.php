@props(['label', 'icon' => null, 'href' => '#', 'active' => false, 'status' => 'aktif'])

@php
    $status = is_string($status) ? strtolower($status) : 'aktif';
    $isDisabled = in_array($status, ['tidak_aktif', 'suspended']);
    $tooltipMessage = $status === 'tidak_aktif'
        ? 'Siswa belum aktif'
        : ($status === 'suspended' ? 'Tidak dapat diakses, silahkan hubungi admin' : null);

    $tooltipId = 'tooltip-' . \Illuminate\Support\Str::slug($label) . '-' . \Illuminate\Support\Str::random(5);
@endphp

<div class="relative w-full">
    @if($isDisabled)
        <button
            type="button"
            disabled
            class="flex items-center w-full px-3 py-1.5 rounded-lg cursor-not-allowed opacity-60"
            data-tooltip-target="{{ $tooltipId }}"
            data-tooltip-placement="right"
        >
            @if($icon)
                <i class="{{ $icon }} mr-2"></i>
            @endif
            <span class="truncate">{{ $label }}</span>
        </button>

        <div id="{{ $tooltipId }}"
             role="tooltip"
             class="absolute z-50 invisible inline-block px-3 py-2 text-sm font-medium text-white bg-gray-900
                    rounded-lg shadow-sm opacity-0 transition-opacity duration-200"
        >
            {{ $tooltipMessage }}
        </div>
    @else
        <a href="{{ $href }}"
           class="flex items-center px-3 py-1.5 rounded-lg w-full transition-colors
                  {{ $active ? 'bg-green-800 text-white' : 'hover:bg-green-700' }}">
            @if($icon)
                <i class="{{ $icon }} mr-2"></i>
            @endif
            <span>{{ $label }}</span>
        </a>
    @endif
</div>
