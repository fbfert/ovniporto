<?php

namespace App\Http\Controllers\Panel;

use App\Application\Region\UseCases\ManageRegionPartners;
use App\Domain\Region\Data\PartnerFields;
use App\Domain\Region\PartnerType;
use App\Domain\Region\PartnerWithoutConsent;
use App\Domain\Sightings\UnsupportedImage;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/** /painel/regiao (admin only). */
class RegionAdminController extends Controller
{
    public function index(ManageRegionPartners $partners): Response
    {
        return Inertia::render('Panel/Content/Region', ['partners' => $partners->list()]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Content/RegionPartner', ['partner' => null, 'origin' => config('ovniporto.location')]);
    }

    public function edit(ManageRegionPartners $partners, int $partner): Response
    {
        return Inertia::render('Panel/Content/RegionPartner', [
            'partner' => $partners->find($partner) ?? abort(404),
            'origin' => config('ovniporto.location'),
        ]);
    }

    public function store(Request $request, ManageRegionPartners $partners): RedirectResponse
    {
        $id = $partners->create($this->actorId($request), $this->fields($request));
        $this->files($request, $partners, $id);

        return redirect()->route('panel.region.edit', $id)->with('toast', 'Parceiro salvo como rascunho.');
    }

    public function update(Request $request, ManageRegionPartners $partners, int $partner): RedirectResponse
    {
        $partners->update($this->actorId($request), $partner, $this->fields($request));
        $this->files($request, $partners, $partner);

        return back()->with('toast', 'Parceiro salvo.');
    }

    public function publish(Request $request, ManageRegionPartners $partners, int $partner): RedirectResponse
    {
        try {
            $partners->publish($this->actorId($request), $partner);
        } catch (PartnerWithoutConsent) {
            throw ValidationException::withMessages(['consentGivenAt' => 'Sem a data do consentimento recebido, o parceiro não vai para o site.']);
        }

        return back()->with('toast', 'Parceiro publicado em /regiao.');
    }

    public function unpublish(Request $request, ManageRegionPartners $partners, int $partner): RedirectResponse
    {
        $partners->unpublish($this->actorId($request), $partner);

        return back()->with('toast', 'Parceiro saiu do site.');
    }

    public function destroy(Request $request, ManageRegionPartners $partners, int $partner): RedirectResponse
    {
        $partners->delete($this->actorId($request), $partner);

        return redirect()->route('panel.region')->with('toast', 'Parceiro removido.');
    }

    public function locate(Request $request, ManageRegionPartners $partners): JsonResponse
    {
        $data = $request->validate(['address' => ['required', 'string', 'max:255']]);

        return response()->json(['point' => $partners->locate($data['address'])]);
    }

    public function proof(ManageRegionPartners $partners, int $partner): HttpResponse
    {
        $proof = $partners->consentProof($partner) ?? abort(404);

        return response($proof['contents'], 200, [
            'Content-Type' => $proof['extension'] === 'pdf' ? 'application/pdf' : 'image/'.$proof['extension'],
            'Content-Disposition' => "attachment; filename=\"consentimento-{$partner}.{$proof['extension']}\"",
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function fields(Request $request): PartnerFields
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::enum(PartnerType::class)],
            'shortDescription' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:60'],
            'website' => ['nullable', 'url:https,http', 'max:255'],
            'isFeatured' => ['boolean'],
            'consentGivenAt' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'cover' => ['nullable', 'image', 'max:8192'],
            'consentProof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:8192'],
        ], ['name.required' => 'Dê o nome do parceiro.', 'city.required' => 'Diga a cidade.']);

        return new PartnerFields(
            name: $d['name'],
            type: PartnerType::from($d['type']),
            shortDescription: $d['shortDescription'] ?? null,
            city: $d['city'],
            address: $d['address'] ?? null,
            lat: isset($d['lat']) ? (float) $d['lat'] : null,
            lng: isset($d['lng']) ? (float) $d['lng'] : null,
            phone: $d['phone'] ?? null,
            whatsapp: $d['whatsapp'] ?? null,
            instagram: $d['instagram'] ?? null,
            website: $d['website'] ?? null,
            isFeatured: (bool) ($d['isFeatured'] ?? false),
            consentGivenAt: isset($d['consentGivenAt']) ? new DateTimeImmutable($d['consentGivenAt']) : null,
        );
    }

    private function files(Request $request, ManageRegionPartners $partners, int $id): void
    {
        $cover = $request->file('cover');
        if ($cover instanceof UploadedFile) {
            try {
                $partners->setCover($this->actorId($request), $id, (string) $cover->get());
            } catch (UnsupportedImage) {
                throw ValidationException::withMessages(['cover' => 'Não deu para ler essa imagem. Envie JPG, PNG ou WebP.']);
            }
        }
        $proof = $request->file('consentProof');
        if ($proof instanceof UploadedFile) {
            $partners->setConsentProof($this->actorId($request), $id, (string) $proof->get(), $proof->guessExtension() ?? 'pdf');
        }
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
