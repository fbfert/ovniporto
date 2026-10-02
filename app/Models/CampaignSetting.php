<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $status
 * @property int|null $goal_cents
 * @property int|null $raised_cents
 * @property string|null $crowdfunding_url
 * @property string|null $store_share_percent decimal, as returned by the driver
 */
class CampaignSetting extends Model
{
    protected $guarded = ['id'];
}
