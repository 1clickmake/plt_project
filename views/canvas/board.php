<?php
$isEmbed = isset($_GET['embed']) && in_array(strtolower((string)$_GET['embed']), ['1', 'true', 'yes']);
$theme = strtolower((string)($_GET['theme'] ?? 'dark'));
$isLightTheme = in_array($theme, ['light', 'white']);
?>
<!DOCTYPE html>
<html lang="ko" <?= $isLightTheme ? 'data-bs-theme="light"' : 'data-bs-theme="dark"' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($vendor['company_name']) ? htmlspecialchars($vendor['company_name']) . ' - ' : '' ?>게시판 문의 - 파렛트랙 자동 견적 시스템</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.75);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .btn-primary-gradient {
            background: linear-gradient(to right, #0ea5e9, #3b82f6);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary-gradient:hover {
            background: linear-gradient(to right, #0284c7, #2563eb);
            color: white;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
            transform: translateY(-1px);
        }
        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
            border-radius: 0.5rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }
        .attachment-item {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 0.5rem;
            padding: 0.6rem;
            transition: all 0.2s ease;
        }
        .attachment-item:hover {
            border-color: rgba(56, 189, 248, 0.3);
        }
    </style>
</head>
<body class="<?= $isLightTheme ? 'theme-light' : '' ?> p-3 p-md-4">
<div class="container-xl py-2">

    <!-- 상단 헤더 & 모드 전환 네비게이션 -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary flex-wrap gap-3">
        <div>
            <h4 class="fw-bold m-0" style="background: -webkit-linear-gradient(#38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <?= !empty($vendor['company_name']) ? htmlspecialchars($vendor['company_name']) . ' - ' : '' ?>창고 견적 문의
            </h4>
            <p class="text-muted small m-0 mt-1">도면 작성이 번거로우신가요? 글과 파일만 남겨주시면 전문가가 도면 및 맞춤 견적서를 제작해 드립니다.</p>
        </div>

        <!-- 모드 전환 버튼 & 동영상 메뉴얼 -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group btn-group-sm" role="group">
                <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>" class="btn btn-outline-info px-3">📐 스마트 캔버스 배치</a>
                <!-- <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/easy" class="btn btn-outline-info px-3">🟢 이지 모드</a> -->
                <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/board" class="btn btn-primary-gradient px-3 fw-bold">📝 게시판 문의</a>
            </div>
            <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>?tutorial=1" class="btn btn-sm btn-outline-info">🎮 리모콘 사용법</a>
        </div>
    </div>

    <!-- 메인 문의 작성 카드 -->
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="glass-panel p-4 p-md-5">
                
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom border-secondary">
                    <span class="fs-4">📋</span>
                    <h5 class="fw-bold m-0 text-info">고객 맞춤 견적 문의 작성</h5>
                    <span class="badge bg-primary-subtle text-info border border-info ms-auto">게시판 모드</span>
                </div>

                <form id="boardInquiryForm" onsubmit="event.preventDefault(); submitBoardInquiry();">
                    
                    <!-- 1. 기본 인적사항 -->
                    <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-tie text-info"></i> 기본 연락처 정보 <span class="text-danger small">(★ 필수)</span>
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">회사명 / 상호 <span class="text-danger">★</span></label>
                            <input type="text" class="form-control" id="board-company" placeholder="예: (주)파로퀘스트 로지스" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">담당자 성함 <span class="text-danger">★</span></label>
                            <input type="text" class="form-control" id="board-name" placeholder="예: 홍길동 팀장" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">연락처 (카카오톡 알림 수신) <span class="text-danger">★</span></label>
                            <input type="tel" class="form-control" id="board-phone" placeholder="예: 010-1234-5678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">이메일 주소 (견적서 수신용) <span class="text-danger">★</span></label>
                            <input type="email" class="form-control" id="board-email" placeholder="example@company.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">시공 현장 주소 <span class="text-danger">★</span></label>
                            <input type="text" class="form-control" id="board-address" placeholder="예: 경기도 평택시 포승읍 평택항로 123" required>
                        </div>
                    </div>

                    <!-- 2. 설치 및 자재 옵션 -->
                    <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-warning"></i> 견적 희망 옵션
                    </h6>
                    <div class="row g-3 mb-4 p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-2 d-block">자재 상태 선택</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="board_condition" id="b_cond_new" value="new" checked>
                                <label class="form-check-label text-light small" for="b_cond_new">신규 자재</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="board_condition" id="b_cond_used" value="used">
                                <label class="form-check-label text-light small" for="b_cond_used">중고 자재</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="board_condition" id="b_cond_both" value="both">
                                <label class="form-check-label text-light small" for="b_cond_both">모두(비교 견적)</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="board-self-install" value="1">
                                <label class="form-check-label text-light small" for="board-self-install">
                                    직접 설치 (시공 인건비 제외, 자재만 납품받기)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 3. 문의 내용 -->
                    <h6 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-success"></i> 문의 상세 내용
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">문의 제목 <span class="text-danger">★</span></label>
                            <input type="text" class="form-control" id="board-title" placeholder="예: [창고 100평] 3단 파렛트랙 설치 견적 및 배치 문의드립니다." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">상세 요청사항</label>
                            <textarea class="form-control" id="board-content" rows="4" placeholder="창고 크기, 보관할 화물의 규격(파렛트 크기 및 무게), 지게차 보유 유무 등 알려주실 수 있는 모든 정보를 자유롭게 남겨주세요."></textarea>
                        </div>
                    </div>

                    <!-- 4. 다중 첨부파일 (+/- 동적 관리) -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label text-white fw-bold small m-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-paperclip text-info"></i> 도면 및 현장 사진 첨부
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" onclick="addAttachmentRow()">
                                <i class="fa-solid fa-plus"></i> 파일 추가
                            </button>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 0.78rem;">
                            💡 창고 건축도면(CAD, PDF), 손그림 스케치, 현장 사진 등을 첨부해주시면 더욱 정확한 배치가 가능합니다.
                        </p>
                        
                        <div id="attachments-container" class="d-flex flex-column gap-2">
                            <!-- 기본 1번째 파일 인풋 -->
                            <div class="attachment-item d-flex align-items-center gap-2">
                                <input type="file" class="form-control form-control-sm extra-file-input" accept="image/*,.pdf,.xls,.xlsx,.zip">
                                <button type="button" class="btn btn-sm btn-outline-secondary disabled" style="opacity: 0.3;">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 제출 버튼 -->
                    <div class="pt-3 border-top border-secondary text-center">
                        <button type="submit" id="boardSubmitBtn" class="btn btn-primary-gradient w-100 py-3 rounded-3 shadow-lg fs-6">
                            🚀 문의 및 견적 요청 접수하기
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.vendorUserId = <?= json_encode($vendor['url_slug'] ?? '') ?>;

// --- 동적 첨부파일 (+/-) 관리 함수 ---
function addAttachmentRow() {
    const container = document.getElementById('attachments-container');
    const row = document.createElement('div');
    row.className = 'attachment-item d-flex align-items-center gap-2';
    row.innerHTML = `
        <input type="file" class="form-control form-control-sm extra-file-input" accept="image/*,.pdf,.xls,.xlsx,.zip">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAttachmentRow(this)" title="삭제">
            <i class="fa-solid fa-minus"></i>
        </button>
    `;
    container.appendChild(row);
}

function removeAttachmentRow(btn) {
    const row = btn.closest('.attachment-item');
    if (row) {
        row.remove();
    }
}

// --- 게시판 문의 폼 제출 함수 ---
function submitBoardInquiry() {
    const company = document.getElementById('board-company').value.trim();
    const name    = document.getElementById('board-name').value.trim();
    const phone   = document.getElementById('board-phone').value.trim();
    const email   = document.getElementById('board-email').value.trim();
    const address = document.getElementById('board-address').value.trim();
    const title   = document.getElementById('board-title').value.trim();
    const content = document.getElementById('board-content').value.trim();

    const conditionNode = document.querySelector('input[name="board_condition"]:checked');
    const conditionType = conditionNode ? conditionNode.value : 'new';
    
    const selfInstallNode = document.getElementById('board-self-install');
    const selfInstall = (selfInstallNode && selfInstallNode.checked) ? 1 : 0;

    if (!company || !name || !phone || !email || !address || !title) {
        alert('★ 표시된 필수 항목을 모두 입력해주세요.');
        return;
    }

    const submitBtn = document.getElementById('boardSubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ 문의 접수 중...';

    const payload = {
        vendor_user_id: window.vendorUserId || 0,
        company: company,
        name: name,
        phone: phone,
        email: email,
        address: address,
        condition_type: conditionType,
        self_install: selfInstall,
        canvas_data: '',
        summary: content,
        source_mode: 'board',
        rack_indep: 0,
        rack_conn: 0,
        image_data: '',
        title: title,
        content: content
    };

    const formData = new FormData();
    formData.append('json_payload', JSON.stringify(payload));

    // 다중 첨부파일 수집
    const fileInputs = document.querySelectorAll('.extra-file-input');
    fileInputs.forEach(input => {
        if (input.files && input.files[0]) {
            formData.append('extra_files[]', input.files[0]);
        }
    });

    fetch('/quote/submit', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('🎉 고객님의 문의 및 견적 요청이 성공적으로 접수되었습니다!\n담당자가 확인 후 신속히 연락드리겠습니다.');
            window.location.reload();
        } else {
            alert('❌ 저장 실패: ' + (data.message || '알 수 없는 오류'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '🚀 문의 및 견적 요청 접수하기';
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ 서버와 통신 중 오류가 발생했습니다.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '🚀 문의 및 견적 요청 접수하기';
    });
}
</script>
</body>
</html>
