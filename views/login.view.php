<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk &amp; Daftar - Kasir Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/icons.css?v=5">
    <link rel="stylesheet" href="assets/css/app.css?v=2">
    <script src="assets/js/login.js?v=3" defer></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col">
    <a href="#main-content" class="skip-link">Lewati ke formulir login</a>

    <main id="main-content" tabindex="-1" class="flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-xl shadow-sm p-6 sm:p-8">

            <!-- Header Identitas Singkat -->
            <header class="flex items-center gap-3 pb-5 mb-5 border-b border-slate-200">
                <div class="w-11 h-11 rounded-lg bg-orange-700 text-white flex items-center justify-center shrink-0">
                    <span class="app-icon text-2xl" data-icon="register" aria-hidden="true"></span>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-900 leading-tight">Kasir Restoran</h1>
                    <p class="text-xs font-medium text-slate-600 mt-0.5">Point of Sale &amp; Backoffice</p>
                </div>
            </header>

            <!-- Tab Switcher Masuk / Daftar Kasir -->
            <div role="tablist" aria-label="Pilih mode autentikasi" class="grid grid-cols-2 gap-1.5 p-1 rounded-lg bg-slate-100 border border-slate-200 mb-5">
                <button type="button" role="tab" id="tab-login" aria-selected="true" aria-controls="panel-login"
                        class="min-h-[44px] rounded-md text-sm font-bold transition-colors bg-white text-slate-900 border border-slate-200 shadow-sm">
                    Masuk
                </button>
                <button type="button" role="tab" id="tab-register" aria-selected="false" aria-controls="panel-register" tabindex="-1"
                        class="min-h-[44px] rounded-md text-sm font-semibold transition-colors text-slate-600 hover:text-slate-900">
                    Daftar Kasir
                </button>
            </div>

            <!-- Notifikasi Error / Status -->
            <div id="pesan" role="alert" aria-live="polite" class="hidden mb-4 rounded-lg text-sm px-4 py-3 font-medium"></div>

            <!-- Panel Login -->
            <div id="panel-login" role="tabpanel" aria-labelledby="tab-login">
                <form id="formLogin" class="space-y-4">
                    <div>
                        <label for="loginUsername" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                        <input type="text" name="username" id="loginUsername" required autocomplete="username"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                               placeholder="contoh: kasir1">
                    </div>
                    <div>
                        <label for="loginPassword" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="loginPassword" required autocomplete="current-password"
                                   class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white pl-3.5 pr-12 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                                   placeholder="Masukkan password">
                            <button type="button" id="toggleLoginPass" aria-label="Tampilkan password" aria-pressed="false"
                                    class="absolute right-1.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center">
                                <span class="app-icon" data-icon="eye" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" id="tombolLogin"
                            class="w-full min-h-[44px] rounded-lg bg-orange-700 hover:bg-orange-800 active:bg-orange-900 disabled:bg-slate-300 disabled:text-slate-600 text-white font-bold py-2.5 px-4 text-sm transition-colors flex items-center justify-center gap-2">
                        <span class="app-icon" data-icon="login" aria-hidden="true"></span>
                        <span>Masuk</span>
                    </button>
                </form>
            </div>

            <!-- Panel Register Kasir -->
            <div id="panel-register" role="tabpanel" aria-labelledby="tab-register" hidden>
                <form id="formRegister" class="space-y-4">
                    <div>
                        <label for="regNama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                        <input type="text" name="nama" id="regNama" maxlength="100" required autocomplete="name"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                               placeholder="Nama tampilan kasir">
                    </div>
                    <div>
                        <label for="regUsername" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                        <input type="text" name="username" id="regUsername" minlength="3" maxlength="50" required autocomplete="username"
                               pattern="[a-zA-Z0-9_]+"
                               aria-describedby="regUsernameHelp"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                               placeholder="Minimal 3 karakter">
                        <p id="regUsernameHelp" class="text-xs text-slate-600 mt-1">Huruf, angka, dan tanda garis bawah (_).</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="regPassword" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="regPassword" minlength="5" required autocomplete="new-password"
                                       class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white pl-3.5 pr-11 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                                       placeholder="Min. 5 karakter">
                                <button type="button" id="toggleRegPass" aria-label="Tampilkan password pendaftaran" aria-pressed="false"
                                        class="absolute right-0.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center">
                                    <span class="app-icon" data-icon="eye" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="regKonfirmasi" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi</label>
                            <input type="password" name="konfirmasi_password" id="regKonfirmasi" minlength="5" required autocomplete="new-password"
                                   class="w-full min-h-[44px] rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700"
                                   placeholder="Ulangi password">
                        </div>
                    </div>
                    <button type="submit" id="tombolRegister"
                            class="w-full min-h-[44px] rounded-lg bg-slate-900 hover:bg-black active:bg-slate-950 disabled:bg-slate-300 disabled:text-slate-600 text-white font-bold py-2.5 px-4 text-sm transition-colors flex items-center justify-center gap-2">
                        <span class="app-icon" data-icon="user-plus" aria-hidden="true"></span>
                        <span>Daftar Akun Kasir</span>
                    </button>
                </form>
            </div>

            <!-- Isi cepat akun bawaan untuk pengujian lokal -->
            <footer class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between gap-2 flex-wrap">
                <span class="text-xs font-medium text-slate-600">Akun bawaan:</span>
                <div class="flex items-center gap-2">
                    <button type="button" data-demo-user="kasir1" data-demo-pass="kasir123"
                            class="btn-demo min-h-[44px] px-3.5 py-2 rounded-md border border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors">
                        kasir1
                    </button>
                    <button type="button" data-demo-user="admin" data-demo-pass="admin123"
                            class="btn-demo min-h-[44px] px-3.5 py-2 rounded-md border border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors">
                        admin
                    </button>
                </div>
            </footer>
        </div>
    </main>
</body>
</html>
