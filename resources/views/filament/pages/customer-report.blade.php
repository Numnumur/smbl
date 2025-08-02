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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pelanggan</dt>
                        <dd class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ $summary['totalCustomers'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalOrders'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pemasukan</dt>
                        <dd class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ $summary['totalIncome'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata Pesanan Per Pelanggan
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['averageOrdersPerCustomer'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata Pemasukan Per
                            Pelanggan</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['averageIncomePerCustomer'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Terbanyak Pesanan
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['topCustomerByOrders'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Tertinggi Pemasukan
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['topCustomerByIncome'] }}</dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- Customers Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-shopping-bag class="w-5 h-5" />
                        Detail Pesanan Pelanggan
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
                                        Nama Pelanggan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                                        Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                                        Pemasukan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-48">
                                        Paket Pesanan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getCustomersData() as $index => $customer)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $customer['name'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="space-y-1">
                                                <div><strong>Total:</strong>
                                                    {{ number_format($customer['total_orders']) }}</div>
                                                <div><strong>Terakhir:</strong> {{ $customer['last_order_date'] }}
                                                </div>
                                                @if ($customer['last_order_diff'] !== '-')
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $customer['last_order_diff'] }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="space-y-1">
                                                <div><strong>Total:</strong> Rp
                                                    {{ number_format($customer['total_income'], 0, ',', '.') }}</div>
                                                <div><strong>Rata-rata:</strong> Rp
                                                    {{ number_format($customer['average_income'], 0, ',', '.') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="space-y-1">
                                                @forelse($customer['packages'] as $package => $count)
                                                    <div>• {{ $package }} ({{ $count }})</div>
                                                @empty
                                                    <div class="text-gray-500 dark:text-gray-400">-</div>
                                                @endforelse
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data pelanggan
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
                        Belum Ada Data
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Silakan pilih rentang tanggal untuk melihat laporan pelanggan.
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
