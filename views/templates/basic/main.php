<?php $title = '물류 파렛트랙 B2B SaaS'; include_header($title, $siteConfig); ?>
<script>
    // 페이지 로드 시 라이트 모드 강제 적용 (랜딩 전용)
    document.documentElement.setAttribute('data-bs-theme', 'light');
</script>
<style>
/* 🎨 커스텀 CSS - Premium SaaS Landing Page */
.landing-wrapper {
    font-family: 'Inter', 'Noto Sans KR', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #1e293b;
    background-color: #ffffff;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
    padding-top: 0;
}

/* Custom Colors & Utilities */
.landing-wrapper .bg-brand-orange { background-color: #f16819 !important; }
.landing-wrapper .text-brand-orange { color: #f16819 !important; }
.landing-wrapper .bg-light-gray { background-color: #f4f6f8 !important; }
.landing-wrapper .bg-deep-navy { background-color: #0f172a !important; }
.landing-wrapper .text-deep-navy { color: #0f172a !important; }
.landing-wrapper .btn-orange { background: #f16819; color: #fff; border: none; }
.landing-wrapper .btn-orange:hover { background: #e05300; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(241,104,25,0.3); }

.landing-wrapper .text-dark-blue { color: #0B1733; }
.landing-wrapper .text-deep-navy { color: #1e293b; }
.landing-wrapper .text-primary-blue { color: #2A5BDA; }
.landing-wrapper .text-light-blue { color: #62D6FF; }
.landing-wrapper .bg-dark-blue { background-color: #0B1733; }
.landing-wrapper .bg-navy { background-color: #0F1E3D; }
.landing-wrapper .bg-black { background-color: #0B1120 !important; }
.landing-wrapper .bg-primary-blue { background-color: #2A5BDA; }
.landing-wrapper .bg-light-blue { background-color: #62D6FF; }
.landing-wrapper .bg-dark-surface { background-color: #111827; }

.landing-wrapper .text-deep-navy-45 { color: rgba(255,255,255,0.45); }
.landing-wrapper .text-deep-navy-50 { color: rgba(255,255,255,0.50); }
.landing-wrapper .text-deep-navy-60 { color: rgba(255,255,255,0.60); }
.landing-wrapper .text-deep-navy-70 { color: rgba(255,255,255,0.70); }
.landing-wrapper .text-deep-navy-80 { color: rgba(255,255,255,0.80); }
.landing-wrapper .text-deep-navy-90 { color: rgba(255,255,255,0.90); }

.landing-wrapper .border-white-10 { border-color: rgba(255,255,255,0.1) !important; }
.landing-wrapper .border-white-15 { border-color: rgba(255,255,255,0.15) !important; }

/* Gradients */
.landing-wrapper .bg-gradient-primary {
    background: linear-gradient(to right, #62D6FF, #2A5BDA);
}
.landing-wrapper .text-gradient-primary {
    background: linear-gradient(to right, #62D6FF, #2A5BDA);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.landing-wrapper .text-gradient-navy-blue {
    background: linear-gradient(to right, #2A5BDA, #62D6FF);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.landing-wrapper .bg-gradient-popular {
    background: linear-gradient(to bottom right, #2A5BDA, #62D6FF);
}

/* Patterns */
.landing-wrapper .grid-pattern {
    background-image:
    linear-gradient(rgba(15,30,61,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15,30,61,0.04) 1px, transparent 1px);
    background-size: 32px 32px;
}
.landing-wrapper .grid-pattern-dark {
    background-image:
    linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
    background-size: 36px 36px;
}

/* Glass & Effects */
.landing-wrapper .glass {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}
.landing-wrapper .glow-blue {
    box-shadow: 0 0 12px rgba(98,214,255,0.8);
}
.landing-wrapper .shadow-soft {
    box-shadow: 0 8px 32px rgba(15,30,61,0.06);
}
.landing-wrapper .shadow-heavy {
    box-shadow: 0 24px 80px rgba(0,0,0,0.35), inset 0 0 0 1px rgba(255,255,255,0.2);
}

/* Base Sections */
.landing-wrapper .section-padding { padding: 5rem 1rem; }
@media (min-width: 768px) {
    .landing-wrapper .section-padding { padding: 7rem 1.5rem; }
}

.landing-wrapper .max-w-1200 { max-width: 1200px; margin: 0 auto; }
.landing-wrapper .max-w-720 { max-width: 720px; }

/* Reveal Animation */
.landing-wrapper .reveal { opacity: 0; transform: translateY(24px); transition: all 0.7s cubic-bezier(.16,1,.3,1); }
.landing-wrapper .reveal.in-view { opacity: 1; transform: translateY(0); }

/* Buttons */
.landing-wrapper .btn-pill {
    border-radius: 50rem;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.landing-wrapper .btn-pill:hover { transform: translateY(-1px); }
.landing-wrapper .btn-white { background: #fff; color: #0B1733; }
.landing-wrapper .btn-white:hover { background: rgba(255,255,255,0.9); }
.landing-wrapper .btn-navy { background: #0F1E3D; color: #fff; }
.landing-wrapper .btn-navy:hover { background: rgba(15,30,61,0.9); }
.landing-wrapper .btn-light { background: #F1F5F9; color: #1e293b; border: 1px solid rgba(0,0,0,0.05); }
.landing-wrapper .btn-light:hover { background: #E8EEF6; }

/* Custom Badge */
.landing-wrapper .badge-soft-primary { background: #E8F0FF; color: #2A5BDA; border: 1px solid #C7D9FF; }
.landing-wrapper .badge-soft-danger { background: #FFF0F0; color: #C0392B; border: 1px solid #FFD5D5; }
.landing-wrapper .badge-glass { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: #fff; }

.landing-wrapper .fs-7 { font-size: 0.85rem; }
.landing-wrapper .fs-8 { font-size: 0.75rem; }
.landing-wrapper .fs-9 { font-size: 0.65rem; }
.landing-wrapper .fw-extrabold { font-weight: 800; }
.landing-wrapper .fw-black { font-weight: 900; }
.landing-wrapper .tracking-tight { letter-spacing: -0.03em; }
.landing-wrapper .tracking-widest { letter-spacing: 0.1em; }

/* Demo UI elements */
.landing-wrapper .demo-canvas-wrap {
    transform: rotate(-0.6deg);
    border-radius: 20px;
    background: #fff;
    padding: 10px;
}
.landing-wrapper .demo-canvas {
    border-radius: 14px;
    overflow: hidden;
    background: #F6F8FB;
    border: 1px solid rgba(15,30,61,0.06);
}

/* 덮어쓰기 방지용 */
body { background-color: #0B1120 !important; }
</style>

<div class="landing-wrapper">
    <!-- Hero Section -->
    <section class="position-relative bg-deep-navy text-deep-navy overflow-hidden py-5" style="background-image: url('/assets/images/canvas_demo.png'); background-size: cover; background-position: center;">
        <div class="position-absolute w-100 h-100 top-0 start-0 bg-deep-navy" style="opacity: 0.85;"></div>
        <div class="position-absolute w-100 h-100 top-0 start-0 pointer-events-none">
            <div class="position-absolute start-50 translate-middle-x" style="top:-300px; width: min(1200px, 100vw); height: 800px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(241,104,25,0.35), transparent 60%); filter: blur(20px);"></div>
            <div class="position-absolute" style="top:120px; right:-100px; width: min(600px, 80vw); height: 600px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(98,214,255,0.18), transparent 60%);"></div>
            <div class="position-absolute w-100 h-100 grid-pattern-dark opacity-50"></div>
        </div>
        
        <div class="max-w-1200 section-padding position-relative z-1">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    
                    
                    <h1 class="mt-4 fw-extrabold tracking-tight text-light" style="font-size: clamp(32px, 5vw, 46px); line-height: 1.1;">
                        물류 파렛트랙<br>
                        <span class="text-light">자동설계 및 실시간 견적</span><br>
                        <span class="text-brand-orange">B2B SaaS</span>
                    </h1>
                    
                    <p class="mt-4 fs-6 text-deep-navy-70 lh-lg" style="max-width: 520px;">
                        1.5~3시간 걸리던 견적을 <span class="text-brand-orange fw-bold">5분 안에</span> CAD 없이 브라우저에서 끝내는 2D Canvas 자동설계. 도면·BOM·PDF 견적서 원클릭 발행.
                    </p>
                    
                    <div class="mt-5 d-flex flex-wrap gap-3">
                        <a href="#pricing" class="btn-pill btn-orange shadow">무료로 시작하기 <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="https://cmake.work/quote/demo" target="_blank" class="btn-pill badge-glass"><span class="d-flex align-items-center justify-content-center btn-orange rounded-circle" style="width:28px;height:28px;"><i class="fa-solid fa-play fs-8 text-light"></i></span> 견적신청데모</a>
                    </div>
                    
                    <div class="mt-5 d-flex align-items-center gap-4 fs-8 text-deep-navy-50">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex" style="margin-left: 10px;">
                                <div class="rounded-circle border border-dark bg-dark-surface opacity-75" style="width:24px;height:24px; margin-left:-10px;"></div>
                                <div class="rounded-circle border border-dark bg-dark-surface opacity-50" style="width:24px;height:24px; margin-left:-10px;"></div>
                                <div class="rounded-circle border border-dark bg-dark-surface opacity-25" style="width:24px;height:24px; margin-left:-10px;"></div>
                            </div>
                            <span class="text-deep-navy-70">50+ 공급업체 검증</span>
                        </div>
                        <div style="width:1px; height:12px; background: rgba(255,255,255,0.15);"></div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-brand-orange"></i>
                            <span class="text-deep-navy-70">오류율 0% 엔진</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 position-relative">
                    <div class="demo-canvas-wrap shadow-heavy">
                        <div class="demo-canvas">
                            <div class="d-flex align-items-center justify-content-between bg-dark-surface border-bottom px-3 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex gap-1">
                                        <div class="rounded-circle" style="width:10px;height:10px;background:#FF5F56;"></div>
                                        <div class="rounded-circle" style="width:10px;height:10px;background:#FFBD2E;"></div>
                                        <div class="rounded-circle" style="width:10px;height:10px;background:#27C93F;"></div>
                                    </div>
                                    <span class="ms-2 fs-8 fw-bold text-deep-navy-70">WAREHOUSE CANVAS • 32.5m x 18.2m</span>
                                </div>
                                <div class="d-flex gap-2 fs-9">
                                    <span class="badge rounded-pill bg-navy fw-normal"><i class="fa-solid fa-microchip"></i> Auto 배치</span>
                                    <span class="badge rounded-pill bg-brand-orange fw-normal">3.2s 연산</span>
                                </div>
                            </div>
                            <div class="position-relative bg-white shadow-sm p-3" style="aspect-ratio: 1.65/1;">
                                <div class="w-100 h-100 rounded-3 bg-dark-surface border position-relative overflow-hidden grid-pattern">
                                    <!-- Mockup elements -->
                                    <div class="position-absolute rounded-1 bg-navy" style="width:16px;height:16px;left:18%;top:18%;"></div>
                                    <div class="position-absolute rounded-1 bg-navy" style="width:16px;height:16px;left:18%;bottom:18%;"></div>
                                    <div class="position-absolute rounded-1 bg-navy" style="width:16px;height:16px;right:28%;top:18%;"></div>
                                    <div class="position-absolute d-flex gap-2" style="bottom:8px; left:50%; transform:translateX(-50%);">
                                        <div class="rounded-pill" style="width:60px; height:8px; background:rgba(255,189,46,0.8);"></div>
                                        <div class="rounded-pill" style="width:60px; height:8px; background:rgba(255,189,46,0.8);"></div>
                                    </div>
                                    <!-- Mockup Racks -->
                                    <div class="position-absolute w-100 h-100 p-4">
                                        <div class="row g-1 h-100">
                                            <?php for($i=0; $i<18; $i++): ?>
                                                <?php if($i%3==2): ?>
                                                    <div class="col-2 d-flex align-items-center"><div class="w-100 border-top border-primary opacity-25" style="border-top-style:dashed!important;"></div></div>
                                                <?php else: ?>
                                                    <div class="col-2"><div class="w-100 h-100 rounded-1 border" style="background:#DCE6FF; border-color:rgba(42,91,218,0.2);"></div></div>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Floating Panels -->
                                <div class="position-absolute rounded-3 bg-navy text-deep-navy p-2 shadow" style="bottom:20px; left:25px; width: 140px;">
                                    <div class="fs-9 tracking-widest text-deep-navy-50 fw-bold">BOM AUTO</div>
                                    <div class="mt-1 fs-8">
                                        <div class="d-flex justify-content-between"><span class="text-deep-navy-60">주기둥</span><span class="fw-bold">48 EA</span></div>
                                        <div class="d-flex justify-content-between"><span class="text-deep-navy-60">로드빔</span><span class="fw-bold">96 EA</span></div>
                                        <div class="d-flex justify-content-between"><span class="text-deep-navy-60">타이빔</span><span class="fw-bold">36 EA</span></div>
                                        <div class="d-flex justify-content-between"><span class="text-deep-navy-60">앙카</span><span class="fw-bold">192 EA</span></div>
                                    </div>
                                </div>
                                <div class="position-absolute rounded-pill bg-dark-surface border shadow-sm px-3 py-2 d-flex align-items-center gap-2" style="top:40px; right:15px;">
                                    <i class="fa-regular fa-file-pdf text-primary-blue"></i>
                                    <span class="fs-8 fw-bold text-deep-navy">PDF 견적서 • 5분 발행</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section id="problem" class="bg-black border-top border-white-10">
        <div class="max-w-1200 section-padding">
            <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-danger px-3 py-1 fs-8 fw-bold">
                <i class="fa-solid fa-triangle-exclamation"></i> AS-IS 시장의 치명적 비효율
            </div>
            <h2 class="reveal mt-4 fw-extrabold tracking-tight text-light" style="font-size: clamp(24px, 4vw, 36px); line-height: 1.2;">
                왜 아직도 <span style="color:#C0392B;">AutoCAD</span>로 3시간을 쓰나요?
            </h2>
            <p class="reveal mt-3 fs-6 text-deep-navy-60 lh-lg" style="max-width: 640px;">
                전국 460~1,540건의 월간 견적 기회가 수작업·지연·오발주로 사라집니다. 공급업체는 인건비만 월 200만원 이상을 태우고 있습니다.
            </p>
            
            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 p-1" style="background: linear-gradient(to bottom right, #FFF5F5, #FFE9E9);">
                        <div class="rounded-4 bg-dark-surface p-4 h-100">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px; background:#FFE2E2; color:#C0392B;">
                                <i class="fa-regular fa-clock fs-5"></i>
                            </div>
                            <h3 class="mt-4 fs-5 fw-bold tracking-tight text-light">막대한 견적 공수 & 인건비 낭비</h3>
                            <p class="mt-2 fs-7 text-deep-navy-60 lh-base">1건당 1~3시간, AutoCAD + BOM 수작업</p>
                            <div class="mt-3 d-inline-flex rounded-pill bg-navy text-light fs-8 fw-bold px-3 py-1">월 25건 기준 200만원 이상 소모</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 p-1" style="background: linear-gradient(to bottom right, #FFF8F0, #FFEDD5); transition-delay: 100ms;">
                        <div class="rounded-4 bg-dark-surface p-4 h-100">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px; background:#FFE6CC; color:#D35400;">
                                <i class="fa-solid fa-users-slash fs-5"></i>
                            </div>
                            <h3 class="mt-4 fs-5 fw-bold tracking-tight text-light">견적 지연으로 인한 고객 이탈</h3>
                            <p class="mt-2 fs-7 text-deep-navy-60 lh-base">1~3일 소요, 실시간 응대 불가</p>
                            <div class="mt-3 d-inline-flex rounded-pill bg-navy text-light fs-8 fw-bold px-3 py-1">수주 전환율 -30% 하락</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 p-1" style="background: linear-gradient(to bottom right, #FFF7E6, #FFEECC); transition-delay: 200ms;">
                        <div class="rounded-4 bg-dark-surface p-4 h-100">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width:40px;height:40px; background:#FFE9B8; color:#B7791F;">
                                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                            </div>
                            <h3 class="mt-4 fs-5 fw-bold tracking-tight text-light">오발주 및 자재 누락 손실</h3>
                            <p class="mt-2 fs-7 text-deep-navy-60 lh-base">오류율 5~10%, 재출장/운임 낭비</p>
                            <div class="mt-3 d-inline-flex rounded-pill bg-navy text-light fs-8 fw-bold px-3 py-1">연 300~500만원 손실</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Easy Installation Section -->
    <section class="bg-light-gray border-top border-white-10 py-5">
        <div class="max-w-1200 section-padding py-5">
            <div class="reveal text-center max-w-720 mx-auto">
                <div class="d-inline-flex align-items-center gap-2 rounded-pill bg-navy text-light px-3 py-1 fs-8 fw-bold mb-3">
                    <i class="fa-solid fa-link"></i> 초간편 연동
                </div>
                <h2 class="fw-extrabold tracking-tight text-deep-navy" style="font-size: clamp(24px, 4vw, 36px); line-height: 1.2;">
                    개발자 없이, <span class="text-brand-orange">간단하게</span><br>우리 회사 홈페이지에 탑재하세요
                </h2>
                <p class="mt-3 fs-6 text-secondary lh-lg">
                    복잡한 설치나 코딩이 전혀 필요 없습니다.<br>발급받은 링크를 기존 홈페이지 버튼에 연결하기만 하면 끝납니다.
                </p>
            </div>
            
            <div class="row g-4 mt-5 position-relative">
                <!-- Connecting Line (PC Only) -->
                <div class="d-none d-lg-block position-absolute top-50 start-50 translate-middle-y z-0" style="width: 70%; height: 2px; border-top: dashed 2px #cbd5e1; left: 15%;"></div>
                
                <!-- Step 1 -->
                <div class="col-lg-4 position-relative z-1">
                    <div class="reveal h-100 rounded-4 bg-white shadow-sm p-4 p-md-5 text-center border border-white-10">
                        <div class="rounded-circle bg-navy text-light d-flex align-items-center justify-content-center mx-auto shadow mb-4" style="width:56px;height:56px; font-size: 1.25rem;">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <div class="fs-8 fw-bold tracking-widest text-brand-orange mb-2">STEP 01</div>
                        <h3 class="fs-5 fw-bold text-deep-navy">도입 신청 및 가입</h3>
                        <p class="mt-2 fs-7 text-secondary lh-base mb-0">무료 플랜이나 원하는 요금제로 가입을 완료하고 솔루션 환경을 세팅합니다.</p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="col-lg-4 position-relative z-1">
                    <div class="reveal h-100 rounded-4 bg-white shadow-sm p-4 p-md-5 text-center border border-white-10" style="transition-delay: 100ms;">
                        <div class="rounded-circle bg-navy text-light d-flex align-items-center justify-content-center mx-auto shadow mb-4" style="width:56px;height:56px; font-size: 1.25rem;">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <div class="fs-8 fw-bold tracking-widest text-brand-orange mb-2">STEP 02</div>
                        <h3 class="fs-5 fw-bold text-deep-navy">전용 링크 발급</h3>
                        <p class="mt-2 fs-7 text-secondary lh-base mb-0">귀사만의 고유한 2D Canvas 접속 링크(URL)가 즉시 발급됩니다.</p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="col-lg-4 position-relative z-1">
                    <div class="reveal h-100 rounded-4 bg-white shadow-sm p-4 p-md-5 text-center border border-white-10" style="transition-delay: 200ms;">
                        <div class="rounded-circle bg-brand-orange text-light d-flex align-items-center justify-content-center mx-auto shadow mb-4" style="width:56px;height:56px; font-size: 1.25rem;">
                            <i class="fa-solid fa-mouse-pointer"></i>
                        </div>
                        <div class="fs-8 fw-bold tracking-widest text-brand-orange mb-2">STEP 03</div>
                        <h3 class="fs-5 fw-bold text-deep-navy">홈페이지 연동</h3>
                        <p class="mt-2 fs-7 text-secondary lh-base mb-0">운영 중인 홈페이지의 '자동 견적 내기' 버튼에 발급된 링크를 넣으면 끝!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section id="solution" class="position-relative bg-dark-surface border-top border-white-10 overflow-hidden">
        <div class="position-absolute w-100 h-100 grid-pattern opacity-30 pointer-events-none"></div>
        <div class="max-w-1200 section-padding position-relative z-1">
            <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill badge-soft-primary px-3 py-1 fs-8 fw-bold">
                <i class="fa-solid fa-wand-magic-sparkles"></i> 2D Canvas 자동설계 & 견적 B2B SaaS
            </div>
            <h2 class="reveal mt-4 fw-extrabold tracking-tight text-light" style="font-size: clamp(24px, 4vw, 38px); line-height: 1.2; max-width: 720px;">
                CAD 없이 브라우저에서 끝내는<br>
                <span class="text-gradient-navy-blue">2D 설계 · 3초 배치 · 원클릭 견적</span>
            </h2>
            
            <div class="row g-4 mt-5">
                <!-- Feature 1 -->
                <div class="col-md-7">
                    <div class="reveal h-100 rounded-4 bg-dark-surface border border-white-10 shadow-soft p-4 p-md-5">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="rounded-3 bg-navy d-flex align-items-center justify-content-center shadow" style="width:48px;height:48px; color: #53B5F5;">
                                <i class="fa-solid fa-table-cells-large fs-5"></i>
                            </div>
                            <div class="rounded-pill border border-primary px-3 py-1 fs-9 fw-bold text-primary-blue bg-primary bg-opacity-10 d-flex align-items-center gap-1">
                                <i class="fa-solid fa-check"></i> 자동화
                            </div>
                        </div>
                        <h3 class="mt-4 fs-4 fw-bold tracking-tight" style="color:#C0392B;">2D Canvas 웹 설계</h3>
                        <p class="mt-2 fs-6 text-deep-navy-60 lh-base">가로x세로 치수 입력, 기둥/셔터/장애물 드래그로 즉시 레이아웃. CAD 불필요.</p>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">실시간 치수 스냅</span>
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">장애물 충돌 자동 감지</span>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="col-md-5">
                    <div class="reveal h-100 rounded-4 bg-dark-surface border border-white-10 shadow-soft p-4 p-md-5" style="transition-delay: 100ms;">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="rounded-3 bg-navy d-flex align-items-center justify-content-center shadow" style="width:48px;height:48px; color: #53B5F5;">
                                <i class="fa-solid fa-bolt fs-5"></i>
                            </div>
                            <div class="rounded-pill border border-primary px-3 py-1 fs-9 fw-bold text-primary-blue bg-primary bg-opacity-10 d-flex align-items-center gap-1">
                                <i class="fa-solid fa-check"></i> 자동화
                            </div>
                        </div>
                        <h3 class="mt-4 fs-4 fw-bold tracking-tight" style="color:#C0392B;">자동 랙 배치 연산</h3>
                        <p class="mt-2 fs-6 text-deep-navy-60 lh-base">파렛트 규격 1100/1200, 단수, 통로 3초 연산.</p>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">통로 3,000mm 확보</span>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-md-5">
                    <div class="reveal h-100 rounded-4 bg-dark-surface border border-white-10 shadow-soft p-4 p-md-5">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="rounded-3 bg-navy d-flex align-items-center justify-content-center shadow" style="width:48px;height:48px; color: #53B5F5;">
                                <i class="fa-solid fa-calculator fs-5"></i>
                            </div>
                        </div>
                        <h3 class="mt-4 fs-4 fw-bold tracking-tight" style="color:#C0392B;">정밀 BOM 자동 산출</h3>
                        <p class="mt-2 fs-6 text-deep-navy-60 lh-base">주기둥/로드빔 등 100% 자동 산출. 오발주 0%.</p>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">규격별 단가 DB 연동</span>
                        </div>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-md-7">
                    <div class="reveal h-100 rounded-4 bg-dark-surface border border-white-10 shadow-soft p-4 p-md-5" style="transition-delay: 100ms;">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="rounded-3 bg-navy d-flex align-items-center justify-content-center shadow" style="width:48px;height:48px; color: #53B5F5;">
                                <i class="fa-regular fa-file-pdf fs-5"></i>
                            </div>
                        </div>
                        <h3 class="mt-4 fs-4 fw-bold tracking-tight" style="color:#C0392B;">원클릭 PDF 견적서</h3>
                        <p class="mt-2 fs-6 text-deep-navy-60 lh-base">로고가 삽입된 정식 견적서를 5분 내 발행하여 고객사로 바로 발송.</p>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">브랜드 커스텀 템플릿</span>
                            <span class="badge rounded-pill bg-white shadow-sm text-deep-navy-70 border fw-medium px-3 py-2 text-dark">도면 + BOM + 금액 통합</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ROI Section -->
    <section id="roi" class="bg-white border-top border-white-10">
        <div class="max-w-1200 section-padding">
            <div class="reveal d-flex align-items-center gap-3">
                <div class="rounded-3 bg-navy text-deep-navy d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
                <div>
                    <div class="fs-8 fw-bold tracking-widest text-primary-blue">ROI ANALYSIS</div>
                    <h2 class="fs-3 fw-extrabold tracking-tight">도입 시 원가 절감 효과 (공급업체 ROI)</h2>
                </div>
            </div>
            
            <div class="reveal mt-5 rounded-4 border border-white-10 overflow-hidden shadow">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0 align-middle">
                        <thead class="bg-navy text-deep-navy fs-8 tracking-wide">
                            <tr>
                                <th class="py-3 px-4 fw-semibold w-25">항목</th>
                                <th class="py-3 px-4 fw-semibold text-dark">AS-IS</th>
                                <th class="py-3 px-4 fw-semibold">TO-BE</th>
                                <th class="py-3 px-4 fw-semibold text-brand-orange d-none d-md-table-cell">효과</th>
                            </tr>
                        </thead>
                        <tbody class="fs-6">
                            <tr class="border-bottom border-white-10 bg-light-gray">
                                <td class="py-4 px-4 fw-semibold"><i class="fa-regular fa-clock text-primary-blue me-2"></i> 건당 견적 소요시간</td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-danger px-3 py-2">1.5~3시간</span></td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-primary px-3 py-2">5분 이내</span></td>
                                <td class="py-4 px-4 d-none d-md-table-cell fw-bold text-deep-navy">95% 단축 <div class="fs-8 text-secondary fw-normal">생산성 20배</div></td>
                            </tr>
                            <tr class="border-bottom border-white-10 bg-white shadow-sm">
                                <td class="py-4 px-4 fw-semibold"><i class="fa-solid fa-wallet text-primary-blue me-2"></i> 월간 인건비 (25건)</td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-danger px-3 py-2">200만원</span></td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-primary px-3 py-2">6.5만원</span></td>
                                <td class="py-4 px-4 d-none d-md-table-cell fw-bold text-deep-navy">193.5만원 절감 <div class="fs-8 text-secondary fw-normal">연 2,322만원</div></td>
                            </tr>
                            <tr class="border-bottom border-white-10 bg-light-gray">
                                <td class="py-4 px-4 fw-semibold"><i class="fa-solid fa-chart-column text-primary-blue me-2"></i> 월 구독료 대비 순수익</td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-danger px-3 py-2">비용 지속</span></td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-primary px-3 py-2">29만원</span></td>
                                <td class="py-4 px-4 d-none d-md-table-cell fw-bold text-deep-navy">ROI 560% <div class="fs-8 text-secondary fw-normal">+164.5만원 순이익</div></td>
                            </tr>
                            <tr class="border-bottom border-white-10 bg-white shadow-sm">
                                <td class="py-4 px-4 fw-semibold"><i class="fa-solid fa-bolt text-primary-blue me-2"></i> 견적 응대 리드타임</td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-danger px-3 py-2">1~3일</span></td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-primary px-3 py-2">10분 내 발송</span></td>
                                <td class="py-4 px-4 d-none d-md-table-cell fw-bold text-deep-navy">수주 전환율 <div class="fs-8 text-secondary fw-normal">15~30% 개선</div></td>
                            </tr>
                            <tr class="bg-light-gray">
                                <td class="py-4 px-4 fw-semibold"><i class="fa-solid fa-shield-halved text-primary-blue me-2"></i> 설계/BOM 오류율</td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-danger px-3 py-2">5~10%</span></td>
                                <td class="py-4 px-4"><span class="badge rounded-pill badge-soft-primary px-3 py-2">0%</span></td>
                                <td class="py-4 px-4 d-none d-md-table-cell fw-bold text-deep-navy">손실 방지 <div class="fs-8 text-secondary fw-normal">300~500만원</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="reveal mt-3 d-flex flex-wrap gap-2 align-items-center fs-8">
                <span class="badge rounded-pill bg-navy text-light px-3 py-2 fw-normal"><i class="fa-solid fa-wand-magic-sparkles text-brand-orange me-1"></i> 월 25건 기준, 구독료 29만원 대비 순수익 +164.5만원</span>
                <span class="text-dark ms-2">* 인건비 산정: 건당 2.5시간 × 시급 3만원 초과 인력 가정 / 오류 손실: 재출장·운임·자재 폐기 포함</span>
            </div>
        </div>
    </section>

    <!-- TAM/SAM/SOM Section -->
    <section class="position-relative bg-navy text-deep-navy overflow-hidden border-top border-white-10">
        <div class="position-absolute w-100 h-100 pointer-events-none">
            <div class="position-absolute start-50 translate-middle-x" style="top:-160px; width: 900px; height: 600px; border-radius: 50%; background: radial-gradient(ellipse at center, rgba(42,91,218,0.35), transparent 60%);"></div>
            <div class="position-absolute w-100 h-100 grid-pattern-dark opacity-50"></div>
        </div>
        
        <div class="max-w-1200 section-padding position-relative z-1">
            <div class="reveal d-inline-flex align-items-center gap-2 rounded-pill border border-white-15 bg-light-gray bg-opacity-10 px-3 py-1 fs-8 fw-bold">
                <i class="fa-solid fa-database"></i> TAM / SAM / SOM
            </div>
            
            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 bg-light-gray bg-opacity-10 p-4 glass">
                        <div class="rounded-3 bg-gradient-primary d-flex align-items-center justify-content-center shadow mb-3" style="width:40px;height:40px;">
                            <i class="fa-solid fa-box text-deep-navy"></i>
                        </div>
                        <div class="fs-8 tracking-widest text-deep-navy-50 fw-bold">국내 랙 시장</div>
                        <div class="mt-1 fw-extrabold tracking-tight text-deep-navy" style="font-size: 36px; line-height:1;">1,386억</div>
                        <div class="mt-2 fs-7 text-secondary lh-base">1.98억 달러 중 50% • 연 6~8% 성장</div>
                        <div class="mt-4 rounded-pill bg-dark overflow-hidden" style="height:2px;">
                            <div class="h-100 bg-gradient-primary" style="width:72%;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 bg-light-gray bg-opacity-10 p-4 glass" style="transition-delay: 100ms;">
                        <div class="rounded-3 d-flex align-items-center justify-content-center shadow mb-3" style="width:40px;height:40px; background: linear-gradient(to right, #62D6FF, #A5B4FC);">
                            <i class="fa-regular fa-building text-deep-navy"></i>
                        </div>
                        <div class="fs-8 tracking-widest text-deep-navy-50 fw-bold">전국 등록 창고</div>
                        <div class="mt-1 fw-extrabold tracking-tight text-deep-navy" style="font-size: 36px; line-height:1;">5,156개</div>
                        <div class="mt-2 fs-7 text-secondary lh-base">경기/경남/인천/부산 64.4% 집중</div>
                        <div class="mt-4 rounded-pill bg-dark overflow-hidden" style="height:2px;">
                            <div class="h-100" style="width:72%; background: linear-gradient(to right, #62D6FF, #A5B4FC);"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 bg-light-gray bg-opacity-10 p-4 glass" style="transition-delay: 200ms;">
                        <div class="rounded-3 d-flex align-items-center justify-content-center shadow mb-3" style="width:40px;height:40px; background: linear-gradient(to right, #A5B4FC, #2A5BDA);">
                            <i class="fa-solid fa-chart-column text-deep-navy"></i>
                        </div>
                        <div class="fs-8 tracking-widest text-deep-navy-50 fw-bold">월간 견적 기회</div>
                        <div class="mt-1 fw-extrabold tracking-tight text-deep-navy" style="font-size: 36px; line-height:1;">~900건</div>
                        <div class="mt-2 fs-7 text-secondary lh-base">460~1,540건 추산 • 전환 시 MRR 핵심</div>
                        <div class="mt-4 rounded-pill bg-dark overflow-hidden" style="height:2px;">
                            <div class="h-100" style="width:72%; background: linear-gradient(to right, #A5B4FC, #2A5BDA);"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="reveal mt-5 row g-4">
                <div class="col-md-7">
                    <div class="rounded-4 bg-light-gray text-deep-navy p-4 d-flex align-items-center justify-content-between h-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-navy text-deep-navy d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <div>
                                <div class="fs-6 fw-bold">전국 물류 거점 64.4%가 4개 권역 집중</div>
                                <div class="fs-8 text-secondary">경기·경남·인천·부산 타겟 영업 시 효율 극대화</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="rounded-4 border border-white-15 bg-light-gray bg-opacity-10 p-4 d-flex align-items-center gap-3 h-100">
                        <div class="rounded-3 bg-light-gray text-deep-navy d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                        </div>
                        <div class="fs-7 lh-base">
                            <div class="fw-semibold text-deep-navy">SOM: 월 900건 중 5% 전환 시 MRR 2,200만원</div>
                            <div class="fs-8 text-secondary">STARTER 29만원 × 45사 기준 초기 가설</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="bg-light-gray border-top border-white-10">
        <div class="max-w-1200 section-padding">
            <div class="reveal text-center max-w-720 mx-auto">
                <div class="d-inline-flex align-items-center gap-2 rounded-pill bg-navy text-light px-3 py-1 fs-8 fw-bold">
                    <i class="fa-solid fa-wallet"></i> REVENUE MODEL & PRICING
                </div>
                <h2 class="mt-4 fw-extrabold tracking-tight" style="font-size: clamp(28px, 4vw, 36px); line-height: 1.2; color: #C0392B;">
                    견적 1건의 인건비보다 저렴한<br>월 구독으로 시작하세요
                </h2>
                <p class="mt-3 fs-6 text-secondary lh-lg">
                    FREE부터 PRO까지, 현장 검증된 기능만 담았습니다.<br>10건 무료 체험 • 카드 등록 불필요
                </p>
            </div>
            
            <div class="row g-4 mt-5 align-items-stretch">
                <!-- FREE -->
                <div class="col-lg-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 bg-white p-4 p-md-5 d-flex flex-column">
                        <div class="fs-8 fw-bold tracking-widest text-deep-navy">FREE</div>
                        <div class="mt-3 d-flex align-items-baseline gap-1">
                            <span class="fs-2 fw-extrabold tracking-tight text-deep-navy">무료</span>
                        </div>
                        <div class="mt-2 fs-7 text-secondary lh-base">솔루션 체험용</div>
                        <div class="mt-4 mb-4 flex-grow-1">
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 모든 기능 100% 동일 제공</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 월 10건 견적 발행</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 직원 등록 1명</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 이메일 지원</div>
                        </div>
                        <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-light w-100 justify-content-center fs-7">무료로 시작하기 <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <!-- STARTER -->
                <div class="col-lg-4">
                    <div class="reveal h-100 rounded-4 border p-1 bg-gradient-popular shadow-heavy position-relative" style="transition-delay: 100ms;">
                        <div class="position-absolute start-50 translate-middle-x rounded-pill bg-brand-orange text-light px-3 py-1 fs-9 fw-bold shadow" style="top:-12px; z-index:2; width: 140px; text-align: center;">
                            <i class="fa-solid fa-star"></i> MOST POPULAR
                        </div>
                        <div class="h-100 rounded-4 bg-white p-4 p-md-5 d-flex flex-column position-relative z-1">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-8 fw-bold tracking-widest text-deep-navy">STARTER</div>
                                <span class="badge rounded-pill badge-soft-primary fs-9">⭐ 추천</span>
                            </div>
                            <div class="mt-3 d-flex align-items-baseline gap-1">
                                <span class="fs-2 fw-extrabold tracking-tight text-deep-navy">월 29만원</span>
                                <span class="fs-8 text-deep-navy-50">/ VAT 별도</span>
                            </div>
                            <div class="mt-2 fs-7 text-secondary lh-base">소규모 업체용</div>
                            <div class="mt-4 mb-4 flex-grow-1">
                                <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 모든 기능 100% 동일 제공</div>
                                <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 월 30건 견적 발행</div>
                                <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 직원 등록 무제한</div>
                                <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 우선 지원 & 온보딩</div>
                            </div>
                            <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=starter'; ?>" class="btn-pill w-100 justify-content-center fs-7 shadow border-0" style="background: linear-gradient(to right, #2A5BDA, #62D6FF); color: #fff;">STARTER 선택 <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- PRO -->
                <div class="col-lg-4">
                    <div class="reveal h-100 rounded-4 border border-white-10 bg-white p-4 p-md-5 d-flex flex-column" style="transition-delay: 200ms;">
                        <div class="fs-8 fw-bold tracking-widest text-deep-navy">PRO</div>
                        <div class="mt-3 d-flex align-items-baseline gap-1">
                            <span class="fs-2 fw-extrabold tracking-tight text-deep-navy">월 49만원</span>
                            <span class="fs-8 text-deep-navy-50">/ VAT 별도</span>
                        </div>
                        <div class="mt-2 fs-7 text-secondary lh-base">일반 업체용</div>
                        <div class="mt-4 mb-4 flex-grow-1">
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 모든 기능 100% 동일 제공</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 견적 발행 무제한</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 직원 등록 무제한</div>
                            <div class="d-flex align-items-center gap-2 fs-7 mb-2 fw-medium text-deep-navy"><div class="rounded-circle bg-white shadow-sm text-primary-blue d-flex align-items-center justify-content-center" style="width:20px;height:20px;"><i class="fa-solid fa-check fs-9"></i></div> 24/7 전담 지원</div>
                        </div>
                        <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=pro'; ?>" class="btn-pill btn-light w-100 justify-content-center fs-7">PRO 시작하기 <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="reveal mt-5 rounded-4 bg-navy text-deep-navy px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="fs-7 d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width:24px;height:24px;">
                        <i class="fa-solid fa-calculator fs-9"></i>
                    </div>
                    <span class="text-deep-navy-80">STARTER 29만원은 <span class="text-warning fw-bold">견적 1건 인건비</span>보다 저렴합니다. 월 25건 기준 순이익 +164.5만원.</span>
                </div>
                <a href="#roi" class="btn-pill btn-white fs-9 px-3 py-2">ROI 계산기 다시보기</a>
            </div>
        </div>
    </section>

    <!-- Growth & Roadmap -->
    <section class="position-relative border-top border-white-10" style="background: url('/assets/images/graph_back.jpg') no-repeat center center fixed; background-size: cover;">
        <div class="position-absolute w-100 h-100 top-0 start-0 bg-white" style="opacity: 0.5;"></div>
        <div class="max-w-1200 section-padding position-relative z-1">
            <div class="row g-5">
                <div class="col-lg-8 mx-auto">
                    <div class="reveal rounded shadow p-4">
                        <div class="d-inline-flex align-items-center gap-2 rounded-pill bg-white shadow-sm border border-white-10 px-3 py-1 fs-8 fw-bold">
                            <i class="fa-solid fa-chart-line"></i> GROWTH MODEL
                        </div>
                        <h3 class="mt-4 fs-3 fw-extrabold tracking-tight text-deep-navy">20사에서 200사까지, MRR 1.38억 스케일</h3>
                        <p class="mt-2 fs-7 text-secondary lh-lg">검증된 DB 영업 → PoC → SaaS 상용화 → 중개 플랫폼 확장</p>
                        
                        <div class="mt-5 rounded-4 border border-white-10 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <thead class="bg-white shadow-sm text-secondary fs-8">
                                        <tr>
                                            <th class="px-4 py-3 fw-semibold">구분</th>
                                            <th class="px-3 py-3 fw-semibold">고객사</th>
                                            <th class="px-3 py-3 fw-semibold">MRR</th>
                                            <th class="px-4 py-3 fw-semibold">ARR</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <tr class="border-top border-white-10">
                                            <td class="px-4 py-3 fw-semibold text-deep-navy">Year 0</td>
                                            <td class="px-3 py-3 text-deep-navy">20사</td>
                                            <td class="px-3 py-3 fw-bold text-deep-navy">780만</td>
                                            <td class="px-4 py-3 d-flex align-items-center gap-2">
                                                <span class="fw-bold text-deep-navy">0.94억</span>
                                                <div class="d-none d-md-block flex-grow-1 rounded-pill bg-white shadow-sm overflow-hidden" style="height:6px;">
                                                    <div class="h-100 bg-brand-orange" style="width:18%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border-top border-white-10">
                                            <td class="px-4 py-3 fw-semibold text-deep-navy">Year 1</td>
                                            <td class="px-3 py-3 text-deep-navy">50사</td>
                                            <td class="px-3 py-3 fw-bold text-deep-navy">2,450만</td>
                                            <td class="px-4 py-3 d-flex align-items-center gap-2">
                                                <span class="fw-bold text-deep-navy">2.94억</span>
                                                <div class="d-none d-md-block flex-grow-1 rounded-pill bg-white shadow-sm overflow-hidden" style="height:6px;">
                                                    <div class="h-100 bg-brand-orange" style="width:35%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border-top border-white-10">
                                            <td class="px-4 py-3 fw-semibold text-deep-navy">Year 2</td>
                                            <td class="px-3 py-3 text-deep-navy">100사</td>
                                            <td class="px-3 py-3 fw-bold text-deep-navy">5,900만</td>
                                            <td class="px-4 py-3 d-flex align-items-center gap-2">
                                                <span class="fw-bold text-deep-navy">7.08억</span>
                                                <div class="d-none d-md-block flex-grow-1 rounded-pill bg-white shadow-sm overflow-hidden" style="height:6px;">
                                                    <div class="h-100 bg-brand-orange" style="width:62%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="border-top border-white-10 bg-navy text-deep-navy">
                                            <td class="px-4 py-3 fw-semibold">Year 3</td>
                                            <td class="px-3 py-3">200사</td>
                                            <td class="px-3 py-3 fw-bold">1.38억</td>
                                            <td class="px-4 py-3 d-flex align-items-center gap-2">
                                                <span class="fw-bold">16.56억</span>
                                                <div class="d-none d-md-block flex-grow-1 rounded-pill bg-white bg-opacity-25 overflow-hidden" style="height:6px;">
                                                    <div class="h-100 bg-white shadow-sm-blue" style="width:100%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3 fs-9 text-dark">* PRO 49만원 기준 보수적 산정, 상향 확장 시 ARR 20억+ 가능</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="position-relative bg-dark-blue text-deep-navy overflow-hidden text-center border-top border-white-10">
        <div class="position-absolute w-100 h-100 grid-pattern-dark opacity-40 pointer-events-none"></div>
        <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 900px; height: 500px; background: radial-gradient(ellipse at center, rgba(42,91,218,0.35), transparent 60%);"></div>
        
        <div class="max-w-1200 section-padding position-relative z-1 py-5">
            <div class="reveal d-inline-flex rounded-pill badge-glass px-3 py-1 fs-8 fw-bold tracking-widest text-light mt-4">
                READY TO SHIP • 2026
            </div>
            <h2 class="reveal mt-4 fw-extrabold tracking-tight" style="font-size: clamp(28px, 5vw, 42px); line-height: 1.1; color:orange;">
                지금, 견적 공수 95%를<br>줄이세요.
            </h2>
            <p class="reveal mt-3 fs-6 text-secondary lh-lg max-w-720 mx-auto">
                AutoCAD 없이 5분 안에 끝내는 2D Canvas 자동설계.<br>14일 무료 체험으로 오늘 바로 검증하세요.
            </p>
            
            <div class="reveal mt-5 d-flex flex-column flex-sm-row align-items-center justify-content-center gap-3 pb-4">
                <a href="<?php echo isset($_SESSION['user']) ? '/subscribe' : '/register?plan=free'; ?>" class="btn-pill btn-white shadow-heavy" style="height: 52px; font-size: 16px; padding: 0 2rem;">무료로 시작하기 <i class="fa-solid fa-arrow-right"></i></a>
                <a href="https://cmake.work/quote/demo" target="_blank" class="btn-pill badge-glass text-light" style="height: 52px; font-size: 16px; padding: 0 2rem;"><i class="fa-solid fa-play"></i> 견적신청데모</a>
            </div>
            <div class="reveal mt-4 fs-8 text-deep-navy-45 pb-5">
                평균 온보딩 12분 • PoC 3~5곳 진행 중
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Reveal Animation Setup
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

<?php include_footer($siteConfig); ?>
