<?php 
$title = '맞춤형 웹 & ERP 시스템 제작 - 기업 홈페이지, 쇼핑몰, 사내 프로그램 구축'; 
include_header($title, $siteConfig ?? []); 
?>

<!-- 
    ===================================================================
    [두목님 전용 작업 공간]
    이곳에 제작하신 홈페이지 내용(HTML, CSS, JS)을 자유롭게 채워 넣으시면 됩니다! 💕
    기본 상단 헤더와 하단 푸터가 자동으로 감싸고 있으니 본문 컨텐츠만 구성하시면 됩니다.
    ===================================================================
-->

<div class="custom-website-container py-5" style="min-height: 70vh; background-color: #f8fafc;">
    <div class="container py-4">
        <!-- 헤더 인트로 -->
        <div class="text-center py-4">
            <span class="badge rounded-pill px-3 py-2 fs-7 fw-bold mb-3" style="background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;">
                <i class="fa-solid fa-code"></i> ALL-IN-ONE 맞춤형 웹 개발 에이전시
            </span>
            <h1 class="fw-bold text-dark mt-2" style="font-size: clamp(28px, 4vw, 42px); line-height: 1.3;">
                기업 홈페이지 · 쇼핑몰 · 사내 ERP<br>
                <span style="color: #f16819;">원하시는 모든 웹 시스템</span>을 맞춤 제작해 드립니다
            </h1>
            <p class="text-muted fs-6 mt-3 mx-auto" style="max-width: 720px; line-height: 1.8;">
                단순 홍보형 회사 홈페이지부터 결제 연동 쇼핑몰, 사내 업무 자동화 ERP 및 2D 캔버스 특수 시스템까지!<br class="d-none d-md-block">
                귀사의 비즈니스에 가장 최적화된 기획과 디자인, 강력한 기능을 갖춘 맞춤형 웹을 완성해 드립니다.
            </p>
        </div>

        <!-- 3대 핵심 제작 영역 카드 -->
        <div class="row g-4 mt-2 justify-content-center">
            <!-- 1. 회사 홈페이지 -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border rounded-4 p-4 shadow-sm bg-white">
                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; background-color: #e0e7ff; color: #4338ca;">
                        <i class="fa-solid fa-building fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark fs-5">기업 / 회사 홈페이지</h4>
                    <p class="text-muted fs-7 mt-2 lh-base">
                        신뢰도를 높이는 감각적인 UI/UX 디자인과 모바일·태블릿·PC 완벽 반응형 레이아웃, 포털 검색(SEO) 최적화로 회사의 브랜드 가치를 극대화합니다.
                    </p>
                    <ul class="list-unstyled text-muted fs-8 mt-auto pt-3 border-top mb-0">
                        <li class="mb-1"><i class="fa-solid fa-check text-primary me-1"></i> 반응형 모던 디자인 & 브랜드 아이덴티티</li>
                        <li><i class="fa-solid fa-check text-primary me-1"></i> 온라인 간편 견적 / 문의 접수 폼</li>
                    </ul>
                </div>
            </div>

            <!-- 2. 쇼핑몰 & 이커머스 -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border rounded-4 p-4 shadow-sm bg-white">
                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; background-color: #ffedd5; color: #c2410c;">
                        <i class="fa-solid fa-cart-shopping fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark fs-5">온라인 쇼핑몰 & 전자상거래</h4>
                    <p class="text-muted fs-7 mt-2 lh-base">
                        신용카드·가상계좌·간편결제(PG) 연동, 장바구니, 주문/배송 추적 및 실시간 재고 관리 시스템까지 매출을 부르는 전문 쇼핑몰을 구축합니다.
                    </p>
                    <ul class="list-unstyled text-muted fs-8 mt-auto pt-3 border-top mb-0">
                        <li class="mb-1"><i class="fa-solid fa-check text-warning me-1"></i> PG 결제대행사(PG) 완벽 연동</li>
                        <li><i class="fa-solid fa-check text-warning me-1"></i> 회원등급, 적립금, 쿠폰 및 프로모션 시스템</li>
                    </ul>
                </div>
            </div>

            <!-- 3. 사내 ERP & 업무관리 시스템 -->
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border rounded-4 p-4 shadow-sm bg-white">
                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-3" style="width: 52px; height: 52px; background-color: #dcfce7; color: #15803d;">
                        <i class="fa-solid fa-network-wired fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark fs-5">사내 ERP & 관리자 시스템</h4>
                    <p class="text-muted fs-7 mt-2 lh-base">
                        견적·발주·매출·정산 관리, 고객사 CRM, 직원별 권한 부여 및 2D 캔버스 자동견적 엔진 연동까지 엑셀 수작업을 없애는 맞춤 ERP를 개발합니다.
                    </p>
                    <ul class="list-unstyled text-muted fs-8 mt-auto pt-3 border-top mb-0">
                        <li class="mb-1"><i class="fa-solid fa-check text-success me-1"></i> 엑셀 일괄 업로드/다운로드 및 자동 집계</li>
                        <li><i class="fa-solid fa-check text-success me-1"></i> 2D 도면/견적 특수 솔루션 완벽 임베드</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 하단 제작 문의 안내 카드 -->
        <div class="mt-5 p-5 bg-white rounded-4 shadow-sm border mx-auto text-start" style="max-width: 960px;">
            <div class="row align-items-center gy-4">
                <div class="col-lg-8">
                    <h4 class="fw-bold text-dark mb-2">
                        <i class="fa-solid fa-headset text-brand-orange me-2"></i>홈페이지 & 시스템 제작 상담 안내
                    </h4>
                    <p class="text-muted fs-7 mb-3 lh-base">
                        원하시는 사이트 유형(회사 소개, 쇼핑몰, 사내 프로그램 등)과 기능 요구사항을 알려주시면<br class="d-none d-sm-block">
                        합리적인 견적과 상세한 제작 일정, 무료 샘플 시안을 빠르게 제안해 드립니다.
                    </p>
                    <div class="d-flex flex-wrap gap-2 fs-8 text-secondary">
                        <span class="badge bg-light text-dark border px-2 py-1">#기업홈페이지</span>
                        <span class="badge bg-light text-dark border px-2 py-1">#쇼핑몰</span>
                        <span class="badge bg-light text-dark border px-2 py-1">#사내ERP</span>
                        <span class="badge bg-light text-dark border px-2 py-1">#2D견적엔진</span>
                        <span class="badge bg-light text-dark border px-2 py-1">#반응형웹</span>
                    </div>
                </div>
                <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                    <div class="fs-7 text-dark opacity-75 mb-1">고객센터 / 제작상담</div>
                    <div class="fw-bold mb-3" style="font-size: 1.6rem; letter-spacing: -0.5px; color: #f16819;"><i class="fa-solid fa-phone me-2"></i>0507-1346-3957</div>
                    <a href="mailto:info@cmake.work" class="btn rounded-pill px-4 py-3 text-white fw-bold shadow-sm fs-7 w-100 w-lg-auto" style="background-color: #f16819; border: none;">
                        제작상담 신청하기 <i class="fa-solid fa-envelope ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- ===================================================================
             [포트폴리오 갤러리 섹션]
             실제 제작/구축된 사이트 URL과 썸네일 자동 캡처 연동
             =================================================================== -->
        <section id="portfolio" class="mt-5 pt-4">
            <div class="text-center mb-5 pb-2">
                <h2 class="fw-bolder text-dark mb-2" style="font-size: clamp(28px, 4.5vw, 42px); letter-spacing: 3px;">
                    - PORTFOLIO -
                </h2>
                <h3 class="fw-bold text-secondary fs-5 mb-2">제작 포트폴리오 & 구축 사례</h3>
                <p class="text-muted fs-7 mb-0">실제 구축되어 운영 중인 다양한 고객사 웹사이트 및 비즈니스 시스템입니다.</p>
                <?php if (!empty($portfolios)): ?>
                <div class="text-muted fs-8 mt-2">
                    총 <strong class="text-dark"><?= count($portfolios) ?></strong>개의 구축 사례
                </div>
                <?php endif; ?>
            </div>

            <!-- 최고관리자 전용: 포트폴리오 사이트 등록 폼 -->
            <?php if (!empty($is_super) || !empty($is_admin)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark fw-bold px-2 py-1 fs-8">
                                <i class="fa-solid fa-shield-halved me-1"></i> 최고관리자 전용
                            </span>
                            <span class="fw-bold fs-6">새 포트폴리오 등록</span>
                        </div>
                        <small class="text-light opacity-75 fs-8">URL만 입력하면 실시간 스크린샷이 자동 생성됩니다.</small>
                    </div>

                    <form action="/website/portfolio/add" method="POST" class="row g-3 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <input type="hidden" name="category" value="홈페이지">
                        <input type="hidden" name="description" value="">
                        
                        <div class="col-md-6">
                            <label class="form-label text-light fs-8 mb-1">사이트 URL <span class="text-warning">*</span></label>
                            <input type="url" name="url" class="form-control form-control-sm bg-dark text-white border-secondary" 
                                   placeholder="https://example.com" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-light fs-8 mb-1">사이트/고객사명</label>
                            <input type="text" name="title" class="form-control form-control-sm bg-dark text-white border-secondary" 
                                   placeholder="예: (주)대한물류">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2" style="background-color: #f16819; border-color: #f16819;">
                                <i class="fa-solid fa-plus me-1"></i> 등록하기
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- 포트폴리오 갤러리 그리드 -->
            <?php if (!empty($portfolios)): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($portfolios as $item): ?>
                        <?php 
                            $itemUrl = $item['url'];
                            // 1차: WordPress Automattic CDN mShots (무제한급 글로벌 캡처)
                            $thumbWpUrl = "https://s0.wp.com/mshots/v1/" . urlencode($itemUrl) . "?w=800";
                            // 2차: Thum.io 실시간 캡처 백업
                            $thumbThumUrl = "https://image.thum.io/get/width/800/crop/500/noanimate/" . urlencode($itemUrl);
                            // 3차: Microlink API 백업
                            $thumbMicroUrl = "https://api.microlink.io?url=" . urlencode($itemUrl) . "&screenshot=true&meta=false&embed=screenshot.url";
                        ?>
                        <div class="col">
                            <div class="card h-100 border rounded-4 shadow-sm overflow-hidden portfolio-card bg-white position-relative">
                                <!-- 썸네일 영역 (클릭 시 해당 사이트로 이동) -->
                                <div class="portfolio-thumb-wrapper position-relative overflow-hidden" style="height: 210px; background-color: #f1f5f9;">
                                    <img src="<?= htmlspecialchars($thumbWpUrl) ?>" 
                                         data-fallback-thum="<?= htmlspecialchars($thumbThumUrl) ?>"
                                         data-fallback-micro="<?= htmlspecialchars($thumbMicroUrl) ?>"
                                         alt="<?= htmlspecialchars($item['title']) ?>" 
                                         class="w-100 h-100 object-fit-cover portfolio-thumb-img"
                                         loading="lazy"
                                         onerror="handlePortfolioImgError(this);">
                                    
                                    <!-- 호버 오버레이 -->
                                    <a href="<?= htmlspecialchars($itemUrl) ?>" target="_blank" rel="noopener noreferrer" 
                                       class="portfolio-thumb-overlay d-flex flex-column align-items-center justify-content-center text-white text-decoration-none"
                                       title="사이트 바로가기">
                                        <div class="btn btn-light btn-sm fw-bold rounded-pill px-3 shadow">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-dark me-1"></i> 사이트 바로가기
                                        </div>
                                    </a>
                                </div>

                                <!-- 카드 본문 -->
                                <div class="card-body d-flex flex-column p-3">
                                    <h5 class="card-title fw-bold text-dark fs-6 mb-3 text-truncate">
                                        <a href="<?= htmlspecialchars($itemUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-dark text-decoration-none portfolio-title-link">
                                            <?= htmlspecialchars($item['title']) ?>
                                        </a>
                                    </h5>

                                    <!-- 하단 액션 버튼 바 -->
                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                        <a href="<?= htmlspecialchars($itemUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm fs-8 rounded-pill px-3">
                                            방문하기 <i class="fa-solid fa-external-link ms-1"></i>
                                        </a>

                                        <!-- 관리자 전용 삭제 버튼 -->
                                        <?php if (!empty($is_super) || !empty($is_admin)): ?>
                                        <form action="/website/portfolio/delete" method="POST" class="m-0" onsubmit="return confirm('이 포트폴리오를 삭제하시겠습니까?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm fs-8 rounded-pill px-2 py-1" title="삭제">
                                                <i class="fa-solid fa-trash-can me-1"></i>삭제
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- 빈 상태 (Empty State) -->
                <div class="text-center py-5 bg-white rounded-4 border shadow-sm">
                    <div class="text-muted mb-3">
                        <i class="fa-solid fa-laptop-code fs-1 opacity-50" style="color: #f16819;"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">등록된 포트폴리오가 아직 없습니다</h5>
                    <p class="text-muted fs-7 mb-0">
                        <?php if (!empty($is_super) || !empty($is_admin)): ?>
                            상단의 등록 폼에서 제작하셨던 사이트 URL을 입력하여 첫 포트폴리오를 등록해 보세요! 💕
                        <?php else: ?>
                            곧 멋진 제작 사례들이 업데이트될 예정입니다. 맞춤 웹 개발 상담은 언제든 환영합니다!
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <!-- 포트폴리오 스타일 -->
        <style>
            .portfolio-card {
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }
            .portfolio-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
            }
            .portfolio-thumb-img {
                transition: transform 0.4s ease;
            }
            .portfolio-card:hover .portfolio-thumb-img {
                transform: scale(1.05);
            }
            .portfolio-thumb-overlay {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                opacity: 0;
                transition: opacity 0.3s ease;
                backdrop-filter: blur(2px);
            }
            .portfolio-thumb-wrapper:hover .portfolio-thumb-overlay {
                opacity: 1;
            }
            .portfolio-title-link:hover {
                color: #f16819 !important;
            }
        </style>

        <script>
            function handlePortfolioImgError(img) {
                if (!img.dataset.step) {
                    img.dataset.step = '1';
                    if (img.dataset.fallbackThum) {
                        img.src = img.dataset.fallbackThum;
                        return;
                    }
                }
                if (img.dataset.step === '1') {
                    img.dataset.step = '2';
                    if (img.dataset.fallbackMicro) {
                        img.src = img.dataset.fallbackMicro;
                        return;
                    }
                }
                img.onerror = null;
                img.src = 'https://placehold.co/800x500/1e293b/f8fafc?text=' + encodeURIComponent(img.alt || 'Website');
            }
        </script>
    </div>
</div>

<?php include_footer($siteConfig ?? []); ?>
