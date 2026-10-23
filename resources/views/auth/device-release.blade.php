<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Lepaskan Perangkat — SISFOPERS KC</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:#F1F5F9;color:#0F172A;min-height:100vh}
        .hero{background:linear-gradient(135deg,#1E40AF,#2563EB 55%,#3B82F6);color:#fff;padding:40px 20px 90px;text-align:center;border-radius:0 0 36px 36px}
        .hero h1{font-size:22px;font-weight:800;margin-top:14px}
        .hero p{font-size:13px;opacity:.85;margin-top:6px;line-height:1.5}
        .badge{width:64px;height:64px;border-radius:20px;background:#fff;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 10px 25px rgba(0,0,0,.15)}
        .card{max-width:440px;margin:-60px auto 32px;background:#fff;border-radius:24px;padding:24px;box-shadow:0 20px 40px rgba(37,99,235,.15);border:1px solid #E2E8F0}
        .steps{display:flex;gap:8px;margin-bottom:18px}
        .steps div{flex:1;height:6px;border-radius:9px;background:#E2E8F0}
        .steps div.on{background:#2563EB}
        h2{font-size:17px;font-weight:800}
        .muted{font-size:13px;color:#64748B;line-height:1.55;margin-top:4px}
        label{display:block;font-size:12.5px;font-weight:700;color:#334155;margin:14px 0 6px}
        input{width:100%;padding:13px 14px;border:1px solid #CBD5E1;border-radius:14px;font-size:15px;background:#F8FAFC;outline:none}
        input:focus{border-color:#2563EB;background:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
        .otp{letter-spacing:10px;text-align:center;font-size:24px;font-weight:800}
        button{width:100%;margin-top:20px;padding:14px;border:0;border-radius:14px;background:#2563EB;color:#fff;font-size:15px;font-weight:800;cursor:pointer;box-shadow:0 8px 18px rgba(37,99,235,.3)}
        .alert{border-radius:14px;padding:12px 14px;font-size:13px;line-height:1.5;margin-top:14px}
        .err{background:#FEF2F2;color:#991B1B;border:1px solid #FECACA}
        .info{background:#EFF6FF;color:#1E40AF;border:1px solid #BFDBFE}
        .ok{background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0}
        .center{text-align:center}
        .foot{text-align:center;font-size:11.5px;color:#94A3B8;margin-bottom:24px}
    </style>
</head>
<body>
<div class="hero">
    <div class="badge">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M11 18h2"/></svg>
    </div>
    <h1>Lepaskan Perangkat Lama</h1>
    <p>SISFOPERS KC — Sistem Informasi Personel Komponen Cadangan</p>
</div>

<div class="card">
@if ($step === 'invalid')
    <div class="center">
        <h2>Tautan tidak berlaku</h2>
        <p class="muted">{{ $reason ?? 'Tautan pelepasan perangkat sudah kedaluwarsa atau tidak valid. Silakan minta tautan baru melalui aplikasi Android (menu "Kehilangan akses HP lama?").' }}</p>
    </div>
@elseif ($step === 'verify')
    <div class="steps"><div class="on"></div><div></div><div></div></div>
    <h2>Verifikasi Pemilik Akun</h2>
    <p class="muted">Masukkan data akun Anda untuk melepaskan HP lama. Setelah itu kode OTP dikirim ke email terdaftar.</p>
    @if (!empty($formError))<div class="alert err">{{ $formError }}</div>@endif
    @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ url('/lepas-perangkat/verify') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="identity">{{ $isPju ? 'Username / Email' : 'NIKC' }}</label>
        <input id="identity" name="identity" value="{{ old('identity') }}" required inputmode="{{ $isPju ? 'text' : 'numeric' }}" placeholder="{{ $isPju ? 'Username akun PJU' : '17 digit NIKC' }}">
        @unless ($isPju)
            <label for="dob">Tanggal Lahir</label>
            <input id="dob" type="date" name="dob" value="{{ old('dob') }}" required>
        @endunless
        <label for="password">Kata Sandi</label>
        <input id="password" type="password" name="password" required placeholder="Kata sandi akun">
        <button type="submit">Kirim Kode OTP</button>
    </form>
@elseif ($step === 'otp')
    <div class="steps"><div class="on"></div><div class="on"></div><div></div></div>
    <h2>Masukkan Kode OTP</h2>
    <p class="muted">Kode 6 digit telah dikirim ke email <b>{{ $email }}</b>. Kode berlaku 10 menit.</p>
    @if (!empty($otpError))<div class="alert err">{{ $otpError }}</div>@endif
    @if ($errors->any())<div class="alert err">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ url('/lepas-perangkat/confirm') }}" autocomplete="off">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="otp">Kode OTP</label>
        <input id="otp" class="otp" name="otp" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required autofocus>
        <button type="submit">Lepaskan Perangkat</button>
    </form>
@else
    <div class="steps"><div class="on"></div><div class="on"></div><div class="on"></div></div>
    <div class="center">
        <h2>Perangkat Berhasil Dilepas</h2>
        <div class="alert ok">Semua perangkat Android yang tertaut dengan akun Anda telah dilepas. Silakan buka aplikasi SISFOPERS KC di HP baru lalu login kembali.</div>
    </div>
@endif
</div>
<p class="foot">© {{ date('Y') }} SISFOPERS KC · Komponen Cadangan Tri Matra</p>
</body>
</html>
