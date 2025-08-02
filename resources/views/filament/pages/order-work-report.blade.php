<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Laporan
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($this->reportData)
            {{-- Summary Statistics --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                        <div>
                            <div>Ringkasan Laporan</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                Gunakan tombol "Cetak Laporan PDF" di bagian atas untuk mengunduh laporan
                            </div>
                        </div>
                    </div>
                </x-slot>

                @php
                    $summary = $this->getSummaryData();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['period'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Masuk</dt>
                        <dd class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ $summary['totalPesananMasuk'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Selesai</dt>
                        <dd class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ $summary['totalPesananSelesai'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paket Pesanan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalPaketPesanan'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tipe Paket Pesanan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalTipePaketPesanan'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Paket Terpopuler</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['paketTerpopuler'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipe Terpopuler</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['tipeTerpopuler'] }}</dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- Package Work Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-cube class="w-5 h-5" />
                        Pengerjaan Berdasarkan Paket Pesanan
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama Paket
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipe
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                        Total Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pengerjaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getPackageData() as $index => $package)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $package['package'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if ($package['type'] === 'Kiloan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($package['type'] === 'Karpet') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($package['type'] === 'Lembaran') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $package['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            {{ number_format($package['jumlah_pesanan']) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if ($package['type'] === 'Karpet')
                                                <div class="text-xs space-y-1">
                                                    @foreach (explode(', ', $package['total_pengerjaan']) as $ukuran)
                                                        <div>{{ $ukuran }}</div>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ number_format($package['total_pengerjaan']) }}
                                                {{ $package['unit'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data paket
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            {{-- Type Work Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                        Pengerjaan Berdasarkan Tipe Pesanan
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipe Paket
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                        Total Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pengerjaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getTypeData() as $index => $type)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if ($type['type'] === 'Kiloan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($type['type'] === 'Karpet') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($type['type'] === 'Lembaran') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $type['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            {{ number_format($type['jumlah_pesanan']) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if ($type['type'] === 'Karpet')
                                                <div class="text-xs space-y-1">
                                                    @foreach (explode(', ', $type['total_pengerjaan']) as $ukuran)
                                                        <div>{{ $ukuran }}</div>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{ number_format($type['total_pengerjaan']) }} {{ $type['unit'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data tipe
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            {{-- Carpet Size Table (if exists) --}}
            @if ($this->getKarpetData()->count() > 0)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-squares-plus class="w-5 h-5" />
                            Detail Ukuran Karpet
                        </div>
                    </x-slot>

                    <div class="-mx-6 -mb-6">
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                            No
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Ukuran Karpet
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                            Jumlah
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($this->getKarpetData() as $index => $karpet)
                                        <tr>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $karpet['ukuran'] }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                                {{ number_format($karpet['jumlah']) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-filament::section>
            @endif
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <x-heroicon-o-document-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Belum Ada Data
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Silakan pilih rentang tanggal untuk melihat laporan pengerjaan pesanan.
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
