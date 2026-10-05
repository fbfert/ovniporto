<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $location
 * @property list<string> $areas
 * @property string $message
 * @property string $consent_text
 * @property Carbon $consented_at
 */
class ResearchCollaborator extends Model
{
    protected $fillable = ['name', 'email', 'location', 'areas', 'message', 'consent_text', 'consented_at'];

    protected function casts(): array
    {
        return ['areas' => 'array', 'consented_at' => 'datetime'];
    }
}
