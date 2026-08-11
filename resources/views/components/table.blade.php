@props([
    'columns' => [],
    'pagination' => null,
    'empty' => 'Tidak ada data.',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-max">
            <thead>
                <tr class="bg-gray-100">
                    @foreach($columns as $column)
                        <th class="px-4 py-2.5 text-left font-semibold text-gray-700 whitespace-nowrap">
                            {{ is_array($column) ? ($column['label'] ?? '') : $column }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @if(!$slot->isEmpty())
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($columns) ?: 1 }}" class="px-4 py-8 text-center text-gray-500">{{ $empty }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($pagination && $pagination->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 py-3 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $pagination->firstItem() ?? 0 }}–{{ $pagination->lastItem() ?? 0 }} dari {{ $pagination->total() }} data
            </p>
            <div class="text-sm">{{ $pagination->links() }}</div>
        </div>
    @endif
</div>
