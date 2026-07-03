<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        $customer = $request->user()->customer;
        $approved = (bool) ($customer?->is_approved);
        $pct = (float) ($customer?->price_adjustment_pct ?? 0);

        $categories = Category::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name_es' => $c->name_es,
                'name_en' => $c->name_en,
                'color' => $c->color,
                'icon' => $c->icon ? asset($c->icon) : null,
            ])
            ->values();

        $products = Product::published()
            ->whereHas('availableVariants')
            ->with(['availableVariants' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'category_id' => $p->category_id,
                'name_es' => $p->name_es,
                'name_en' => $p->name_en,
                'english_cut' => $p->english_cut,
                'icon' => $p->icon ? asset($p->icon) : null,
                'image' => $p->image ? asset('storage/'.$p->image) : null,
                'variants' => $p->availableVariants->map(fn ($v) => [
                    'id' => $v->id,
                    'label_es' => $v->label_es,
                    'label_en' => $v->label_en ?: $v->label_es,
                    'unit_label_es' => $v->unit_label_es,
                    'unit_label_en' => $v->unit_label_en,
                    'is_frozen' => (bool) $v->is_frozen,
                    'price' => $approved ? round((float) $v->base_price * (1 + $pct / 100), 2) : null,
                ])->values(),
            ])
            ->values();

        return Inertia::render('Shop/Index', [
            'categories' => $categories,
            'products' => $products,
            'approved' => $approved,
            'hasProfile' => (bool) $customer,
        ]);
    }
}
