<?php

use Illuminate\Database\Seeder;

class SeedGroups extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups[] = [
            'name' => 'Midsouth Makers',
            'website' => 'http://www.midsouthmakers.org',
            'twitter' => 'MidsouthMakers',
        ];
        $groups[] = [
            'name' => 'Game Developers',
            'website' => '',
            'twitter' => 'MemphisGameDev',
        ];
        $groups[] = [
            'name' => 'Web Workers',
            'website' => 'http://memphiswebworkers.com',
            'twitter' => 'MemphisWW',
        ];
        $groups[] = [
            'name' => 'Ruby',
            'website' => '',
            'twitter' => 'MemphisRuby',
        ];
        $groups[] = [
            'name' => 'Python',
            'website' => '',
            'twitter' => 'MemphisPython',
        ];
        $groups[] = [
            'name' => 'PHP',
            'website' => 'http://www.memphisphp.org',
            'twitter' => 'MemphisPHP',
        ];
        $groups[] = [
            'name' => 'Java',
            'website' => '',
            'twitter' => 'memphisjug',
        ];
        $groups[] = [
            'name' => '.net',
            'website' => '',
            'twitter' => 'memdotnet',
        ];
        $groups[] = [
            'name' => 'pass',
            'website' => '',
            'twitter' => 'mempass',
        ];

        foreach ($groups as $group)
        {
            \MemtechEvents\Group::create($group);
        }

    }
}
