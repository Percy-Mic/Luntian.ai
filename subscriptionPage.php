<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="luntian-api-base" content=""/>
<title>Luntian AI - Upgrade to Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-card {
            background: rgba(253, 248, 253, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glass-card {
            background: rgba(49, 48, 52, 0.8);
        }
        .brand-gradient-bg {
            background: linear-gradient(135deg, #00D2FF 0%, #6B4EE6 100%);
        }
        ::-webkit-scrollbar {
            width: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #C7C6D0;
            border-radius: 10px;
        }
        .modal-overlay {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
        }
        .gcash-button:hover .gcash-icon {
            transform: scale(1.1) rotate(5deg);
        }
        /* Toggle Switch Styling */
        .toggle-dot {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        input:checked ~ .toggle-dot {
            transform: translateX(100%);
        }
        .yearly-only { display: none; }
        .is-yearly .yearly-only { display: inline; }
        .is-yearly .monthly-only { display: none; }
    </style>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface": "#fdf8fd",
                        "surface-container-highest": "#e5e1e7",
                        "on-secondary": "#ffffff",
                        "on-surface-variant": "#484555",
                        "surface-bright": "#fdf8fd",
                        "secondary-fixed": "#b6ebff",
                        "secondary": "#00677f",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "secondary-fixed-dim": "#47d6ff",
                        "inverse-on-surface": "#f4eff5",
                        "error-container": "#ffdad6",
                        "on-primary-fixed": "#1b0062",
                        "surface-container-lowest": "#ffffff",
                        "tertiary": "#822b8a",
                        "on-primary-container": "#eee7ff",
                        "inverse-primary": "#cabeff",
                        "outline": "#797586",
                        "primary-fixed": "#e6deff",
                        "on-background": "#1c1b1f",
                        "surface-container": "#f1ecf2",
                        "surface-container-high": "#ebe7ec",
                        "sage-mist": "#8DA47E",
                        "inverse-surface": "#313034",
                        "on-surface": "#1c1b1f",
                        "surface-variant": "#e5e1e7",
                        "moss-deep": "#2D3E1A",
                        "on-tertiary-fixed": "#36003d",
                        "on-tertiary-fixed-variant": "#751e7e",
                        "tertiary-fixed": "#ffd6fb",
                        "wet-stone": "#E1E5DC",
                        "on-primary-fixed-variant": "#4720c2",
                        "background-cool": "#F8F9FE",
                        "on-primary": "#ffffff",
                        "on-secondary-container": "#00566a",
                        "secondary-container": "#00d2ff",
                        "surface-tint": "#5f41da",
                        "surface-dim": "#ddd9de",
                        "surface-glass": "rgba(253, 253, 255, 0.8)",
                        "outline-variant": "#c9c4d7",
                        "primary": "#5230cd",
                        "tertiary-fixed-dim": "#ffa9ff",
                        "surface-container-low": "#f7f2f8",
                        "on-secondary-fixed-variant": "#004e60",
                        "primary-container": "#6b4ee6",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#ffe2fa",
                        "on-secondary-fixed": "#001f28",
                        "tertiary-container": "#9e45a4",
                        "primary-fixed-dim": "#cabeff",
                        "brand-gradient": "linear-gradient(135deg, #00D2FF 0%, #6B4EE6 100%)",
                        "background": "#fdf8fd",
                        "error": "#ba1a1a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "max-content-width": "896px",
                        "xl": "64px",
                        "sm": "12px",
                        "md": "24px",
                        "xs": "4px",
                        "sidebar-width": "320px",
                        "base": "8px",
                        "lg": "40px",
                        "container-margin": "24px",
                        "stack-lg": "40px"
                    },
                    "fontFamily": {
                        "caption-italic": ["Inter"],
                        "label-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "label-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-xl": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-lg": ["Inter"]
                    },
                    "fontSize": {
                        "caption-italic": ["10px", {"lineHeight": "12px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "headline-xl": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-background-cool dark:bg-inverse-surface text-on-surface dark:text-inverse-on-surface font-body-md min-h-screen transition-colors duration-300">
<!-- TopAppBar -->
<header class="bg-surface/70 dark:bg-inverse-surface/70 backdrop-blur-xl fixed top-0 w-full z-50 border-b border-outline-variant/20 shadow-sm">
<div class="flex items-center justify-between px-container-margin h-16 w-full">
<div class="flex items-center gap-4">
<span class="font-headline-lg text-headline-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent" style="font-size: 5vmin;">Luntian AI - Upgrade</span>
</div>
<div class="hidden md:flex items-center gap-8">
<nav class="flex gap-6">
<a class="flex items-center gap-sm px-sm py-2 rounded-xl hover:bg-error/5 text-error text-label-md transition-colors" href="signupPage.php">Cancel</a>
<a class="text-primary border-b-2 border-primary font-label-md px-3 py-2" href="#">Pricing</a>
<a class="text-on-surface-variant dark:text-outline-variant font-label-md transition-colors hover:bg-primary/5 dark:hover:bg-primary/20 px-3 py-2 rounded-lg" href="signinPage.php">Sign in</a>
</nav>
<div class="flex items-center gap-4 text-on-surface-variant dark:text-outline-variant">
<button class="material-symbols-outlined cursor-pointer hover:bg-primary/5 dark:hover:bg-primary/20 p-2 rounded-full transition-colors" onclick="document.documentElement.classList.toggle('dark')">dark_mode</button>
</div>
</div>
</div>
</header>
<main class="pt-32 pb-24 px-container-margin">
<div class="max-w-6xl mx-auto">
<!-- Hero Section -->
<div class="text-center mb-12">
<h1 class="font-headline-xl text-headline-xl text-on-surface dark:text-inverse-on-surface mb-6">Choose Your Intelligence Path</h1>
<p class="font-body-lg text-body-lg text-on-surface-variant dark:text-outline-variant max-w-2xl mx-auto mb-10">
                Unlock the full potential of Luntian AI with Precision Intelligence. Seamlessly scale your creative and technical workflows.
            </p>
<!-- Billing Toggle -->
<div class="flex items-center justify-center gap-4 mb-xl">
<span class="font-label-md text-on-surface-variant dark:text-outline-variant" id="monthlyLabel">Monthly</span>
<label class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" id="billingToggle" onchange="updateBilling()" type="checkbox"/>
<div class="w-14 h-7 bg-surface-container-highest dark:bg-surface-container rounded-full peer peer-checked:bg-primary/20 transition-all"></div>
<div class="toggle-dot absolute left-1 top-1 bg-primary w-5 h-5 rounded-full shadow-md"></div>
</label>
<div class="flex items-center gap-2">
<span class="font-label-md text-on-surface-variant dark:text-outline-variant" id="yearlyLabel">Yearly</span>
<span class="bg-tertiary-container text-on-tertiary-container text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Save 20%</span>
</div>
</div>
</div>
<!-- Bento Grid Pricing -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch" id="pricingContainer">
<!-- Standard Plan -->
<div class="md:col-span-4 glass-card border border-outline-variant/30 dark:border-outline/30 rounded-3xl p-lg flex flex-col shadow-[0_4px_24px_-4px_rgba(107,78,230,0.05)] relative overflow-hidden group transition-all h-full">
<div class="mb-8">
<span class="font-label-sm text-label-sm text-on-surface-variant dark:text-outline-variant uppercase tracking-widest bg-surface-container dark:bg-surface-container-high px-3 py-1 rounded-full">Foundation</span>
<h2 class="font-headline-lg text-headline-lg mt-4 text-on-surface dark:text-inverse-on-surface">Standard</h2>
<div class="flex items-baseline mt-2">
<span class="font-headline-lg text-headline-lg text-on-surface dark:text-inverse-on-surface">$0</span>
<span class="text-on-surface-variant dark:text-outline-variant ml-2">/mo</span>
</div>
</div>
<ul class="space-y-4 mb-lg flex-grow">
<li class="flex items-center gap-3 text-on-surface-variant dark:text-outline-variant">
<span class="material-symbols-outlined text-primary text-xl">check_circle</span>
<span class="text-sm">AI chat access</span>
</li>
<li class="flex items-center gap-3 text-on-surface-variant dark:text-outline-variant">
<span class="material-symbols-outlined text-primary text-xl">check_circle</span>
<span class="text-sm">Conversation history</span>
</li>
<li class="flex items-center gap-3 text-on-surface-variant dark:text-outline-variant">
<span class="material-symbols-outlined text-primary text-xl">check_circle</span>
<span class="text-sm">Available AI features</span>
</li>
</ul>
<button class="w-full py-4 rounded-xl border-2 border-outline-variant text-outline font-label-md cursor-default opacity-60">
                    Current Plan
                </button>
</div>
<!-- Pro Plan -->
<div class="md:col-span-8 bg-surface dark:bg-surface-container-low border-[3px] border-primary rounded-3xl p-lg flex flex-col shadow-[0_12px_48px_-8px_rgba(107,78,230,0.25)] relative overflow-hidden group transition-all transform hover:scale-[1.01]">
<!-- Most Popular Badge -->
<div class="absolute top-0 right-0">
<div class="bg-primary text-on-primary font-label-sm py-1.5 px-8 rounded-bl-2xl shadow-lg flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
        PRO PLAN
    </div>
</div>
<!-- Subtle pattern background -->
<div class="absolute inset-0 opacity-10 pointer-events-none brand-gradient-bg" style="mask-image: radial-gradient(circle at 2px 2px, black 1px, transparent 0); mask-size: 24px 24px;"></div>
<div class="flex flex-col md:flex-row justify-between items-start relative z-10 gap-6">
<div class="mb-8">
<div class="flex items-center gap-3">
<span class="font-label-sm text-label-sm text-primary uppercase tracking-widest bg-primary/10 px-3 py-1 rounded-full">Precision Pro</span>
</div>
<h2 class="font-headline-lg text-headline-lg mt-4 text-on-surface dark:text-inverse-on-surface">Pro Intelligence</h2>
<div class="flex flex-col mt-2">
<div class="flex items-baseline">
<span class="font-headline-xl text-headline-xl text-primary monthly-only">₱1,499</span>
<span class="font-headline-xl text-headline-xl text-primary yearly-only">₱14,390</span>
<span class="text-on-surface-variant dark:text-outline-variant ml-2 monthly-only">/mo</span>
<span class="text-on-surface-variant dark:text-outline-variant ml-2 yearly-only">/year</span>
</div>
<p class="text-on-tertiary-fixed-variant text-sm font-medium yearly-only mt-1">Total ₱14,390/year (about ₱1,199.17/month)</p>
</div>
</div>
<div class="hidden sm:block p-5 bg-primary/10 rounded-2xl">
<span class="material-symbols-outlined text-primary text-5xl" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
</div>
</div>
<!-- Features Grid Split -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-6 mb-lg relative z-10">
<!-- Column 1 -->
<div class="space-y-4">
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-primary font-bold">screen_search_desktop</span>
<span class="font-label-md">Saved conversation history</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-primary font-bold">bolt</span>
<span class="font-label-md">Context-aware AI responses</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-primary font-bold">movie</span>
<span class="font-label-md">Subscription status tracking</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-primary font-bold">support_agent</span>
<span class="font-label-md">Manual payment review</span>
</div>
</div>
<!-- Column 2 (New Value Hooks) -->
<div class="space-y-4">
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-secondary font-bold">rocket_launch</span>
<span class="font-label-md">Premium features as released</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-secondary font-bold">verified</span>
<span class="font-label-md">Secure account-based access</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-secondary font-bold">api</span>
<span class="font-label-md">Payment status tracking</span>
</div>
<div class="flex items-center gap-3 text-on-surface dark:text-inverse-on-surface">
<span class="material-symbols-outlined text-secondary font-bold">psychology</span>
<span class="font-label-md">Pro expiry and renewal management</span>
</div>
</div>
</div>
<div class="flex flex-col gap-4 relative z-10 mt-auto">
<!-- GCash Option -->
<button type="button" id="btn-gcash-pay" data-gcash-pay data-billing-cycle="monthly" class="w-full py-5 rounded-xl brand-gradient-bg text-on-primary font-headline-md shadow-lg shadow-primary/30 hover:brightness-110 transition-all active:scale-95">
                        Pay with GCash (manual)
                    </button>
                    <p class="text-center text-xs text-on-surface-variant dark:text-outline-variant">No GCash API — pay via QR, then submit proof for approval.</p>
<!-- Card Option -->
<button type="button" disabled aria-disabled="true" title="Card payments are not available yet" class="gcash-button flex items-center justify-center gap-3 w-full py-4 rounded-xl border border-outline-variant/30 opacity-50 cursor-not-allowed">
<span class="font-label-md">Card payments unavailable</span>
</button>
</div>
</div>
</div>
<!-- Features Comparison Detail -->
<section class="mt-xl">
<div class="text-center mb-12">
<h3 class="font-headline-md text-headline-md text-on-surface dark:text-inverse-on-surface">Engineered for Excellence</h3>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="glass-card p-8 rounded-3xl border border-outline-variant/10 dark:border-outline/10">
<div class="w-12 h-12 rounded-2xl bg-surface-container dark:bg-surface-container-highest flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary">verified_user</span>
</div>
<h4 class="font-headline-md text-xl mb-3 text-on-surface dark:text-inverse-on-surface">Enterprise Security</h4>
<p class="text-on-surface-variant dark:text-outline-variant">Pro members get Your account and payment records are linked to your signed-in account.</p>
</div>
<div class="glass-card p-8 rounded-3xl border border-outline-variant/10 dark:border-outline/10">
<div class="w-12 h-12 rounded-2xl bg-surface-container dark:bg-surface-container-highest flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary">cloud_sync</span>
</div>
<h4 class="font-headline-md text-xl mb-3 text-on-surface dark:text-inverse-on-surface">Instant Sync</h4>
<p class="text-on-surface-variant dark:text-outline-variant">Saved conversations are stored in your account database.</p>
</div>
<div class="glass-card p-8 rounded-3xl border border-outline-variant/10 dark:border-outline/10">
<div class="w-12 h-12 rounded-2xl bg-surface-container dark:bg-surface-container-highest flex items-center justify-center mb-6">
<span class="material-symbols-outlined text-primary">architecture</span>
</div>
<h4 class="font-headline-md text-xl mb-3 text-on-surface dark:text-inverse-on-surface">Custom Workflows</h4>
<p class="text-on-surface-variant dark:text-outline-variant">Additional workflow tools will be introduced as they become available.</p>
</div>
</div>
</section>
<!-- Visual Trust Section -->
<section class="mt-xl rounded-3xl overflow-hidden relative min-h-[400px] flex items-center px-lg shadow-xl">
<img alt="Abstract Intelligence" class="absolute inset-0 w-full h-full object-cover" data-alt="A macro photography shot of flowing, iridescent liquid metals in deep violet and vibrant cyan. The textures resemble organic neural networks or digital water flowing in a serene, high-tech environment. The lighting is soft and ethereal, capturing the biophilic intelligence aesthetic of Luntian AI with a premium, modern professional feel." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBv_YLpzDHWNmcETiSpKZ89eHNj0zNHx6b-PTisZCdDdkNrhusKkW03Hdvxr5abt1-SaUaAhMZp1-d7pw3qW2IUUFk49MPgBNa6SMVZ6HOslqRh8F7GrfYrharSUKi1depam4_zjnoQZARWC5JBXKYGTGN92rOGj9LJ2e5nZR2Ty40L6XY_z8yqBCSof9BhpfF6LR80Z-PZKBKBgR-pLFu2UPDQNZNXGxJdcjd96ZuacgHjKZWlwsaNFN2d0IMTFesWSkfdK040Hcjk"/>
<div class="absolute inset-0 bg-gradient-to-r from-surface/90 via-surface/40 to-transparent dark:from-inverse-surface/90 dark:via-inverse-surface/40"></div>
<div class="relative z-10 max-w-lg py-12 md:py-0">
<h3 class="font-headline-lg text-headline-lg mb-4 text-on-surface dark:text-inverse-on-surface">Experience Harmony</h3>
<p class="text-body-lg text-on-surface-variant dark:text-outline-variant mb-8">
                    Luntian AI isn't just a tool; it's a creative partner designed to blend into your natural workflow seamlessly.
                </p>
<div class="flex gap-4">
<div class="flex -space-x-3">
<img alt="User" class="w-10 h-10 rounded-full border-2 border-surface dark:border-inverse-surface" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9_aGyTFdN6D45grTwusJuQnXznl2gvu1DNovJ33Vp1SGH8pQPT-6Q-mLMTG7Zefj_Z8-eHJTXNiUcs6NvCPy8OFFwwQnddIVNBnxzL7WSyay4A933cvyNCJLuVVDEf1_GuFfhli_BDpHL22f_i0S3HuIziKEhRLNCrgfKnYcWgpiaghxDGh1qq_lOZyaGuKTe7miSDT1YXqCJq8iYz0p4cW2CNXcnzd7cRTBPyScr4-Rb1G-w0jcvLWjtWZMUltgQZRv2_o1fw8_I"/>
<img alt="User" class="w-10 h-10 rounded-full border-2 border-surface dark:border-inverse-surface" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCV4q6fUd3d_QYY2QARpo6yA9cZ6i3ATKd0fj6ziyrlo05YIgqVfWxtI8e5eXVYOz6trAtP_hXTLeYxb8T3-aGB2dzHgQvDjAKq5oUPt0NSLBOmMq22LMBtO2W9wePPh-gqUKhMKCcIbOs220rmi4P1-U9GKpEz1ocIh4fKVE2yYUCdmc3xPA9gc2w_osNO98nRcM0jFDxxTv-hjRY0bOH27EG6XOhF31Ked34FboZS5Fqk7hFKI6NAOHdInHI-JdBaRwsRAMHRFsAA"/>
<img alt="User" class="w-10 h-10 rounded-full border-2 border-surface dark:border-inverse-surface" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDAaBMUhr8GwTkoLf84v_HEXyhcOGxmd8DGWdFrUILvhCDre1XoGRLmfkDvyPLVEfwYSwpiOXXqQQPQM9ldj5iYKwsWL-a8ejziAFO1xOOyDXsZsALWiyRw6B6mz4WohAVc2UjT30x1DRB5AYrdMPdR08XWEi-2iqxMlyj7WbDlflqekEUe8XbU40HoQ1tsfGrLuLEhI7igpyQKGawbsrYW3PgBtoSFWl4nUGPzrhIoRWnfNWVnDLyq5HU1akStTkSL6uf2y1gxaUhZ"/>
</div>
<div class="flex flex-col">
<span class="font-label-md text-on-surface dark:text-inverse-on-surface">Made for learners and builders</span>
<span class="text-sm text-on-surface-variant dark:text-outline-variant">Students, creators, and developers</span>
</div>
</div>
</div>
</section>
</div>
</main>
<!-- Subscription Removal Confirmation Modal -->
<div class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4" id="removalModal">
<div class="modal-overlay absolute inset-0" onclick="toggleModal(false)"></div>
<div class="glass-card relative w-full max-w-md rounded-3xl border border-outline-variant/30 shadow-2xl p-8 overflow-hidden transform transition-all scale-100">
<div class="mb-6 text-center">
<div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mx-auto mb-4">
<span class="material-symbols-outlined text-4xl">warning</span>
</div>
<h3 class="font-headline-md text-on-surface dark:text-inverse-on-surface mb-2">Cancel Subscription?</h3>
<p class="text-on-surface-variant dark:text-outline-variant">You will lose access to 4K video generation and unlimited analysis at the end of your billing cycle.</p>
</div>
<div class="flex flex-col gap-3">
<button class="w-full py-4 rounded-xl bg-surface-container-high dark:bg-surface-container-highest text-on-surface dark:text-inverse-on-surface font-label-md hover:brightness-95 transition-all" onclick="toggleModal(false)">
                Keep My Plan
            </button>
<button class="w-full py-4 rounded-xl border border-error/30 text-error font-label-md hover:bg-error/5 transition-all" onclick="toggleModal(false)">
                Yes, Remove Subscription
            </button>
</div>
</div>
</div>
<!-- Remove Subscription Action -->
<div class="mt-4 pt-4 border-t border-outline-variant/20">
<button class="text-error hover:bg-error/5 px-4 py-2 rounded-lg text-label-md transition-colors w-full text-center" onclick="toggleModal(true)">
                            Remove Subscription
                        </button>
</div>
<!-- Simple Footer -->
<footer class="py-12 border-t border-outline-variant/10 dark:border-outline/10">
<div class="max-w-6xl mx-auto px-container-margin flex flex-col md:flex-row justify-between items-center gap-8">
<span class="font-headline-md text-primary opacity-50">Luntian AI Pro</span>
<div class="flex flex-wrap justify-center gap-8 text-on-surface-variant dark:text-outline-variant font-label-md">
<a class="hover:text-primary transition-colors" href="privacypolicyPage.php">Privacy Policy</a>
<a class="hover:text-primary transition-colors" href="termsofservicePage.php">Terms of service</a>
</div>
<p class="text-label-sm text-on-surface-variant dark:text-outline-variant text-center">© 2026 Luntian AI Pro. Precision Intelligence.</p>
</div>
</footer>
<script>
    function toggleModal(show) {
        const modal = document.getElementById('removalModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    function updateBilling() {
        const toggle = document.getElementById('billingToggle');
        const container = document.getElementById('pricingContainer');
        const mLabel = document.getElementById('monthlyLabel');
        const yLabel = document.getElementById('yearlyLabel');
        
        if (toggle.checked) {
            container.classList.add('is-yearly');
            yLabel.classList.add('text-primary', 'font-bold');
            mLabel.classList.remove('text-on-surface', 'font-bold');
        } else {
            container.classList.remove('is-yearly');
            mLabel.classList.add('text-on-surface', 'font-bold');
            yLabel.classList.remove('text-primary', 'font-bold');
        }
    }

    // Initialize labels
    updateBilling();

    document.querySelector('meta[name="luntian-api-base"]').content =
        window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '');

    const gcashBtn = document.getElementById('btn-gcash-pay');
    const billingToggle = document.getElementById('billingToggle');
    if (gcashBtn && billingToggle) {
        billingToggle.addEventListener('change', () => {
            gcashBtn.setAttribute('data-billing-cycle', billingToggle.checked ? 'yearly' : 'monthly');
        });
    }

    // Micro-interaction: Smooth hover effect for pricing cards
    const cards = document.querySelectorAll('.glass-card, .md\\:col-span-8');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
</script>
<script src="assets/js/luntian-api.js"></script>
<script src="assets/js/payment-gcash.js"></script>
</body></html>
