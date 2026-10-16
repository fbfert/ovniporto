<?php

namespace App\Domain\Privacy;

use LogicException;

/** The moments someone says yes to us. The value is what the export shows. */
enum ConsentType: string
{
    case Terms = 'termos';
    case SightingPublication = 'publicacao_relato';
    case Newsletter = 'newsletter';
    case PartnerListing = 'listagem_parceiro';
    case Collaboration = 'colaboracao';
    case MinimumAge = 'idade_minima';
    case PurchaseAuthorization = 'compra_autorizada';

    /**
     * Version of the wording people agree to. Change the date together with the text:
     * report consent in resources/js/i18n/pt-BR.ts (report.consent), newsletter in
     * SubscribeToWaitlist::CONSENT_TEXT, collaboration in ApplyAsCollaborator::CONSENT_TEXT, partner listing in the panel's consent form,
     * minimum age in pt-BR.ts (members.ageConfirm), purchase authorization in pt-BR.ts (checkout.purchaseAuthorization).
     * The terms are versioned by their publication date (CurrentTermsVersion).
     */
    public function textVersion(): string
    {
        return match ($this) {
            self::SightingPublication => '2026-10-02',
            self::Newsletter => '2026-10-01',
            self::PartnerListing => '2026-10-02',
            self::Collaboration => '2026-10-05',
            self::MinimumAge => '2026-10-16',
            self::PurchaseAuthorization => '2026-10-16',
            self::Terms => throw new LogicException('The terms version comes from CurrentTermsVersion.'),
        };
    }
}
