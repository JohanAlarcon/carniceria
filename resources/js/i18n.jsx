import { createContext, useCallback, useContext, useEffect, useState } from 'react';

const translations = {
    es: {
        shop: 'Tienda', my_orders: 'Mis pedidos', account: 'Cuenta', cart: 'Carrito',
        search: 'Buscar producto…', all: 'Todos', from: 'Desde', add: 'Agregar',
        add_to_cart: 'Agregar al carrito', frozen: 'Congelado', options: 'presentaciones',
        empty_cart: 'Tu carrito está vacío', subtotal: 'Subtotal', total: 'Total',
        checkout: 'Ir a pagar', place_order: 'Confirmar pedido', quantity: 'Cantidad',
        remove: 'Quitar', continue_shopping: 'Seguir comprando', delivery: 'Entrega',
        delivery_address: 'Dirección de entrega', address: 'Dirección', address2: 'Dirección 2',
        city: 'Ciudad', state: 'Estado', zip: 'ZIP', phone: 'Teléfono', notes: 'Notas',
        requested_date: 'Fecha deseada de entrega', order_placed: '¡Pedido enviado!',
        pending_title: 'Cuenta en revisión',
        pending_text: 'El carnicero revisará y aprobará tu cuenta pronto. Podrás ver precios y ordenar en cuanto te aprueben.',
        status: 'Estado', order: 'Pedido', date: 'Fecha', view: 'Ver detalle', logout: 'Cerrar sesión',
        no_orders: 'Aún no tienes pedidos', select_option: 'Elige una presentación',
        business_name: 'Nombre del negocio', contact_name: 'Nombre de contacto',
        register: 'Crear cuenta', login: 'Iniciar sesión', email: 'Correo', password: 'Contraseña',
        confirm_password: 'Confirmar contraseña', already_registered: '¿Ya tienes cuenta?',
        clear_cart: 'Vaciar carrito', items: 'artículos', your_adjustment: 'Tu ajuste',
        back: 'Volver', order_summary: 'Resumen del pedido', go_shop: 'Ir a la tienda',
        received: 'Recibido', delivered_to: 'Entregar en', no_products: 'No hay productos en esta categoría',
        registering: 'Registrando negocio', order_again: 'Pedir de nuevo',
    },
    en: {
        shop: 'Shop', my_orders: 'My orders', account: 'Account', cart: 'Cart',
        search: 'Search product…', all: 'All', from: 'From', add: 'Add',
        add_to_cart: 'Add to cart', frozen: 'Frozen', options: 'options',
        empty_cart: 'Your cart is empty', subtotal: 'Subtotal', total: 'Total',
        checkout: 'Checkout', place_order: 'Place order', quantity: 'Quantity',
        remove: 'Remove', continue_shopping: 'Continue shopping', delivery: 'Delivery',
        delivery_address: 'Delivery address', address: 'Address', address2: 'Address 2',
        city: 'City', state: 'State', zip: 'ZIP', phone: 'Phone', notes: 'Notes',
        requested_date: 'Requested delivery date', order_placed: 'Order placed!',
        pending_title: 'Account under review',
        pending_text: 'The butcher will review and approve your account soon. You will see prices and be able to order once approved.',
        status: 'Status', order: 'Order', date: 'Date', view: 'View details', logout: 'Log out',
        no_orders: "You don't have orders yet", select_option: 'Choose an option',
        business_name: 'Business name', contact_name: 'Contact name',
        register: 'Create account', login: 'Log in', email: 'Email', password: 'Password',
        confirm_password: 'Confirm password', already_registered: 'Already have an account?',
        clear_cart: 'Clear cart', items: 'items', your_adjustment: 'Your adjustment',
        back: 'Back', order_summary: 'Order summary', go_shop: 'Go to shop',
        received: 'Received', delivered_to: 'Deliver to', no_products: 'No products in this category',
        registering: 'Registering business', order_again: 'Order again',
    },
};

const LangContext = createContext(null);

export function LangProvider({ children }) {
    const [lang, setLang] = useState(() => localStorage.getItem('lang') || 'es');

    useEffect(() => {
        localStorage.setItem('lang', lang);
        document.documentElement.lang = lang;
    }, [lang]);

    const t = useCallback((key) => translations[lang][key] ?? key, [lang]);
    const toggle = useCallback(() => setLang((l) => (l === 'es' ? 'en' : 'es')), []);
    const name = useCallback(
        (obj, base = 'name') => (lang === 'en' ? obj[`${base}_en`] || obj[`${base}_es`] : obj[`${base}_es`]),
        [lang],
    );

    return <LangContext.Provider value={{ lang, setLang, toggle, t, name }}>{children}</LangContext.Provider>;
}

export const useLang = () => useContext(LangContext);
