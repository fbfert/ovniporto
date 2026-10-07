<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string $value encrypted when is_secret
 * @property bool $is_secret
 * @property int|null $updated_by
 */
class OperationalSettingRow extends Model
{
    protected $table = 'operational_settings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_secret' => 'boolean'];
    }
}
