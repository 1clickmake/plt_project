<?php $title = "정산 관리"; include_header($siteConfig); ?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="glass-card">
                <h2 class="h4 mb-4">정산 신청</h2>
                <div class="balance-card bg-primary text-white p-4 rounded-4 mb-4">
                    <p class="mb-1 opacity-75">출금 가능 금액</p>
                    <h3 class="display-6 fw-bold mb-0">$<?= number_format($balance, 2) ?></h3>
                </div>

                <?php if ($hasPending): ?>
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <i class="fa-solid fa-clock me-2"></i> 현재 대기 중인 정산 요청이 있습니다. 처리가 완료된 후 다시 신청해 주세요.
                    </div>
                <?php endif; ?>

                <form action="/seller/settlement/request" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-3">
                        <label class="form-label">정산 신청 금액 ($)</label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="1" max="<?= $balance ?>" required <?= ($balance <= 0 || $hasPending) ? "disabled" : "" ?>>
                        <small class="text-muted">수수료(<?= $commission ?>%)가 차감된 금액이 실제 지급됩니다.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">메모 (계좌 정보 등)</label>
                        <textarea name="memo" class="form-control" rows="3" placeholder="정산 받으실 계좌 정보나 요청 사항을 적어주세요." required <?= $hasPending ? "disabled" : "" ?>></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3" <?= ($balance <= 0 || $hasPending) ? "disabled" : "" ?>>
                        <i class="fa-solid fa-paper-plane me-2"></i> <?= $hasPending ? "정산 대기 중..." : "정산 요청하기" ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="glass-card">
                <h2 class="h4 mb-4">정산 내역</h2>
                
                <?php if (isset($_GET["msg"])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_GET["msg"]) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table mypage-table">
                        <thead>
                            <tr>
                                <th>요청일</th>
                                <th>신청 금액</th>
                                <th>실제 지급액</th>
                                <th>상태</th>
                                <th>처리일</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($history)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">정산 요청 내역이 없습니다.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><?= date("Y-m-d", strtotime($h["request_date"])) ?></td>
                                        <td>$<?= number_format($h["amount"], 2) ?></td>
                                        <td class="text-primary fw-bold">$<?= number_format($h["amount"] * (1 - (($commission ?? 10) / 100)), 2) ?></td>
                                        <td>
                                            <?php 
                                                $badge = "bg-warning text-dark";
                                                $statusText = "대기";
                                                if ($h["status"] === "approved") { $badge = "bg-info text-dark"; $statusText = "승인됨"; }
                                                elseif ($h["status"] === "paid") { $badge = "bg-success"; $statusText = "지급완료"; }
                                                elseif ($h["status"] === "rejected") { $badge = "bg-danger"; $statusText = "반려됨"; }
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $statusText ?></span>
                                        </td>
                                        <td><?= $h["process_date"] ? date("Y-m-d", strtotime($h["process_date"])) : "-" ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_footer($siteConfig); ?>