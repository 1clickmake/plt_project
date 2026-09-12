<?php 
$title = '서비스 소개 - 물류 파렛트랙 B2B SaaS 자동설계 솔루션'; 
include_header($title, $siteConfig ?? []); 
?>
<script>
    document.documentElement.setAttribute('data-bs-theme', 'light');
</script>
<style>
.about-wrapper {
    font-family: 'Inter', 'Noto Sans KR', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #1e293b;
    background-color: #ffffff;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}

.about-wrapper .bg-brand-orange { background-color: #f16819 !important; }
.about-wrapper .text-brand-orange { color: #f16819 !important; }
.about-wrapper .bg-light-gray { background-color: #f8fafc !important; }
.about-wrapper .bg-deep-navy { background-color: #0f172a !important; }
.about-wrapper .text-deep-navy { color: #0f172a !important; }

/* 🌟 어두운 배경 내부 텍스트 밝은 색상 보장 */
.about-wrapper .bg-deep-navy,
.about-wrapper .bg-deep-navy h1,
.about-wrapper .bg-deep-navy h2,
.about-wrapper .bg-deep-navy h3,
.about-wrapper .bg-deep-navy p,
.about-wrapper .bg-deep-navy span:not(.badge):not(.text-brand-orange),
.about-wrapper .bg-deep-navy .text-light,
.about-wrapper .bg-deep-navy .btn-outline-light {
    color: #ffffff !important;
}
.about-wrapper .text-light {
    color: #ffffff !important;
}
.about-wrapper .bg-deep-navy p {
    color: rgba(255, 255, 255, 0.9) !important;
}

.about-wrapper .section-padding { padding: 5rem 1.5rem; }
@media (min-width: 768px) {
    .about-wrapper .section-padding { padding: 6.5rem 2rem; }
}

.about-wrapper .max-w-1200 { max-width: 1200px; margin: 0 auto; }
.about-wrapper .max-w-800 { max-width: 800px; margin: 0 auto; }

.about-wrapper .reveal { opacity: 0; transform: translateY(24px); transition: all 0.7s cubic-bezier(.16,1,.3,1); }
.about-wrapper .reveal.in-view { opacity: 1; transform: translateY(0); }

.about-wrapper .btn-pill {
    border-radius: 50rem;
    padding: 0.65rem 1.75rem;
    font-weight: 600;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.about-wrapper .btn-pill:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
.about-wrapper .btn-orange { background: #f16819; color: #fff; border: none; }
.about-wrapper .btn-orange:hover { background: #e05300; color: #fff; }
.about-wrapper .btn-navy { background: #0f172a; color: #fff; }
.about-wrapper .btn-navy:hover { background: #1e293b; color: #fff; }

.about-wrapper .badge-soft-primary { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
.about-wrapper .badge-soft-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.about-wrapper .badge-soft-orange { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }

.about-wrapper .feature-card {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid #e2e8f0;
    background: #ffffff;
}
.about-wrapper .feature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

.about-wrapper .grid-pattern-dark {
    background-image:
    linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 32px 32px;
}
</style>

<div class="about-wrapper">

    <!-- Hero Header -->
    <section class="position-relative bg-deep-navy text-light overflow-hidden py-5" style="padding-top: 6rem !important;">
        <div class="position-absolute w-100 h-100 top-0 start-0 grid-pattern-dark opacity-50"></div>
        <div class="position-absolute start-50 translate-middle-x" style="top:-150px; width: 800px; height: 500px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(241,104,25,0.25), transparent 70%); filter: blur(30px);"></div>
        
        <div class="max-w-1200 section-padding position-relative z-1 text-center">
            <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-orange px-3 py-1 fs-7 fw-bold mb-3">
                <i class="fa-solid fa-sparkles"></i> 솔루션 도입 가이드 & 상세 소개
            </div>
            <h1 class="reveal mt-3 fw-bold tracking-tight" style="font-size: clamp(32px, 5vw, 48px); line-height: 1.25;">
                왜 전국의 파렛트랙 공급사는<br>
                <span class="text-brand-orange">자동설계 B2B SaaS</span>를 선택할까요?
            </h1>
            <p class="reveal mt-4 fs-5 text-light text-opacity-75 max-w-800 lh-lg">
                수작업 AutoCAD 도면 작업과 엑셀 계산으로 낭비되던 견적 시간 3시간을 <strong class="text-warning">단 5분</strong>으로 단축합니다.<br class="d-none d-md-block">
                오발주율 0%, 실시간 수주 전환율 극대화의 비결을 확인하세요.
            </p>
            <div class="reveal mt-5 d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-orange shadow-lg">1개월 무료 체험하기 <i class="fa-solid fa-arrow-right"></i></a>
                <a href="https://cmake.work/quote/2" target="_blank" class="btn-pill btn-outline-light text-light border-white border-opacity-50"><i class="fa-solid fa-play"></i> 실시간 2D 데모 보기</a>
            </div>
        </div>
    </section>

    <!-- 1. The Problem: AS-IS Market Pain -->
    <section class="section-padding bg-white border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-800 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-danger px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> AS-IS : 기존 수작업 견적의 치명적 한계
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    아직도 도면 1장 그리는데 <span class="text-danger">반나절</span>을 쓰고 계십니까?
                </h2>
                <p class="reveal mt-3 text-muted fs-6 lh-base">
                    고객은 빠른 견적을 원하지만, 전문 인력 부재와 복잡한 CAD 계산 때문에 수많은 수주 기회가 경쟁사로 넘어가고 있습니다.
                </p>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5">
                        <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center mb-4" style="width: 48px; height: 48px;">
                            <i class="fa-regular fa-clock fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">막대한 시간 & 인건비 낭비</h4>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            건당 1.5~3시간 소요되는 AutoCAD 및 엑셀 수작업. 월 25건만 작성해도 인건비 200만 원 이상이 순수 견적 작업에만 허비됩니다.
                        </p>
                        <div class="badge rounded-pill bg-light text-dark border px-3 py-2 mt-2 fs-8 fw-semibold">월 50시간 이상 업무 소모</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5" style="transition-delay: 100ms;">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center mb-4" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-user-xmark fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">견적 지연으로 고객 이탈</h4>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            문의 접수 후 견적서 발송까지 평균 1~3일 소요. 즉시 답변을 원하는 고객은 먼저 빠른 견적을 제시한 업체로 이탈하여 수주율이 30% 이상 급락합니다.
                        </p>
                        <div class="badge rounded-pill bg-light text-dark border px-3 py-2 mt-2 fs-8 fw-semibold">고객 수주 전환율 -30%</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5" style="transition-delay: 200ms;">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-4" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-calculator fs-4"></i>
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">자재 누락 및 오발주 손실</h4>
                        <p class="text-muted fs-7 mt-3 lh-lg">
                            기둥수, 로드빔, 타이빔, 앙카 수량 수동 계산 시 오류율 5~10% 발생. 현장 설치 시 자재 부족으로 인한 재출장, 화물 운임 낭비가 연 수백만 원에 이릅니다.
                        </p>
                        <div class="badge rounded-pill bg-light text-dark border px-3 py-2 mt-2 fs-8 fw-semibold">연 300~500만 원 직간접 손실</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. The Solution: Core Features -->
    <section class="section-padding bg-light-gray border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-800 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-primary px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> TO-BE : 올인원 파렛트랙 솔루션
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    웹 브라우저에서 끝내는 <span class="text-primary">4대 혁신 기능</span>
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    프로그램 설치 없이, 스마트폰이나 태블릿, PC 어디서든 즉시 작동합니다.
                </p>
            </div>

            <div class="row g-4 mt-2">
                <!-- Feature 1 -->
                <div class="col-lg-6">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-brand-orange rounded-pill px-3 py-1 fs-8">FEATURE 01</span>
                                <i class="fa-solid fa-drafting-compass text-brand-orange fs-4"></i>
                            </div>
                            <h3 class="fw-bold text-deep-navy fs-4">2D Canvas 웹 직관 설계</h3>
                            <p class="text-muted fs-6 mt-3 lh-lg">
                                복잡한 CAD 소프트웨어 없이 창고 가로/세로 규격만 입력하면 도면이 즉시 생성됩니다. 기둥, 셔터, 출입문 등의 장애물도 마우스 드래그로 자유롭게 배치할 수 있습니다.
                            </p>
                        </div>
                        <ul class="list-unstyled text-muted fs-7 mt-4 pt-3 border-top mb-0">
                            <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> 실시간 치수 스냅 및 비율 가이드 자동 계산</li>
                            <li><i class="fa-solid fa-circle-check text-success me-2"></i> 장애물 충돌 감지 및 통로 안전거리 확보 경고</li>
                        </ul>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-lg-6">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-primary rounded-pill px-3 py-1 fs-8">FEATURE 02</span>
                                <i class="fa-solid fa-bolt text-primary fs-4"></i>
                            </div>
                            <h3 class="fw-bold text-deep-navy fs-4">3초 자동 랙 배치 엔진</h3>
                            <p class="text-muted fs-6 mt-3 lh-lg">
                                표준 파렛트 규격(1100×1100, 1200×1000) 및 단수, 지게차 통로폭(3,000mm)을 선택하면 알고리즘이 창고 공간 효율을 극대화하는 최적 배치를 3초 만에 완료합니다.
                            </p>
                        </div>
                        <ul class="list-unstyled text-muted fs-7 mt-4 pt-3 border-top mb-0">
                            <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> 단열(Single) / 복열(Double) 자동 분할 배치</li>
                            <li><i class="fa-solid fa-circle-check text-success me-2"></i> 적재 효율 및 수용 파렛트 수량 즉시 디스플레이</li>
                        </ul>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-lg-6">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-dark rounded-pill px-3 py-1 fs-8">FEATURE 03</span>
                                <i class="fa-solid fa-list-check text-dark fs-4"></i>
                            </div>
                            <h3 class="fw-bold text-deep-navy fs-4">100% 정밀 BOM 자동 산출</h3>
                            <p class="text-muted fs-6 mt-3 lh-lg">
                                배치된 도면을 기반으로 독립형(주기둥 2조), 연결형(주기둥 1조), 로드빔 단수별 수량, 타이빔, 앙카볼트 및 라이너까지 공학적 계산 공식으로 오차 없이 산출합니다.
                            </p>
                        </div>
                        <ul class="list-unstyled text-muted fs-7 mt-4 pt-3 border-top mb-0">
                            <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> 자재 누락 및 잉여 발주 오차 0% 달성</li>
                            <li><i class="fa-solid fa-circle-check text-success me-2"></i> 업체 전용 단가표 DB와 연동되어 자동 금액 계산</li>
                        </ul>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-lg-6">
                    <div class="reveal feature-card h-100 rounded-4 p-4 p-lg-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-danger rounded-pill px-3 py-1 fs-8">FEATURE 04</span>
                                <i class="fa-solid fa-file-invoice-dollar text-danger fs-4"></i>
                            </div>
                            <h3 class="fw-bold text-deep-navy fs-4">원클릭 PDF 견적서 & 승인</h3>
                            <p class="text-muted fs-6 mt-3 lh-lg">
                                귀사의 로고, 직인, 계좌번호 및 거래조건이 포함된 정식 전자 견적서를 즉시 발행합니다. 고객용 다운로드 링크 공유 및 견적서 상태(승인/반려/잠금) 관리까지 완벽 지원합니다.
                            </p>
                        </div>
                        <ul class="list-unstyled text-muted fs-7 mt-4 pt-3 border-top mb-0">
                            <li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> 2D 배치 도면 + 세부 내역서 통합 PDF 출력</li>
                            <li><i class="fa-solid fa-circle-check text-success me-2"></i> 이메일 원클릭 발송 및 인쇄 최적화 포맷</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Easy 3-Step Setup -->
    <section class="section-padding bg-white border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-800 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-orange px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-plug"></i> 초간편 3단계 연동
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    개발자 없이, 단 5분 만에 연동 완료
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    복잡한 시스템 구축 비용 없이, 기존 운영 중인 블로그나 홈페이지에 바로 적용할 수 있습니다.
                </p>
            </div>

            <div class="row g-4 mt-2 position-relative">
                <div class="col-md-4">
                    <div class="reveal text-center p-4">
                        <div class="rounded-circle bg-deep-navy text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            1
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">간편 가입 & 환경 설정</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg">
                            이메일과 기본 정보로 1분 만에 가입하고, 자사 상호명과 로고, 기본 단가표를 등록합니다.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal text-center p-4" style="transition-delay: 100ms;">
                        <div class="rounded-circle bg-brand-orange text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            2
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">고유 견적 링크 자동 생성</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg">
                            귀사만의 고유한 2D 캔버스 웹 주소(URL)와 홈페이지 삽입용 임베드 코드가 즉시 생성됩니다.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal text-center p-4" style="transition-delay: 200ms;">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4 shadow" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            3
                        </div>
                        <h4 class="fw-bold text-deep-navy fs-5">홈페이지 버튼 연결 끝!</h4>
                        <p class="text-muted fs-7 mt-2 lh-lg">
                            홈페이지의 [실시간 견적 내기] 버튼에 발급된 주소를 연결하면 24시간 자동 견적 시스템 완성!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Quantified ROI Table -->
    <section class="section-padding bg-light-gray border-bottom">
        <div class="max-w-1200">
            <div class="text-center max-w-800 mb-5">
                <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-primary px-3 py-1 fs-7 fw-bold mb-2">
                    <i class="fa-solid fa-arrow-trend-up"></i> 정량적 기대 효과 & ROI
                </div>
                <h2 class="reveal mt-3 fw-bold text-deep-navy" style="font-size: clamp(26px, 4vw, 36px);">
                    도입 첫 달부터 비용을 회수하는 압도적 수익성
                </h2>
                <p class="reveal mt-3 text-muted fs-6">
                    월 220,000원의 구독료로 월 193만 원 이상의 순수 인건비를 아끼고 수주를 늘립니다.
                </p>
            </div>

            <div class="reveal rounded-4 border bg-white shadow-sm overflow-hidden mt-4">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-deep-navy text-white fs-7">
                            <tr>
                                <th class="py-3 px-4 fw-semibold w-25">비교 지표</th>
                                <th class="py-3 px-4 fw-semibold text-danger">기존 방식 (AS-IS)</th>
                                <th class="py-3 px-4 fw-semibold text-success">솔루션 도입 (TO-BE)</th>
                                <th class="py-3 px-4 fw-semibold text-brand-orange">개선 효과</th>
                            </tr>
                        </thead>
                        <tbody class="fs-6">
                            <tr>
                                <td class="py-4 px-4 fw-bold text-deep-navy"><i class="fa-regular fa-clock text-primary me-2"></i> 건당 견적 소요시간</td>
                                <td class="py-4 px-4 text-muted"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">1.5 ~ 3시간</span></td>
                                <td class="py-4 px-4"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">5분 이내</span></td>
                                <td class="py-4 px-4 fw-bold text-brand-orange">95% 시간 단축</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-4 fw-bold text-deep-navy"><i class="fa-solid fa-coins text-warning me-2"></i> 월간 견적 인건비 (25건 기준)</td>
                                <td class="py-4 px-4 text-muted"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">약 200만 원</span></td>
                                <td class="py-4 px-4"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">약 6.5만 원</span></td>
                                <td class="py-4 px-4 fw-bold text-success">월 193.5만 원 절감 (연 2,320만 원)</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-4 fw-bold text-deep-navy"><i class="fa-solid fa-bullseye text-danger me-2"></i> 고객 견적 응대 리드타임</td>
                                <td class="py-4 px-4 text-muted"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">1 ~ 3일 대기</span></td>
                                <td class="py-4 px-4"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">10분 내 발송</span></td>
                                <td class="py-4 px-4 fw-bold text-primary">수주 전환율 15~30% 상승</td>
                            </tr>
                            <tr>
                                <td class="py-4 px-4 fw-bold text-deep-navy"><i class="fa-solid fa-shield-halved text-info me-2"></i> 설계 및 발주 오류율</td>
                                <td class="py-4 px-4 text-muted"><span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2">5 ~ 10%</span></td>
                                <td class="py-4 px-4"><span class="badge bg-success bg-opacity-10 text-success px-3 py-2">0% (자동 계산)</span></td>
                                <td class="py-4 px-4 fw-bold text-success">연 300~500만 원 손실 방지</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="reveal mt-4 p-3 rounded-3 bg-white border text-center fs-7 text-muted">
                <i class="fa-solid fa-circle-info text-brand-orange me-1"></i> 시급 3만 원 기준 인건비 및 재출장·화물 운임 손실 방지를 종합적으로 반영한 실제 검증 수치입니다.
            </div>
        </div>
    </section>

    <!-- 5. Bottom CTA -->
    <section class="section-padding bg-deep-navy text-white text-center position-relative overflow-hidden">
        <div class="position-absolute w-100 h-100 top-0 start-0 grid-pattern-dark opacity-40"></div>
        <div class="max-w-800 position-relative z-1 py-4">
            <h2 class="reveal fw-bold tracking-tight" style="font-size: clamp(28px, 4.5vw, 40px);">
                지금 바로 귀사만의<br>
                <span class="text-brand-orange">자동 견적 시스템</span>을 구축하세요
            </h2>
            <p class="reveal mt-3 fs-6 text-light text-opacity-75 lh-lg">
                1개월 동안 총 200건의 견적서를 무료로 발행해 보실 수 있습니다.<br>
                신용카드 등록 없이, 1분 만에 시작하세요.
            </p>
            <div class="reveal mt-5 d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-orange shadow-lg" style="font-size: 1.05rem; padding: 0.8rem 2.2rem;">무료 체험 시작하기 <i class="fa-solid fa-arrow-right"></i></a>
                <a href="/#pricing" class="btn-pill btn-outline-light text-light border-white border-opacity-50" style="font-size: 1.05rem; padding: 0.8rem 2.2rem;">요금제 비교하기</a>
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
