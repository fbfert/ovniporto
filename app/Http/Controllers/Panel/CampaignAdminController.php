<?php

namespace App\Http\Controllers\Panel;

use App\Application\Campaign\UseCases\ManageCampaign;
use App\Application\Campaign\UseCases\ManageWaitlist;
use App\Domain\Campaign\CampaignStatus;
use App\Domain\Campaign\Data\CampaignSettings;
use App\Domain\Campaign\Data\SupporterEntry;
use App\Domain\Sightings\UnsupportedImage;
use App\Http\Controllers\Controller;
use App\Http\Responses\CsvDownload;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** /painel/campanha and /painel/avise-me (admin only). */
class CampaignAdminController extends Controller
{
    public function show(ManageCampaign $campaign): Response
    {
        return Inertia::render('Panel/Content/Campaign', $campaign->page());
    }

    public function update(Request $request, ManageCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(CampaignStatus::class)],
            'goal' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'raised' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'crowdfundingUrl' => ['nullable', 'url:https', 'max:255'],
            'storeSharePercent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $campaign->saveSettings($this->actorId($request), new CampaignSettings(
            status: CampaignStatus::from($data['status']),
            goalCents: self::cents($data['goal'] ?? null),
            raisedCents: self::cents($data['raised'] ?? null),
            crowdfundingUrl: $data['crowdfundingUrl'] ?? null,
            storeSharePercent: isset($data['storeSharePercent']) ? (float) $data['storeSharePercent'] : null,
        ));

        return back()->with('toast', 'Campanha salva.');
    }

    public function addSupporter(Request $request, ManageCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'reward' => ['nullable', 'string', 'max:120'],
            'publishName' => ['boolean'],
            'supportedAt' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $campaign->addSupporter($this->actorId($request), new SupporterEntry(
            name: $data['name'],
            amountCents: self::cents($data['amount'] ?? null),
            reward: $data['reward'] ?? null,
            publishName: (bool) ($data['publishName'] ?? false),
            supportedAt: isset($data['supportedAt']) ? new DateTimeImmutable($data['supportedAt']) : null,
        ));

        return back()->with('toast', 'Apoiador adicionado.');
    }

    public function importSupporters(Request $request, ManageCampaign $campaign): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']], ['file.required' => 'Escolha o arquivo CSV.']);
        $file = $request->file('file');
        abort_unless($file instanceof UploadedFile, 422);

        try {
            $count = $campaign->importSupporters($this->actorId($request), (string) $file->get());
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }

        return back()->with('toast', $count === 1 ? '1 apoiador importado.' : "{$count} apoiadores importados.");
    }

    public function deleteSupporter(Request $request, ManageCampaign $campaign, int $supporter): RedirectResponse
    {
        $campaign->deleteSupporter($this->actorId($request), $supporter);

        return back()->with('toast', 'Apoiador removido.');
    }

    public function addSponsor(Request $request, ManageCampaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tier' => ['nullable', 'string', 'max:60'],
            'url' => ['nullable', 'url:https', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);
        $logo = $request->file('logo');

        try {
            $campaign->addSponsor(
                $this->actorId($request),
                $data['name'],
                $data['tier'] ?? null,
                $data['url'] ?? null,
                $logo instanceof UploadedFile ? (string) $logo->get() : null,
            );
        } catch (UnsupportedImage) {
            throw ValidationException::withMessages(['logo' => 'Não deu para ler essa imagem. Envie PNG, JPG ou WebP.']);
        }

        return back()->with('toast', 'Patrocinador adicionado.');
    }

    public function deleteSponsor(Request $request, ManageCampaign $campaign, int $sponsor): RedirectResponse
    {
        $campaign->deleteSponsor($this->actorId($request), $sponsor);

        return back()->with('toast', 'Patrocinador removido.');
    }

    public function waitlist(ManageWaitlist $waitlist): Response
    {
        return Inertia::render('Panel/Content/Waitlist', $waitlist->page());
    }

    public function exportWaitlist(ManageWaitlist $waitlist): StreamedResponse
    {
        return CsvDownload::make('avise-me-'.now()->format('Y-m-d').'.csv', $waitlist->export());
    }

    public function removeSubscriber(Request $request, ManageWaitlist $waitlist, int $subscriber): RedirectResponse
    {
        $waitlist->remove($this->actorId($request), $subscriber);

        return back()->with('toast', 'Inscrito removido.');
    }

    /** "1.500,50" never reaches here: the form sends a plain number in reais. */
    private static function cents(mixed $reais): ?int
    {
        return $reais === null || $reais === '' ? null : (int) round((float) $reais * 100);
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
