<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Laporan
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($this->reportData && $this->summaryData)
            {{-- Summary Statistics --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                        <div>
                            <div>Ringkasan Laporan Pengerjaan</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                Laporan ini mencakup pesanan yang sudah selesai (status selain 'Baru' dan 'Terkendala').
                            </div>
                        </div>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->reportData['startDate'] }} - {{ $this->reportData['endDate'] }}
                            ({{ $this->reportData['totalDays'] }} hari)
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Masuk</dt>
                        <dd class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ number_format($this->summaryData['totalPesananMasuk']) }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Selesai</dt>
                        <dd class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ number_format($this->summaryData['totalPesananSelesai']) }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengerjaan Kiloan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($this->summaryData['totalPengerjaanKiloan'], 2) }} kg
                            ({{ number_format($this->summaryData['totalOrdersKiloan']) }} pesanan)
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengerjaan Lembaran</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($this->summaryData['totalPengerjaanLembaran']) }} lembar
                            ({{ number_format($this->summaryData['totalOrdersLembaran']) }} pesanan)
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengerjaan Satuan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($this->summaryData['totalPengerjaanSatuan']) }} item
                            ({{ number_format($this->summaryData['totalOrdersSatuan']) }} pesanan)
                        </dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- Tables --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-list-bullet class="w-5 h-5" />
                        Detail Pengerjaan Berdasarkan Paket Pesanan
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
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pengerjaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($this->reportData['ordersByPackage'] as $index => $item)
                                    <tr
                                        class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $item['package'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $item['type'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($item['jumlah_pesanan']) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if ($item['type'] === 'Karpet')
                                                {{ $item['detail_ukuran'] }}
                                            @else
                                                {{ number_format($item['total_pengerjaan']) }} {{ $item['unit'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data pengerjaan berdasarkan paket untuk periode ini.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-tag class="w-5 h-5" />
                        Detail Pengerjaan Berdasarkan Tipe Pesanan
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
                                        Tipe Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Pengerjaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($this->reportData['ordersByType'] as $index => $item)
                                    <tr
                                        class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $item['type'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($item['jumlah_pesanan']) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if ($item['type'] === 'Karpet')
                                                {{ $item['total_pengerjaan'] }}
                                            @else
                                                {{ number_format($item['total_pengerjaan']) }} {{ $item['unit'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data pengerjaan berdasarkan tipe untuk periode ini.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <x-heroicon-o-document-chart-bar class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Belum Ada Data Laporan
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Silakan pilih rentang tanggal untuk melihat laporan pengerjaan pesanan.
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
