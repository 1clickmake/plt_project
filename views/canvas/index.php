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
        .file-preview-thumb {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 0.4rem;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-size: 0.6rem;
            color: #94a3b8;
            flex-shrink: 0;
        }
        .file-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .del-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 16px;
            height: 16px;
            background: transparent;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            cursor: pointer;
            z-index: 10;
            filter: drop-shadow(0 0 3px rgba(0,0,0,0.9));
            transition: transform 0.2s, color 0.2s;
        }
        .del-btn:hover {
            transform: scale(1.2);
            color: #ef4444;
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

        <!-- 모드 전환 버튼 제거 및 간소화 -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/cad?tutorial=1" class="btn btn-sm btn-outline-info">🎮 AI 도면 리모콘 사용법</a>
        </div>
    </div>

    <!-- 메인 문의 작성 카드 (투트랙 하이브리드) -->
    <div class="row">
        <!-- 좌측: 간편 게시판 폼 (col-lg-8) -->
        <div class="col-lg-8 mb-4">
            <div class="glass-panel p-4 p-md-5 h-100">
                
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
                        <div class="col-12">
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
                        <div class="col-12 mt-2">
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

                    <!-- 4. 다중 첨부파일 (멀티 업로드) -->
                    <div class="mb-4">
                        <label class="form-label text-white fw-bold small mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-paperclip text-info"></i> 도면 및 현장 사진 첨부
                        </label>
                        <p class="text-muted small mb-2" style="font-size: 0.78rem;">
                            💡 창고 건축도면(CAD, PDF), 손그림 스케치, 현장 사진 등을 한 번에 여러 장 선택하여 첨부하실 수 있습니다.
                        </p>
                        
                        <div class="attachment-item">
                            <input type="file" id="board-file-input" class="form-control form-control-sm extra-file-input" accept="image/*,.pdf,.xls,.xlsx,.zip" multiple>
                        </div>
                        <!-- 미리보기 영역 -->
                        <div id="file-preview-area" class="d-flex flex-wrap gap-2 mt-3"></div>
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

        <!-- 우측: AI CAD 배너 (col-lg-4) -->
        <div class="col-lg-4 mb-4">
            <div class="glass-panel d-flex flex-column h-100 justify-content-between text-center overflow-hidden" style="background: rgba(14, 165, 233, 0.03); border-color: rgba(56, 189, 248, 0.2);">
                <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/cad" class="d-block position-relative" style="transition: transform 0.3s; overflow: hidden;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <img src="/assets/images/banner_ai_cad.webp" alt="AI 스마트 랙킹 시스템" class="img-fluid w-100 border-bottom border-info border-opacity-25" style="object-fit: cover;">
                </a>
                
                <div class="p-4 d-flex flex-column align-items-center flex-grow-1 justify-content-center">
                    <div class="mb-3">
                        <span style="font-size: 2.5rem; filter: drop-shadow(0 0 10px rgba(56, 189, 248, 0.4));">🤖</span>
                    </div>
                    <h5 class="fw-bold text-white mb-2" style="line-height: 1.4; font-size: 1.1rem;">"알아서 그려준다는데,<br>굳이 내가 왜 그려야 할까?"</h5>
                    <p class="text-info small mb-4 text-decoration-underline" data-bs-toggle="modal" data-bs-target="#benefitsModal" style="cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#7dd3fc'" onmouseout="this.style.color='#0dcaf0'">
                        🎁 직접 그리면 얻게 되는 3가지 혜택 보기
                    </p>
                    <a href="/quote/<?= htmlspecialchars($vendor['url_slug'] ?? '') ?>/cad" class="btn btn-outline-info rounded-pill px-4 py-3 fw-bold w-100" style="transition: all 0.3s; box-shadow: 0 4px 15px rgba(56,189,248,0.2);">
                        📐 AI 스마트 설계 시작하기 <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div> <!-- .container-xl -->

<!-- 3가지 혜택 모달 -->
<div class="modal fade" id="benefitsModal" tabindex="-1" aria-labelledby="benefitsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content glass-panel" style="background: rgba(15, 23, 42, 0.95);">
      <div class="modal-header border-secondary">
        <h5 class="modal-title fw-bold text-white" id="benefitsModalLabel">왜 직접 그려보는 것이 유리할까요?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-light p-4 p-md-5" style="font-size: 0.95rem; line-height: 1.6;">
          <h6 class="text-info fw-bold mb-2">🕒 1. 귀찮은 연락 NO! 5분 만에 끝나는 즉각적인 물량 확인</h6>
          <p class="text-muted mb-4">기존에는 업체에 견적을 문의하고 도면이 나오기까지 하염없이 기다려야 했습니다. 바쁜 업무 중에 걸려 오는 확인 전화는 덤이죠. 이제 마우스로 가볍게 창고 형태만 그리고 <strong>'AI 자동 배치'</strong> 버튼을 누르세요. 귀찮은 소통 없이 단 5분 만에 내 창고의 최적 랙 수량과 도면을 즉시 확인할 수 있습니다.</p>

          <h6 class="text-info fw-bold mb-2">🛡️ 2. 확실하다! (내 비즈니스에 딱 맞는 공간 효율 검증)</h6>
          <p class="text-muted mb-4">내 창고의 실제 업무 동선과 적재 품목의 특성은 내가 가장 잘 압니다. 직접 그려보며 우리 회사에 가장 최적화된 배치를 눈으로 확인하세요. 확보한 도면 데이터를 바탕으로 시공 업체와 상담하면, 불필요한 오해나 커뮤니케이션 미스 없이 가장 빠르고 완벽한 맞춤형 창고를 완성할 수 있습니다.</p>

          <h6 class="text-info fw-bold mb-2">🎮 3. 눈치 볼 필요 없는 무한 시뮬레이션</h6>
          <p class="text-muted mb-4">“통로를 3m로 넓히면 수량이 얼마나 줄까?”, “가로 배열과 세로 배열 중 어느 쪽이 좋을까?” 궁금증이 생길 때마다 도면 수정을 요청하기는 부담스럽습니다. 이제 클릭 몇 번으로 실시간 배치를 변경하고 수량을 업데이트하세요. 내 창고에 딱 맞는 최적의 세팅을 직접 설계하고 비교해 볼 수 있습니다.</p>
          
          <hr class="border-secondary my-4">
          <div class="text-center">
              <span style="font-size: 2.5rem; filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));">✨</span>
              <h6 class="text-white fw-bold mt-2">어렵지 않냐고요? AI 설계 비서가 함께합니다!</h6>
              <p class="text-muted small mb-0">마치 게임을 하듯 쉽고 직관적입니다.<br>지금 바로 내 비즈니스 공간의 진짜 가치를 스마트하게 확인해 보세요!</p>
          </div>
      </div>
      <div class="modal-footer border-secondary justify-content-center pb-4">
        <button type="button" class="btn btn-primary-gradient px-5 py-2 rounded-pill" data-bs-dismiss="modal" onclick="document.querySelector('.btn-outline-info').click();">
            👉 지금 바로 스마트 설계 시작하기
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.vendorUserId = <?= json_encode($vendor['url_slug'] ?? '') ?>;

// --- 첨부파일 미리보기 처리 ---
let selectedFilesList = [];

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.querySelector('.extra-file-input');
    const previewArea = document.getElementById('file-preview-area');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            selectedFilesList = selectedFilesList.concat(newFiles);
            updateFileInputAndPreview();
        });
    }
    
    window.removeFileFromPreview = function(index) {
        selectedFilesList.splice(index, 1);
        updateFileInputAndPreview();
    };

    function updateFileInputAndPreview() {
        const dt = new DataTransfer();
        selectedFilesList.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        
        previewArea.innerHTML = '';
        selectedFilesList.forEach((file, index) => {
            const thumb = document.createElement('div');
            thumb.className = 'file-preview-thumb shadow-sm';
            
            const delBtn = document.createElement('div');
            delBtn.className = 'del-btn';
            delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            delBtn.onclick = () => removeFileFromPreview(index);
            thumb.appendChild(delBtn);
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = ev => {
                    const img = document.createElement('img');
                    img.src = ev.target.result;
                    img.alt = 'preview';
                    thumb.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                const shortName = file.name.length > 9 ? file.name.substring(0, 7) + '…' : file.name;
                const docDiv = document.createElement('div');
                docDiv.innerHTML = `<div style="font-size:1.5rem; margin-bottom:2px; color:#38bdf8; text-align:center;">📄</div><div style="text-align:center;word-break:break-all;line-height:1.1;padding:0 2px;">${shortName}</div>`;
                thumb.appendChild(docDiv);
            }
            previewArea.appendChild(thumb);
        });
    }
});


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

    // 다중 첨부파일 수집 (멀티 업로드 처리)
    const fileInput = document.querySelector('.extra-file-input');
    if (fileInput && fileInput.files) {
        Array.from(fileInput.files).forEach(file => {
            formData.append('extra_files[]', file);
        });
    }

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
