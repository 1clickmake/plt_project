<?php include_admin_header('Chatbot Logs'); ?>

<div class="glass-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Chatbot Conversation Logs</h1>
            <p class="text-muted-small">고객과 AI 챗봇의 대화 내용을 모니터링하고 관리합니다.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                <i class="fa-solid fa-broom me-1"></i> 데이터 정리
            </button>
        </div>
    </div>

    <!-- 검색 필터 -->
    <form method="GET" class="row g-3 mb-4 p-3 rounded-4" style="background: rgba(255,255,255,0.05);">
        <div class="col-md-4">
            <label class="form-label text-muted-small">내용 검색 (질문/답변)</label>
            <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="키워드 입력...">
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted-small">시작일</label>
            <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label text-muted-small">종료일</label>
            <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fa-solid fa-search me-1"></i> 검색
            </button>
        </div>
    </form>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'cleanup'): ?>
        <div class="alert alert-success bg-success text-white border-0 mb-4" style="--bs-bg-opacity: .4;">
            <i class="fa-solid fa-check-circle me-2"></i> 성공적으로 <?= $_GET['count'] === 'all' ? '전체' : (int)$_GET['count'] . '개' ?>의 로그를 정리했습니다.
        </div>
    <?php endif; ?>

    <!-- 로그 테이블 -->
    <div class="table-responsive">
        <table class="table table-dark table-hover table-striped text-center admin-table">
            <thead>
                <tr>
                    <th style="width: 150px;">일시</th>
                    <th style="width: 120px;">IP / 사용자</th>
                    <th>대화 내용 (질문 & 답변)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">기록된 대화 내용이 없습니다.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted-small"><?= $log['created_at'] ?></td>
                            <td>
                                <div class="text-muted-small"><?= $log['ip_address'] ?></div>
                                <?php if ($log['user_id']): ?>
                                    <span class="badge bg-info btn-xs"><?= $log['user_id'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary btn-xs">Guest</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-start">
                                <div class="mb-2">
                                    <span class="badge bg-primary me-1">Q</span> 
                                    <strong><?= htmlspecialchars($log['question']) ?></strong>
                                </div>
                                <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.2); font-size: 0.9rem;">
                                    <span class="badge bg-success me-1">A</span> 
                                    <div class="d-inline text-muted-small"><?= nl2br(strip_tags(htmlspecialchars_decode($log['answer']))) ?></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 페이징 -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center admin-pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&date_start=<?= urlencode($date_start) ?>&date_end=<?= urlencode($date_end) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="background: #1e1e2d; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
            <form action="/admin/chatbot/logs/cleanup" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <div class="modal-header border-0">
                    <h5 class="modal-title">오래된 로그 자동 정리</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-3 text-muted-small">얼마나 오래된 데이터를 삭제하시겠습니까?</p>
                    <div class="d-flex flex-column gap-2">
                        <label class="cleanup-option">
                            <input type="radio" name="months" value="3" checked> 3개월 이전 데이터 삭제
                        </label>
                        <label class="cleanup-option">
                            <input type="radio" name="months" value="6"> 6개월 이전 데이터 삭제
                        </label>
                        <label class="cleanup-option">
                            <input type="radio" name="months" value="12"> 12개월 이전 데이터 삭제
                        </label>
                        <label class="cleanup-option border-danger border-1">
                            <input type="radio" name="months" value="all"> <span class="text-danger fw-bold">전체 데이터 삭제 (로그 비우기)</span>
                        </label>
                    </div>
                    <div class="mt-3 p-3 bg-danger bg-opacity-10 rounded-3">
                        <small class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> 주의: 삭제된 데이터는 복구할 수 없습니다.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-sm btn-danger">지금 정리하기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cleanup-option {
    padding: 15px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}
.cleanup-option:hover { background: rgba(255,255,255,0.1); }
.cleanup-option input { margin-right: 10px; }
.btn-xs { padding: 1px 5px; font-size: 10px; }
</style>

<script>
    $('#link-chatbot-logs').addClass('active');
</script>

<?php include_admin_footer(); ?>
