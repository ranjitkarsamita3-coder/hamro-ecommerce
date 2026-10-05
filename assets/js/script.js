document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');
    if (toggle && nav) toggle.addEventListener('click', () => nav.classList.toggle('open'));

    document.querySelectorAll('[data-validate="register"]').forEach(form => {
        form.addEventListener('submit', function (event) {
            const password = form.querySelector('[name="password"]');
            const confirm = form.querySelector('[name="confirm_password"]');
            if (password && confirm && password.value !== confirm.value) {
                event.preventDefault();
                alert('Passwords do not match.');
                confirm.focus();
            }
        });
    });

    document.querySelectorAll('[data-validate="checkout"]').forEach(form => {
        form.addEventListener('submit', function (event) {
            const method = form.querySelector('input[name="payment_method"]:checked');
            if (!method) {
                event.preventDefault();
                alert('Please select a payment method.');
            }
        });
    });

    const paymentForm = document.getElementById('paymentForm');
    const overlay = document.getElementById('loadingOverlay');
    const payButton = document.getElementById('payButton');
    if (paymentForm && overlay) {
        paymentForm.addEventListener('submit', function (event) {
            if (!paymentForm.checkValidity()) return;
            if (payButton) { payButton.disabled = true; payButton.textContent = 'Processing...'; }
            overlay.classList.add('show');
        });
    }

});
