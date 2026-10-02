<?php

namespace App\Domain\Sightings;

/** Why the tower turned a report down; told to the author by e-mail. */
enum RejectionReason: string
{
    case IdentifiablePerson = 'identifiable_person';
    case LicensePlate = 'license_plate';
    case Offensive = 'offensive';
    case NotASighting = 'not_a_sighting';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IdentifiablePerson => 'Foto com pessoa identificável',
            self::LicensePlate => 'Foto com placa de carro',
            self::Offensive => 'Conteúdo ofensivo',
            self::NotASighting => 'Não é um relato de avistamento',
            self::Other => 'Outro motivo',
        };
    }
}
