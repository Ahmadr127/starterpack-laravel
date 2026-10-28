@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('content')
@php
    $roleDataMap = $roles->getCollection()->mapWithKeys(fn ($role) => [
        $role->id => [
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'is_active' => (bool) $role->is_active,
        ],
    ])->all();

    $permissionMatrix = $roles->getCollection()->mapWithKeys(fn ($role) => [
        $role->id => $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
    ])->all();
@endphp

<div class="space-y-5" x-data="roleMatrix({{ Js::from([
    'matrix' => $permissionMatrix,
    'roles' => $roleDataMap,
    'baseUrl' => url('roles'),
    'createOpen' => old('form') === 'create',
]) }})">

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <x-stats label="Total Role" :value="$totalRoles" icon="bi-shield-lock-fill" color="bg-blue-500" />
        <x-stats label="Role Aktif" :value="$activeRoles" icon="bi-shield-check" color="bg-sp-primary" />
        <x-stats label="Role Tidak Aktif" :value="$inactiveRoles" icon="bi-shield-x" color="bg-red-500" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- ===================== MATRIKS PERMISSION ===================== --}}
        <div class="lg:col-span-12">
            <x-card padding="false">
                <x-slot name="title">Hak Akses Role</x-slot>
                <x-slot name="subtitle">Klik tombol centang untuk mengatur permission tiap role</x-slot>
                <x-slot name="actions">
                    <form method="GET" class="relative">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari role..."
                            class="pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-full bg-gray-50 focus:bg-white focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary outline-none transition-colors w-44">
                    </form>
                    <button type="button" @click="openCreate()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark transition-colors whitespace-nowrap">
                        <i class="bi bi-plus-lg"></i> Tambah Role
                    </button>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-max">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="sticky left-0 z-10 bg-gray-100 px-4 py-2.5 text-left font-semibold text-gray-700 min-w-[13rem]">
                                    Permission
                                </th>
                                @foreach($roles as $role)
                                    <th class="px-3 py-2.5 text-center border-l border-gray-200 min-w-[9rem] align-top">
                                        <div class="font-semibold text-gray-800">{{ $role->display_name }}</div>
                                        <div class="text-xs font-normal text-gray-500">{{ $role->name }}</div>
                                        <div class="mt-1 flex items-center justify-center gap-1">
                                            {{-- <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[.65rem] font-semibold rounded-full {{ $role->is_active ? 'bg-sp-primary/10 text-sp-primary' : 'bg-gray-200 text-gray-600' }}">
                                                <i class="bi {{ $role->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                                {{ $role->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[.65rem] font-semibold rounded-full bg-blue-100 text-blue-700">
                                                <i class="bi bi-people-fill"></i>{{ $role->users_count }}
                                            </span> --}}
                                        </div>
                                        <div class="mt-1 flex items-center justify-center gap-1">
                                            <button type="button" @click="openEdit({{ $role->id }})" title="Edit role"
                                                class="inline-flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-white hover:text-sp-primary transition-colors">
                                                <i class="bi bi-pencil text-xs"></i>
                                            </button>
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus role ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus role"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-white hover:text-red-600 transition-colors">
                                                    <i class="bi bi-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($permissions as $permission)
                                <tr class="group hover:bg-gray-50 transition-colors">
                                    <td class="sticky left-0 z-10 bg-white group-hover:bg-gray-50 px-4 py-2 transition-colors">
                                        <div class="font-medium text-gray-800">{{ $permission->display_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $permission->name }}</div>
                                    </td>
                                    @foreach($roles as $role)
                                        <td class="px-3 py-2 text-center border-l border-gray-100">
                                            <button type="button"
                                                @click="toggle({{ $role->id }}, {{ $permission->id }})"
                                                :disabled="isSaving({{ $role->id }}, {{ $permission->id }})"
                                                :aria-pressed="has({{ $role->id }}, {{ $permission->id }}) ? 'true' : 'false'"
                                                title="{{ $role->display_name }} — {{ $permission->display_name }}"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-md border transition-colors disabled:opacity-50 disabled:cursor-wait"
                                                :class="has({{ $role->id }}, {{ $permission->id }}) ? 'bg-sp-primary border-sp-primary text-white hover:bg-sp-primary-dark' : 'bg-white border-gray-300 text-gray-300 hover:border-sp-primary hover:text-sp-primary'">
                                                <i class="bi bi-check-lg text-sm"></i>
                                            </button>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $roles->count() + 1 }}" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada permission. Jalankan seeder untuk mengisi data permission.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($roles->count() === 0)
                    <div class="px-4 py-8 text-center text-gray-500">
                        Tidak ada role yang cocok dengan pencarian.
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 py-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500">
                        Menampilkan {{ $roles->firstItem() ?? 0 }}–{{ $roles->lastItem() ?? 0 }} dari {{ $roles->total() }} role
                    </p>
                    <div class="text-sm">{{ $roles->links() }}</div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ===================== MODAL TAMBAH ROLE ===================== --}}
    <div x-show="createOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closeCreate()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="closeCreate()"></div>

        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-sp-primary text-white rounded-t-lg">
                <h5 class="font-bold"><i class="bi bi-plus-circle mr-2"></i>Tambah Role</h5>
                <button type="button" @click="closeCreate()" class="text-white/80 hover:text-white transition-colors"><i class="bi bi-x-lg"></i></button>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="p-4">
                @csrf
                <input type="hidden" name="form" value="create">

                <x-input name="name" label="Nama Role" placeholder="Contoh: admin" :required="true" />

                <x-input name="display_name" label="Display Name" placeholder="Contoh: Administrator" :required="true" />

                <div class="mb-3">
                    <label for="description" class="block text-sm font-semibold text-sp-navy mb-1">Deskripsi</label>
                    <textarea name="description" id="description" rows="3" placeholder="Deskripsi role"
                        class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md outline-none focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary transition-colors">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="is_active" class="block text-sm font-semibold text-sp-navy mb-1">Status</label>
                    <select name="is_active" id="is_active"
                        class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary transition-colors">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <p class="mb-3 text-xs text-gray-500">
                    <i class="bi bi-info-circle mr-1"></i>Permission dapat diatur lewat matriks setelah role dibuat.
                </p>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="closeCreate()"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark transition-colors">
                        <i class="bi bi-floppy"></i> Simpan Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL EDIT ROLE ===================== --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="close()"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" @click="close()"></div>

        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-sp-primary text-white rounded-t-lg">
                <h5 class="font-bold"><i class="bi bi-pencil-square mr-2"></i>Edit Role</h5>
                <button type="button" @click="close()" class="text-white/80 hover:text-white transition-colors"><i class="bi bi-x-lg"></i></button>
            </div>

            <form :action="action" method="POST" class="p-4">
                <input type="hidden" name="_method" value="PUT">
                @csrf

                <x-input x-model="name" id="edit-name" name="name" label="Nama Role" :required="true" />
                <x-input x-model="displayName" id="edit-display-name" name="display_name" label="Display Name" :required="true" />

                <div class="mb-3">
                    <label for="edit_description" class="block text-sm font-semibold text-sp-navy mb-1">Deskripsi</label>
                    <textarea name="description" id="edit_description" rows="3" x-model="description"
                        class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md outline-none focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary transition-colors"></textarea>
                </div>

                <div class="mb-3">
                    <label for="edit_is_active" class="block text-sm font-semibold text-sp-navy mb-1">Status</label>
                    <select name="is_active" id="edit_is_active" x-model="isActive"
                        class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary transition-colors">
                        <option :value="1">Aktif</option>
                        <option :value="0">Tidak Aktif</option>
                    </select>
                </div>

                <p class="mb-3 text-xs text-gray-500">
                    <i class="bi bi-info-circle mr-1"></i>Permission diatur langsung dari matriks pada tabel.
                </p>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="close()"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark transition-colors">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roleMatrix', (config = {}) => ({
        matrix: config.matrix || {},
        roles: config.roles || {},
        baseUrl: config.baseUrl || '',
        saving: {},
        createOpen: config.createOpen || false,
        open: false,
        action: '',
        name: '',
        displayName: '',
        description: '',
        isActive: 1,

        has(roleId, permissionId) {
            return (this.matrix[roleId] || []).map(String).includes(String(permissionId));
        },

        isSaving(roleId, permissionId) {
            return !! this.saving[roleId + '-' + permissionId];
        },

        async toggle(roleId, permissionId) {
            const key = roleId + '-' + permissionId;
            if (this.saving[key]) return;

            this.saving[key] = true;

            const before = (this.matrix[roleId] || []).map(String);
            const wasAttached = before.includes(String(permissionId));
            const after = wasAttached
                ? before.filter(id => id !== String(permissionId))
                : [...before, String(permissionId)];

            // Optimistic update
            this.matrix[roleId] = after;

            try {
                const response = await fetch(`${this.baseUrl}/${roleId}/permissions/${permissionId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (! response.ok) {
                    throw new Error('Request gagal');
                }

                const data = await response.json();
                this.matrix[roleId] = data.attached ? after : before;
            } catch (error) {
                this.matrix[roleId] = before;
                window.alert('Gagal menyimpan permission. Silakan coba lagi.');
            } finally {
                delete this.saving[key];
            }
        },

        openEdit(roleId) {
            const role = this.roles[roleId];
            if (! role) return;

            this.action = `${this.baseUrl}/${roleId}`;
            this.name = role.name;
            this.displayName = role.display_name;
            this.description = role.description || '';
            this.isActive = role.is_active ? 1 : 0;
            this.open = true;
        },

        close() {
            this.open = false;
        },

        openCreate() {
            this.createOpen = true;
        },

        closeCreate() {
            this.createOpen = false;
        },
    }));
});
</script>
@endpush
