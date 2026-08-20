<?php
// Layout Variables
$pageTitle = "직원 관리";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>공급사 관리 센터 - 직원 관리</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- External Vendor Dashboard CSS -->
    <link href="/css/vendor_dashboard.css" rel="stylesheet">
    
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .color-picker { display: flex; gap: 10px; flex-wrap: wrap; }
        .color-option {
            width: 30px; height: 30px; border-radius: 50%;
            cursor: pointer; border: 2px solid transparent;
            transition: transform 0.2s;
        }
        .color-option:hover { transform: scale(1.1); }
        .color-option.selected {
            border: 2px solid #fff;
            box-shadow: 0 0 8px rgba(255,255,255,0.8);
        }
        .employee-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 5px solid #ccc;
            transition: all 0.3s ease;
            color: #e2e8f0;
        }
        .employee-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .employee-card .card-footer {
            background: rgba(0,0,0,0.2) !important;
            border-top: 1px solid rgba(255,255,255,0.05) !important;
        }
        
        /* 모달 다크 테마 커스텀 */
        .modal-content {
            background-color: #1e293b;
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .modal-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .modal-footer { border-top: 1px solid rgba(255,255,255,0.1); }
        .modal .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
        .form-control {
            background-color: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }
        .form-control:focus {
            background-color: rgba(0,0,0,0.3);
            border-color: #60a5fa;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25);
        }
        .text-muted { color: #94a3b8 !important; }
    </style>
</head>
<body>

    <!-- 🧭 좌측 네비게이션 사이드바 -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- 💻 우측 메인 대시보드 영역 -->
    <main class="main-content">
        <div class="top-navbar">
            <div class="navbar-title fw-bold text-light" style="font-size: 1.1rem;">
                SaaS Dashboard &gt; 직원 관리
            </div>
            <div class="user-profile d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-user text-info fs-5"></i>
                <span class="small font-monospace text-light"><?= htmlspecialchars($_SESSION['user']['username'] ?? '관리자') ?>님</span>
            </div>
        </div>

        <div class="content-body">
            <div class="glass-panel p-0">
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.01); border-radius: 16px 16px 0 0;">
                    <h4 class="m-0 fw-bold d-flex align-items-center gap-2" style="color: #38bdf8;">
                        <i class="fa-solid fa-users"></i> 영업 담당자(직원) 관리
                    </h4>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none;">
                        <i class="fa-solid fa-plus me-1"></i> 신규 직원 등록
                    </button>
                </div>
                
                <div class="p-4">
                    <p class="text-light opacity-75 small mb-4">
                        <i class="fa-solid fa-circle-info me-1"></i> 등록된 직원은 전용 프로필로 접속할 수 있으며, 견적서 출력 시 해당 직원의 고유 컬러와 개인 연락처가 적용됩니다.
                    </p>

                    <div class="row">
                        <?php if (empty($employees)): ?>
                            <div class="col-12 text-center py-5">
                                <span style="font-size: 3rem;">👥</span>
                                <h5 class="text-light mt-3 fw-bold opacity-75">등록된 직원이 없습니다.<br><small class="fw-normal">우측 상단 버튼을 눌러 신규 직원을 등록해 주세요.</small></h5>
                            </div>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card employee-card h-100 shadow" style="border-left-color: <?= htmlspecialchars($emp['color_code']) ?> !important;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="card-title fw-bold text-white mb-1"><?= htmlspecialchars($emp['name']) ?></h5>
                                                    <h6 class="card-subtitle text-muted mb-3"><?= htmlspecialchars($emp['title'] ?: '직책 없음') ?></h6>
                                                </div>
                                                <span class="badge rounded-pill shadow-sm" style="background-color: <?= htmlspecialchars($emp['color_code']) ?>; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                                                    <i class="fa-solid fa-palette me-1"></i>시그니처
                                                </span>
                                            </div>
                                            <p class="card-text mb-2 text-light opacity-75"><i class="fa-solid fa-phone me-2"></i><?= htmlspecialchars($emp['phone'] ?: '연락처 없음') ?></p>
                                            <p class="card-text text-muted small m-0"><i class="fa-regular fa-clock me-2"></i>등록일: <?= date('Y-m-d', strtotime($emp['created_at'])) ?></p>
                                        </div>
                                        <div class="card-footer d-flex justify-content-end gap-2 p-3">
                                            <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="editEmployee(<?= htmlspecialchars(json_encode($emp)) ?>)">
                                                <i class="fa-solid fa-pen"></i> 수정
                                            </button>
                                            <form action="/vendor/employees/delete" method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fa-solid fa-trash"></i> 삭제
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 🟢 직원 등록/수정 모달 -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <form id="employeeForm" action="/vendor/employees/create" method="POST">
                    <input type="hidden" name="id" id="emp_id" value="">
                    
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-user-plus text-primary me-2"></i>직원 등록</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-light opacity-75">이름 (필수)</label>
                            <input type="text" class="form-control" name="name" id="emp_name" required placeholder="예: 홍길동">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-light opacity-75">직책</label>
                            <input type="text" class="form-control" name="title" id="emp_title" placeholder="예: 대리, 과장, 팀장">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-light opacity-75">개인 연락처 (직통/휴대폰)</label>
                            <input type="text" class="form-control" name="phone" id="emp_phone" placeholder="견적서에 표기될 연락처">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold text-light opacity-75">견적서 시그니처 컬러</label>
                            <input type="hidden" name="color_code" id="emp_color" value="#a5d6a7"> <!-- Default green -->
                            <div class="color-picker mt-2 bg-dark p-3 rounded" style="border: 1px solid rgba(255,255,255,0.05);">
                                <!-- 10 preset colors -->
                                <div class="color-option selected" style="background-color: #a5d6a7;" data-color="#a5d6a7" title="연두색 (기본)"></div>
                                <div class="color-option" style="background-color: #90caf9;" data-color="#90caf9" title="하늘색"></div>
                                <div class="color-option" style="background-color: #f48fb1;" data-color="#f48fb1" title="핑크색"></div>
                                <div class="color-option" style="background-color: #ce93d8;" data-color="#ce93d8" title="보라색"></div>
                                <div class="color-option" style="background-color: #ffcc80;" data-color="#ffcc80" title="주황색"></div>
                                <div class="color-option" style="background-color: #bcaaa4;" data-color="#bcaaa4" title="갈색"></div>
                                <div class="color-option" style="background-color: #80cbc4;" data-color="#80cbc4" title="민트색"></div>
                                <div class="color-option" style="background-color: #e6ee9c;" data-color="#e6ee9c" title="라임색"></div>
                                <div class="color-option" style="background-color: #9e9e9e;" data-color="#9e9e9e" title="회색"></div>
                                <div class="color-option" style="background-color: #5c6bc0;" data-color="#5c6bc0" title="남색"></div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer" style="background: rgba(0,0,0,0.1);">
                        <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">취소</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill" style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none;">저장하기</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Color Picker Logic
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('emp_color').value = this.dataset.color;
            });
        });

        function editEmployee(emp) {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-primary me-2"></i>직원 정보 수정';
            document.getElementById('employeeForm').action = '/vendor/employees/update';
            document.getElementById('emp_id').value = emp.id;
            document.getElementById('emp_name').value = emp.name;
            document.getElementById('emp_title').value = emp.title;
            document.getElementById('emp_phone').value = emp.phone;
            document.getElementById('emp_color').value = emp.color_code;
            
            // Select color option
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            const option = document.querySelector(`.color-option[data-color="${emp.color_code}"]`);
            if (option) option.classList.add('selected');

            new bootstrap.Modal(document.getElementById('addEmployeeModal')).show();
        }

        // Reset modal on close
        document.getElementById('addEmployeeModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus text-primary me-2"></i>직원 등록';
            document.getElementById('employeeForm').action = '/vendor/employees/create';
            document.getElementById('employeeForm').reset();
            document.getElementById('emp_id').value = '';
            
            // Reset color to default
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            document.querySelector('.color-option[data-color="#a5d6a7"]').classList.add('selected');
            document.getElementById('emp_color').value = '#a5d6a7';
        });
    </script>
</body>
</html>
