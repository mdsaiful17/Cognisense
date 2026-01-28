@extends('layouts.app')

@section('content')
<div style="max-width:1100px;margin:24px auto;padding:0 16px;color:#fff;">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <h1 style="margin:0;font-size:30px;">Your Certificate</h1>
            <p style="color:rgba(255,255,255,.70);margin-top:6px;">
                Certificate No: <b>{{ $cert->certificate_no }}</b> • Issued {{ $cert->issued_at?->format('F j, Y') }}
            </p>
            <p style="color:rgba(255,255,255,.70);margin-top:6px;">
                Name: <b>{{ $cert->full_name }}</b> • Avg Score: <b>{{ $cert->avg_score !== null ? number_format($cert->avg_score,2) : 'N/A' }}/10</b>
            </p>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a class="btn" href="{{ route('certificate.index') }}">Back</a>
            <a class="btn btn-glow" href="{{ route('certificate.download', $cert) }}">Download PDF</a>
        </div>
    </div>

    <div style="margin-top:14px;border-radius:18px;overflow:hidden;border:1px solid rgba(255,255,255,.10);background:rgba(255,255,255,.04);">
        <iframe src="{{ route('certificate.view', $cert) }}" style="width:100%;height:78vh;border:0;"></iframe>
    </div>
</div>

<style>
body{background:#070A12}
.btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:14px;border:1px solid rgba(255,255,255,.12);
background:rgba(255,255,255,.06);color:#fff;text-decoration:none;font-weight:700}
.btn:hover{background:rgba(255,255,255,.10)}
.btn-glow{
background:linear-gradient(135deg, rgba(113,63,255,.9), rgba(0,255,163,.65));
border:1px solid rgba(255,255,255,.16);
box-shadow:0 14px 40px rgba(113,63,255,.25), 0 10px 28px rgba(0,255,163,.18);
}
</style>
@endsection
