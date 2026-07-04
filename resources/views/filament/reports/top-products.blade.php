<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-trophy" heading="Productos más vendidos">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-3 text-left font-semibold">Producto</th>
                        <th class="py-2 px-3 text-right font-semibold">Ingresos</th>
                        <th class="py-2 pl-3 text-right font-semibold">Cantidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse ($this->getRows() as $i => $row)
                        <tr>
                            <td class="py-2 pr-3">
                                <span class="mr-2 text-gray-400">{{ $i + 1 }}.</span>
                                {{ $row['product_name'] }}
                            </td>
                            <td class="py-2 px-3 text-right font-semibold text-gray-950 dark:text-white">
                                ${{ number_format($row['revenue'], 2) }}
                            </td>
                            <td class="py-2 pl-3 text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($row['quantity'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                Sin ventas en el período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
