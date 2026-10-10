document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

const productSelect = document.querySelector('#product_id');
const quantityInput = document.querySelector('#quantity');

function updateSaleSummary() {
    if (!productSelect || !quantityInput) return;
    const option = productSelect.options[productSelect.selectedIndex];
    const price = Number(option?.dataset.price || 0);
    const stock = option?.dataset.stock;
    const quantity = Number(quantityInput.value || 0);
    document.querySelector('#summary-product').textContent = option?.value ? option.text.split(' — ')[0] : 'Choose a product';
    document.querySelector('#summary-stock').textContent = stock === undefined ? '—' : `${stock} units`;
    document.querySelector('#summary-price').textContent = option?.value ? `₱${price.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : '—';
    document.querySelector('#summary-total').textContent = option?.value && Number.isInteger(quantity) && quantity > 0
        ? `₱${(price * quantity).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
        : '—';
}

productSelect?.addEventListener('change', updateSaleSummary);
quantityInput?.addEventListener('input', updateSaleSummary);
updateSaleSummary();
