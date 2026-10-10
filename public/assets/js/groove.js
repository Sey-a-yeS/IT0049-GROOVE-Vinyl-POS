document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-image-input]').forEach((input) => {
    const preview = document.getElementById(input.dataset.previewTarget);
    if (!preview) return;

    let previewUrl = null;
    input.addEventListener('change', () => {
        const [file] = input.files;
        if (!file || !file.type.startsWith('image/')) return;
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.alt = `Preview of ${file.name}`;
    });
});

const productSelect = document.querySelector('#product_id');
const quantityInput = document.querySelector('#quantity');

function formatPeso(value) {
    return `₱${value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function updateSaleSummary() {
    if (!productSelect || !quantityInput) return;
    const option = productSelect.options[productSelect.selectedIndex];
    const hasProduct = Boolean(option?.value);
    const priceInCents = Math.round(Number(option?.dataset.price || 0) * 100);
    const stock = Number(option?.dataset.stock || 0);
    const quantity = Number(quantityInput.value || 0);
    const productName = hasProduct ? option.text.split(' — ')[0] : 'Choose a product';

    document.querySelector('#summary-product').textContent = productName;
    document.querySelector('#summary-stock').textContent = hasProduct ? `${stock} ${stock === 1 ? 'unit' : 'units'}` : '—';
    document.querySelector('#summary-price').textContent = hasProduct ? formatPeso(priceInCents / 100) : '—';
    document.querySelector('#summary-total').textContent = hasProduct && Number.isInteger(quantity) && quantity > 0
        ? formatPeso((priceInCents * quantity) / 100)
        : '—';

    const summaryImage = document.querySelector('#summary-image');
    if (summaryImage && option?.dataset.image) summaryImage.src = option.dataset.image;

    const note = document.querySelector('#summary-note');
    if (!note) return;
    if (!hasProduct) {
        note.textContent = 'Select a product to review its price and current inventory.';
    } else if (!Number.isInteger(quantity) || quantity < 1) {
        note.textContent = 'Enter a positive whole-number quantity.';
    } else if (quantity > stock) {
        note.textContent = `Only ${stock} ${stock === 1 ? 'unit is' : 'units are'} currently available. The server will reject an oversale.`;
    } else {
        note.textContent = 'Ready for server verification. The database price and locked inventory remain authoritative.';
    }
}

productSelect?.addEventListener('change', updateSaleSummary);
quantityInput?.addEventListener('input', updateSaleSummary);
updateSaleSummary();

const saleForm = document.querySelector('#sale-form');
saleForm?.addEventListener('submit', () => {
    const button = saleForm.querySelector('button[type="submit"]');
    if (!button) return;
    button.disabled = true;
    button.textContent = 'Processing sale…';
    saleForm.setAttribute('aria-busy', 'true');
});
