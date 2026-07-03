import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

const CartContext = createContext(null);
const KEY = 'carniceria_cart_v1';

export function CartProvider({ children }) {
    const [items, setItems] = useState(() => {
        try {
            return JSON.parse(localStorage.getItem(KEY)) || [];
        } catch {
            return [];
        }
    });

    useEffect(() => {
        localStorage.setItem(KEY, JSON.stringify(items));
    }, [items]);

    const add = useCallback((entry) => {
        setItems((prev) => {
            const i = prev.findIndex((x) => x.variant_id === entry.variant_id);
            if (i >= 0) {
                const copy = [...prev];
                copy[i] = { ...copy[i], quantity: +(copy[i].quantity + entry.quantity).toFixed(2) };
                return copy;
            }
            return [...prev, entry];
        });
    }, []);

    const setQty = useCallback((variantId, quantity) => {
        setItems((prev) =>
            prev
                .map((x) => (x.variant_id === variantId ? { ...x, quantity: +Number(quantity).toFixed(2) } : x))
                .filter((x) => x.quantity > 0),
        );
    }, []);

    const remove = useCallback((variantId) => {
        setItems((prev) => prev.filter((x) => x.variant_id !== variantId));
    }, []);

    const clear = useCallback(() => setItems([]), []);

    const subtotal = useMemo(
        () => items.reduce((s, x) => s + x.quantity * (x.unit_price || 0), 0),
        [items],
    );

    const count = items.length;

    return (
        <CartContext.Provider value={{ items, add, setQty, remove, clear, subtotal, count }}>
            {children}
        </CartContext.Provider>
    );
}

export const useCart = () => useContext(CartContext);
