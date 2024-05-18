<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Setting;
use App\Models\SettingSettingValue;
use App\Models\SettingValue;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{

    public function run()
    {
        $setting = Setting::factory()->create(
            ['key' => 'theme', 'type' => 'string']
        );

        SettingValue::factory()->create(['value' => 'light', 'setting_id' => $setting->id]);
        SettingValue::factory()->create(['value' => 'dark', 'setting_id' => $setting->id]);
        $system = SettingValue::factory()->create(['value' => 'system', 'setting_id' => $setting->id]);

        $setting->default = $system->id;
        $setting->save();
    }

}
