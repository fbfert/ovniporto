<?php

namespace App\Http\Controllers\Panel;

use App\Application\Content\UseCases\ManageSiteContent;
use App\Domain\Content\SiteSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** /painel/configuracoes (admin only). */
class SettingsController extends Controller
{
    public function show(ManageSiteContent $content): Response
    {
        return Inertia::render('Panel/Content/Settings', $content->page());
    }

    public function links(Request $request, ManageSiteContent $content): RedirectResponse
    {
        $data = $request->validate([
            'link_whatsapp' => ['nullable', 'url:https', 'max:255'],
            'link_instagram' => ['nullable', 'url:https', 'max:255'],
            'contact_email' => ['required', 'email', 'max:120'],
        ]);
        $content->saveSettings($this->actorId($request), $this->strings($data, SiteSettings::LINKS));

        return back()->with('toast', 'Links salvos.');
    }

    public function goals(Request $request, ManageSiteContent $content): RedirectResponse
    {
        $data = $request->validate([
            'launch_date' => ['nullable', 'date_format:Y-m-d'],
            'goal_members' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'goal_sightings' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'goal_orders' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        $content->saveSettings($this->actorId($request), $this->strings($data, SiteSettings::GOALS));

        return back()->with('toast', 'Metas salvas.');
    }

    public function block(Request $request, ManageSiteContent $content, string $key): RedirectResponse
    {
        abort_unless(SiteSettings::isBlock($key), 404);
        $data = $request->validate(['value' => ['nullable', 'string', 'max:60000'], 'final' => ['boolean']]);
        $content->saveBlock($this->actorId($request), $key, (string) ($data['value'] ?? ''), $request->has('final') ? $request->boolean('final') : null);

        return back()->with('toast', 'Texto salvo.');
    }

    public function preview(Request $request, ManageSiteContent $content): JsonResponse
    {
        $data = $request->validate(['markdown' => ['nullable', 'string', 'max:60000']]);

        return response()->json(['html' => $content->preview((string) ($data['markdown'] ?? ''))]);
    }

    public function saveItem(Request $request, ManageSiteContent $content, string $list, ?int $item = null): RedirectResponse
    {
        abort_unless(in_array($list, SiteSettings::LISTS, true), 404);
        $data = $request->validate(
            ['title' => ['required', 'string', 'max:255'], 'body' => ['required', 'string', 'max:5000']],
            ['title.required' => 'Escreva o título.', 'body.required' => 'Escreva o texto.'],
        );
        /** @var 'faq'|'regras' $list */
        $content->saveItem($this->actorId($request), $list, $item, $data['title'], $data['body']);

        return back()->with('toast', 'Salvo.');
    }

    public function deleteItem(Request $request, ManageSiteContent $content, string $list, int $item): RedirectResponse
    {
        abort_unless(in_array($list, SiteSettings::LISTS, true), 404);
        /** @var 'faq'|'regras' $list */
        $content->deleteItem($this->actorId($request), $list, $item);

        return back()->with('toast', 'Removido.');
    }

    public function moveItem(Request $request, ManageSiteContent $content, string $list, int $item): RedirectResponse
    {
        abort_unless(in_array($list, SiteSettings::LISTS, true), 404);
        $data = $request->validate(['direction' => ['required', Rule::in([-1, 1])]]);
        /** @var 'faq'|'regras' $list */
        $content->moveItem($this->actorId($request), $list, $item, (int) $data['direction']);

        return back();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     * @return array<string, string|null>
     */
    private function strings(array $data, array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = isset($data[$key]) ? (string) $data[$key] : null;
        }

        return $values;
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
