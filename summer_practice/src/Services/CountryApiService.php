<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Logger\LoggerFactory;
use App\Models\Country;
use App\Models\ApiUpdate;

class CountryApiService
{
    private Client $client;
    private string $baseUrl = 'https://api.restcountries.com/countries/v5';
    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $_ENV['API_KEY'] ?? 'rc_live_demo';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60,
            'connect_timeout' => 30,
            'verify' => false,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ],
        ]);
    }

    public function fetchAllCountries(): array
    {
        $allCountries = [];
        $limit = 100;
        $offset = 0;
        $total = null;

        try {
            $logger = LoggerFactory::create();
            $logger->info('Fetching countries from API with pagination...');

            do {
                $response = $this->client->get('', [
                    'query' => [
                        'limit' => $limit,
                        'offset' => $offset,
                        'response_fields' => implode(',', [
                            'names', 'capitals', 'region', 'subregion',
                            'population', 'area', 'flag', 'coordinates',
                            'timezones', 'borders'
                        ]),
                    ],
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                    ],
                ]);

                $data = json_decode($response->getBody(), true);

                if (isset($data['data']['objects'])) {
                    $objects = $data['data']['objects'];
                    $meta = $data['data']['meta'];
                } elseif (isset($data['objects'])) {
                    $objects = $data['objects'];
                    $meta = $data['meta'] ?? [];
                } else {
                    throw new \Exception('Invalid API response structure');
                }

                $allCountries = array_merge($allCountries, $objects);
                $total = $meta['total'] ?? 0;
                $offset += $limit;

                $logger->debug('Fetched batch', [
                    'count' => count($objects),
                    'offset' => $offset,
                    'total' => $total,
                ]);
            } while ($offset < $total);

            $logger->info('All countries fetched', ['total' => count($allCountries)]);
            return $allCountries;
        } catch (\Exception $e) {
            $logger = LoggerFactory::create();
            $logger->error('Failed to fetch countries from API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public static function transformCountryData(array $countryData): array
    {
        $name = $countryData['names']['common'] ?? 'Unknown';
        $nameRu = $countryData['names']['translations']['rus']['common'] ?? $name;

        $flagUrl = '';
        if (isset($countryData['flag']['url_png']) && !empty($countryData['flag']['url_png'])) {
            $flagUrl = $countryData['flag']['url_png'];
        } elseif (isset($countryData['flag']['url_svg']) && !empty($countryData['flag']['url_svg'])) {
            $flagUrl = $countryData['flag']['url_svg'];
        }

        $capital = 'Unknown';
        if (
            isset($countryData['capitals'])
            && is_array($countryData['capitals'])
            && count($countryData['capitals']) > 0
        ) {
            $capital = $countryData['capitals'][0]['name'] ?? 'Unknown';
        }

        $latLng = null;
        if (isset($countryData['coordinates']['lat']) && isset($countryData['coordinates']['lng'])) {
            $latLng = $countryData['coordinates']['lat'] . ',' . $countryData['coordinates']['lng'];
        }

        $area = null;
        if (isset($countryData['area']['kilometers'])) {
            $area = (float) $countryData['area']['kilometers'];
        }

        $borders = null;
        if (isset($countryData['borders']) && is_array($countryData['borders']) && count($countryData['borders']) > 0) {
            $borders = implode(',', $countryData['borders']);
        }

        $timezones = null;
        if (
            isset($countryData['timezones'])
            && is_array($countryData['timezones'])
            && count($countryData['timezones']) > 0
        ) {
            $timezones = implode(',', $countryData['timezones']);
        }

        return [
            'name' => $nameRu,
            'name_en' => $name,
            'capital' => $capital,
            'region' => $countryData['region'] ?? 'Unknown',
            'subregion' => $countryData['subregion'] ?? null,
            'population' => $countryData['population'] ?? null,
            'area' => $area,
            'flag_url' => $flagUrl,
            'lat_lng' => $latLng,
            'timezones' => $timezones,
            'borders' => $borders,
        ];
    }

    public function importCountries(): array
    {
        $logger = LoggerFactory::create();
        $result = [
            'imported' => 0,
            'errors' => 0,
            'total' => 0,
            'details' => [],
            'error_details' => []
        ];

        try {
            $countries = $this->fetchAllCountries();
            $result['total'] = count($countries);

            Country::deleteAll();
            $logger->info('Existing countries cleared');

            foreach ($countries as $countryData) {
                try {
                    $transformed = self::transformCountryData($countryData);

                    if (empty($transformed['flag_url'])) {
                        $result['errors']++;
                        $result['error_details'][] = [
                            'name' => $transformed['name'],
                            'reason' => 'Нет флага'
                        ];
                        $logger->warning('Country skipped - no flag', [
                            'name' => $transformed['name']
                        ]);
                        continue;
                    }

                    $country = new Country($transformed);
                    if ($country->save()) {
                        $result['imported']++;
                        $result['details'][] = [
                            'name' => $transformed['name'],
                            'region' => $transformed['region'],
                            'status' => 'success'
                        ];
                    } else {
                        $result['errors']++;
                        $result['error_details'][] = [
                            'name' => $transformed['name'],
                            'reason' => 'Ошибка сохранения в БД'
                        ];
                        $logger->warning('Failed to save country', [
                            'name' => $transformed['name']
                        ]);
                    }
                } catch (\Exception $e) {
                    $result['errors']++;
                    $result['error_details'][] = [
                        'name' => $countryData['names']['common'] ?? 'Unknown',
                        'reason' => $e->getMessage()
                    ];
                    $logger->error('Error importing country', [
                        'message' => $e->getMessage(),
                        'country' => $countryData['names']['common'] ?? 'unknown'
                    ]);
                }
            }

            $logger->info('Import completed', [
                'imported' => $result['imported'],
                'errors' => $result['errors'],
                'total' => $result['total'],
            ]);

            ApiUpdate::create([
                'status' => $result['errors'] === 0 ? 'success' : 'partial',
                'countries_imported' => $result['imported'],
                'error_message' => $result['errors'] > 0 ? "Errors: {$result['errors']}" : null
            ]);
        } catch (\Exception $e) {
            $logger->error('Import failed', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }

        return $result;
    }

    public function needsUpdate(): bool
    {
        return ApiUpdate::needsUpdate();
    }

    public function getLastUpdateInfo(): ?array
    {
        return ApiUpdate::getLast();
    }

    public function getLastUpdateStatus(): array
    {
        $info = $this->getLastUpdateInfo();

        if (!$info) {
            return [
                'has_update' => false,
                'message' => 'Данные ещё не импортированы'
            ];
        }

        return [
            'has_update' => true,
            'status' => $info['status'],
            'countries_imported' => $info['countries_imported'],
            'timestamp' => $info['created_at'],
            'is_success' => $info['status'] === 'success'
        ];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->client->get('', [
                'query' => ['limit' => 1],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
