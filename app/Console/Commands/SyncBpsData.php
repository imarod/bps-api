<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataStatistik;
use Illuminate\Support\Facades\Http;

class SyncBpsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bps:sync';
    protected $description = 'Sinkronisasi API BPS kota Lubuklinggau ke database lokal';

    private string $baseUrl = 'https://webapi.bps.go.id/v1/api';
    private string $apiKey;
    private string $domain;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->apiKey = config('services.bps_api.key');
        $this->domain = config('bps_api.domain');

        foreach (config('bps_api.indikator') as $indikator) {
            $this->info("Sync: {$indikator['nama_indikator']}...");
            $this->syncIndikator($indikator);
        }

        $this->info('Selesai');
        return self::SUCCESS;
    }

    private function syncIndikator(array $indikator): void
    {
      $thIds = $this->getDaftarTahun($indikator['var_id']);

      if (empty($thIds)) {
        $this->warn ("Tidak ada data tahun untuk var_id {$indikator['var_id']}");
        return;
      }

      foreach (array_chunk($thIds, 2) as $chunk) {
        $this->syncChunk($indikator, $chunk);
        usleep(1);
      }
    }

    private function getDaftarTahun(int $varId): array
    {
        $result = [];
        $page = 1;

        do {
            $response = Http::get("{$this->baseUrl}/list/model/th/domain/{$this->domain}/var/{$varId}/key/{$this->apiKey}/?&page={$page}")->json();

            if (($response['data-availability'] ?? null) !== 'available') {
                break;
            }
            
            [$meta, $items] = $response['data'];
            
            foreach ($items as $item) {
                $result[] = $item['th_id'];
            }

            $page++;      
        } while ($page <= $meta['pages']);

        return $result;
    }


    private function syncChunk (array $indikator, array $thIds): void
    {
        $thParam = implode(':' , $thIds); 
        $url = "{$this->baseUrl}/list/model/data/domain/{$this->domain}/var/{$indikator['var_id']}/th/{$thParam}/key/{$this->apiKey}/";
         
        $response = Http::get($url)->json();

        if(($response['data-availability'] ?? null) !== 'available') {
            return;
        }

        $vervarList = $response['vervar']  ?? [];
        $tahunList = $response['tahun'] ?? [];
        $dataContent = $response['datacontent'] ?? [];
        $turvarId = $indikator['turvar_id'] ?? [] ;
        $turTahun = 0;

        foreach($vervarList as $vervar) {
            if (!str_contains(strtolower($vervar['label']), 'lubuk')) {
                continue;
            }

            foreach ($tahunList as $tahun) {
                $key = $vervar['val'] . $indikator['var_id'] . $turvarId . $tahun['val'] . $turTahun;
                $nilai = $dataContent[$key] ?? null;

                if ($nilai === null || !is_numeric($nilai)) {
                    continue;
                }

                $existing = DataStatistik::where('nama_indikator', $indikator['nama_indikator'])
                    ->where('tahun', (int) $tahun['label'])
                    ->where('wilayah', $vervar['label'])
                    ->first();

                    if($existing && $existing->is_manual) {
                        continue;
                    }

                DataStatistik::updateorCreate(
                    [
                        'nama_indikator' => $indikator['nama_indikator'],
                        'tahun' => (int) $tahun['label'],
                        'wilayah' => $vervar['label'],
                    ],
                    [
                        'kategori' => $indikator['kategori'],
                        'nilai' => $nilai,
                        'satuan' => $indikator['satuan'],
                        'sumber' => 'BPS Kota Lubuk Linggau',
                        'is_manual' => false,
                    ]
                );
            }
        }

    }
}
