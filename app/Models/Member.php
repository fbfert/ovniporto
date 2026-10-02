<?php

namespace App\Models;

use App\Domain\Members\MemberRole;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * A community member. Authenticated through Google only: there is no password
 * column and none is ever set.
 *
 * @property int $id
 * @property string $google_id
 * @property string $name
 * @property string $email
 * @property string|null $avatar_url
 * @property string|null $nickname
 * @property string|null $city
 * @property MemberRole $role
 * @property Carbon|null $terms_accepted_at
 * @property Carbon|null $blocked_at
 * @property string|null $blocked_reason
 * @property Carbon $created_at
 */
class Member extends Authenticatable
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = ['google_id', 'name', 'email', 'avatar_url', 'nickname', 'city', 'role', 'terms_accepted_at'];

    protected $hidden = ['google_id'];

    protected function casts(): array
    {
        return ['terms_accepted_at' => 'datetime', 'blocked_at' => 'datetime', 'role' => MemberRole::class];
    }

    /** No password: Google is the only way in. */
    public function getAuthPassword(): string
    {
        return '';
    }

    public function hasCompleteProfile(): bool
    {
        return $this->nickname !== null && $this->terms_accepted_at !== null;
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    /** @return HasMany<Sighting, $this> */
    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class);
    }
}
