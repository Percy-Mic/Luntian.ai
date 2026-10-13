/**
 * GCash manual payment flow on subscription + payment pages
 */
(function () {
  if (!window.LuntianAPI) return;

  async function startGcashCheckout(billingCycle) {
    await LuntianAPI.requireAuth('signinPage.php');
    const data = await LuntianAPI.api('/api/payments/create.php', {
      method: 'POST',
      body: JSON.stringify({ billingCycle }),
    });
    window.location.href = data.paymentPageUrl;
  }

  document.querySelectorAll('[data-gcash-pay]').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const cycle = btn.getAttribute('data-billing-cycle') || 'monthly';
      btn.disabled = true;
      try {
        await startGcashCheckout(cycle);
      } catch (err) {
        alert(err.message || 'Could not start payment');
        btn.disabled = false;
      }
    });
  });

  const form = document.getElementById('gcash-proof-form');
  if (!form) return;

  const params = new URLSearchParams(window.location.search);
  const ref = params.get('ref');
  const refInput = document.getElementById('payment-reference');
  if (refInput && ref) refInput.value = ref;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const statusEl = document.getElementById('payment-status-msg');
    const fd = new FormData(form);
    const token = LuntianAPI.getToken();
    try {
      const res = await fetch(LuntianAPI.apiBase() + '/api/payments/submit.php', {
        method: 'POST',
        headers: token ? { Authorization: 'Bearer ' + token } : {},
        body: fd,
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Submit failed');
      if (statusEl) {
        statusEl.className = 'text-sm text-emerald-300 font-medium';
        statusEl.textContent = data.message;
      }
      const currentStatus = document.querySelector('#payment-details p:last-child span');
      if (currentStatus) currentStatus.textContent = data.status || 'submitted';
      const proofInput = form.querySelector('input[type=file][name=proof]');
      if (proofInput) proofInput.disabled = true;
      form.querySelector('button[type=submit]').disabled = true;
    } catch (err) {
      if (statusEl) {
        statusEl.className = 'text-sm text-error font-medium';
        statusEl.textContent = err.message;
      }
    }
  });
})();
