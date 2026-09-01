import re

filepath = 'views/vendor/quote_price.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update module-row HTML
old_tr = '''    <!-- 모듈 요약 행 (클릭 시 토글) -->
    <tr class="module-row" data-mod-index="<?= $modIndex ?>" data-type="<?= htmlspecialchars($mod[\'type\'] ?? \'\') ?>" style="cursor: pointer; background-color: #f8fafc;" data-bs-toggle="collapse" data-bs-target="#collapseBom<?= $modIndex ?>" aria-expanded="false">
      <td class="center fw-bold row-no"><?= $index++ ?></td>
      <td class="center fw-bold text-primary mod-name"><?= htmlspecialchars($mod[\'name\']) ?></td>
      <td class="center mod-spec"><?= htmlspecialchars($mod[\'spec\']) ?></td>
      <td class="center fw-bold text-danger">
          <input type="number" step="1" min="1" class="form-control form-control-sm text-center fw-bold text-danger mod-qty-input bom-input" value="<?= intval($mod[\'qty\']) ?>" onclick="event.stopPropagation();" style="width: 50px; margin: 0 auto;">
      </td>
      <td class="center">대</td>
      <td class="right fw-bold mod-unit-price" data-raw="<?= intval($mod[\'raw_price\'] ?? 0) ?>"><?= number_format($mod[\'unit_price\']) ?></td>
      <td class="right fw-bold mod-total-price"><?= number_format($mod[\'total_price\']) ?></td>
      <td class="center"><span class="mod-remark"><?= htmlspecialchars($mod[\'remark\']) ?></span> <i class="fa-solid fa-chevron-down ms-1 text-muted toggle-icon" style="font-size: 0.8rem; transition: all 0.3s ease;"></i></td>
    </tr>'''

new_tr = '''    <!-- 모듈 요약 행 -->
    <tr class="module-row" data-mod-index="<?= $modIndex ?>" data-type="<?= htmlspecialchars($mod[\'type\'] ?? \'\') ?>" style="background-color: #f8fafc;">
      <td class="center fw-bold row-no"><?= $index++ ?></td>
      <td class="center fw-bold text-primary mod-name"><?= htmlspecialchars($mod[\'name\']) ?></td>
      <td class="center mod-spec"><?= htmlspecialchars($mod[\'spec\']) ?></td>
      <td class="center fw-bold text-danger mod-qty"><?= intval($mod[\'qty\']) ?></td>
      <td class="center">
          <input type="text" class="form-control form-control-sm text-center mod-unit-text" value="<?= htmlspecialchars($mod[\'unit_text\'] ?? \'대\') ?>" style="width: 40px; margin: 0 auto; padding: 1px 4px; font-size: 0.85rem;" title="단위 수정 가능 (대/개/조 등)">
      </td>
      <td class="right fw-bold mod-unit-price" data-raw="<?= intval($mod[\'raw_price\'] ?? 0) ?>"><?= number_format($mod[\'unit_price\']) ?></td>
      <td class="right fw-bold mod-total-price"><?= number_format($mod[\'total_price\']) ?></td>
      <td class="center" style="white-space: nowrap;">
          <input type="text" class="form-control form-control-sm text-center mod-remark" value="<?= htmlspecialchars(($mod[\'remark\'] !== \'자유롭게 수정하세요\' ? $mod[\'remark\'] : \'\') ?? \'\') ?>" placeholder="비고" style="width: calc(100% - 24px); display: inline-block; padding: 1px 4px; font-size: 12px;">
          <i class="fa-solid fa-chevron-down ms-1 text-muted toggle-icon" style="font-size: 0.8rem; transition: all 0.3s ease; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseBom<?= $modIndex ?>" aria-expanded="false"></i>
      </td>
    </tr>'''

content = content.replace(old_tr, new_tr)

# 2. Add module row button before linerRow
old_tbody_end = '''    </tbody>

    <!-- 바닥수평라이너 행 -->'''

new_tbody_end = '''    </tbody>

    <!-- 리스트 항목 추가 버튼 -->
    <tr id="addModuleRowBtn" style="background-color: #f0f9ff;">
        <td colspan="8" class="py-2 text-center">
            <button type="button" id="btnAddModuleRow" class="btn btn-outline-primary btn-sm px-4" style="font-size: 0.82rem;">
                <i class="fa-solid fa-plus me-1"></i> 파렛트랙 항목 추가
            </button>
        </td>
    </tr>

    <!-- 바닥수평라이너 행 -->'''

content = content.replace(old_tbody_end, new_tbody_end)

