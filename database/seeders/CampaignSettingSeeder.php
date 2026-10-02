<?php

namespace Database\Seeders;

use App\Models\CampaignSetting;
use Illuminate\Database\Seeder;

class CampaignSettingSeeder extends Seeder
{
    /** No fundraising before there is a budget: the campaign starts (and stays) in planning. */
    public function run(): void
    {
        if (CampaignSetting::query()->doesntExist()) {
            CampaignSetting::query()->create(['status' => 'planning']);
        }
    }
}
