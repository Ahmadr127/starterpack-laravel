@extends('layouts.app')

@section('title', 'Tambah Unit Organisasi')

@section('content')
@php
    $typeOptions = $types->map(fn ($t) => [
        'value' => $t->id,
        'label' => 'Level ' . $t->level . ': ' . $t->display_name,
    ]);

    $parentOptions = collect([['value' => '', 'label' => 'Tidak Ada (Root)']])
        ->concat($parentUnits->map(fn ($p) => [
            'value' => $p->id,
            'label' => $p->name . ' (' . $p->type->display_name . ')',
        ]));

    $userOptions = collect([['value' => '', 'label' => 'Belum Ditentukan']])
        ->concat($users->map(fn ($u) => [
            'value' => $u->id,
            'label' => $u->name . ' (' . $u->email . ')',
        ]));
@endphp

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Tambah Unit Organisasi Baru</h2>
            <a href="{{ route('organization-units.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>

        <form action="{{ route('organization-units.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Unit</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               placeholder="Nama unit organisasi"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Kode</label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" required
                               placeholder="KODE (akan diubah ke huruf besar)"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm uppercase">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Tipe Organisasi - Searchable Select --}}
                    <x-searchable-select
                        name="type_id"
                        label="Tipe Organisasi"
                        :options="$typeOptions"
                        :selected="old('type_id')"
                        placeholder="Pilih Tipe..."
                        :required="true"
                    />

                    {{-- Parent Unit - Searchable Select --}}
                    <x-searchable-select
                        name="parent_id"
                        label="Parent Unit"
                        :options="$parentOptions"
                        :selected="old('parent_id')"
                        placeholder="Pilih Parent..."
                    />
                </div>

                {{-- Kepala Unit - Searchable Select --}}
                <x-searchable-select
                    name="head_id"
                    label="Kepala Unit"
                    :options="$userOptions"
                    :selected="old('head_id')"
                    placeholder="Pilih Kepala Unit..."
                />

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Unit Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
