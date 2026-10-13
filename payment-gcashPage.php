<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="luntian-api-base" content=""/>
<title>Luntian AI — GCash Payment</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
<script>
  document.querySelector('meta[name="luntian-api-base"]').content =
    window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');
</script>
<style>
  .brand-gradient { background: linear-gradient(135deg, #00D2FF 0%, #6B4EE6 100%); }
</style>
</head>
<body class="min-h-screen bg-[#0b0e14] text-white font-[Inter] p-6">
<div class="max-w-lg mx-auto space-y-6">
<a href="subscriptionPage.php" class="text-sm text-white/60 hover:text-white flex items-center gap-1">
<span class="material-symbols-outlined text-lg">arrow_back</span> Back to plans
</a>
<h1 class="text-2xl font-bold">Pay with GCash</h1>
<p class="text-white/70 text-sm">Send the exact amount below, then submit your GCash reference number and screenshot. Pro activates after manual verification (usually within 24 hours).</p>

<div id="payment-details" class="rounded-2xl border border-white/10 bg-white/5 p-6 space-y-3">
<p class="text-sm text-white/50">Loading payment details…</p>
</div>

<div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-center">
<p class="text-sm text-white/60 mb-3">Scan to pay</p>
<img id="gcash-qr" alt="GCash QR Code" class="mx-auto w-56 h-56 object-contain rounded-xl bg-white p-2" src="public/assets/images/gcash-qr.png" onerror="this.style.display='none';document.getElementById('qr-missing').classList.remove('hidden')"/>
<p id="qr-missing" class="hidden text-amber-300 text-sm">Add your QR image at <code class="bg-black/30 px-1 rounded">public/assets/images/gcash-qr.png</code></p>
<p class="mt-3 text-sm"><span class="text-white/50">Account:</span> <span id="gcash-name">—</span></p>
<p class="text-sm"><span class="text-white/50">Number:</span> <span id="gcash-mobile">—</span></p>
</div>

<form id="gcash-proof-form" class="rounded-2xl border border-white/10 bg-white/5 p-6 space-y-4" enctype="multipart/form-data">
<input type="hidden" name="reference_code" id="payment-reference"/>
<label class="block text-sm font-medium">Your order reference</label>
<input class="w-full rounded-xl bg-black/30 border border-white/10 px-4 py-3" id="payment-reference-display" readonly/>

<label class="block text-sm font-medium">GCash reference no. (from receipt)</label>
<input class="w-full rounded-xl bg-black/30 border border-white/10 px-4 py-3" name="gcash_reference" required placeholder="e.g. 123456789012"/>

<label class="block text-sm font-medium">Screenshot of payment</label>
<input class="w-full text-sm" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required/>

<button type="submit" class="w-full py-4 rounded-xl brand-gradient font-bold shadow-lg hover:brightness-110">Submit proof</button>
<p id="payment-status-msg" class="text-sm"></p>
</form>
</div>
<script src="assets/js/luntian-api.js"></script>
<script src="assets/js/payment-gcash.js"></script>
<script>
(async function () {
  const params = new URLSearchParams(location.search);
  const ref = params.get('ref');
  const refDisp = document.getElementById('payment-reference-display');
  const refHidden = document.getElementById('payment-reference');
  if (ref) {
    refDisp.value = ref;
    refHidden.value = ref;
  }
  await LuntianAPI.requireAuth('signinPage.php');
  try {
    const cfg = await LuntianAPI.api('/api/payments/config.php');
    document.getElementById('gcash-name').textContent = cfg.gcash.accountName;
    document.getElementById('gcash-mobile').textContent = cfg.gcash.mobile || 'Set GCASH_MOBILE in .env';
    if (cfg.gcash.qrImageUrl) document.getElementById('gcash-qr').src = cfg.gcash.qrImageUrl;
    if (ref) {
      const st = await LuntianAPI.api('/api/payments/status.php?ref=' + encodeURIComponent(ref));
      const p = st.payment;
      const details = document.getElementById('payment-details');
      details.replaceChildren();
      const amount = document.createElement('p');
      const amountLabel = document.createElement('strong');
      amountLabel.textContent = 'Amount: ';
      amount.append(amountLabel, '₱' + Number(p.amount_php).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      const plan = document.createElement('p');
      const planLabel = document.createElement('strong');
      planLabel.textContent = 'Plan: ';
      plan.append(planLabel, String(p.billing_cycle || '—'));
      const status = document.createElement('p');
      const statusLabel = document.createElement('strong');
      statusLabel.textContent = 'Status: ';
      const statusValue = document.createElement('span');
      statusValue.className = 'text-emerald-300';
      statusValue.textContent = String(p.status || 'unknown');
      status.append(statusLabel, statusValue);
      details.append(amount, plan, status);
    }
  } catch (e) {
    const details = document.getElementById('payment-details');
    details.replaceChildren();
    const error = document.createElement('p');
    error.className = 'text-rose-300';
    error.textContent = e.message || 'Could not load payment details.';
    details.append(error);
  }
})();
</script>
</body>
</html>
