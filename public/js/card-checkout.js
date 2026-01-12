// Card Checkout JavaScript
(function() {
    'use strict';

    let printingFee = 0;
    let subscriptionOptions = [];
    let profiles = [];
    let useBulkConfig = false;

    // Initialize the checkout functionality with configuration data
    window.initCardCheckout = function(config) {
        printingFee = config.printingFee || 0;
        subscriptionOptions = config.subscriptionOptions || [];
        profiles = config.profiles || [];

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCheckout);
        } else {
            initializeCheckout();
        }
    };

    function initializeCheckout() {
        console.log('Page loaded, initializing...');
        console.log('Profiles:', profiles);
        console.log('Subscription options:', subscriptionOptions);
        
        updateCards();
        updatePrice();

        // Also run as backup
        setTimeout(() => {
            if (!document.getElementById('cardsContainer').innerHTML.trim()) {
                console.log('Cards not generated, running now...');
                updateCards();
                updatePrice();
            }
        }, 100);
    }

    // Make functions globally available
    window.updateCards = function() {
        const quantity = parseInt(document.getElementById('quantity').value);
        document.getElementById('hiddenQuantity').value = quantity;

        const bulkSection = document.getElementById('bulkConfigSection');
        const container = document.getElementById('cardsContainer');

        bulkSection.style.display = quantity > 1 ? 'block' : 'none';

        if (quantity === 1) {
            document.getElementById('useSameConfig').checked = false;
            useBulkConfig = false;
        }

        container.innerHTML = useBulkConfig && quantity > 1 ? generateBulkHTML(quantity) : generateCardsHTML(quantity);
        updatePrice();
    };

    window.toggleBulkConfig = function() {
        useBulkConfig = document.getElementById('useSameConfig').checked;
        updateCards();
    };

    function generateBulkHTML(qty) {
        return `<div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
            <h4 class="text-sm font-medium text-gray-900 mb-4">All ${qty} Cards</h4>
            ${generateCardFields('bulk', 0)}
            <div class="mt-3 p-3 bg-blue-50 rounded text-sm text-blue-700">
                This will apply to all ${qty} cards
            </div>
        </div>`;
    }

    function generateCardsHTML(qty) {
        let html = '';
        for (let i = 0; i < qty; i++) {
            html += `<div class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Card ${i + 1}</h4>
                ${generateCardFields(`cards[${i}]`, i)}
            </div>`;
        }
        return html;
    }

    function generateCardFields(prefix, index) {
        const profileOpts = profiles.map(p => `<option value="${p.id}">${p.profile_name || 'Unnamed'} (${p.slug})</option>`).join('');
        const colors = ['black', 'white', 'silver', 'gold', 'blue'];
        const colorOptions = colors.map(c => `<option value="${c}" ${index === 0 && c === 'black' ? 'selected' : ''}>${c.charAt(0).toUpperCase() + c.slice(1)}</option>`).join('');

        return `
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Profile *</label>
                <select name="${prefix}[profile_id]" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select Profile</option>
                    ${profileOpts}
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Card Color *</label>
                <select name="${prefix}[card_color]" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select Color</option>
                    ${colorOptions}
                </select>
            </div>
            <!-- <div>
                <label class="flex items-start">
                    <input type="checkbox" name="${prefix}[requires_printing]" value="1" class="printing-checkbox rounded mt-0.5" onchange="updatePrice()">
                    <span class="ml-2 text-sm">
                        <span class="font-medium">Custom Printing (+${printingFee.toLocaleString()} TZS)</span>
                        <input type="text" name="${prefix}[printing_text]" placeholder="Text to print" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    </span>
                </label>
            </div> --!>
        `;
    }

    window.updatePrice = function() {
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const selectedYearsRadio = document.querySelector('input[name="subscription_years"]:checked');
        const selectedYears = selectedYearsRadio ? parseInt(selectedYearsRadio.value) : 1;
        const option = subscriptionOptions.find(opt => opt.years === selectedYears);

        if (!option) return;

        const pricePerCard = parseFloat(option.price);
        const baseTotal = pricePerCard * quantity;

        let printingCount = 0;
        document.querySelectorAll('.printing-checkbox:checked').forEach(() => printingCount++);
        const printingTotal = printingCount * printingFee;

        const total = baseTotal + printingTotal;
        const savings = option.savings > 0 ? option.savings * quantity : 0;

        document.getElementById('summaryQuantityLabel').textContent = quantity + ' × ' + pricePerCard.toLocaleString() + ' TZS';
        document.getElementById('summaryBaseTotal').textContent = baseTotal.toLocaleString() + ' TZS';
        document.getElementById('summaryTotal').textContent = total.toLocaleString() + ' TZS';
        document.getElementById('summarySubscriptionInfo').textContent = `✓ Includes ${selectedYears} ${selectedYears > 1 ? 'years' : 'year'} subscription for ${quantity} ${quantity > 1 ? 'profiles' : 'profile'}`;

        document.getElementById('summarySavingsRow').style.display = savings > 0 ? 'flex' : 'none';
        if (savings > 0) document.getElementById('summarySavings').textContent = savings.toLocaleString() + ' TZS';

        document.getElementById('summaryPrintingRow').style.display = printingTotal > 0 ? 'flex' : 'none';
        if (printingTotal > 0) document.getElementById('summaryPrintingTotal').textContent = printingTotal.toLocaleString() + ' TZS';
    };

    window.updateSubscriptionSelection = function(years, price) {
        document.querySelectorAll('.subscription-option').forEach(opt => {
            const isSelected = parseInt(opt.dataset.years) === years;
            opt.classList.toggle('border-brand-500', isSelected);
            opt.classList.toggle('bg-brand-50', isSelected);
            opt.classList.toggle('dark:bg-brand-900/20', isSelected);
            opt.classList.toggle('border-gray-300', !isSelected);
            opt.classList.toggle('dark:border-gray-600', !isSelected);
        });
        updatePrice();
    };
})();
