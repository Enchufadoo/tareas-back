<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Models\SettingSettingValue;
use App\Models\SettingValue;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Retrieve a list of settings for the authenticated user.
     *
     * @return JsonResponse
     */
    public function listSettings(): JsonResponse
    {
        $userSettings = Setting::select('key', 'type', 'default', 'id')
            ->with(['defaultValue', 'fromUser' => function ($query) {
                $query->select('value_id', 'user_id', 'setting_id')
                    ->where('user_id', '=', auth()->user()->id)->with('value');
            }])->get()->toArray();

        $settings = array_column($userSettings, null, 'key');

        $settingsFiltered = array_map(function ($data) {
            $defaultValue = $data['default_value']['value'];

            $options = SettingValue::where('setting_id', '=', $data['id'])->get()->toArray();

            return [
                'id' => $data['id'],
                'value' => is_null($data['from_user']) ? $defaultValue : $data['from_user']['value']['value'],
                'default' => $defaultValue,
                'default_id' => $data['default_value']['id'],
                'options' => $options
            ];
        }, $settings);

        return $this->json([
            'settings' => $settingsFiltered
        ], 'List of settings');
    }

    /**
     * Update a user setting.
     *
     * This method updates the value of a specific user setting based on the provided request and setting object.
     *
     * @param UpdateSettingRequest $request
     * @param Setting $setting
     * @return JsonResponse
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $userSetting = UserSetting::where(
            [['user_id', '=', auth()->user()->id],
                ['setting_id', '=', $setting->id]]
        )->first();

        if (!$userSetting) {
            $userSetting = new UserSetting([
                'user_id' => auth()->user()->id,
                'setting_id' => $setting->id
            ]);
        }

        $settingValue = SettingValue::where(
            [
                ['value', '=', $request->value],
                ['setting_id', '=', $setting->id]
            ]
        )->first();

        $userSetting->value_id = $settingValue->id;
        $userSetting->save();

        return $this->json([], 'Setting updated successfully');
    }

}
