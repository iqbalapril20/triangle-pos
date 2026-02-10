<?php

namespace Modules\Setting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Currency\Entities\Currency;
use Modules\Setting\Entities\Setting;

class SettingDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $idrId = Currency::where('code', 'IDR')->value('id');

        Setting::create([
            'company_name' => 'Triangle POS',
            'company_email' => 'company@test.com',
            'company_phone' => '012345678901',
            'notification_email' => 'notification@test.com',

            // 👉 IDR jadi default
            'default_currency_id' => $idrId,

            'default_currency_position' => 'prefix',
            'footer_text' => 'Triangle Pos © 2021 || Developed by <strong><a target="_blank" href="https://fahimanzam.me">Fahim Anzam</a></strong>',
            'company_address' => 'Tangail, Bangladesh',
        ]);
    }
}
