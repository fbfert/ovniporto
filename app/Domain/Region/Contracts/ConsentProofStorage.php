<?php

namespace App\Domain\Region\Contracts;

/** The partner's written consent (PDF or image). Private: only admins download it from the panel. */
interface ConsentProofStorage
{
    /** @return string the stored path */
    public function put(string $contents, string $extension): string;

    public function get(string $path): ?string;

    public function delete(?string $path): void;
}
