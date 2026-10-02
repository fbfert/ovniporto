<?php

namespace App\Http\Controllers\Sightings;

use App\Application\Sightings\UseCases\SubmitSighting;
use App\Application\Sightings\UseCases\UploadSightingPhoto;
use App\Domain\Sightings\InvalidSubmission;
use App\Domain\Sightings\SubmissionRules;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitSightingRequest;
use App\Http\Requests\UploadSightingPhotoRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ReportController extends Controller
{
    public function create(Request $request): InertiaResponse
    {
        /** @var Member $member */
        $member = $request->user();

        return Inertia::render('Sightings/Report', [
            'nickname' => $member->nickname,
            'today' => now()->toDateString(),
            'lages' => SubmissionRules::LAGES,
            'limits' => [
                'descriptionMin' => SubmissionRules::DESCRIPTION_MIN,
                'descriptionMax' => SubmissionRules::DESCRIPTION_MAX,
                'maxPhotos' => SubmissionRules::MAX_PHOTOS,
                'maxPhotoMb' => UploadSightingPhotoRequest::MAX_KB / 1024,
            ],
        ]);
    }

    public function upload(UploadSightingPhotoRequest $request, UploadSightingPhoto $upload): JsonResponse
    {
        $file = $request->file('photo');
        abort_unless($file !== null && ! is_array($file), 422);

        $id = $upload->execute(
            (int) $request->user()?->getAuthIdentifier(),
            (string) file_get_contents($file->getRealPath()),
            (string) $file->getMimeType(),
        );

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
        }

        return redirect()->route('report.sent');
    }

    public function sent(): InertiaResponse
    {
        return Inertia::render('Sightings/Submitted');
    }
}
