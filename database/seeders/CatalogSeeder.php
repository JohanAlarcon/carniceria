<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /** slug de categoría => clave de icono para la imagen de la categoría. */
    private array $categoryIcon = [
        'res' => 'beef',
        'cerdo' => 'pork',
        'pollo' => 'chicken',
        'cabra' => 'goat',
        'cordero' => 'lamb',
        'pescado-mariscos' => 'fish',
        'otros' => 'meat',
        'preparados' => 'grill',
    ];

    /** unidad => etiquetas por defecto ES/EN. */
    private array $unitLabels = [
        'lb' => ['es' => 'lb', 'en' => 'lb'],
        'caja' => ['es' => 'caja', 'en' => 'box'],
        'pieza' => ['es' => 'pza', 'en' => 'pc'],
        'hank' => ['es' => 'hank', 'en' => 'hank'],
    ];

    public function run(): void
    {
        $dataPath = database_path('data');
        $categories = json_decode(file_get_contents($dataPath.'/categories.json'), true);

        foreach ($categories as $catData) {
            $slug = $catData['slug'];

            $category = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name_es' => $catData['name_es'],
                    'name_en' => $catData['name_en'],
                    'color' => $catData['color'] ?? null,
                    'sort_order' => $catData['sort_order'] ?? 0,
                    'icon' => 'images/icons/'.($this->categoryIcon[$slug] ?? 'meat').'.webp',
                    'is_active' => true,
                ]
            );

            $file = $dataPath.'/catalog/'.$slug.'.json';
            if (! is_file($file)) {
                continue;
            }

            $products = json_decode(file_get_contents($file), true);
            $sort = 0;

            foreach ($products as $prodData) {
                $unit = $prodData['unit'] ?? 'lb';
                $labelEs = $prodData['unit_label_es'] ?? ($this->unitLabels[$unit]['es'] ?? 'lb');
                $labelEn = $prodData['unit_label_en'] ?? ($this->unitLabels[$unit]['en'] ?? 'lb');
                $iconKey = $prodData['icon'] ?? ($this->categoryIcon[$slug] ?? 'meat');

                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($prodData['name_es']).'-'.$slug],
                    [
                        'category_id' => $category->id,
                        'name_es' => $prodData['name_es'],
                        'name_en' => $prodData['name_en'],
                        'english_cut' => $prodData['english_cut'] ?? null,
                        'icon' => 'images/icons/'.$iconKey.'.webp',
                        'is_published' => true,
                        'sort_order' => $sort++,
                    ]
                );

                // Regenera variantes de forma idempotente.
                $product->variants()->delete();

                $vsort = 0;
                foreach ($prodData['variants'] as $v) {
                    $price = $v['price'] ?? null;

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'label_es' => $v['label'],
                        'label_en' => $v['label_en'] ?? null,
                        'brand' => $v['brand'] ?? null,
                        'grade' => $v['grade'] ?? null,
                        'is_frozen' => $v['frozen'] ?? false,
                        'unit' => $v['unit'] ?? $unit,
                        'unit_label_es' => $labelEs,
                        'unit_label_en' => $labelEn,
                        'package_weight_lb' => $v['package_weight_lb'] ?? null,
                        'base_price' => $price ?? 0,
                        'is_available' => $price !== null,
                        'sort_order' => $vsort++,
                    ]);
                }
            }
        }
    }
}