# 3. Update recalculateAll() and input listener and JS logic
old_recalc_section = '''            // 1. 모듈별 BOM 및 합계 계산
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');
                const qtyInput = modRow.querySelector('.mod-qty-input');
                const modQty = parseInt(qtyInput ? qtyInput.value : 0) || 0;

                modRow.querySelector('.row-no').innerText = runningIndex++;

                // BOM 목록 합계 계산
                const bomTable = document.querySelector(`.bom-table[data-mod-index="${modIndex}"]`);
                let bomRawSum = 0;

                if (bomTable) {
                    const bomItemRows = bomTable.querySelectorAll('.bom-item-row');
                    bomItemRows.forEach(bRow => {
                        const isLoss = bRow.getAttribute('data-is-loss') === '1';
                        if (isLoss) {
                            // Loss는 기존 값을 가져오거나 0
                            const totalEl = bRow.querySelector('.bom-part-total');
                            const val = parseFloat(totalEl ? totalEl.getAttribute('data-val') : 0) || 0;
                            bomRawSum += val;
                        } else {
                            const qtyEl = bRow.querySelector('.bom-part-qty');
                            const unitEl = bRow.querySelector('.bom-part-unit');
                            const totalEl = bRow.querySelector('.bom-part-total');

                            const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
                            const unit = parseFloat(unitEl ? unitEl.value : 0) || 0;
                            const rowTotal = Math.floor(qty * unit);

                            if (totalEl) {
                                totalEl.innerText = rowTotal.toLocaleString();
                                totalEl.setAttribute('data-val', rowTotal);
                            }
                            bomRawSum += rowTotal;
                        }
                    });
                }

                // 모듈 단가 및 총액 산출
                const modUnitPrice = calcFinalAmount(bomRawSum);
                const modTotalPrice = modUnitPrice * modQty;

                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                const totalPriceEl = modRow.querySelector('.mod-total-price');

                if (unitPriceEl) {
                    unitPriceEl.innerText = modUnitPrice.toLocaleString();
                    unitPriceEl.setAttribute('data-raw', bomRawSum);
                }
                if (totalPriceEl) {
                    totalPriceEl.innerText = modTotalPrice.toLocaleString();
                }

                grandTotal += modTotalPrice;
                totalRackQty += modQty;

                if (modType === '독립') {
                    totalFrames += modQty * 2;
                } else if (modType === '연결' || modType === '작은연결' || modType === '바이패스') {
                    totalFrames += modQty * 1;
                }
            });'''

new_recalc_section = '''            // 1. 모듈별 BOM 및 합계 계산
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');

                modRow.querySelector('.row-no').innerText = runningIndex++;

                // BOM 목록 합계 계산
                const bomTable = document.querySelector(`.bom-table[data-mod-index="${modIndex}"]`);
                let bomRawSum = 0;

                if (bomTable) {
                    const bomItemRows = bomTable.querySelectorAll('.bom-item-row');
                    bomItemRows.forEach(bRow => {
                        const isLoss = bRow.getAttribute('data-is-loss') === '1';
                        if (isLoss) {
                            const totalEl = bRow.querySelector('.bom-part-total');
                            const val = parseFloat(totalEl ? totalEl.getAttribute('data-val') : 0) || 0;
                            bomRawSum += val;
                        } else {
                            const qtyEl = bRow.querySelector('.bom-part-qty');
                            const unitEl = bRow.querySelector('.bom-part-unit');
                            const totalEl = bRow.querySelector('.bom-part-total');

                            const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
                            const unit = parseFloat(unitEl ? unitEl.value : 0) || 0;
                            const rowTotal = Math.floor(qty * unit);

                            if (totalEl) {
                                totalEl.innerText = rowTotal.toLocaleString();
                                totalEl.setAttribute('data-val', rowTotal);
                            }
                            bomRawSum += rowTotal;
                        }
                    });
                }

                // BOM 첫 번째 행의 품명/규격/수량을 리스트 행과 실시간 동기화
                const hasBom = bomTable && bomTable.querySelectorAll('.bom-item-row').length > 0;
                if (hasBom) {
                    const firstBomRow = bomTable.querySelector('.bom-item-row:not([data-is-loss="1"])');
                    if (firstBomRow) {
                        const bomName = firstBomRow.querySelector('.bom-part-name');
                        const bomSpec = firstBomRow.querySelector('.bom-part-spec');
                        const bomQty = firstBomRow.querySelector('.bom-part-qty');
                        if (bomName && modRow.querySelector('.mod-name')) {
                            modRow.querySelector('.mod-name').innerText = bomName.value.trim() || '파렛트랙';
                        }
                        if (bomSpec && modRow.querySelector('.mod-spec')) {
                            modRow.querySelector('.mod-spec').innerText = bomSpec.value.trim();
                        }
                        if (bomQty && modRow.querySelector('.mod-qty')) {
                            modRow.querySelector('.mod-qty').innerText = parseInt(bomQty.value) || 1;
                        }
                    }
                }

                const qtyEl = modRow.querySelector('.mod-qty');
                const modQty = parseInt(qtyEl ? qtyEl.innerText : 0) || 0;

                let modUnitPrice;
                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                if (hasBom) {
                    modUnitPrice = calcFinalAmount(bomRawSum);
                } else {
                    modUnitPrice = parseInt((unitPriceEl ? unitPriceEl.innerText : '0').replace(/,/g, '')) || 0;
                }

                const modTotalPrice = modUnitPrice * modQty;
                const totalPriceEl = modRow.querySelector('.mod-total-price');

                if (unitPriceEl) {
                    unitPriceEl.innerText = modUnitPrice.toLocaleString();
                    unitPriceEl.setAttribute('data-raw', hasBom ? bomRawSum : modUnitPrice);
                }
                if (totalPriceEl) {
                    totalPriceEl.innerText = modTotalPrice.toLocaleString();
                }

                grandTotal += modTotalPrice;
                totalRackQty += modQty;

                if (modType === '독립') {
                    totalFrames += modQty * 2;
                } else if (modType === '연결' || modType === '작은연결' || modType === '바이패스') {
                    totalFrames += modQty * 1;
                }
            });'''

