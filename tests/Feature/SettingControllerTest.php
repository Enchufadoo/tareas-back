<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\User;
use App\Models\UserSetting;
use App\Tests\Utils;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;
    use Utils;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
        $this->user = User::first();

        $this->actingAs($this->user);
    }

    public function test_list_a_single_setting_selected()
    {
        $setting = Setting::factory()->create();

        SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting->id]);
        $defaultValue = SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting->id]);
        $selectedValue = SettingValue::factory()->create(['value' => 'selected', 'setting_id' => $setting->id]);

        $setting->default = $defaultValue->id;
        $setting->save();

        UserSetting::factory()->create([
            'setting_id' => $setting,
            'value_id' => $selectedValue,
            'user_id' => $this->user
        ]);

        $response = $this->json('get', '/api/setting');
        $response->assertStatus(Response::HTTP_OK);

        $data = $this->convertToJson($response)['data']['settings'];

        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data[$setting->key]);
        $this->assertEquals($selectedValue->value, $data[$setting->key]['value']);
        $this->assertEquals($setting->id, $data[$setting->key]['id']);
        $this->assertCount(3, $data[$setting->key]['options']);
    }

    public function test_list_a_multiple_settings_default_value()
    {
        $setting = Setting::factory()->create();

        SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting->id]);
        $defaultValue = SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting->id]);
        SettingValue::factory()->create(['value' => 'selected', 'setting_id' => $setting->id]);

        $setting->default = $defaultValue->id;
        $setting->save();

        $setting2 = Setting::factory()->create();

        $firstValue2 = SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting2->id]);
        SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting2->id]);

        $setting2->default = $firstValue2->id;
        $setting2->save();

        $response = $this->json('get', '/api/setting');
        $response->assertStatus(Response::HTTP_OK);

        $data = $this->convertToJson($response)['data']['settings'];

        $this->assertCount(2, $data);

        $this->assertNotEmpty($data[$setting->key]);
        $this->assertEquals($defaultValue->value, $data[$setting->key]['value']);
        $this->assertEquals($setting->id, $data[$setting->key]['id']);
        $this->assertCount(3, $data[$setting->key]['options']);

        $this->assertNotEmpty($data[$setting2->key]);
        $this->assertEquals($firstValue2->value, $data[$setting2->key]['value']);
        $this->assertEquals($setting2->id, $data[$setting2->key]['id']);
        $this->assertCount(2, $data[$setting2->key]['options']);
    }

    public function test_updating_a_user_setting()
    {
        $setting = Setting::factory()->create();

        $firstValue = SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting->id]);
        $defaultValue = SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting->id]);
        $selectedValue = SettingValue::factory()->create(['value' => 'selected', 'setting_id' => $setting->id]);

        $setting->default = $defaultValue->id;
        $setting->save();

        $userSetting = UserSetting::factory()->create([
            'setting_id' => $setting,
            'value_id' => $selectedValue,
            'user_id' => $this->user
        ]);

        $response = $this->json('put', '/api/setting/' . $setting->id, ['value' => $firstValue->value]);
        $response->assertStatus(Response::HTTP_OK);

        $userSetting->refresh();

        $this->assertEquals($firstValue->id, $userSetting->value_id);
    }

    public function test_updating_a_user_setting_that_hasnt_been_set_before()
    {
        $setting = Setting::factory()->create();

        $firstValue = SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting->id]);
        $defaultValue = SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting->id]);
        SettingValue::factory()->create(['value' => 'selected', 'setting_id' => $setting->id]);

        $setting->default = $defaultValue->id;
        $setting->save();

        $response = $this->json('put', '/api/setting/' . $setting->id, ['value' => $firstValue->value]);
        $response->assertStatus(Response::HTTP_OK);

        $userSetting = UserSetting::first();

        $this->assertEquals($firstValue->id, $userSetting->value_id);
    }

    public function test_updating_a_user_setting_that_with_a_wrong_value_fails()
    {
        $setting = Setting::factory()->create();
        $wrongSetting = Setting::factory()->create();

        SettingValue::factory()->create(['value' => 'first', 'setting_id' => $setting->id]);
        $defaultValue = SettingValue::factory()->create(['value' => 'default', 'setting_id' => $setting->id]);
        $selectedValue = SettingValue::factory()->create(['value' => 'selected', 'setting_id' => $setting->id]);

        $failedValue = SettingValue::factory()->create(['value' => 'failed', 'setting_id' => $wrongSetting->id]);

        $setting->default = $defaultValue->id;
        $setting->save();

        $userSetting = UserSetting::factory()->create([
            'setting_id' => $setting,
            'value_id' => $selectedValue,
            'user_id' => $this->user
        ]);

        $response = $this->json('put', '/api/setting/' . $setting->id, ['value' => $failedValue->value]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $userSetting->refresh();

        $this->assertEquals($selectedValue->id, $userSetting->value_id);
    }
}
