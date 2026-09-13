<?php 
$title = '물류 파렛트랙 B2B SaaS 자동설계 & 견적 솔루션'; 
include_header($title, $siteConfig ?? []); 
?>
<script>
    // 페이지 로드 시 라이트 모드 강제 적용 (랜딩 전용)
    document.documentElement.setAttribute('data-bs-theme', 'light');
</script>
<style>
/* 🎨 Premium SaaS Minimal Landing Page */
.landing-wrapper {
    font-family: 'Inter', 'Noto Sans KR', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #1e293b;
    background-color: #ffffff;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

/* Colors */
.landing-wrapper .bg-brand-orange { background-color: #f16819 !important; }
.landing-wrapper .text-brand-orange { color: #f16819 !important; }
.landing-wrapper .bg-light-gray { background-color: #f8fafc !important; }
.landing-wrapper .bg-deep-navy { background-color: #0f172a !important; }
.landing-wrapper .text-deep-navy { color: #0f172a !important; }

/* 🌟 어두운 배경(bg-deep-navy) 내부 텍스트 밝은 색상 보장 */
.landing-wrapper .bg-deep-navy,
.landing-wrapper .bg-deep-navy h1,
.landing-wrapper .bg-deep-navy h2,
.landing-wrapper .bg-deep-navy h3,
.landing-wrapper .bg-deep-navy h4,
.landing-wrapper .bg-deep-navy p,
.landing-wrapper .bg-deep-navy span:not(.badge):not(.btn-pill):not(.text-brand-orange),
.landing-wrapper .bg-deep-navy .text-light,
.landing-wrapper .bg-deep-navy .text-white,
.landing-wrapper .bg-deep-navy .btn-outline-light {
    color: #ffffff !important;
}
.landing-wrapper .text-light {
    color: #ffffff !important;
}
.landing-wrapper .text-white {
    color: #ffffff !important;
}
.landing-wrapper .bg-deep-navy p,
.landing-wrapper .bg-deep-navy .text-light-muted {
    color: rgba(255, 255, 255, 0.88) !important;
}

/* Buttons */
.landing-wrapper .btn-pill {
    border-radius: 50rem;
    padding: 0.65rem 1.75rem;
    font-weight: 600;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.landing-wrapper .btn-pill:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 10px 20px -5px rgba(0,0,0,0.15); 
}
.landing-wrapper .btn-orange { 
    background: #f16819; 
    color: #fff; 
    border: none; 
}
.landing-wrapper .btn-orange:hover { 
    background: #e05300; 
    color: #fff; 
}
.landing-wrapper .btn-navy { 
    background: #0f172a; 
    color: #fff; 
}
.landing-wrapper .btn-navy:hover { 
    background: #1e293b; 
    color: #fff; 
}

/* Badges */
.landing-wrapper .badge-soft-orange { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.landing-wrapper .badge-soft-primary { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

/* Grid patterns */
.landing-wrapper .grid-pattern-dark {
    background-image:
    linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
    background-size: 36px 36px;
}
.landing-wrapper .grid-pattern {
    background-image:
    linear-gradient(rgba(15,23,42,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15,23,42,0.04) 1px, transparent 1px);
    background-size: 32px 32px;
}

/* Sections */
.landing-wrapper .section-padding { padding: 5rem 1.5rem; }
@media (min-width: 768px) {
    .landing-wrapper .section-padding { padding: 6.5rem 2rem; }
}
.landing-wrapper .max-w-1200 { max-width: 1200px; margin: 0 auto; }
.landing-wrapper .max-w-720 { max-width: 720px; margin: 0 auto; }

/* Card Animations */
.landing-wrapper .reveal { opacity: 0; transform: translateY(24px); transition: all 0.7s cubic-bezier(.16,1,.3,1); }
.landing-wrapper .reveal.in-view { opacity: 1; transform: translateY(0); }

.landing-wrapper .feature-card {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #e2e8f0;
    background: #ffffff;
}
.landing-wrapper .feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

/* Pricing Card Special */
.pricing-card-box {
    transition: all 0.3s ease;
    border-radius: 1.5rem;
    border: 2px solid transparent;
}
.pricing-card-box:hover {
    transform: translateY(-6px);
}
.pricing-card-pro {
    border-color: #f16819 !important;
    box-shadow: 0 25px 50px -12px rgba(241, 104, 25, 0.15) !important;
}
</style>

<div class="landing-wrapper">

    <!-- Hero Section -->
    <section class="position-relative bg-deep-navy text-light overflow-hidden py-5" style="padding-top: 6rem !important;">
        <div class="position-absolute w-100 h-100 top-0 start-0 grid-pattern-dark opacity-50"></div>
        <div class="position-absolute start-50 translate-middle-x" style="top:-250px; width: min(1200px, 100vw); height: 750px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(241,104,25,0.3), transparent 65%); filter: blur(30px);"></div>
        <div class="position-absolute" style="top:100px; right:-80px; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(99,102,241,0.2), transparent 65%); filter: blur(40px);"></div>
        
        <div class="max-w-1200 section-padding position-relative z-1">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <div class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-1 fs-7 fw-bold mb-3" style="background-color: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;">
                        <i class="fa-solid fa-shield-halved"></i> B2B 단가표 유출 원천 차단 시스템
                    </div>
                    
                    <h1 class="mt-3 fw-bold tracking-tight text-light" style="font-size: clamp(30px, 4.5vw, 42px); line-height: 1.3; letter-spacing: -1px;">
                        사장님의 포스트, 빔 원가...<br>
                        <span class="text-brand-orange">아직도 카톡으로 돌리십니까?</span>
                    </h1>
                    
                    <h2 class="fw-bold mt-3 mb-4" style="color: #94a3b8; font-size: clamp(18px, 2vw, 22px);">
                        퇴사자 폰에 남은 엑셀 하나가, 수주율 30%를 깎아먹습니다.
                    </h2>
                    
                    <div class="mt-4 fs-6 text-light lh-lg bg-white bg-opacity-10 p-4 rounded-3 border border-white border-opacity-20" style="max-width: 540px;">
                        <p class="mb-2"><i class="fa-solid fa-lock text-brand-orange me-2"></i><strong>직원이 마진율 함부로 못 바꾸게, 대표님 5분 승인 후에만 잠깁니다.</strong></p>
                        <p class="mb-0"><i class="fa-solid fa-fingerprint text-brand-orange me-2"></i>견적서마다 보이지 않는 지문을 심어, 유출되면 0.1초 만에 잡습니다.</p>
                    </div>
                    
                    <div class="mt-5 d-flex flex-wrap gap-3 align-items-center">
                        <a href="#pricing" class="btn-pill btn-orange shadow-lg fs-6 px-4 py-3 text-center" style="transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                            5분 승인 잠금 시스템, 내 회사에 적용하기 <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    
                    <div class="mt-4 d-flex align-items-center gap-4 fs-8 text-light">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-check text-success"></i>
                            <span class="text-light opacity-75">견적서 지문 추적</span>
                        </div>
                        <div style="width:1px; height:12px; background: rgba(255,255,255,0.3);"></div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-check text-success"></i>
                            <span class="text-light opacity-75">마진율 5분 승인</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="position-relative rounded-4 p-2 bg-white bg-opacity-10 border border-white border-opacity-20 shadow-lg">
                        <div class="rounded-3 bg-white text-dark overflow-hidden shadow-sm">
                            <div class="d-flex align-items-center justify-content-between bg-light px-3 py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-danger" style="width:10px;height:10px;"></div>
                                    <div class="rounded-circle bg-warning" style="width:10px;height:10px;"></div>
                                    <div class="rounded-circle bg-success" style="width:10px;height:10px;"></div>
                                    <span class="ms-2 fs-8 fw-bold text-muted">2D CANVAS ENGINE • 30m × 18m</span>
                                </div>
                                <span class="badge bg-brand-orange text-white fs-9">연산 속도 2.8s</span>
                            </div>
                            <div class="p-4 position-relative grid-pattern" style="min-height: 280px; background-color: #f8fafc;">
                                <!-- Simple Layout Preview -->
                                <div class="row g-2 h-100">
                                    <?php for($i=0; $i<12; $i++): ?>
                                        <div class="col-3">
                                            <div class="p-2 rounded border text-center" style="background:#e0e7ff; border-color:#c7d2fe !important;">
                                                <div class="fw-bold text-primary" style="font-size:0.75rem;">랙 열 <?= $i+1 ?></div>
                                                <div class="text-muted" style="font-size:0.65rem;">3단 적재 (6 PLT)</div>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <div class="position-absolute bottom-0 end-0 m-3 p-3 bg-white rounded-3 shadow border" style="max-width: 220px;">
                                    <div class="d-flex justify-content-between fs-8 mb-1">
                                        <span class="text-muted">총 주기둥</span>
                                        <strong class="text-dark">48 EA</strong>
                                    </div>
                                    <div class="d-flex justify-content-between fs-8 mb-1">
                                        <span class="text-muted">총 로드빔</span>
                                        <strong class="text-dark">96 EA</strong>
                                    </div>
                                    <div class="d-flex justify-content-between fs-8">
                                        <span class="text-muted">적재 파렛트</span>
                                        <strong class="text-brand-orange">72 PLT</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Highlights (3 Core Values) -->
    <section class="section-padding bg-white border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-720 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-primary px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-cubes"></i> 핵심 기능 요약
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    견적 업무의 모든 과정을 하나로 연결합니다
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    프로그램 설치 없이 웹 브라우저에서 2D 도면, 자재 수량, 견적서 발송까지 한 번에 처리하세요.
                </p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5">
                        <div class="rounded-3 bg-brand-orange bg-opacity-10 text-brand-orange d-flex align-items-center justify-content-center mb-4" style="width: 52px; height: 52px;">
                            <i class="fa-solid fa-drafting-compass fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-deep-navy fs-5">1. 2D 웹 인터랙티브 설계</h3>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            창고 치수를 입력하고 마우스 클릭 몇 번으로 최적 랙을 배치합니다. 기둥, 셔터 등 현장 장애물도 자유롭게 등록할 수 있습니다.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5" style="transition-delay: 100ms;">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 52px; height: 52px;">
                            <i class="fa-solid fa-calculator fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-deep-navy fs-5">2. 100% 정밀 자재 BOM 산출</h3>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            주기둥(독립/연결), 로드빔, 타이빔, 앙카볼트 수량이 공학 계산식으로 자동 연산되어 자재 오발주와 누락이 0%로 줄어듭니다.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5" style="transition-delay: 200ms;">
                        <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center mb-4" style="width: 52px; height: 52px;">
                            <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
                        </div>
                        <h3 class="fw-bold text-deep-navy fs-5">3. 원클릭 정식 PDF 견적서</h3>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            귀사의 상호와 로고, 직인이 포함된 공식 견적서가 PDF로 즉시 생성됩니다. 고객 링크 공유 및 승인 관리까지 손쉽게 가능합니다.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Use Us Banner (Direct Link to About Page) -->
    <section class="py-5 bg-light-gray border-bottom">
        <div class="max-w-1200 px-3">
            <div class="reveal rounded-4 p-4 p-md-5 bg-deep-navy text-light d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 shadow-sm position-relative overflow-hidden">
                <div class="position-absolute end-0 top-0 w-50 h-100 grid-pattern-dark opacity-30 pointer-events-none"></div>
                <div class="position-relative z-1">
                    <span class="badge bg-brand-orange text-white mb-2 px-3 py-1 fs-8">도입 효과 심층 분석</span>
                    <h3 class="fw-bold fs-4 mb-2 text-light">왜 수많은 랙 시공·유통사가 이 솔루션을 선택했을까요?</h3>
                    <p class="text-light fs-7 mb-0 lh-base" style="color: rgba(255, 255, 255, 0.9) !important;">
                        기존 AutoCAD 수작업의 치명적 한계(인건비 월 200만원 낭비)와 정량적 ROI(월 193만 원 절감)를 상세 페이지에서 확인하세요.
                    </p>
                </div>
                <div class="position-relative z-1 flex-shrink-0">
                    <a href="/about" class="btn-pill btn-light text-brand-orange fw-bold px-4 py-3 fs-7 shadow">
                        서비스 소개 자세히 보기 <i class="fa-solid fa-arrow-right text-brand-orange ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section (Strictly 2 Plans: FREE & PRO) -->
    <section id="pricing" class="section-padding bg-white border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-720 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-orange px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-wallet"></i> 투명하고 단순한 요금제
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(28px, 4vw, 38px);">
                    복잡한 옵션 없이, <span class="text-brand-orange">딱 2가지</span> 플랜
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    신용카드 등록 없이 1개월 동안 200건의 견적을 무료로 경험해 보세요.<br>
                    본격적인 비즈니스 수주를 원하시면 무제한 PRO 플랜을 선택하세요.
                </p>
            </div>

            <div class="row g-4 justify-content-center mt-2 align-items-stretch">
                
                <!-- Plan 1: FREE -->
                <div class="col-lg-5 col-md-6">
                    <div class="reveal pricing-card-box h-100 bg-light p-4 p-xl-5 border d-flex flex-column justify-content-between shadow-sm">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-secondary rounded-pill px-3 py-1 fs-8 fw-bold">FREE 플랜</span>
                                <span class="text-muted fs-8">1달 무료 체험</span>
                            </div>
                            <h3 class="fw-bold text-deep-navy fs-3 mb-1">0원</h3>
                            <p class="text-muted fs-7 mb-4">솔루션 기능 검증 및 소규모 시공업체용</p>

                            <div class="py-3 border-top border-bottom mb-4">
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-success"></i> 총 200건 견적서 발행 제공
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-success"></i> 직원 계정 3명 등록
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-success"></i> 1달(30일) 무료 이용
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted fs-7 mb-2">
                                    <i class="fa-solid fa-check text-muted"></i> 2D Canvas 자동 랙 배치 엔진
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted fs-7 mb-2">
                                    <i class="fa-solid fa-check text-muted"></i> 자재 BOM 실시간 자동 산출
                                </div>
                                <div class="d-flex align-items-center gap-2 text-muted fs-7">
                                    <i class="fa-solid fa-check text-muted"></i> 표준 PDF 견적서 즉시 발행
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-outline-dark w-100 justify-content-center fs-7 py-3">
                                1개월 무료로 시작하기 <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="text-center text-muted fs-8 mt-2">신용카드 정보 입력 없음</div>
                        </div>
                    </div>
                </div>

                <!-- Plan 2: PRO (Featured) -->
                <div class="col-lg-5 col-md-6">
                    <div class="reveal pricing-card-box pricing-card-pro h-100 bg-white p-4 p-xl-5 border d-flex flex-column justify-content-between position-relative shadow-lg" style="transition-delay: 100ms;">
                        <div class="position-absolute top-0 start-50 translate-middle badge bg-brand-orange rounded-pill px-4 py-2 fs-8 fw-bold shadow">
                            ⭐ 가장 인기 있는 비즈니스 플랜
                        </div>

                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
                                <span class="badge bg-brand-orange bg-opacity-10 text-brand-orange rounded-pill px-3 py-1 fs-8 fw-bold">PRO 무제한 플랜</span>
                                <span class="badge bg-success bg-opacity-10 text-success fs-8">무제한 수주 지원</span>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h3 class="fw-bold text-deep-navy fs-2 mb-0">₩220,000</h3>
                                <span class="text-muted fs-7">/ 월 (VAT 별도)</span>
                            </div>
                            <p class="text-muted fs-7 mb-4">본격적인 수주 확대 및 견적 자동화를 원하는 전문 기업</p>

                            <div class="py-3 border-top border-bottom mb-4">
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-bold text-brand-orange">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 견적서 발행 무제한 (UNLIMITED)
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-bold">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 직원 등록 무제한 (팀원 전원 계정)
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 업체 맞춤 단가표 & 엑셀 일괄 연동
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 견적 잠금(Lock) 및 승인 관리 시스템
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 mb-2 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 귀사 전용 독립 견적 링크 & 홈페이지 임베드
                                </div>
                                <div class="d-flex align-items-center gap-2 text-dark fs-7 fw-semibold">
                                    <i class="fa-solid fa-circle-check text-brand-orange"></i> 24/7 우선 기술 지원 & 전담 온보딩
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=pro'; ?>" class="btn-pill btn-orange w-100 justify-content-center fs-7 py-3 shadow">
                                PRO 비즈니스 구독하기 <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <div class="text-center text-muted fs-8 mt-2">약정 없이 언제든 변경 및 해지 가능</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Value Comparison Note -->
            <div class="reveal mt-5 p-4 rounded-4 bg-light text-center max-w-720 mx-auto border">
                <div class="fs-7 text-deep-navy fw-semibold">
                    <i class="fa-solid fa-calculator text-brand-orange me-2"></i>
                    PRO 플랜(월 22만원)은 <span class="text-brand-orange fw-bold">견적 단 1건 작성 인건비</span>보다 저렴합니다.
                </div>
                <div class="fs-8 text-muted mt-1">
                    월 25건 견적 기준 약 193만 원의 순수 인건비 절감 효과를 즉시 경험하세요.
                </div>
            </div>
        </div>
    </section>

    <!-- 3-Step Simple Integration -->
    <section class="section-padding bg-light-gray border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-720 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-primary px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-link"></i> 초간편 3단계 연동
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    개발자 없이, 단 5분 만에 연동 끝
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    복잡한 설치 과정 없이 귀사 홈페이지나 블로그에 견적 버튼만 연결하면 끝납니다.
                </p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="reveal bg-white rounded-4 p-4 p-lg-5 text-center border h-100">
                        <div class="rounded-circle bg-deep-navy text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 56px; height: 56px; font-size: 1.25rem;">
                            1
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">무료 가입</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg mb-0">
                            이메일과 상호명 입력으로 1분 만에 가입 완료.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="reveal bg-white rounded-4 p-4 p-lg-5 text-center border h-100" style="transition-delay: 100ms;">
                        <div class="rounded-circle bg-brand-orange text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 56px; height: 56px; font-size: 1.25rem;">
                            2
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">전용 링크 발급</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg mb-0">
                            귀사 로고와 단가표가 적용된 고유 2D 캔버스 링크 자동 발급.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="reveal bg-white rounded-4 p-4 p-lg-5 text-center border h-100" style="transition-delay: 200ms;">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 56px; height: 56px; font-size: 1.25rem;">
                            3
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">홈페이지 버튼 연결</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg mb-0">
                            기존 홈페이지의 [견적 내기] 버튼에 링크를 걸면 24시간 자동 견적 작동!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="section-padding bg-deep-navy text-light text-center position-relative overflow-hidden">
        <div class="position-absolute w-100 h-100 top-0 start-0 grid-pattern-dark opacity-40"></div>
        <div class="max-w-720 position-relative z-1 py-4">
            <h2 class="reveal fw-bold tracking-tight text-light" style="font-size: clamp(28px, 4.5vw, 42px);">
                오늘부터 견적 공수 95%를<br>
                <span class="text-brand-orange">즉시 줄여보세요</span>
            </h2>
            <p class="reveal mt-3 fs-6 text-light lh-lg" style="color: rgba(255, 255, 255, 0.9) !important;">
                AutoCAD 없이 브라우저에서 끝내는 2D Canvas 자동설계.<br>
                1개월 무료 체험으로 200건의 실전 견적을 직접 검증하세요.
            </p>
            <div class="reveal mt-5 d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-orange shadow-lg fs-6 py-3 px-4">
                    1개월 무료 체험 시작하기 <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="https://cmake.work/quote/2" target="_blank" class="btn-pill btn-outline-light text-light border-white border-opacity-50 fs-6 py-3 px-4">
                    <i class="fa-solid fa-play text-brand-orange"></i> 실시간 데모 보기
                </a>
            </div>
        </div>
    </section>

    <!-- Custom Website Development Banner (Above Footer) -->
    <section class="py-5 bg-white border-top border-bottom">
        <div class="max-w-1200 px-3">
            <div class="reveal rounded-4 p-4 p-lg-5 bg-light-gray border d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 shadow-sm position-relative overflow-hidden" style="border-color: #cbd5e1 !important;">
                <div class="position-relative z-1" style="max-width: 760px;">
                    <div class="d-inline-flex align-items-center gap-2 rounded-pill mb-3 px-3 py-1 fs-8 fw-bold" style="background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;">
                        <i class="fa-solid fa-code"></i> 기업 맞춤형 웹 & ERP 시스템 개발
                    </div>
                    <h3 class="fw-bold text-deep-navy mb-2" style="font-size: clamp(22px, 3.5vw, 30px); line-height: 1.3;">
                        회사 홈페이지부터 쇼핑몰, 사내 ERP까지<br class="d-none d-sm-block">
                        <span class="text-brand-orange">원하시는 모든 웹 시스템을 맞춤 제작</span>해 드립니다
                    </h3>
                    <p class="text-muted fs-6 mb-0 lh-base mt-2">
                        파렛트랙 2D 자동견적 연동은 물론, 기업 홍보용 웹사이트, 온라인 쇼핑몰, 재고/정산/주문 관리 사내 ERP 시스템까지 비즈니스 목적에 맞춰 완벽하게 개발해 드립니다.
                    </p>
                </div>
                <div class="position-relative z-1 flex-shrink-0">
                    <a href="/website" class="btn-pill btn-orange text-white fw-bold px-4 py-3 fs-6 shadow-sm">
                        홈페이지 & 시스템 제작 문의 <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const revealElements = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) {
                entry.target.classList.add('in-view');
            }
        });
    }, { threshold: 0.15 });

    revealElements.forEach(el => observer.observe(el));
});
</script>

<?php include_footer($siteConfig ?? []); ?>
