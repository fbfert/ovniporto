<?php

namespace App\Application\Settings\UseCases;

use App\Domain\Audit\Contracts\Auditor;
use App\Domain\Audit\Data\AuditEntry;
use App\Domain\Settings\Contracts\MailTester;
use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Settings\Contracts\SettingsBaseline;
use App\Domain\Settings\Data\MailTestResult;
use App\Domain\Settings\OperationalSetting;
use App\Domain\Settings\SettingsGroup;

/** The two test buttons of /painel/coordenadas, sent right away and audited with their result. */
final readonly class TestCoordinates
{
    public function __construct(
        private MailTester $tester,
        private OperationalSettingsRepository $settings,
        private SettingsBaseline $baseline,
        private Auditor $auditor,
    ) {}

    public function mail(int $actorId, string $adminEmail): MailTestResult
    {
        $result = $this->audited($actorId, 'coordinates.mail_tested', SettingsGroup::Mail, fn () => $this->tester->send(
            [$adminEmail],
            'Teste de e-mail do OVNIPORTO',
            "Se esta mensagem chegou, o e-mail do site está funcionando.\n\nEnviada pelo botão \"Enviar e-mail de teste\" em Painel → Coordenadas.",
        ));
        $this->settings->recordMailTest($result->sent);

        return $result;
    }

    public function alert(int $actorId): MailTestResult
    {
        $to = $this->settings->values()[OperationalSetting::AlertsEmail->value] ?? $this->baseline->envValue(OperationalSetting::AlertsEmail);
        if (blank($to)) {
            return new MailTestResult(false, 'no-recipient');
        }

        return $this->audited($actorId, 'coordinates.alert_tested', SettingsGroup::Alerts, fn () => $this->tester->send(
            [(string) $to],
            'Torre: alerta de teste',
            "Este é um alerta de teste. Os avisos de tarefa que falhou e de fila esperando demais chegam neste endereço.\n\nEnviado pelo botão \"Enviar alerta de teste\" em Painel → Coordenadas.",
        ));
    }

    /** @param callable(): MailTestResult $send */
    private function audited(int $actorId, string $action, SettingsGroup $group, callable $send): MailTestResult
    {
        $result = $send();
        $this->auditor->audited(
            new AuditEntry($actorId, $action, SaveCoordinates::SUBJECT, SaveCoordinates::subjectId($group), array_filter([
                'sent' => $result->sent,
                'problem' => $result->problem,
            ], fn ($v) => $v !== null)),
            fn () => null,
            fn () => null,
        );

        return $result;
    }
}
