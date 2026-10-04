<?php

namespace App\Http\Controllers\Sightings;

use App\Application\Sightings\UseCases\GetSightingDraft;
use App\Application\Sightings\UseCases\ResubmitSighting;
use App\Application\Sightings\UseCases\SubmitSighting;
use App\Application\Sightings\UseCases\UploadSightingPhoto;
use App\Domain\Members\MemberBlocked;
use App\Domain\Sightings\InvalidSubmission;
use App\Domain\Sightings\SubmissionRules;
use App\Domain\Sightings\UnsupportedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSightingRequest;
use App\Http\Requests\UploadSightingPhotoRequest;
use App\Models\Member;
use App\Models\Sighting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReportController extends Controller
{
    public function create(Request $request): InertiaResponse
    {
        return Inertia::render('Sightings/Report', $this->wizardProps($request));
    }

    /** A report the tower sent back: same wizard, data loaded, consent asked again. */
    public function edit(Request $request, GetSightingDraft $drafts, Sighting $sighting): InertiaResponse|RedirectResponse
    {
        Gate::authorize('update', $sighting);
        $draft = $drafts->execute((int) $request->user()?->getAuthIdentifier(), $sighting->id);
        if ($draft === null) {
            return redirect()->route('account')->with('toast', 'Esse relato não está esperando ajuste.');
        }

        return Inertia::render('Sightings/Report', [...$this->wizardProps($request), 'editing' => $draft]);
    }

    public function update(SubmitSightingRequest $request, ResubmitSighting $resubmit, Sighting $sighting): RedirectResponse
    {
        Gate::authorize('update', $sighting);
        try {
            $resubmit->execute((int) $request->user()?->getAuthIdentifier(), $sighting->id, $request->submission());
        } catch (InvalidSubmission $e) {
            throw ValidationException::withMessages([$e->field => $e->getMessage()]);
        } catch (MemberBlocked $e) {
            throw ValidationException::withMessages(['status' => $e->getMessage()]);
        }

        return redirect()->route('report.sent');
    }

    /** @return array<string, mixed> */
    private function wizardProps(Request $request): array
    {
        /** @var Member $member */
        $member = $request->user();

        return [
            'nickname' => $member->nickname,
            'today' => now()->toDateString(),
            'lages' => SubmissionRules::LAGES,
            'limits' => [
                'descriptionMin' => SubmissionRules::DESCRIPTION_MIN,
                'descriptionMax' => SubmissionRules::DESCRIPTION_MAX,
                'maxPhotos' => SubmissionRules::MAX_PHOTOS,
                'maxPhotoMb' => UploadSightingPhotoRequest::MAX_KB / 1024,
            ],
        ];
    }

    public function upload(UploadSightingPhotoRequest $request, UploadSightingPhoto $upload): JsonResponse
    {
        $file = $request->file('photo');
        abort_unless($file !== null && ! is_array($file), 422);

        try {
            $id = $upload->execute((int) $request->user()?->getAuthIdentifier(), (string) file_get_contents($file->getRealPath()));
        } catch (UnsupportedImage) {
            throw ValidationException::withMessages(['photo' => UploadSightingPhotoRequest::UNREADABLE]);
        }

        return response()->json(['id' => $id], 201);
    }

    public function discard(Request $request, string $upload, UploadSightingPhoto $uploads): Response
    {
        $uploads->discard((int) $request->user()?->getAuthIdentifier(), $upload);

        return response()->noContent();
    }

    public function store(SubmitSightingRequest $request, SubmitSighting $submit): RedirectResponse
    {
        try {
            $submit->execute((int) $request->user()?->getAuthIdentifier(), $request->submission());
        } catch (InvalidSubmission $e) {
            throw ValidationException::withMessages([$e->field => $e->getMessage()]);
        } catch (MemberBlocked $e) {
            throw ValidationException::withMessages(['status' => $e->getMessage()]);
        }

        return redirect()->route('report.sent');
    }

    public function sent(): InertiaResponse
    {
        return Inertia::render('Sightings/Submitted');
    }
}
