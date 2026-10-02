<?php

namespace App\Http\Controllers\Members;

use App\Application\Members\UseCases\CheckNickname;
use App\Application\Members\UseCases\CompleteProfile;
use App\Domain\Members\NicknameUnavailable;
use App\Domain\Members\TermsNotAccepted;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function show(Request $request, CheckNickname $checkNickname): Response|RedirectResponse
    {
        /** @var Member $member */
        $member = $request->user();
        if ($member->hasCompleteProfile()) {
            return redirect()->route('account');
        }

        return Inertia::render('Members/Welcome', [
            'firstName' => strtok($member->name, ' ') ?: $member->name,
            'suggestion' => $checkNickname->suggestFor($member->name),
        ]);
    }

    public function store(CompleteProfileRequest $request, CompleteProfile $completeProfile): RedirectResponse
    {
        try {
            $completeProfile->execute(
                (int) $request->user()?->getAuthIdentifier(),
                $request->string('nickname')->toString(),
                $request->input('city'),
                $request->boolean('terms'),
            );
        } catch (NicknameUnavailable $e) {
            throw ValidationException::withMessages(['nickname' => CompleteProfileRequest::nicknameMessage($e->reason)]);
        } catch (TermsNotAccepted) {
            throw ValidationException::withMessages(['terms' => CompleteProfileRequest::TERMS_MESSAGE]);
        }

        return redirect()->intended(route('account'))->with('toast', 'Bem-vindo à vigília.');
    }

    /** Live check while the nickname is typed. */
    public function nickname(Request $request, CheckNickname $checkNickname): JsonResponse
    {
        $nickname = (string) $request->query('apelido', '');
        $problem = $checkNickname->execute($nickname, (int) $request->user()?->getAuthIdentifier());

        return response()->json([
            'available' => $problem === null,
            'message' => $problem === null ? null : CompleteProfileRequest::nicknameMessage($problem),
        ]);
    }
}
