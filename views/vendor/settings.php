<?php
include_header('SaaS Vendor Settings');
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark text-light" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">
                <div class="card-header border-bottom-0" style="background: rgba(255,255,255,0.05); border-radius: 12px 12px 0 0;">
                    <h4 class="mb-0 py-2"><i class="fa-solid fa-building"></i> Company SaaS Settings</h4>
                </div>
                <div class="card-body p-4">
                    <form action="/vendor/settings" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label text-muted">Company Name (회사명)</label>
                            <input type="text" name="company_name" class="form-control bg-dark text-light" placeholder="e.g. Asamiya Rack Co." value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" required style="border: 1px solid rgba(255,255,255,0.2);">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted">Custom URL Slug (고유 주소)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark text-muted" style="border: 1px solid rgba(255,255,255,0.2); border-right: none;"><?= $_ENV['APP_URL'] ?? 'http://localhost:8000' ?>/quote/</span>
                                <input type="text" name="url_slug" class="form-control bg-dark text-light" placeholder="e.g. asamiya" value="<?= htmlspecialchars($settings['url_slug'] ?? '') ?>" required style="border: 1px solid rgba(255,255,255,0.2); border-left: none;">
                            </div>
                            <small class="text-muted mt-1 d-block">이 주소로 외부 고객들이 접속하게 됩니다.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted">Contact Number (연락처)</label>
                            <input type="text" name="contact_number" class="form-control bg-dark text-light" placeholder="e.g. 02-1234-5678" value="<?= htmlspecialchars($settings['contact_number'] ?? '') ?>" style="border: 1px solid rgba(255,255,255,0.2);">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted">Company Logo (로고 이미지)</label>
                            <?php if(!empty($settings['company_logo'])): ?>
                                <div class="mb-2">
                                    <img src="<?= htmlspecialchars($settings['company_logo']) ?>" alt="Current Logo" style="max-height: 80px; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="company_logo" class="form-control bg-dark text-light" accept="image/*" style="border: 1px solid rgba(255,255,255,0.2);">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted">Price List Excel (단가표 엑셀 업로드)</label>
                            <?php if(!empty($settings['price_excel_path'])): ?>
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="fa-solid fa-file-excel"></i> 엑셀 파일 적용 중</span>
                                    <small class="text-muted ms-2"><?= htmlspecialchars(basename($settings['price_excel_path'])) ?></small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="price_excel" class="form-control bg-dark text-light" accept=".xlsx,.xls" style="border: 1px solid rgba(255,255,255,0.2);">
                            <small class="text-muted mt-1 d-block">단가 데이터를 추출하기 위해 엑셀 파일을 업로드해 주세요.</small>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg" style="background: #a855f7; border: none; font-weight: 600;">Save Settings (설정 저장)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_footer();
?>
