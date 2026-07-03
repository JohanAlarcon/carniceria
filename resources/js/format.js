export const money = (n) =>
    '$' +
    Number(n || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

export const qty = (n) => {
    const s = Number(n || 0).toFixed(2);
    return s.replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');
};
