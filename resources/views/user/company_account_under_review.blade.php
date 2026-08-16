@extends('template_footeronly')

@section('main')
@php
    $statusTitle = [
        'pending_approval' => 'Account under review',
        'rejected' => 'Submission requires changes',
        'suspended' => 'Account suspended',
        'draft' => 'Continue onboarding',
        'documents_pending' => 'Documents still required',
        'phone_pending' => 'Phone verification required',
        'email_pending' => 'Email verification required',
    ][$status] ?? 'Company onboarding status';
@endphp
<style>
    .review-shell{min-height:100vh;background:linear-gradient(180deg,#081321,#0f172a 42%,#111827);color:#fff;display:flex;align-items:center;padding:32px 0}
    .review-wrap{width:min(920px,calc(100% - 32px));margin:0 auto}
    .review-card{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:30px;padding:34px;backdrop-filter:blur(16px);box-shadow:0 28px 70px rgba(0,0,0,.25)}
    .review-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:28px 0}
    .review-item{padding:18px;border-radius:18px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1)}
    .review-item span{display:block;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.64);font-weight:800;margin-bottom:8px}
    .review-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}
    .review-btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 18px;border-radius:16px;font-weight:800;text-decoration:none;border:1px solid rgba(255,255,255,.18);color:#fff}
    .review-btn--primary{background:#14b8a6;border-color:#14b8a6}
    .timeline{display:grid;gap:12px;margin-top:18px}
    .timeline div{padding:14px 16px;border-radius:16px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
    @media (max-width:720px){.review-grid{grid-template-columns:1fr}.review-card{padding:24px}}
</style>
<div class="review-shell">
    <div class="review-wrap">
        <div class="review-card">
            <img src="{{ $logo_url }}" alt="{{ $site_name }}" style="height:48px;max-width:180px;object-fit:contain;filter:brightness(0) invert(1);margin-bottom:18px;">
            <div style="display:inline-flex;padding:10px 14px;border-radius:999px;background:rgba(20,184,166,.16);color:#99f6e4;font-weight:800;">{{ $statusTitle }}</div>
            <h1 style="font-size:clamp(2rem,5vw,3.4rem);line-height:1.05;margin:16px 0 12px;">{{ $company->name ?: $company->legal_name }}</h1>
            <p style="color:rgba(255,255,255,.78);font-size:17px;line-height:1.8;margin:0;">Your company account is being processed through the WiTaxi onboarding and approval flow. You can review what was submitted, continue onboarding if needed, or sign out while the team completes their checks.</p>

            <div class="review-grid">
                <div class="review-item"><span>Reference</span>{{ $company->registration_reference ?: 'Pending reference' }}</div>
                <div class="review-item"><span>Submitted at</span>{{ $company->submitted_for_approval_at ? $company->submitted_for_approval_at->format('d M Y H:i') : 'Not submitted yet' }}</div>
                <div class="review-item"><span>Current status</span>{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                <div class="review-item"><span>Support</span>{{ $supportEmail ?: 'Support email not configured' }}{{ $supportPhone ? ' / '.$supportPhone : '' }}</div>
            </div>

            @if($status === 'rejected' || $status === 'suspended')
                <div class="review-item" style="margin-bottom:18px;">
                    <span>Admin note</span>{{ $company->rejection_reason ?: 'No reason supplied.' }}
                </div>
            @endif

            <div class="timeline">
                <div>1. Registration submitted</div>
                <div>2. Contact verification completed</div>
                <div>3. Documents under review</div>
                <div>4. Approval pending</div>
            </div>

            <div class="review-actions">
                <a class="review-btn review-btn--primary" href="{{ url('company/onboarding?step='.($status === 'pending_approval' ? 'review' : 'company-details')) }}">{{ in_array($status, ['draft','documents_pending','phone_pending','email_pending','rejected'], true) ? 'Continue onboarding' : 'View submitted information' }}</a>
                <a class="review-btn" href="{{ url('company/logout') }}">Log out</a>
            </div>
        </div>
    </div>
</div>
@stop
