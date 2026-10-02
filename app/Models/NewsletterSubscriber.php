<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string $source
 * @property string $consent_text
 * @property Carbon $consented_at
 * @property Carbon|null $confirmed_at
 */
class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'source', 'consent_text', 'consented_at', 'confirmed_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }
}