content = content.replace(old_recalc_section, new_recalc_section)

# 4. Update input event listener
old_input_listener = '''        // 🌟 실시간 이벤트 리스너 등록 (수량/단가 입력 시 자동 재계산)
        document.addEventListener('input', function(e) {
            if (e.target.matches('.mod-qty-input, .bom-part-qty, .bom-part-unit, .custom-item-qty, .custom-item-price')) {
                recalculateAll();
            }
        });'''

new_input_listener = '''        // 🌟 실시간 이벤트 리스너 등록 (BOM 수량/단가/품명/규격 입력 시 자동 재계산 및 리스트 동기화)
        document.addEventListener('input', function(e) {
            if (e.target.matches('.bom-part-qty, .bom-part-unit, .bom-part-name, .bom-part-spec, .custom-item-qty, .custom-item-price, .mod-remark, .mod-unit-text')) {
                recalculateAll();
            }
        });'''

content = content.replace(old_input_listener, new_input_listener)

# 5. Update save JS data collection
old_save_data = '''            // 데이터 수집
            const modules = [];
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');
                const name = modRow.querySelector('.mod-name').innerText.trim();
                const spec = modRow.querySelector('.mod-spec').innerText.trim();
                const remark = modRow.querySelector('.mod-remark').innerText.trim();
                const qty = parseInt(modRow.querySelector('.mod-qty-input').value) || 0;
                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                const unitPrice = parseInt(unitPriceEl.innerText.replace(/,/g, '')) || 0;
                const rawPrice = parseInt(unitPriceEl.getAttribute('data-raw')) || 0;
                const totalPrice = parseInt(modRow.querySelector('.mod-total-price').innerText.replace(/,/g, '')) || 0;'''

new_save_data = '''            // 데이터 수집
            const modules = [];
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');
                const name = (modRow.querySelector('.mod-name') || {}).innerText?.trim() || '';
                const spec = (modRow.querySelector('.mod-spec') || {}).innerText?.trim() || '';
                const remarkEl = modRow.querySelector('.mod-remark');
                const remark = remarkEl ? (remarkEl.tagName === 'INPUT' ? remarkEl.value.trim() : remarkEl.innerText.trim()) : '';
                const qty = parseInt((modRow.querySelector('.mod-qty') || {}).innerText || '0') || 0;
                const unitText = (modRow.querySelector('.mod-unit-text') || {}).value?.trim() || '대';
                const unitPriceEl = modRow.querySelector('.mod-unit-price');
                const unitPrice = parseInt((unitPriceEl ? unitPriceEl.innerText : '0').replace(/,/g, '')) || 0;
                const rawPrice = parseInt(unitPriceEl ? unitPriceEl.getAttribute('data-raw') : '0') || 0;
                const totalPrice = parseInt((modRow.querySelector('.mod-total-price') || {}).innerText?.replace(/,/g, '') || '0') || 0;'''

content = content.replace(old_save_data, new_save_data)

