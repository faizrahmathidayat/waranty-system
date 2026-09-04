<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Warranty {{ $warranty->kode_warranty }}</title>
    <style>
        :root{--ink:#0b0b0c;--ink-raised:#17171b;--gold:#e5a828;--gold-bright:#f6c85a;--line:rgba(255,255,255,.14);--text:#f4f4f5;--text-muted:#a2a2ab}*{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;padding:24px;font-family:Arial,Helvetica,sans-serif;color:var(--text);background:radial-gradient(circle at 10% 15%,rgba(229,168,40,.28) 0,transparent 32%),radial-gradient(circle at 88% 90%,rgba(229,168,40,.14) 0,transparent 38%),linear-gradient(135deg,#050506,var(--ink))}.verify-card{width:min(100%,440px);overflow:hidden;border:1px solid var(--line);border-radius:24px;background:var(--ink-raised);box-shadow:0 28px 70px rgba(0,0,0,.5)}.hero{position:relative;overflow:hidden;padding:31px 30px 27px;color:#fff;background:linear-gradient(135deg,#0d0d0f,#1a1a1d);text-align:center;border-bottom:1px solid var(--line)}.hero:before,.hero:after{content:"";position:absolute;border:1px solid rgba(229,168,40,.18);border-radius:50%}.hero:before{width:220px;height:220px;top:-145px;left:-85px}.hero:after{width:170px;height:170px;right:-76px;bottom:-106px}.shield{position:relative;z-index:1;display:grid;place-items:center;width:62px;height:62px;margin:0 auto 13px;border-radius:50%;background:rgba(229,168,40,.14);border:1px solid rgba(229,168,40,.35)}.shield svg{width:31px;height:31px;fill:var(--gold-bright)}.hero h1{position:relative;z-index:1;margin:0;font-size:21px;letter-spacing:.8px}.hero p{position:relative;z-index:1;margin:8px 0 0;color:rgba(255,255,255,.7);font-size:13px}.body{padding:30px}.body h2{margin:0;font-size:19px;text-align:center;color:var(--text)}.help{margin:9px 0 23px;color:var(--text-muted);font-size:14px;line-height:1.55;text-align:center}.code{display:inline-block;margin-top:2px;padding:5px 11px;border-radius:99px;background:rgba(229,168,40,.14);color:var(--gold-bright);font-weight:700;letter-spacing:.7px;font-size:12px}label{display:block;margin-bottom:8px;font-size:13px;font-weight:700;color:var(--text)}.pin-wrap{position:relative}.pin-wrap svg{position:absolute;top:18px;left:17px;width:20px;height:20px;fill:var(--text-muted)}input{width:100%;height:56px;padding:0 16px 0 49px;border:1px solid var(--line);border-radius:12px;outline:none;color:var(--text);background:#0f0f11;font-size:22px;font-weight:700;letter-spacing:8px;text-align:center;transition:.2s}input:focus{border-color:var(--gold);background:#141416;box-shadow:0 0 0 4px rgba(229,168,40,.16)}.invalid{border-color:#d9455f}.error{margin:9px 2px 0;color:#ff8a97;font-size:12px}button{width:100%;height:52px;margin-top:23px;border:0;border-radius:12px;cursor:pointer;color:#1a1205;background:linear-gradient(100deg,var(--gold-bright),var(--gold));box-shadow:0 10px 22px rgba(229,168,40,.28);font-size:15px;font-weight:700;transition:transform .15s,filter .15s}button:hover{filter:brightness(1.08);transform:translateY(-1px)}.secure{display:flex;align-items:center;justify-content:center;gap:7px;margin:20px 0 0;color:var(--text-muted);font-size:12px}.secure svg{width:15px;height:15px;fill:#3ec97a}@media(max-width:480px){body{padding:15px}.hero{padding:27px 20px 23px}.body{padding:25px 20px}.verify-card{border-radius:19px}}
    </style>
</head>
<body>
    <main class="verify-card">
        <header class="hero">
            <div class="shield"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.1 3.4 9.8 8 11 4.6-1.2 8-5.9 8-11V5l-8-3Zm3.6 7.8-4.1 4.1a1 1 0 0 1-1.4 0l-2-2 1.4-1.4 1.3 1.3 3.4-3.4 1.4 1.4Z"/></svg></div>
            <h1>DIGITAL WARRANTY</h1><p>Verifikasi akses kartu garansi digital Anda</p>
        </header>
        <section class="body">
            <h2>Masukkan PIN Warranty</h2>
            <p class="help">Masukkan 6 digit PIN untuk melihat detail warranty.<br><span class="code">{{ $warranty->kode_warranty }}</span></p>
            <form method="post" action="{{ route('warranty.verify-pin', $warranty->kode_warranty) }}">
                @csrf
                <label for="pin_warranty">PIN 6 Digit</label>
                <div class="pin-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 9h-1V7a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2Zm-7-2a2 2 0 0 1 4 0v2h-4V7Zm3 9.7V18h-2v-1.3a2 2 0 1 1 2 0Z"/></svg><input id="pin_warranty" name="pin_warranty" type="password" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" class="@error('pin_warranty') invalid @enderror" placeholder="••••••" value="{{ old('pin_warranty') }}" required autofocus></div>
                @error('pin_warranty')<p class="error">{{ $message }}</p>@enderror
                <button type="submit">Verifikasi PIN</button>
            </form>
            <p class="secure"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4Zm4.3 8.5-5 5a1 1 0 0 1-1.4 0l-2.2-2.2 1.4-1.4 1.5 1.5 4.3-4.3 1.4 1.4Z"/></svg>Akses aman dan terlindungi</p>
        </section>
    </main>
    <script>document.getElementById('pin_warranty').addEventListener('input',function(){this.value=this.value.replace(/\D/g,'').slice(0,6);});</script>
</body>
</html>
