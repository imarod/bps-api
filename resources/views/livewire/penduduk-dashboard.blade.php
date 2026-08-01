<div>
    @if (session('message'))
        <div class="bg-green-100 text-green-800 px-4 py-4 rounded mb-4 text-sm">
            {{ session('message') }}
        </div>
    @endif


    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 ">
        <div class="bg-white rounded-lg p-6" style="background: #4665c2">
            <p class="text-semibold text-white">Total Penduduk Tahun {{ $tahun }}</p>
            <p class="text2xl font-bold text-white">{{ number_format($metrics['total']) }}</p>
        </div>
        <div class="bg-white rounded-lg p-6" style="background: #cd5742">
            <p class="text-semibold text-white">Kecamatan Terpadat Tahun {{ $tahun }}</p>
            <p class="text2xl font-bold text-white">{{ $metrics['kecamatan_terpadat'] }}</p>
        </div>

    </div>


    {{-- filter --}}
    <div class="flex flex-wrap gap-3 mb-6 ">
        <select wire:model.live="tahun" class="cursor-pointer shadow-[0_0_4px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm ">
            @foreach ($daftarTahun as $th)
                <option value="{{ $th }}">Tahun {{ $th }} </option>
            @endforeach
        </select>

        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama kecamatan..."
            class="shadow-[0_0_4px_rgba(0,0,0,0.4)] rounded px-3 py-2 text-sm flex-1 min-w-[200px]">
        <button wire:click="createData" class="bg-blue-600 text-white px-4 py-2 rounded text-sm cursor-pointer">Tambah Data</button>
    </div>


    {{-- donut chart --}}
    <div class="bg-white shadow-[0_0_10px_rgba(0,0,0,0.1)] rounded-lg p-4 mb-6">
        <p class="font-medium mb-2">Perbandingan Gender Kota Lubuklinggau ({{ $tahun }})</p>
        <div id="donut-chart" style="height:300px" wire:ignore></div>
        <p class="text-center text-sm  mt-2">
            Total penduduk: <span class="font-semibold text-gray-800">
                {{ number_format($donutData['laki_laki'] + $donutData['perempuan'], 0, ',', '.') }}
            </span > Jiwa
            <span></br>
            Laki-Laki: <span class="font-bold">{{ number_format($donutData['laki_laki'], 0, ',', '.') }}</span>,
            Perempuan: <span class="font-bold">{{ number_format($donutData['perempuan'], 0, ',', '.') }}</span>
        </span>
        </p>
    </div>

    {{-- bar chart --}}
    <div class="bg-white shadow-[0_0_10px_rgba(0,0,0,0.1)] rounded-lg p-4 mb-6">
        <p class="font-medium mb-2">Penduduk Menurut Gender per Kecamatan Tahun {{ $tahun }}</p>
        <div id="bar-chart" style="height:350px" wire:ignore></div>
    </div>

    {{-- form --}}
    @if ($showForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="font-semibold mb4">{{ $editingId ? 'Edit' : 'Tambah' }} Data</h3>

                <div class="space-y-3">
                    <div>
                        <lable class="text-sm text-gray-600">Nama Indikator</lable>
                        <input type="text" name="" id="" wire:model="nama_indikator"
                            placeholder="Contoh: Jumlah Penduduk Laki-laki"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                        @error('nama_indikator')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Kategori</label>
                        <input type="text" wire:model="kategori" placeholder="Contoh: Gender"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Wilayah</label>
                        <input type="text" wire:model="wilayah" placeholder="Contoh: Kota Lubuklinggau Barat 1"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Tahun</label>
                        <input type="number" wire:model="form_tahun" placeholder="Contoh: 2023"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                        @error('form_tahun')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Nilai</label>
                        <input type="text" wire:model="nilai" placeholder="Contoh: 1000"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                        @error('nilai')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Satuan</label>
                        <input type="text" wire:model="satuan" placeholder="Contoh: Jiwa"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                    </div>
                </div>

                <div class="flex gap-2 mt-5">
                    <button wire:click="saveData "
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm items-center cursor-pointer">Simpan</button>
                    <button wire:click="cancelForm"
                        class="bg-red-600 text-white items-center px-4 py-2 rounded text-sm cursor-pointer">Batal</button>
                </div>
            </div>
        </div>
    @endif

    {{-- tabel --}}
    <div class="bg-white shadow-[0_0_10px_rgba(0,0,0,0.1)] rounded-lg p-6">
        <div class="flex justify-between items-center mb-3">
            <p class="font-medium">Kelola Data</p>

        </div>
        <p class="text-sm text-gray-900 mb-4">
            Data tersedia untuk tahun {{ $daftarTahun->min() }} - {{ $daftarTahun->max() }}.
            Menampilkan data tahun <span class="font-semibold">{{ $tahun }}</span>.
        </p>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-blue-900 text-white text-left">
                    <th class="py-2 px-3">Kecamatan</th>
                    <th class="py-2 px-3 text-center">Laki-Laki</th>
                    <th class="py-2 px-3 text-center">Perempuan</th>
                    <th class="py-2 px-3 text-center">Total</th>
                    <th class="py-2 px-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pivotData as $i => $row)
                    <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-blue-100' }}">
                        <td class="py-2 px-3">{{ $row['wilayah'] }}</td>
                        <td class="py-2 px-3 text-center">{{ number_format($row['laki_laki'], 1, ',', '.') }}</td>
                        <td class="py-2 px-3 text-center">{{ number_format($row['perempuan'], 1, ',', '.') }}</td>
                        <td class="py-2 px-3 text-center font-medium">{{ number_format($row['total'], 1, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 text-center space-x-2">
                            <button wire:click="editPenduduk('{{ $row['wilayah'] }}')"
                                class="text-blue-600 cursor-pointer">Edit</button>
                            <button wire:click="deletePenduduk('{{ $row['wilayah'] }}')"
                                wire:confirm="Yakin hapus data {{ $row['wilayah'] }}?"
                                class="text-red-600 cursor-pointer">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-400">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- <div class="mt-4">
            {{ $tableData->links(data: ['scrollTo' => false]) }}
        </div> --}}
    </div>

    {{-- Modal --}}

    @if ($showPendudukForm)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="font-semibold mb-4">Edit Data Penduduk — {{ $editWilayah }}</h3>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600">Jumlah Laki-Laki</label>
                        <input type="number" wire:model="editLakiLaki"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                        @error('editLakiLaki')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Jumlah Perempuan</label>
                        <input type="number" wire:model="editPerempuan"
                            class="shadow-[0_0_3px_rgba(0,0,0,0.5)] rounded px-3 py-2 text-sm w-full">
                        @error('editPerempuan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-2 mt-5">
                    <button wire:click="savePenduduk"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm cursor-pointer">Simpan</button>
                    <button wire:click="cancelPendudukForm" class="border px-4 py-2 rounded text-sm text-white bg-red-600 cursor-pointer">Batal</button>
                </div>
            </div>
        </div>
    @endif



</div>

@script
    <script>
        let donutChart, barChart;

        function renderDonut(data) {
            donutChart = Highcharts.chart('donut-chart', {
                chart: {
                    type: 'pie',
                    backgroundColor: '#FFFFFF'
                },
                title: {
                    text: null
                },
                plotOptions: {
                    series: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: [{
                            enabled: true,
                            distance: 20,
                            format: '{point.name}'
                        }, {
                            enabled: true,
                            distance: -18,
                            format: '{point.percentage:.2f}%',
                            style: {
                                fontSize: '0.9em'
                            }
                        }],
                        showInLegend: true
                    }
                },
                series: [{
                    innerSize: '60%',
                    data: [{
                            name: 'Laki-Laki',
                            y: data.laki_laki,
                            color: '#3B82F6'
                        },
                        {
                            name: 'Perempuan',
                            y: data.perempuan,
                            color: '#EC4899'
                        }
                    ]
                }]
            });
        }

        function renderBar(data) {
            barChart = Highcharts.chart('bar-chart', {
                chart: {
                    type: 'column',
                    backgroundColor: '#FFFFFF'
                },
                title: {
                    text: null
                },
                xAxis: {
                    categories: data.kategori
                },
                yAxis: {
                    title: {
                        text: 'Jumlah Penduduk'
                    }
                },
                series: [{
                        name: 'Laki-Laki',
                        data: data.laki_laki,
                        color: '#378ADD'
                    },
                    {
                        name: 'Perempuan',
                        data: data.perempuan,
                        color: '#D4537E'
                    },
                ]
            });
        }

        renderDonut(@js($donutData));
        renderBar(@js($barChartData));

        $wire.on('chart-updated', (event) => {
            donutChart?.destroy();
            barChart?.destroy();
            renderDonut(event.donutData);
            renderBar(event.barChartData);
        });
    </script>
@endscript
