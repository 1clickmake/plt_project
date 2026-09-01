import re

filepath = 'views/vendor/quote_price.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the recalculateAll function logic in JS
old_recalc = '''            // 1. 모듈별 BOM 및 합계 계산
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

new_recalc = '''            // 1. 모듈별 BOM 및 합계 계산
            const isBoardQuote = <?= json_encode(($quote['source_mode'] ?? '') === 'board') ?>;
            const moduleRows = document.querySelectorAll('.module-row');
            moduleRows.forEach(modRow => {
                const modIndex = modRow.getAttribute('data-mod-index');
                const modType = modRow.getAttribute('data-type');

                modRow.querySelector('.row-no').innerText = runningIndex++;

                const bomTable = document.querySelector(`.bom-table[data-mod-index="${modIndex}"]`);
                let bomRawSum = 0;
                const bomItemRows = bomTable ? bomTable.querySelectorAll('.bom-item-row') : [];
                const nonLossRows = Array.from(bomItemRows).filter(r => r.getAttribute('data-is-loss') !== '1');

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

                let modQty = 1;
                let modUnitPrice = 0;
                const isDirectInput = isBoardQuote || modType === '직접입력';

                if (nonLossRows.length > 0) {
                    const firstBomRow = nonLossRows[0];
                    const bomName = firstBomRow.querySelector('.bom-part-name');
                    const bomSpec = firstBomRow.querySelector('.bom-part-spec');
                    const bomQtyEl = firstBomRow.querySelector('.bom-part-qty');
                    const bomUnitEl = firstBomRow.querySelector('.bom-part-unit');

                    if (bomName && modRow.querySelector('.mod-name')) {
                        modRow.querySelector('.mod-name').innerText = bomName.value.trim() || '파렛트랙';
                    }
                    if (bomSpec && modRow.querySelector('.mod-spec')) {
                        modRow.querySelector('.mod-spec').innerText = bomSpec.value.trim();
                    }

                    if (nonLossRows.length === 1) {
                        // 단일 품목 BOM (수량/단가를 그대로 모듈과 1:1 일치)
                        const bQty = parseInt(bomQtyEl ? bomQtyEl.value : 1) || 1;
                        const bUnit = parseFloat(bomUnitEl ? bomUnitEl.value : 0) || 0;

                        modQty = bQty;
                        modUnitPrice = isDirectInput ? bUnit : calcFinalAmount(bUnit);

                        if (modRow.querySelector('.mod-qty')) {
                            modRow.querySelector('.mod-qty').innerText = modQty;
                        }
                    } else {
                        // 다중 부품 BOM (구성 부품별 합산)
                        const qtyEl = modRow.querySelector('.mod-qty');
                        modQty = parseInt(qtyEl ? qtyEl.innerText : 1) || 1;
                        modUnitPrice = isDirectInput ? bomRawSum : calcFinalAmount(bomRawSum);
                    }
                } else {
                    const qtyEl = modRow.querySelector('.mod-qty');
                    modQty = parseInt(qtyEl ? qtyEl.innerText : 1) || 1;
                    const unitPriceEl = modRow.querySelector('.mod-unit-price');
                    modUnitPrice = parseInt((unitPriceEl ? unitPriceEl.innerText : '0').replace(/,/g, '')) || 0;
                }

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

content = content.replace(old_recalc, new_recalc)

with open(filepath, 'w', encoding='utf-8', newline='') as f:
    f.write(content)

print("Updated recalculateAll calculation logic successfully!")
