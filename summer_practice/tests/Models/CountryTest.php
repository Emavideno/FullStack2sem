<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Country;
use App\Database\Database;

class CountryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Country::deleteAll();
    }

    public function testCreateCountry()
    {
        $country = new Country([
            'name' => 'Test Country',
            'capital' => 'Test Capital',
            'region' => 'Europe',
            'population' => 10000000,
            'area' => 500000,
            'flag_url' => 'https://example.com/flag.png'
        ]);
        
        $result = $country->save();
        $this->assertTrue($result);
        $this->assertNotNull($country->getId());
    }

    public function testFindById()
    {
        $country = new Country([
            'name' => 'Test Country 2',
            'capital' => 'Test Capital 2',
            'region' => 'Asia',
            'flag_url' => 'https://example.com/flag2.png'
        ]);
        $country->save();
        
        $found = Country::findById($country->getId());
        $this->assertNotNull($found);
        $this->assertEquals('Test Country 2', $found->getName());
    }

    public function testFindByName()
    {
        $country = new Country([
            'name' => 'Unique Country',
            'capital' => 'Unique Capital',
            'region' => 'Africa',
            'flag_url' => 'https://example.com/flag3.png'
        ]);
        $country->save();
        
        $found = Country::findByName('Unique Country');
        $this->assertNotNull($found);
        $this->assertEquals('Unique Capital', $found->getCapital());
    }

    public function testFindByRegion()
    {
        $country = new Country([
            'name' => 'Region Country',
            'capital' => 'Region Capital',
            'region' => 'Oceania',
            'flag_url' => 'https://example.com/flag4.png'
        ]);
        $country->save();
        
        $countries = Country::findByRegion('Oceania');
        $this->assertNotEmpty($countries);
        $this->assertEquals('Region Country', $countries[0]['name']);
    }

    public function testGetRegions()
    {
        $regions = ['Europe', 'Asia', 'Africa'];
        foreach ($regions as $region) {
            $country = new Country([
                'name' => $region . ' Country',
                'capital' => $region . ' Capital',
                'region' => $region,
                'flag_url' => 'https://example.com/flag.png'
            ]);
            $country->save();
        }
        
        $foundRegions = Country::getRegions();
        foreach ($regions as $region) {
            $this->assertContains($region, $foundRegions);
        }
    }

    public function testGetCount()
    {
        $initialCount = Country::getCount();
        
        $country = new Country([
            'name' => 'Count Country',
            'capital' => 'Count Capital',
            'region' => 'Americas',
            'flag_url' => 'https://example.com/flag.png'
        ]);
        $country->save();
        
        $newCount = Country::getCount();
        $this->assertEquals($initialCount + 1, $newCount);
    }

    public function testGetRandomCountries()
    {
        for ($i = 1; $i <= 10; $i++) {
            $country = new Country([
                'name' => 'Random Country ' . $i,
                'capital' => 'Random Capital ' . $i,
                'region' => 'Europe',
                'flag_url' => 'https://example.com/flag.png'
            ]);
            $country->save();
        }
        
        $random = Country::getRandomCountries(3);
        $this->assertCount(3, $random);
    }

    public function testGetLastUpdated()
    {
        $lastUpdated = Country::getLastUpdated();
        $this->assertTrue($lastUpdated === null || is_string($lastUpdated));
    }

    public function testUpdateCountry()
    {
        $country = new Country([
            'name' => 'Update Country',
            'capital' => 'Old Capital',
            'region' => 'Europe',
            'flag_url' => 'https://example.com/flag.png'
        ]);
        $country->save();
        
        $country->setCapital('New Capital');
        $country->save();
        
        $found = Country::findById($country->getId());
        $this->assertEquals('New Capital', $found->getCapital());
    }

    public function testDeleteCountry()
    {
        $country = new Country([
            'name' => 'Delete Country',
            'capital' => 'Delete Capital',
            'region' => 'Europe',
            'flag_url' => 'https://example.com/flag.png'
        ]);
        $country->save();
        $id = $country->getId();
        
        $country->delete();
        
        $found = Country::findById($id);
        $this->assertNull($found);
    }

    public function testGetRegionsStats()
    {
        $countries = [
            ['name' => 'Europe Country 1', 'region' => 'Europe', 'population' => 10000000, 'area' => 500000],
            ['name' => 'Europe Country 2', 'region' => 'Europe', 'population' => 20000000, 'area' => 600000],
            ['name' => 'Asia Country 1', 'region' => 'Asia', 'population' => 30000000, 'area' => 700000],
        ];
        
        foreach ($countries as $data) {
            $country = new Country([
                'name' => $data['name'],
                'capital' => $data['name'] . ' Capital',
                'region' => $data['region'],
                'population' => $data['population'],
                'area' => $data['area'],
                'flag_url' => 'https://example.com/flag.png'
            ]);
            $country->save();
        }
        
        $stats = Country::getRegionsStats();
        $this->assertNotEmpty($stats);
        
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('region', $stat);
            $this->assertArrayHasKey('country_count', $stat);
            $this->assertArrayHasKey('avg_population', $stat);
            $this->assertArrayHasKey('avg_area', $stat);
        }
    }
}
