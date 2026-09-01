<?php
// views/vendor/inquiry_detail.php
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 게시판 문의 상세</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 게시판 문의 상세 보기
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/vendor/quotes" class="btn btn-outline-secondary btn-sm rounded px-3" style="font-size:0.8rem; border-color: rgba(255,255,255,0.15); color:#cbd5e1;">
                    <i class="fa-solid fa-arrow-left"></i> 목록으로 돌아가기
                </a>
                <div class="user-profile d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-user text-info fs-5"></i>
                    <span class="small font-monospace text-light"><?= htmlspecialchars($user['username'] ?? 'User') ?>님</span>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="glass-panel p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="m-0 fw-bold text-light">
                        <?= htmlspecialchars($inquiry['title'] ?? '제목 없음') ?>
                    </h4>
                    <div>
                        <?php if($inquiry['status'] === 'pending'): ?>
                            <span class="badge bg-danger fs-6">미답변</span>
                        <?php elseif($inquiry['status'] === 'answered'): ?>
                            <span class="badge bg-success fs-6">답변완료</span>
                        <?php else: ?>
                            <span class="badge bg-secondary fs-6">종료</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <h6 class="text-info fw-bold mb-3"><i class="fa-solid fa-building me-2"></i>고객 정보</h6>
                            <table class="table table-sm table-borderless text-light mb-0" style="--bs-table-bg: transparent;">
                                <tr><th style="width: 100px;" class="text-secondary">회사명</th><td><?= htmlspecialchars($inquiry['company'] ?: '개인') ?></td></tr>
                                <tr><th class="text-secondary">담당자명</th><td><?= htmlspecialchars($inquiry['name']) ?></td></tr>
                                <tr><th class="text-secondary">연락처</th><td class="font-monospace text-warning"><?= htmlspecialchars($inquiry['phone']) ?></td></tr>
                                <tr><th class="text-secondary">이메일</th><td><?= htmlspecialchars($inquiry['email'] ?: '-') ?></td></tr>
                                <tr><th class="text-secondary">현장 주소</th><td><?= htmlspecialchars($inquiry['address'] ?: '-') ?></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <h6 class="text-info fw-bold mb-3"><i class="fa-regular fa-clock me-2"></i>문의 정보</h6>
                            <table class="table table-sm table-borderless text-light mb-0" style="--bs-table-bg: transparent;">
                                <tr><th style="width: 100px;" class="text-secondary">문의 번호</th><td class="font-monospace">#<?= $inquiry['id'] ?></td></tr>
                                <tr><th class="text-secondary">작성 일시</th><td class="font-monospace"><?= $inquiry['created_at'] ?></td></tr>
                                <tr><th class="text-secondary">최근 수정</th><td class="font-monospace"><?= $inquiry['updated_at'] ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);">
                    <h6 class="text-warning fw-bold mb-3"><i class="fa-solid fa-comment-dots me-2"></i>문의 내용</h6>
                    <div class="text-light" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;"><?= htmlspecialchars($inquiry['content'] ?? '') ?></div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="/vendor/quotes" class="btn btn-secondary px-4 py-2 fw-bold" style="border-radius: 12px;">목록으로 돌아가기</a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
