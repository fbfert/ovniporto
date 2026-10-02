<?php

namespace App\Http\Controllers\Panel;

use App\Application\Place\UseCases\ManageConstructionDiary;
use App\Application\Place\UseCases\ManagePlace;
use App\Domain\Place\Data\DiaryPostDraft;
use App\Domain\Sightings\UnsupportedImage;
use App\Http\Controllers\Controller;
use Closure;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/** /painel/lugar and /painel/obra (admin only). */
class PlaceAdminController extends Controller
{
    /** "open" is not offered: the place does not exist yet and the site never says otherwise. */
    private const SPACE_STATUSES = ['planning', 'building'];

    public function show(ManagePlace $place): Response
    {
        return Inertia::render('Panel/Content/Place', $place->page());
    }

    public function updateSpace(Request $request, ManagePlace $place, int $space): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'role' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phase' => ['required', 'integer', 'min:1', 'max:9'],
            'status' => ['required', Rule::in(self::SPACE_STATUSES)],
        ]);

        return $this->run(fn () => $place->updateSpace($this->actorId($request), $space, [
            'name' => $data['name'],
            'role' => $data['role'],
            'description' => $data['description'] ?? null,
            'phase' => (int) $data['phase'],
            'status' => $data['status'],
        ]), 'Espaço salvo.');
    }

    public function moveSpace(Request $request, ManagePlace $place, int $space): RedirectResponse
    {
        $place->moveSpace($this->actorId($request), $space, $this->direction($request));

        return back();
    }

    public function concept(Request $request, ManagePlace $place, int $space): RedirectResponse
    {
        $request->validate(['image' => ['required', 'image', 'max:8192']], ['image.required' => 'Escolha a ilustração.']);

        return $this->run(fn () => $place->setConcept($this->actorId($request), $space, $this->bytes($request, 'image')), 'Ilustração salva. Ela aparece com o selo "conceito".', 'image');
    }

    public function addPhoto(Request $request, ManagePlace $place): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'takenAt' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], ['alt.required' => 'Descreva a foto (texto alternativo).']);

        return $this->run(fn () => $place->addPhoto(
            $this->actorId($request),
            $this->bytes($request, 'image'),
            $data['alt'],
            $data['caption'] ?? null,
            isset($data['takenAt']) ? new DateTimeImmutable($data['takenAt']) : null,
        ), 'Foto do terreno publicada.', 'image');
    }

    public function updatePhoto(Request $request, ManagePlace $place, int $photo): RedirectResponse
    {
        $data = $request->validate([
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'takenAt' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ], ['alt.required' => 'Descreva a foto (texto alternativo).']);
        $place->updatePhoto(
            $this->actorId($request),
            $photo,
            $data['alt'],
            $data['caption'] ?? null,
            isset($data['takenAt']) ? new DateTimeImmutable($data['takenAt']) : null,
        );

        return back()->with('toast', 'Foto salva.');
    }

    public function deletePhoto(Request $request, ManagePlace $place, int $photo): RedirectResponse
    {
        $place->deletePhoto($this->actorId($request), $photo);

        return back()->with('toast', 'Foto removida.');
    }

    public function movePhoto(Request $request, ManagePlace $place, int $photo): RedirectResponse
    {
        $place->movePhoto($this->actorId($request), $photo, $this->direction($request));

        return back();
    }

    public function map3d(Request $request, ManagePlace $place): RedirectResponse
    {
        $data = $request->validate(['embed' => ['nullable', 'string', 'max:2000']]);

        return $this->run(fn () => $place->setMap3d($this->actorId($request), $data['embed'] ?? null), 'Mapa 3D salvo.', 'embed');
    }

    public function diary(ManageConstructionDiary $diary): Response
    {
        return Inertia::render('Panel/Content/Diary', ['posts' => $diary->list(), 'now' => now()->toIso8601String()]);
    }

    public function createPost(): Response
    {
        return Inertia::render('Panel/Content/DiaryPost', ['post' => null]);
    }

    public function editPost(ManageConstructionDiary $diary, int $post): Response
    {
        return Inertia::render('Panel/Content/DiaryPost', ['post' => $diary->find($post) ?? abort(404)]);
    }

    public function storePost(Request $request, ManageConstructionDiary $diary): RedirectResponse
    {
        $draft = $this->draft($request);
        $id = 0;
        $this->run(function () use ($request, $diary, $draft, &$id) {
            $id = $diary->create($this->actorId($request), $draft, $this->optionalBytes($request, 'cover'));
        }, '', 'coverAlt', 'cover');

        return redirect()->route('panel.diary.edit', $id)->with('toast', 'Post salvo.');
    }

    public function updatePost(Request $request, ManageConstructionDiary $diary, int $post): RedirectResponse
    {
        $draft = $this->draft($request);

        return $this->run(fn () => $diary->update($this->actorId($request), $post, $draft, $this->optionalBytes($request, 'cover')), 'Post salvo.', 'coverAlt', 'cover');
    }

    public function deletePost(Request $request, ManageConstructionDiary $diary, int $post): RedirectResponse
    {
        $diary->delete($this->actorId($request), $post);

        return redirect()->route('panel.diary')->with('toast', 'Post removido.');
    }

    private function draft(Request $request): DiaryPostDraft
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:60000'],
            'phase' => ['required', 'integer', 'min:1', 'max:9'],
            'publishedAt' => ['nullable', 'date'],
            'cover' => ['nullable', 'image', 'max:8192'],
            'coverAlt' => ['nullable', 'string', 'max:255'],
        ], ['title.required' => 'Dê um título ao post.', 'body.required' => 'Escreva o post.']);

        return new DiaryPostDraft(
            title: $data['title'],
            excerpt: $data['excerpt'] ?? null,
            body: $data['body'],
            phase: (int) $data['phase'],
            publishedAt: isset($data['publishedAt']) ? new DateTimeImmutable($data['publishedAt']) : null,
            coverAlt: $data['coverAlt'] ?? null,
        );
    }

    /** Runs the action, turning domain refusals and unreadable images into form errors. */
    private function run(Closure $action, string $done, string $refusedField = 'form', string $imageField = 'image'): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([$refusedField => $e->getMessage()]);
        } catch (UnsupportedImage) {
            throw ValidationException::withMessages([$imageField => 'Não deu para ler essa imagem. Envie JPG, PNG ou WebP.']);
        }

        return back()->with('toast', $done);
    }

    private function bytes(Request $request, string $field): string
    {
        return $this->optionalBytes($request, $field) ?? throw ValidationException::withMessages([$field => 'Escolha a imagem.']);
    }

    private function optionalBytes(Request $request, string $field): ?string
    {
        $file = $request->file($field);

        return $file instanceof UploadedFile ? (string) $file->get() : null;
    }

    private function direction(Request $request): int
    {
        return (int) $request->validate(['direction' => ['required', Rule::in([-1, 1])]])['direction'];
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
