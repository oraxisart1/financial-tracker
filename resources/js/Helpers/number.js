export function formatCurrency(value, currency = "") {
    value = Number(value || 0);

    let options = {
        maximumFractionDigits: 2,
        minimumFractionDigits: 0,
    };

    if (currency) {
        options = {
            ...options,
            style: "currency",
            currency,
        };
    }

    return value.toLocaleString(undefined, options);
}
