@extends('layouts.app')

@section('title', 'SNMP Network Monitoring')

@section('content')
<div class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
    @include('admin.partials.sidebar')

    <div class="lg:pl-64">
        @include('admin.partials.topbar')

        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">SNMP Network Monitoring</h1>
                    <p class="text-gray-600">Monitor perangkat jaringan real-time</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.snmp.dashboard') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <button onclick="document.getElementById('addDeviceModal').classList.remove('hidden')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-2"></i>Add Device
                    </button>
                </div>
            </div>

            @if(!$enabled)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-yellow-800 font-medium">SNMP Tidak Aktif</h3>
                        <p class="text-yellow-700 text-sm mt-1">
                            Aktifkan SNMP dengan mengatur <code class="bg-yellow-100 px-1 rounded">SNMP_ENABLED=true</code> di file .env.
                            Pastikan PHP SNMP extension sudah terinstall.
                        </p>
                    </div>
                </div>
            </div>
            @else

            <!-- Devices Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($devices as $device)
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg {{ $device['type'] == 'router' ? 'bg-blue-100' : ($device['type'] == 'switch' ? 'bg-green-100' : 'bg-gray-100') }}">
                                <i class="fas {{ $device['type'] == 'router' ? 'fa-server text-blue-600' : ($device['type'] == 'switch' ? 'fa-network-wired text-green-600' : 'fa-server text-gray-600') }} text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-bold text-gray-800">{{ $device['name'] }}</h3>
                                <p class="text-sm text-gray-500">{{ $device['host'] }}</p>
                            </div>
                        </div>
                        <span class="status-indicator w-3 h-3 rounded-full bg-gray-300" data-host="{{ $device['host'] }}"></span>
                    </div>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('admin.snmp.device', $device['host']) }}" class="flex-1 text-center py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 text-sm">
                            <i class="fas fa-eye mr-1"></i>Detail
                        </a>
                        <form action="{{ route('admin.snmp.devices.delete', $device['id']) }}" method="POST" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm" onclick="return confirm('Hapus device ini?')">
                                <i class="fas fa-trash mr-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="col-span-3 bg-gray-50 rounded-xl p-8 text-center text-gray-500">
                    <i class="fas fa-server text-4xl mb-3"></i>
                    <p>Belum ada perangkat yang dimonitor</p>
                </div>
                @endforelse
            </div>
            @endif
        </div>
    </div>
</div>


<!-- Add Device Modal -->
<div id="addDeviceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Add Network Device</h3>
        <form action="{{ route('admin.snmp.devices.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Device Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">IP Address</label>
                    <input type="text" name="host" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" placeholder="192.168.1.1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SNMP Community</label>
                    <input type="text" name="community" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" placeholder="public">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Device Type</label>
                    <select name="type" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="router">Router</option>
                        <option value="switch">Switch</option>
                        <option value="olt">OLT</option>
                        <option value="server">Server</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('addDeviceModal').classList.add('hidden')" class="px-4 py-2 border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Add Device</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-indicator').forEach(function(el) {
        const host = el.dataset.host;
        fetch(`{{ route('admin.snmp.ping') }}?host=${host}`)
            .then(r => r.json())
            .then(data => {
                el.classList.remove('bg-gray-300');
                el.classList.add(data.online ? 'bg-green-500' : 'bg-red-500');
            });
    });
});
</script>
@endpush
@endsection