# Also update modules.push in save JS to include unit_text
old_mod_push = '''                modules.push({
                    type: modType,
                    name: name,
                    spec: spec,
                    remark: remark,
                    qty: qty,
                    unit_price: unitPrice,
                    raw_price: rawPrice,
                    total_price: totalPrice,
                    bom: bom
                });'''

new_mod_push = '''                modules.push({
                    type: modType,
                    name: name,
                    spec: spec,
                    remark: remark,
                    qty: qty,
                    unit_text: unitText,
                    unit_price: unitPrice,
                    raw_price: rawPrice,
                    total_price: totalPrice,
                    bom: bom
                });'''

content = content.replace(old_mod_push, new_mod_push)

# 6. Add collapse auto-add empty BOM row & Add Module Row JS button click handler
old_collapse_js = '''        // 토글 아이콘(화살표) 애니메이션 변경 로직
        const collapsibles = document.querySelectorAll('.bom-collapse-row .collapse');
        collapsibles.forEach(col => {
            col.addEventListener('show.bs.collapse', function () {
                const moduleRow = this.closest('.bom-collapse-row').previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-down', 'text-muted');
                    icon.classList.add('fa-chevron-up', 'text-danger');
                }
            });
            col.addEventListener('hide.bs.collapse', function () {
                const moduleRow = this.closest('.bom-collapse-row').previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'text-danger');
                    icon.classList.add('fa-chevron-down', 'text-muted');
                }
            });
        });'''

