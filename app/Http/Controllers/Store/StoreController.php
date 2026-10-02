<?php

namespace App\Http\Controllers\Store;

use App\Application\Catalog\UseCases\GetProductPage;
use App\Application\Catalog\UseCases\GetStorePage;
use App\Application\Shipping\UseCases\QuoteShipping;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class StoreController extends Controller
{
    public function index(GetStorePage $store): Response
    {
        return Inertia::render('Store/Index', $store->execute());
    }

    public function show(Request $request, GetProductPage $page, string $slug): Response
    {
        $data = $page->execute($slug, $request->url()) ?? abort(404);

        return Inertia::render('Store/Product', $data);
    }

    /** The CEP is validated before the provider is ever asked. */
    public function shipping(Request $request, QuoteShipping $quote): JsonResponse
    {
        $data = $request->validate([
            'cep' => ['required', 'string', 'max:12'],
            'variantId' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        try {
            return response()->json($quote->forVariant($data['cep'], (int) $data['variantId'], (int) ($data['quantity'] ?? 1)));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['cep' => [$e->getMessage()]]], 422);
        }
    }
}
