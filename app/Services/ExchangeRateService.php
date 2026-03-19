<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\ExchangeRateConfig;
use App\Models\ExchangeRateMonthlyHistory;
use App\Models\ExchangeRateDailyHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExchangeRateService
{
    private const DOLARVZLA_API = 'https://api.dolarvzla.com/public/exchange-rate';
    private const BACKUP_API = 'https://api.exchangerate-api.com/v4/latest/USD';

    /**
     * Obtener configuración actual del país
     */
    private function getCurrentConfig(): ?ExchangeRateConfig
    {
        return ExchangeRateConfig::getCurrentConfig();
    }

    /**
     * Obtener tasa para un país específico
     */
    public function getRateForPais($paisId = null): ?float
    {
        if (!$paisId) {
            $config = $this->getCurrentConfig();
            $paisId = $config?->pais_id;
        }

        // Si usa tasa fija, retornarla
        $config = ExchangeRateConfig::find($paisId);
        if ($config && $config->getFixedRate()) {
            return $config->getFixedRate();
        }

        // Si no, obtener de la base de datos
        return ExchangeRate::getLatestRate('USD', $paisId);
    }

    public function fetchAndStoreRates($paisId = null): bool
    {
        try {
            // Si no se proporciona país, usar el actual
            if (!$paisId) {
                $config = $this->getCurrentConfig();
                $paisId = $config?->pais_id;
            }

            // Obtener configuración del país
            $config = ExchangeRateConfig::find($paisId);
            
            // Si usa tasa fija, no hacer nada
            if ($config && $config->getFixedRate()) {
                Log::info('País usa tasa fija, no se actualiza desde API', [
                    'pais_id' => $paisId,
                    'tasa_fija' => $config->getFixedRate()
                ]);
                return false;
            }

            // Intentar obtener tasas según configuración
            $rates = null;
            
            if ($config && $config->usesBCVApi()) {
                // Venezuela - Usar API del BCV
                $rates = $this->fetchFromDolarVzla();
            } else {
                // Otros países - Usar API de respaldo
                $rates = $this->fetchFromBackupAPI();
            }

            if ($rates) {
                return $this->storeRates($rates, $paisId);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error fetching exchange rates: ' . $e->getMessage());
            return false;
        }
    }

    public function fetchHistoricalBCV(string $from, string $to): ?array
    {
        try {
            $base = config('services.dolarvzla.base_url', 'https://api.dolarvzla.com/public');
            $key = config('services.dolarvzla.key');
            $headers = [];
            if (!empty($key)) {
                $headers['x-dolarvzla-key'] = $key;
            }
            $url = rtrim($base, '/') . '/bcv/exchange-rate/list';
            $response = Http::timeout(20)
                ->withHeaders($headers)
                ->get($url, [
                    'from' => $from,
                    'to' => $to,
                ]);
            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? null;
                if (is_array($rates)) {
                    return $rates;
                }
            } else {
                Log::warning('DolarVzla historical API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('fetchHistoricalBCV error: ' . $e->getMessage());
        }
        return null;
    }


    /**
     * Fetch from DolarVzla API (Venezuela BCV)
     */
    private function fetchFromDolarVzla(): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::DOLARVZLA_API);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['usd'], $data['eur'])) {
                    return [
                        'usd_rate' => (float) $data['usd'],
                        'eur_rate' => (float) $data['eur'],
                        'source' => 'dolarvzla',
                        'raw_data' => $data
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching from DolarVzla: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch from Backup API (otros países)
     */
    private function fetchFromBackupAPI(): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BACKUP_API);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['rates']['USD'])) {
                    return [
                        'usd_rate' => (float) $data['rates']['USD'],
                        'eur_rate' => (float) ($data['rates']['EUR'] ?? 0),
                        'source' => 'backup_api',
                        'raw_data' => $data
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching from backup API: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Almacenar tasas en la base de datos
     */
    private function storeRates(array $rates, $paisId = null): bool
    {
        try {
            // Actualizar o crear la tasa del día para este país
            ExchangeRate::updateOrCreate(
                [
                    'date' => today(),
                    'pais_id' => $paisId
                ],
                [
                    'usd_rate' => $rates['usd_rate'],
                    'eur_rate' => $rates['eur_rate'],
                    'source' => $rates['source'],
                    'fetch_time' => now()->format('H:i:s'),
                    'raw_data' => $rates
                ]
            );

            Log::info('Exchange rates stored successfully', [
                'pais_id' => $paisId,
                'rates' => $rates
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error storing exchange rates: ' . $e->getMessage());
            return false;
        }
    }

    public function ensureMonthlyHistory(int $year, int $month): ?\App\Models\ExchangeRateMonthlyHistory
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();
        $this->backfillMonthBCV((int)$start->year, (int)$start->month);
        $rates = ExchangeRate::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();
        $count = $rates->count();
        $usdAvg = round((float) $rates->avg('usd_rate'), 4);
        $usdMin = round((float) $rates->min('usd_rate'), 4);
        $usdMax = round((float) $rates->max('usd_rate'), 4);
        $eurCollection = $rates->pluck('eur_rate')->filter(fn($v) => $v !== null);
        $eurAvg = $eurCollection->isNotEmpty() ? round((float) $eurCollection->avg(), 4) : null;
        $eurMin = $eurCollection->isNotEmpty() ? round((float) $eurCollection->min(), 4) : null;
        $eurMax = $eurCollection->isNotEmpty() ? round((float) $eurCollection->max(), 4) : null;
        $sources = $rates->pluck('source')->unique()->values()->all();
        $period = new \Carbon\CarbonPeriod($start, $end);
        $dailyRecords = [];
        foreach ($period as $day) {
            $found = $rates->first(function ($r) use ($day) {
                return $r->date instanceof Carbon ? $r->date->isSameDay($day) : Carbon::parse($r->date)->isSameDay($day);
            });
            $dailyRecords[] = [
                'date' => $day->toDateString(),
                'usd_rate' => $found ? (float)$found->usd_rate : null,
                'eur_rate' => $found && $found->eur_rate !== null ? (float)$found->eur_rate : null,
                'source' => $found ? $found->source : null,
                'fetch_time' => $found && $found->fetch_time ? ($found->fetch_time instanceof Carbon ? $found->fetch_time->format('H:i:s') : (string)$found->fetch_time) : null,
            ];
        }
        $monthly = ExchangeRateMonthlyHistory::updateOrCreate(
            ['year' => (int)$start->year, 'month' => (int)$start->month],
            [
                'usd_avg' => $usdAvg,
                'usd_min' => $usdMin,
                'usd_max' => $usdMax,
                'eur_avg' => $eurAvg,
                'eur_min' => $eurMin,
                'eur_max' => $eurMax,
                'records_count' => $count,
                'sources' => $sources,
                'daily_records' => $dailyRecords,
                'generated_at' => now(),
                'generated_by' => auth()->id(),
            ]
        );
        foreach ($rates as $rate) {
            ExchangeRateDailyHistory::updateOrCreate(
                [
                    'monthly_history_id' => $monthly->id,
                    'date' => $rate->date,
                ],
                [
                    'usd_rate' => $rate->usd_rate,
                    'eur_rate' => $rate->eur_rate,
                    'source' => $rate->source,
                    'fetch_time' => $rate->fetch_time,
                    'recorded_at' => now(),
                    'recorded_by' => auth()->id(),
                ]
            );
        }
        return $monthly;
    }

    public function getUsdRateForDate(Carbon $date): ?float
    {
        $hist = ExchangeRateDailyHistory::whereDate('date', $date->toDateString())->first();
        if ($hist && $hist->usd_rate) {
            return (float)$hist->usd_rate;
        }
        $this->ensureMonthlyHistory((int)$date->year, (int)$date->month);
        $hist = ExchangeRateDailyHistory::whereDate('date', $date->toDateString())->first();
        if ($hist && $hist->usd_rate) {
            return (float)$hist->usd_rate;
        }
        $rate = ExchangeRate::whereDate('date', $date->toDateString())->first();
        return $rate ? (float)$rate->usd_rate : null;
    }
    public function getLatestRate(string $currency = 'USD', $paisId = null): ?float
    {
        return ExchangeRate::getLatestRate($currency, $paisId);
    }

    public function getTodayRates($paisId = null)
    {
        return ExchangeRate::getTodayRate($paisId);
    }

    /**
     * Rellenar histórico mensual para un país
     */
    public function backfillMonthBCV(int $year, int $month, $paisId = null): int
    {
        // Solo funciona para Venezuela con API del BCV
        $config = ExchangeRateConfig::find($paisId);
        
        if (!$config || !$config->usesBCVApi()) {
            Log::warning('backfillMonthBCV solo disponible para países con API BCV');
            return 0;
        }

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $list = $this->fetchHistoricalBCV($startDate->toDateString(), $endDate->toDateString());
        
        if (!$list) {
            return 0;
        }

        $byDate = [];
        foreach ($list as $item) {
            if (!isset($item['date'])) {
                continue;
            }
            $d = Carbon::parse($item['date'])->toDateString();
            $byDate[$d] = $item;
        }

        $prevStart = $startDate->copy()->subMonth()->startOfMonth()->toDateString();
        $prevEnd = $startDate->copy()->subMonth()->endOfMonth()->toDateString();
        $prevList = $this->fetchHistoricalBCV($prevStart, $prevEnd) ?? [];
        $prevLast = null;
        
        if (!empty($prevList)) {
            usort($prevList, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
            $prevLast = end($prevList);
        }

        $updated = 0;
        $daysInMonth = (int) $startDate->copy()->endOfMonth()->format('j');
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $startDate->copy()->day($day)->toDateString();
            
            if (isset($byDate[$dateStr])) {
                $item = $byDate[$dateStr];
                $usd = (float) ($item['usd'] ?? $item['price'] ?? 0);
                $eur = isset($item['eur']) ? (float) $item['eur'] : null;
                
                if ($usd > 0) {
                    ExchangeRate::updateOrCreate(
                        ['date' => $dateStr, 'pais_id' => $paisId],
                        [
                            'usd_rate' => $usd,
                            'eur_rate' => $eur,
                            'source' => 'BCV (Histórico)',
                            'fetch_time' => '12:00:00',
                            'raw_data' => array_merge($item, [
                                'backfilled' => true,
                                'backfilled_at' => now()->toISOString(),
                            ])
                        ]
                    );
                    $updated++;
                }
            } elseif ($prevLast) {
                $usd = (float) ($prevLast['usd'] ?? $prevLast['price'] ?? 0);
                $eur = isset($prevLast['eur']) ? (float) $prevLast['eur'] : null;
                
                if ($usd > 0) {
                    ExchangeRate::updateOrCreate(
                        ['date' => $dateStr, 'pais_id' => $paisId],
                        [
                            'usd_rate' => $usd,
                            'eur_rate' => $eur,
                            'source' => 'BCV (Estimado)',
                            'fetch_time' => '12:00:00',
                            'raw_data' => array_merge($prevLast, [
                                'estimated' => true,
                                'estimated_from' => $prevLast['date'],
                                'backfilled_at' => now()->toISOString(),
                            ])
                        ]
                    );
                    $updated++;
                }
            }
        }

        return $updated;
    }
}
