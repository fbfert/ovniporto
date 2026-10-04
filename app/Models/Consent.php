<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property string $version
 * @property int|null $member_id
 * @property string|null $email
 * @property string|null $subject
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon $given_at
 */
class Consent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['given_at' => 'datetime'];
    }
}
