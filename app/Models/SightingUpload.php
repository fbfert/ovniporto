<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A photo uploaded during the wizard, waiting for its report.
 *
 * @property string $id
 * @property int $member_id
 * @property string $path
 * @property string $mime
 * @property int $size
 * @property Carbon $created_at
 */
class SightingUpload extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $guarded = [];
}
