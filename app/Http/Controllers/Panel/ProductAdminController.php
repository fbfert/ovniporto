<?php

namespace App\Http\Controllers\Panel;

use App\Application\Catalog\UseCases\ManageProducts;
use App\Domain\Catalog\Data\ProductDraft;
use App\Domain\Sightings\UnsupportedImage;
use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/** /painel/produtos (store and admin). Prices arrive in reais and are kept in cents. */
class ProductAdminController extends Controller
{
    public function index(ManageProducts $products): Response
    {
        return Inertia::render('Panel/Products/Index', ['products' => $products->list()]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Products/Edit', ['product' => null]);
    }

    public function edit(ManageProducts $products, int $product): Response
    {
        return Inertia::render('Panel/Products/Edit', ['product' => $products->find($product) ?? abort(404)]);
    }

    public function store(Request $request, ManageProducts $products): RedirectResponse
    {
        $id = $products->create($this->actorId($request), $this->draft($request));
        // "Salvar e voltar" goes back to the list; plain "Salvar" stays to add photos and variants.
        $next = $request->boolean('returnToList') ? redirect()->route('panel.products') : redirect()->route('panel.products.edit', $id);

        return $next->with('toast', 'Produto criado (inativo até você ativar).');
    }

    public function update(Request $request, ManageProducts $products, int $product): RedirectResponse
    {
        $products->update($this->actorId($request), $product, $this->draft($request));

        return back()->with('toast', 'Produto salvo.');
    }

    public function addVariant(Request $request, ManageProducts $products, int $product): RedirectResponse
    {
        $d = $this->variant($request);

        return $this->run(fn () => $products->addVariant($this->actorId($request), $product, $d['name'], $d['sku'], self::cents($d['priceDelta'] ?? 0), (bool) ($d['active'] ?? true)), 'Variante criada.', 'sku');
    }

    public function updateVariant(Request $request, ManageProducts $products, int $product, int $variant): RedirectResponse
    {
        $d = $this->variant($request);

        return $this->run(fn () => $products->updateVariant($this->actorId($request), $product, $variant, $d['name'], $d['sku'], self::cents($d['priceDelta'] ?? 0), (bool) ($d['active'] ?? true)), 'Variante salva.', 'sku');
    }

    public function adjustStock(Request $request, ManageProducts $products, int $product, int $variant): RedirectResponse
    {
        $d = $request->validate(
            ['quantity' => ['required', 'integer', 'min:0'], 'reason' => ['required', 'string', 'max:160']],
            ['reason.required' => 'Diga o motivo do ajuste.'],
        );

        return $this->run(fn () => $products->adjustStock($this->actorId($request), $product, $variant, (int) $d['quantity'], $d['reason']), 'Estoque ajustado.', 'reason');
    }

    public function addImage(Request $request, ManageProducts $products, int $product): RedirectResponse
    {
        $request->validate(
            ['image' => ['required', 'image', 'max:8192'], 'alt' => ['required', 'string', 'max:255']],
            ['alt.required' => 'Descreva a foto (texto alternativo).', 'image.required' => 'Escolha a foto.'],
        );
        $file = $request->file('image');
        abort_unless($file instanceof UploadedFile, 422);

        return $this->run(fn () => $products->addImage($this->actorId($request), $product, (string) $file->get(), $request->string('alt')->toString()), 'Foto adicionada.', 'alt');
    }

    public function updateImage(Request $request, ManageProducts $products, int $product, int $image): RedirectResponse
    {
        $request->validate(['alt' => ['required', 'string', 'max:255']], ['alt.required' => 'Descreva a foto (texto alternativo).']);

        return $this->run(fn () => $products->updateImage($this->actorId($request), $product, $image, $request->string('alt')->toString()), 'Foto salva.', 'alt');
    }

    public function deleteImage(Request $request, ManageProducts $products, int $product, int $image): RedirectResponse
    {
        $products->deleteImage($this->actorId($request), $product, $image);

        return back()->with('toast', 'Foto removida.');
    }

    public function moveImage(Request $request, ManageProducts $products, int $product, int $image): RedirectResponse
    {
        $d = $request->validate(['direction' => ['required', Rule::in([-1, 1])]]);
        $products->moveImage($this->actorId($request), $product, $image, (int) $d['direction']);

        return back();
    }

    private function draft(Request $request): ProductDraft
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'shortDescription' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'comparePrice' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'madeToOrder' => ['boolean'],
            'productionDays' => ['nullable', 'integer', 'min:0', 'max:120'],
            'weightGrams' => ['required', 'integer', 'min:1', 'max:30000'],
            'length' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100'],
            'width' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100'],
            'height' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100'],
            'label' => ['nullable', 'string', 'max:30'],
            'active' => ['boolean'],
            'featured' => ['boolean'],
        ], [
            'name.required' => 'Dê um nome ao produto.',
            'weightGrams.required' => 'O peso é usado no frete.',
            '*.decimal' => 'Use no máximo duas casas: 0,01 cm é um décimo de milímetro.',
            'length.min' => 'A medida mínima é 0,01 cm.',
            'width.min' => 'A medida mínima é 0,01 cm.',
            'height.min' => 'A medida mínima é 0,01 cm.',
        ]);

        $hasDimensions = isset($d['length'], $d['width'], $d['height']);

        return new ProductDraft(
            name: $d['name'],
            shortDescription: $d['shortDescription'] ?? null,
            description: $d['description'] ?? null,
            priceCents: self::cents($d['price']),
            comparePriceCents: isset($d['comparePrice']) ? self::cents($d['comparePrice']) : null,
            madeToOrder: (bool) ($d['madeToOrder'] ?? false),
            productionDays: (int) ($d['productionDays'] ?? 0),
            weightGrams: (int) $d['weightGrams'],
            dimensions: $hasDimensions ? ['length' => self::centimetres($d['length']), 'width' => self::centimetres($d['width']), 'height' => self::centimetres($d['height'])] : null,
            label: isset($d['label']) ? mb_strtoupper($d['label']) : null,
            active: (bool) ($d['active'] ?? false),
            featured: (bool) ($d['featured'] ?? false),
        );
    }

    /** @return array{name: string, sku: string, priceDelta?: numeric-string|int|float|null, active?: bool} */
    private function variant(Request $request): array
    {
        /** @var array{name: string, sku: string, priceDelta?: numeric-string|int|float|null, active?: bool} $data */
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'sku' => ['required', 'string', 'max:40'],
            'priceDelta' => ['nullable', 'numeric', 'min:-100000', 'max:100000'],
            'active' => ['boolean'],
        ], ['name.required' => 'Dê um nome à variante (ex.: M, Azul).', 'sku.required' => 'Informe o SKU.']);

        return $data;
    }

    private function run(Closure $action, string $done, string $field): RedirectResponse
    {
        try {
            $action();
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([$field => $e->getMessage()]);
        } catch (UnsupportedImage) {
            throw ValidationException::withMessages(['image' => 'Não deu para ler essa imagem. Envie JPG, PNG ou WebP.']);
        }

        return back()->with('toast', $done);
    }

    /** "12.5" reais → 1250 cents, through a string so no float rounding sneaks in. */
    /** Package side in cm, kept to 0.01 cm (a tenth of a millimetre). */
    private static function centimetres(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private static function cents(mixed $reais): int
    {
        $value = number_format((float) $reais, 2, '.', '');

        return (int) str_replace('.', '', $value);
    }

    private function actorId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
