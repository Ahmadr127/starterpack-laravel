@props([
    'name',
    'options' => [],
    'selected' => '',
    'placeholder' => '-- Pilih --',
    'required' => false,
    'id' => null,
    'label' => null,
    'error' => null,
])

@php
    $id = $id ?? $name;
    $error = $error ?? ($name ? $errors->first($name) : null);

    // Normalisasi opsi: menerima array/Collection berisi ['value' => ..., 'label' => ...]
    // maupun model/objek dengan atribut id/name.
    $normalizedOptions = collect($options)->map(function ($option) {
        $option = (array) $option;

        return [
            'value' => (string) ($option['value'] ?? $option['id'] ?? ''),
            'label' => (string) ($option['label'] ?? $option['name'] ?? ''),
        ];
    })->values()->all();
@endphp

<div {{ $attributes->class($attributes->has('class') ? [] : ['mb-3']) }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-sp-navy mb-1">
            {{ $label }}@if($required)<span class="text-red-500"> *</span>@endif
        </label>
    @endif

    <div x-data="{
        open: false,
        search: '',
        value: @js((string) $selected),
        label: '',
        placeholder: @js($placeholder),
        options: @js($normalizedOptions),
        get filtered() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o => String(o.label ?? '').toLowerCase().includes(q));
        },
        init() {
            const found = this.options.find(o => String(o.value) === String(this.value));
            this.label = found ? found.label : '';
            this.$nextTick(() => {
                const el = this.$refs.hidden;
                if (el && this.value !== '') {
                    el.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: this.value, option: found } }));
                }
            });
        },
        select(o) {
            this.value = o.value;
            this.label = o.label;
            this.open = false;
            this.search = '';
            this.$nextTick(() => {
                const el = this.$refs.hidden;
                if (el) {
                    el.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: o.value, option: o } }));
                }
            });
        }
    }" class="relative" @click.outside="open = false">
        <input type="hidden" name="{{ $name }}" id="{{ $id }}" :value="value"
               @if($required) required @endif
               x-ref="hidden"
               @change.stop>

        <button type="button"
                @click="open = !open"
                @class([
                    'w-full text-sm px-3 py-2 border rounded-md outline-none transition-colors bg-white text-left flex items-center justify-between gap-2',
                    'border-red-400 focus:ring-2 focus:ring-red-100 focus:border-red-400' => $error,
                    'border-gray-300 focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary' => ! $error,
                ])>
            <span class="truncate" x-text="label || placeholder" :class="label ? '' : 'text-gray-400'"></span>
            <svg class="w-3 h-3 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-64 overflow-auto">
            <div class="p-2 border-b border-gray-100">
                <input type="text" x-model="search" placeholder="Cari..."
                       class="w-full text-sm px-2 py-1.5 border border-gray-300 rounded-md outline-none focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                       @click.stop>
            </div>
            <div class="py-1">
                <template x-for="o in filtered" :key="o.value">
                    <div class="px-3 py-1.5 text-sm cursor-pointer"
                         :class="String(o.value) === String(value) ? 'bg-sp-primary/10 font-semibold text-sp-primary' : 'hover:bg-gray-50'"
                         @click="select(o)" x-text="o.label"></div>
                </template>
                <div x-show="filtered.length === 0" class="px-3 py-2 text-xs text-gray-400">Tidak ada hasil</div>
            </div>
        </div>
    </div>

    @if($error)
        <p class="mt-1 text-xs text-red-500">{{ $error }}</p>
    @endif
</div>
