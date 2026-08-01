<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DataStatistik;

class PendudukDashboard extends Component
{
    use WithPagination;

    //filter
    public $tahun = '';
    public $search = '';
    public $filterKategori = '';

    //form create dan update
    public $editingId = null;
    public $nama_indikator = '';
    public $kategori = '';
    public $wilayah = '';
    public $form_tahun = '';
    public $nilai = '';
    public $satuan = '';
    public $showForm = false;

    public $editWilayah = '';
    public $editLakiLaki = '';
    public $editPerempuan = '';
    public $showPendudukForm = false;

    protected function rules()
    {
        return [
            'nama_indikator' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'wilayah' => 'required|string|max:255',
            'form_tahun' => 'required|integer|min:1900|max:2100',
            'nilai' => 'required|numeric',
            'satuan' => 'required|string|max:50',
        ];
    }

    public function mount()
    {
        $this->tahun = DataStatistik::where('nama_indikator', 'like', 'Jumlah Penduduk%')
            ->max('tahun') ?? now()->year;
    }

    public function updatingTahun()
    {
        $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFilterKategori()
    {
        $this->resetPage();
    }

    public function updatedTahun()
    {
        $this->resetPage();
        $this->dispatch(
            'chart-updated',
            donutData: $this->getDonutData(),
            barChartData: $this->getBarChartData(),
        );
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->dispatch(
            'chart-updated',
            donutData: $this->getDonutData(),
            barChartData: $this->getBarChartData(),
        );
    }


    public function render()
    {
        return view('livewire.penduduk-dashboard', [
            'metrics' => $this->getMetrics(),
            'donutData' => $this->getDonutData(),
            'barChartData' => $this->getBarChartData(),
            'tableData' => $this->getTableData()->paginate(10),
            'daftarTahun' => DataStatistik::where('nama_indikator', 'like', 'Jumlah Penduduk%')
                ->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'daftarKategori' => DataStatistik::distinct()->orderBy('kategori')->pluck('kategori'),
            'pivotData' => $this->getPivotTable(),
        ]);
    }

    private function getMetrics(): array
    {
        $total = DataStatistik::where('nama_indikator', 'Jumlah Penduduk (Total)')
            ->where('wilayah', 'Kota Lubuklinggau')
            ->sum('nilai');

        $terpadat = DataStatistik::where('nama_indikator', 'Jumlah Penduduk (Total)')
            ->where('wilayah', '!=', 'Kota Lubuklinggau')
            ->where('tahun', $this->tahun)
            ->orderByDesc('nilai')
            ->value('wilayah');

        return [
            'total' => (float) ($total ?? 0),
            'kecamatan_terpadat' => $terpadat ?? '-',
        ];
    }

    private function getDonutData(): array
    {
        $lakiLaki = (float) (DataStatistik::where('nama_indikator', 'Jumlah Penduduk Laki-laki')
            ->where('wilayah', 'Kota Lubuklinggau')
            ->where('tahun', $this->tahun)
            ->value('nilai') ?? 0);

        $perempuan = (float) (DataStatistik::where('nama_indikator', 'Jumlah Penduduk Perempuan')
            ->where('wilayah', 'Kota Lubuklinggau')
            ->where('tahun', $this->tahun)
            ->value('nilai') ?? 0);

        return ['laki_laki' => $lakiLaki, 'perempuan' => $perempuan];
    }

    private function getBarChartData(): array
    {
        $query = DataStatistik::where('wilayah', '!=', 'Kota Lubuklinggau')
            ->where('tahun', $this->tahun);

        if ($this->search) {
            $query->where('wilayah', 'like', "%{$this->search}%");
        }

        $lakiLaki = (clone $query)->where('nama_indikator', 'Jumlah Penduduk Laki-laki')
            ->orderBy('wilayah')
            ->pluck('nilai', 'wilayah');

        $perempuan = (clone $query)->where('nama_indikator', 'Jumlah Penduduk Perempuan')
            ->orderBy('wilayah')
            ->pluck('nilai', 'wilayah');

        return [
            'kategori' => $lakiLaki->keys()->values()->all(),
            'laki_laki' => $lakiLaki->values()->map(fn($v) => (float) $v)->all(),
            'perempuan' => $perempuan->values()->map(fn($v) => (float) $v)->all(),
        ];
    }

    private function getTableData()
    {
        $query = DataStatistik::query();

        if ($this->tahun) {
            $query->where('tahun', $this->tahun);
        }
        if ($this->search) {
            $query->where('wilayah', 'like', "%{$this->search}%");
        }
        if ($this->filterKategori) {
            $query->where('kategori', $this->filterKategori);
        }

        return $query->orderByDesc('tahun');
    }

    private function getPivotTable(): array
    {
        $query = DataStatistik::where('wilayah', '!=', 'Kota Lubuklinggau')
            ->where('tahun', $this->tahun);

        if ($this->search) {
            $query->where('wilayah', 'like', "%{$this->search}%");
        }

        $lakiLaki = (clone $query)->where('nama_indikator', 'Jumlah Penduduk Laki-laki')
            ->orderBy('wilayah')->pluck('nilai', 'wilayah');

        $perempuan = (clone $query)->where('nama_indikator', 'Jumlah Penduduk Perempuan')
            ->orderBy('wilayah')->pluck('nilai', 'wilayah');
        $hasil = [];

        foreach ($lakiLaki as $wilayah => $nilaiLaki) {
            $nilaiPerempuan = (float) ($perempuan[$wilayah] ?? 0);
            $nilaiLaki = (float) $nilaiLaki;

            $hasil[] = [
                'wilayah' => $wilayah,
                'laki_laki' => $nilaiLaki,
                'perempuan' => $nilaiPerempuan,
                'total' => $nilaiLaki + $nilaiPerempuan,
            ];
        }
        return $hasil;
    }

    public function editPenduduk($wilayah)
    {
        $laki = DataStatistik::where('nama_indikator', 'Jumlah Penduduk Laki-laki')
            ->where('wilayah', $wilayah)->where('tahun', $this->tahun)->value('nilai');

        $perempuan = DataStatistik::where('nama_indikator', 'Jumlah Penduduk Perempuan')
            ->where('wilayah', $wilayah)->where('tahun', $this->tahun)->value('nilai');

        $this->editWilayah = $wilayah;
        $this->editLakiLaki = $laki ?? 0;
        $this->editPerempuan = $perempuan ?? 0;
        $this->showPendudukForm = true;
    }


    public function savePenduduk()
    {
        $this->validate([
            'editLakiLaki' => 'required|numeric|min:0',
            'editPerempuan' => 'required|numeric|min:0',
        ]);

        DataStatistik::updateOrCreate(
            ['nama_indikator' => 'Jumlah Penduduk Laki-laki', 'tahun' => $this->tahun, 'wilayah' => $this->editWilayah],
            ['kategori' => 'Gender', 'nilai' => $this->editLakiLaki, 'satuan' => 'Jiwa', 'is_manual' => true]
        );

        DataStatistik::updateOrCreate(
            ['nama_indikator' => 'Jumlah Penduduk Perempuan', 'tahun' => $this->tahun, 'wilayah' => $this->editWilayah],
            ['kategori' => 'Gender', 'nilai' => $this->editPerempuan, 'satuan' => 'Jiwa', 'is_manual' => true]
        );

        $this->showPendudukForm = false;
        session()->flash('message', 'Data penduduk berhasil diperbarui.');

        $this->dispatch(
            'chart-updated',
            donutData: $this->getDonutData(),
            barChartData: $this->getBarChartData(),
        );
    }

    public function deletePenduduk($wilayah)
    {
        DataStatistik::where('wilayah', $wilayah)->where('tahun', $this->tahun)
            ->whereIn('nama_indikator', ['Jumlah Penduduk Laki-laki', 'Jumlah Penduduk Perempuan', 'Jumlah Penduduk (Total)'])
            ->delete();

        session()->flash('message', 'Data penduduk berhasil dihapus.');

        $this->dispatch(
            'chart-updated',
            donutData: $this->getDonutData(),
            barChartData: $this->getBarChartData(),
        );
    }

    public function cancelPendudukForm()
    {
        $this->showPendudukForm = false;
    }


    public function createData()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editData($id)
    {
        $item = DataStatistik::findOrFail($id);
        $this->editingId = $item->id;
        $this->nama_indikator = $item->nama_indikator;
        $this->kategori = $item->kategori;
        $this->wilayah = $item->wilayah;
        $this->form_tahun = $item->tahun;
        $this->nilai = $item->nilai;
        $this->satuan = $item->satuan;
        $this->showForm = true;
    }

    public function saveData()
    {
        $this->validate();

        DataStatistik::updateOrCreate(
            ['id' => $this->editingId],
            [
                'nama_indikator' => $this->nama_indikator,
                'kategori' => $this->kategori,
                'wilayah' => $this->wilayah,
                'tahun' => $this->form_tahun,
                'nilai' => $this->nilai,
                'satuan' => $this->satuan,
                'is_manual' => true,
            ]
        );

        $this->resetForm();
        $this->showForm = false;
        session()->flash('message', 'Data berhasil disimpan.');
    }

    public function deleteData($id)
    {
        DataStatistik::findOrFail($id)->delete();
        session()->flash('message', 'Data berhasil dihapus.');
    }

    public function cancelForm()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->nama_indikator = '';
        $this->kategori = '';
        $this->wilayah = '';
        $this->form_tahun = '';
        $this->nilai = '';
        $this->satuan = '';
        $this->resetErrorBag();
    }
}