new_collapse_js = '''        // 토글 아이콘(화살표) 애니메이션 및 빈 BOM 시 자동 행 추가
        const collapsibles = document.querySelectorAll('.bom-collapse-row .collapse');
        collapsibles.forEach(col => {
            col.addEventListener('show.bs.collapse', function () {
                const collapseRow = this.closest('.bom-collapse-row');
                const moduleRow = collapseRow.previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-down', 'text-muted');
                    icon.classList.add('fa-chevron-up', 'text-danger');
                }
                const modIndex = collapseRow.getAttribute('data-mod-index');
                const tbody = this.querySelector(`.bom-tbody[data-mod-index="${modIndex}"]`);
                if (tbody && tbody.querySelectorAll('.bom-item-row').length === 0) {
                    const tr = document.createElement('tr');
                    tr.className = 'bom-item-row';
                    tr.setAttribute('data-is-loss', '0');
                    tr.innerHTML = `
                        <td><input type="text" class="form-control form-control-sm bom-input bom-part-name" placeholder="부품명 (예: 파렛트랙)"></td>
                        <td><input type="text" class="form-control form-control-sm bom-input bom-part-spec" placeholder="규격"></td>
                        <td class="text-center"><input type="number" step="any" class="form-control form-control-sm text-center bom-input bom-part-qty" value="1"></td>
                        <td class="text-end"><input type="number" step="any" class="form-control form-control-sm text-end bom-input bom-part-unit" value="0"></td>
                        <td class="text-end fw-bold bom-part-total" data-val="0">0</td>
                        <td class="text-center"><i class="fa-solid fa-trash-can btn-del-row btn-del-bom" title="삭제"></i></td>
                    `;
                    tbody.appendChild(tr);
                }
            });
            col.addEventListener('hide.bs.collapse', function () {
                const moduleRow = this.closest('.bom-collapse-row').previousElementSibling;
                const icon = moduleRow.querySelector('.toggle-icon');
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'text-danger');
                    icon.classList.add('fa-chevron-down', 'text-muted');
                }
            });
        });

        // 리스트 항목 추가 버튼 이벤트 (새 module-row + bom-collapse-row 생성)
        let newModIndex = 10000;
        const addModBtn = document.getElementById('btnAddModuleRow');
        if (addModBtn) {
            addModBtn.addEventListener('click', function() {
                const idx = newModIndex++;
                const colId = `collapseBom${idx}`;

                const moduleRow = document.createElement('tr');
                moduleRow.className = 'module-row';
                moduleRow.setAttribute('data-mod-index', idx);
                moduleRow.setAttribute('data-type', '직접입력');
                moduleRow.style.backgroundColor = '#f8fafc';
                moduleRow.innerHTML = `
                    <td class="center fw-bold row-no">-</td>
                    <td class="center fw-bold text-primary mod-name">파렛트랙</td>
                    <td class="center mod-spec"></td>
                    <td class="center fw-bold text-danger mod-qty">1</td>
                    <td class="center">
                        <input type="text" class="form-control form-control-sm text-center mod-unit-text" value="대" style="width: 40px; margin: 0 auto; padding: 1px 4px; font-size: 0.85rem;">
                    </td>
                    <td class="right fw-bold mod-unit-price" data-raw="0">0</td>
                    <td class="right fw-bold mod-total-price">0</td>
                    <td class="center" style="white-space: nowrap;">
                        <input type="text" class="form-control form-control-sm text-center mod-remark" value="" placeholder="비고" style="width: calc(100% - 24px); display: inline-block; padding: 1px 4px; font-size: 12px;">
                        <i class="fa-solid fa-chevron-down ms-1 text-muted toggle-icon" style="font-size: 0.8rem; transition: all 0.3s ease; cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#${colId}" aria-expanded="false"></i>
                    </td>
                `;

                const bomCollapseRow = document.createElement('tr');
                bomCollapseRow.className = 'bom-collapse-row';
                bomCollapseRow.setAttribute('data-mod-index', idx);
                bomCollapseRow.innerHTML = `
                    <td colspan="8" class="p-0 border-0">
                        <div class="collapse" id="${colId}">
                            <div class="p-3" style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="small fw-bold text-secondary"><i class="fa-solid fa-cube me-1"></i> 1대당 구성 부품 <span class="text-muted">(수량/단가를 직접 수정할 수 있습니다)</span></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 btn-add-bom-row" data-mod-index="${idx}" style="font-size: 0.78rem;"><i class="fa-solid fa-plus me-1"></i> 부품 추가</button>
                                </div>
                                <table class="table table-sm table-bordered mb-0 bom-table" data-mod-index="${idx}" style="font-size: 0.85rem; background-color: white;">
                                    <thead><tr>
                                        <th style="background-color: #FFFFCC !important; width: 160px;">부품명</th>
                                        <th style="background-color: #FFFFCC !important; width: 220px;">규격</th>
                                        <th class="text-center" style="background-color: #FFFFCC !important; width: 80px;">수량</th>
                                        <th class="text-end" style="background-color: #FFFFCC !important; width: 110px;">단가</th>
                                        <th class="text-end" style="background-color: #FFFFCC !important; width: 120px;">합계금액</th>
                                        <th class="text-center" style="background-color: #FFFFCC !important; width: 50px;">삭제</th>
                                    </tr></thead>
                                    <tbody class="bom-tbody" data-mod-index="${idx}"></tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                `;

                const addRowBtn = document.getElementById('addModuleRowBtn');
                addRowBtn.parentNode.insertBefore(moduleRow, addRowBtn);
                addRowBtn.parentNode.insertBefore(bomCollapseRow, addRowBtn);

                const collapseEl = bomCollapseRow.querySelector('.collapse');
                collapseEl.addEventListener('show.bs.collapse', function () {
                    const icon = moduleRow.querySelector('.toggle-icon');
                    if (icon) { icon.classList.remove('fa-chevron-down', 'text-muted'); icon.classList.add('fa-chevron-up', 'text-danger'); }
                    const tbody = this.querySelector(`.bom-tbody[data-mod-index="${idx}"]`);
                    if (tbody && tbody.querySelectorAll('.bom-item-row').length === 0) {
                        const tr = document.createElement('tr');
                        tr.className = 'bom-item-row';
                        tr.setAttribute('data-is-loss', '0');
                        tr.innerHTML = `<td><input type="text" class="form-control form-control-sm bom-input bom-part-name" placeholder="부품명 (예: 파렛트랙)"></td><td><input type="text" class="form-control form-control-sm bom-input bom-part-spec" placeholder="규격"></td><td class="text-center"><input type="number" step="any" class="form-control form-control-sm text-center bom-input bom-part-qty" value="1"></td><td class="text-end"><input type="number" step="any" class="form-control form-control-sm text-end bom-input bom-part-unit" value="0"></td><td class="text-end fw-bold bom-part-total" data-val="0">0</td><td class="text-center"><i class="fa-solid fa-trash-can btn-del-row btn-del-bom" title="삭제"></i></td>`;
                        tbody.appendChild(tr);
                    }
                });
                collapseEl.addEventListener('hide.bs.collapse', function () {
                    const icon = moduleRow.querySelector('.toggle-icon');
                    if (icon) { icon.classList.remove('fa-chevron-up', 'text-danger'); icon.classList.add('fa-chevron-down', 'text-muted'); }
                });

                recalculateAll();
            });
        }'''

content = content.replace(old_collapse_js, new_collapse_js)

with open(filepath, 'w', encoding='utf-8', newline='') as f:
    f.write(content)

print("Successfully applied updates!")
