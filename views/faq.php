<?php include_header('FAQ', $siteConfig); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="text-center mb-5" data-aos="fade-up">
                <h1 class="display-5 fw-bold mb-3">자주 묻는 질문</h1>
                <p class="text-muted lead mb-4">궁금하신 점을 빠르게 확인하실 수 있습니다.</p>
                
                <div class="search-trigger">
                    <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#faqSearchModal">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> 질문 검색하기
                    </button>
                    
                    <?php if (!empty($searchTerm)): ?>
                        <div class="mt-3">
                            <span class="badge bg-white text-dark p-2 border rounded-pill px-3 shadow-sm">
                                검색어: <span class="text-primary fw-bold">"<?= htmlspecialchars($searchTerm) ?>"</span> 
                                <a href="/faq<?= $currentCategory ? '?category='.urlencode($currentCategory) : '' ?>" class="ms-2 text-danger text-decoration-none" title="검색 취소">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" data-aos="fade-up" data-aos-delay="100">
                <a href="/faq<?= !empty($searchTerm) ? '?search='.urlencode($searchTerm) : '' ?>" 
                   class="btn <?= empty($currentCategory) ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-4">전체</a>
                <?php foreach ($categories as $cat): $cat = trim($cat); if (!$cat) continue; ?>
                    <a href="/faq?category=<?= urlencode($cat) ?><?= !empty($searchTerm) ? '&search='.urlencode($searchTerm) : '' ?>" 
                       class="btn <?= $currentCategory === $cat ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-4">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- FAQ List -->
            <div class="faq-accordion" data-aos="fade-up" data-aos-delay="200">
                <?php if (empty($faqs)): ?>
                    <div class="text-center py-5 bg-light rounded-4 border">
                        <i class="fa-solid fa-circle-info fa-3x text-muted mb-3 opacity-50"></i>
                        <p class="mb-0 text-muted fs-5">등록된 FAQ가 없거나 검색 결과가 없습니다.</p>
                        <?php if(!empty($searchTerm)): ?>
                            <a href="/faq" class="btn btn-link mt-2 text-decoration-none">모든 질문 보기</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="accordion accordion-flush" id="publicFaqAccordion">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="accordion-item border rounded-4 mb-2 overflow-hidden shadow-sm">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed py-3 px-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $faq['id'] ?>" aria-expanded="false">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-3 px-3"><?= htmlspecialchars($faq['category']) ?></span>
                                        <?= htmlspecialchars($faq['question']) ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#publicFaqAccordion">
                                    <div class="accordion-body bg-light-subtle p-4 border-top">
                                        <div class="d-flex">
                                            <div class="me-3 p-2 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px; height:32px; flex-shrink:0;">
                                                <i class="fa-solid fa-comment-dots" style="font-size:0.8rem;"></i>
                                            </div>
                                            <div class="lh-lg text-secondary">
                                                <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-5 d-flex justify-content-center">
                        <?php 
                        $pgQuery = [];
                        if($currentCategory) $pgQuery[] = "category=".urlencode($currentCategory);
                        if(!empty($searchTerm)) $pgQuery[] = "search=".urlencode($searchTerm);
                        $pgQueryStr = implode('&', $pgQuery);
                        ?>
                        <?= get_pagination($page, $totalPages, $pgQueryStr) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="faqSearchModal" tabindex="-1" aria-labelledby="faqSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="background: rgba(255,255,255,0.98); backdrop-filter: blur(10px);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="faqSearchModalLabel"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>질문 검색</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <form action="/faq" method="GET">
                    <?php if ($currentCategory): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory) ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-lg border rounded-pill overflow-hidden bg-light focus-ring" style="border-width: 2px !important;">
                        <input type="text" name="search" class="form-control bg-transparent border-0 py-3 ps-4" placeholder="무엇이 궁금하신가요?" value="<?= htmlspecialchars($searchTerm ?? '') ?>" autofocus>
                        <button class="btn btn-primary px-4 border-0" type="submit" style="border-radius: 0 50rem 50rem 0;">검색</button>
                    </div>
                    <div class="mt-3 text-center text-muted small">
                        <i class="fa-solid fa-circle-info me-1"></i> 제목과 내용에서 원하는 키워드를 찾아보세요.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php include_footer($siteConfig); ?>
