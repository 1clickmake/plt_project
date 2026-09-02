<?php
// chat.php (AI 챗봇형 견적 마법사)
?>
<style>
/* Chat Wizard Styles (White Glassmorphism) */
.chat-wizard-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 380px;
    height: 700px;
    max-height: calc(100vh - 60px);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    border-radius: 1.2rem;
    overflow: hidden;
    transition: box-shadow 0.3s;
}
.chat-wizard-container.dragging {
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    opacity: 0.95;
}
.chat-header {
    cursor: grab;
    background: rgba(255, 255, 255, 0.85);
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 10;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.chat-header:active {
    cursor: grabbing;
}
.asamiya-profile {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
}
.chat-body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    scrollbar-width: thin;
}
.chat-msg {
    display: flex;
    gap: 12px;
    max-width: 95%;
    animation: fadeIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
.chat-msg.bot {
    align-self: flex-start;
}
.chat-msg.user {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.chat-bubble {
    background: #ffffff;
    padding: 14px 18px;
    border-radius: 20px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    font-size: 0.95rem;
    color: #1e293b;
    border: 1px solid rgba(255,255,255,1);
    position: relative;
    line-height: 1.5;
}
.chat-msg.bot .chat-bubble {
    border-top-left-radius: 4px;
    background: rgba(255, 255, 255, 0.95);
}
.chat-msg.user .chat-bubble {
    border-top-right-radius: 4px;
    background: linear-gradient(135deg, #0ea5e9, #3b82f6);
    color: white;
    border: none;
    box-shadow: 0 6px 16px rgba(14, 165, 233, 0.25);
}
.form-card {
    background: rgba(248, 250, 252, 0.8);
    border-radius: 12px;
    padding: 15px;
    margin-top: 12px;
    border: 1px solid #e2e8f0;
}
.chat-btn {
    background: linear-gradient(to right, #0ea5e9, #3b82f6);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 10px 16px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s;
    width: 100%;
    margin-top: 12px;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}
.chat-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(14, 165, 233, 0.45);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Custom Input styles */
.chat-input-custom {
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.9rem;
    width: 100%;
    color: #0f172a;
    transition: all 0.2s;
}
.chat-input-custom:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
}
.drag-item {
    color: #0f172a !important;
    font-weight: 700 !important;
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
}
.drag-item:hover {
    background-color: #f8fafc !important;
    border-color: #0ea5e9 !important;
}
.step-indicator {
    font-size: 0.75rem;
    color: #0ea5e9;
    font-weight: 700;
    margin-bottom: 6px;
    display: inline-block;
    background: rgba(14, 165, 233, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
}
/* Floating Avatar Toggle Button */
.chat-toggle-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
    border: 3px solid #0ea5e9;
    cursor: pointer;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, box-shadow 0.2s;
    animation: pulseGlow 2s infinite;
}
.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 35px rgba(14, 165, 233, 0.5);
}
.chat-toggle-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}
.chat-toggle-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #10b981;
    color: white;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid white;
}
@keyframes pulseGlow {
    0% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.5); }
    70% { box-shadow: 0 0 0 12px rgba(14, 165, 233, 0); }
    100% { box-shadow: 0 0 0 0 rgba(14, 165, 233, 0); }
}
</style>

<div class="chat-wizard-container" id="chat-wizard-container">
    <!-- Header -->
    <div class="chat-header" id="chat-wizard-header">
        <img src="/asamiya_profile.png" alt="Asamiya" class="asamiya-profile" onerror="this.src='https://ui-avatars.com/api/?name=Asamiya&background=0ea5e9&color=fff'">
        <div>
            <h6 class="m-0 fw-bold" style="color: #0284c7; font-size: 1.05rem;">아사미야 💕</h6>
            <small class="text-secondary" style="font-size: 0.8rem;">스마트 견적 AI 매니저</small>
        </div>
        <button type="button" class="btn btn-sm text-secondary p-0 text-decoration-none border-0 ms-auto fw-bold" onclick="ChatWizard.minimize()" title="채팅창 닫기" style="font-size: 1.2rem; line-height: 1; padding: 2px 8px !important;">✕</button>
    </div>
    
    <!-- Chat Body -->
    <div class="chat-body" id="chat-wizard-body">
        <!-- Messages will be injected here by JS -->
    </div>
</div>

<!-- Floating Avatar Toggle Button when chat is minimized -->
<div id="chat-wizard-toggle" class="chat-toggle-btn d-none" onclick="ChatWizard.restore()" title="아사미야와 대화하기">
    <img src="/asamiya_profile.png" alt="Asamiya" class="chat-toggle-img" onerror="this.src='https://ui-avatars.com/api/?name=Asamiya&background=0ea5e9&color=fff'">
    <span class="chat-toggle-badge">AI</span>
</div>

<!-- Hidden inputs to store values for canvas engine compatibility -->
<div style="display:none;">
    <input type="radio" name="forkDirection" id="forkW" value="W" checked>
    <input type="radio" name="forkDirection" id="forkD" value="D">
    <input type="number" id="pallet-w" value="1100">
    <input type="number" id="pallet-d" value="1100">
    <input type="number" id="pallet-h" value="1000">
    <input type="number" id="pallet-weight" value="1000">
    <select id="forklift-type">
        <option value="reach">입승식</option>
        <option value="counter">좌승식</option>
        <option value="vna">삼방향</option>
    </select>
    <input type="number" id="forklift-lift-height" value="4500">
    <input type="number" id="forklift-ast" value="2800">
    <input type="number" id="rack-levels" value="3">
    <input type="number" id="rack-height" value="">
    <!-- The file input must be available for JS -->
    <input type="file" id="file-input" multiple accept="image/*,.pdf" style="display:none;" onchange="handleFileSelect(this.files)">
</div>

<script>
// Chat Wizard Logic
const ChatWizard = {
    currentStep: 1,
    body: null,
    
    init() {
        this.body = document.getElementById('chat-wizard-body');
        setTimeout(() => this.startStep1(), 500); // Slight delay for intro animation
    },

    minimize() {
        const container = document.getElementById('chat-wizard-container');
        const toggle = document.getElementById('chat-wizard-toggle');
        if (container) container.classList.add('d-none');
        if (toggle) toggle.classList.remove('d-none');
    },

    restore() {
        const container = document.getElementById('chat-wizard-container');
        const toggle = document.getElementById('chat-wizard-toggle');
        if (container) container.classList.remove('d-none');
        if (toggle) toggle.classList.add('d-none');
    },

    appendBotMsg(html) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg bot';
        msgDiv.innerHTML = `
            <img src="/asamiya_profile.png" alt="Asamiya" class="asamiya-profile" style="width: 38px; height: 38px; margin-top: 4px;" onerror="this.src='https://ui-avatars.com/api/?name=Asamiya&background=0ea5e9&color=fff'">
            <div class="chat-bubble">${html}</div>
        `;
        this.body.appendChild(msgDiv);
        this.scrollToBottom();
    },

    appendUserMsg(text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg user';
        msgDiv.innerHTML = `<div class="chat-bubble">${text}</div>`;
        this.body.appendChild(msgDiv);
        this.scrollToBottom();
    },

    scrollToBottom() {
        setTimeout(() => {
            this.body.scrollTo({
                top: this.body.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    },

    startStep1() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 1/7</span><br>
            <span class="small">안녕하세요? 💕<br>
            스마트 창고 배치를 도와드리는 AI<br><b>아사미야</b> 입니다.<br>
            천천히 따라오시면 멋진 도면을<br>만드실 수 있어요.💕<br><br>
            먼저 바탕화면의 넓은 도화지(캔버스)에<br>마우스로 점을 찍어<br><b>창고 외곽선(모양)</b>을 직접 그려주세요!<br>
            </span>
            <span class="text-secondary small">(마지막에 처음 찍은 점을 클릭하면 도형이 완성됩니다.)</span>
        `);
    },

    // Called externally when polygon is closed by canvas-interactions.js
    onPolygonClosed() {
        if(this.currentStep === 1) {
            this.appendUserMsg("도면 그리기 완료!");
            this.currentStep = 2;
            setTimeout(() => this.startStep2(), 800);
        }
    },

    startStep2() {
        let inputsHtml = this.step2Html || `<div class="text-center text-secondary small py-2">도면을 닫아주세요.</div>`;
        // Step 2 form is dynamically generated based on segments
        this.appendBotMsg(`
            <span class="step-indicator">Step 2/7</span><br>
            <span class="small">
                와! 도면을 잘 그려주셨네요. 👏<br>
                이제 각 벽면의 <b>실제 길이(mm)</b>를<br>아래 폼에 입력해주세요.<br>
                <span class="text-secondary">(숫자만 입력후, 탭(Tab)키를 누르면<br>편하게 다음칸으로 이동할수 있어요!)</span>
            </span>
            
            <div class="form-card" id="chat-inputs-container">
                ${inputsHtml}
            </div>
            
            <button class="chat-btn" onclick="ChatWizard.submitStep2()">입력 완료</button>
        `);
    },

    submitStep2() {
        this.appendUserMsg("벽면 길이 입력 완료!");
        
        // Trigger actual calculation update
        if(typeof window.updateRealLengths === 'function') {
            window.updateRealLengths(); 
        }
        
        this.currentStep = 3;
        setTimeout(() => this.startStep3(), 800);
    },

    startStep3() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 3/7</span><br>
            <span class="small">
                완벽해요! 💕<br>
                이번엔 <b>출입문이나 기둥 같은 장애물</b>을<br>도면 위로 끌어다 놓아주세요.<br>
                그리고 각 장애물의 사이즈(mm)를<br>입력하세요!<br>
                <span class="text-secondary">(장애물을 끌어다 놓은후,<br>아이콘위에 마우스를 올려놓고<br>드래그하면 이동할수 있어요!)</span>
            </span>
            <div class="form-card p-2">
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-door" draggable="true" ondragstart="handleDragStart(event, 'door')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">🚪 출입문</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-pillar" draggable="true" ondragstart="handleDragStart(event, 'pillar')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">◼️ 기둥</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-shutter" draggable="true" ondragstart="handleDragStart(event, 'shutter')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">🪟 셔터</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-machine" draggable="true" ondragstart="handleDragStart(event, 'machine')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">⚙️ 기계</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-hydrant" draggable="true" ondragstart="handleDragStart(event, 'hydrant')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">🧯 소화전</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-panel" draggable="true" ondragstart="handleDragStart(event, 'panel')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">⚡ 전기판넬</div>
                    <div class="drag-item bg-white border rounded shadow-sm" id="drag-forbidden" draggable="true" ondragstart="handleDragStart(event, 'forbidden')" style="cursor: grab; font-size: 0.78rem; padding: 4px 8px; margin: 2px;">🚫 사용불가</div>
                </div>
                <div id="obstacle-inputs-container" class="mt-2 d-flex flex-column gap-2"></div>
            </div>
            <button class="chat-btn" onclick="ChatWizard.submitStep3()">배치 완료</button>
        `);
    },

    submitStep3() {
        this.appendUserMsg("장애물 배치 완료!");
        this.currentStep = 4;
        setTimeout(() => this.startStep4(), 800);
    },

    startStep4() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 4/7</span><br>
            <span class="small">이제 보관하실 <b>파렛트의 사이즈</b>를<br>알려주세요.</span>
            <div class="form-card">
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">가로(W) / 세로(D) mm</label>
                    <div class="d-flex gap-2">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-w" class="chat-input-custom small" value="1100" placeholder="W" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-d" class="chat-input-custom small" value="1100" placeholder="D" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">파랫트 포함 적재 높이(H) mm</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-h" class="chat-input-custom small" value="1000" placeholder="화물 포함 높이" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">총 중량 (kg)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-weight" class="chat-input-custom small" value="1000" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="mb-2">
                    <label class="small text-secondary mb-1 fw-bold d-block">포크 진입 방향</label>
                    <div class="d-flex gap-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="chatForkDirection" id="chatForkW" value="W" checked>
                            <label class="form-check-label small text-secondary fw-bold" for="chatForkW">가로(W)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="chatForkDirection" id="chatForkD" value="D">
                            <label class="form-check-label small text-secondary fw-bold" for="chatForkD">세로(D)</label>
                        </div>
                    </div>
                </div>
            </div>
            <button class="chat-btn" onclick="ChatWizard.submitStep4()">입력 완료</button>
        `);
    },

    submitStep4() {
        document.getElementById('pallet-w').value = document.getElementById('chat-pallet-w').value;
        document.getElementById('pallet-d').value = document.getElementById('chat-pallet-d').value;
        document.getElementById('pallet-h').value = document.getElementById('chat-pallet-h').value;
        document.getElementById('pallet-weight').value = document.getElementById('chat-pallet-weight').value;
        
        const forkW = document.getElementById('chatForkW');
        let forkDir = (forkW && forkW.checked) ? 'W' : 'D';
        if(forkDir === 'W') {
            document.getElementById('forkW').checked = true;
        } else {
            document.getElementById('forkD').checked = true;
        }

        this.appendUserMsg("파렛트 사이즈 입력 완료!");
        this.currentStep = 5;
        setTimeout(() => this.startStep5(), 800);
    },

    startStep5() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 5/7</span><br>
            <span class="small">다음은 창고에서 사용하실 <b>지게차 정보</b>를<br>선택해주세요! 🚜</span>
            <div class="form-card">
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">지게차 종류</label>
                    <select id="chat-forklift-type" class="chat-input-custom" style="font-size: 0.78rem; padding: 6px 8px;" onchange="ChatWizard.onForkliftTypeChange(this.value)">
                        <option value="reach">입승식 (리치형) - 좁은 통로용</option>
                        <option value="counter">좌승식 (카운터) - 일반용</option>
                        <option value="vna">삼방향 (VNA) - 초소형 통로</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">최대 인상높이 (mm)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-forklift-height" class="chat-input-custom small" value="4500" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="mb-2">
                    <label class="small text-secondary mb-1 fw-bold">직각교차 통로폭(AST)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-forklift-ast" class="chat-input-custom small" value="2800" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
            </div>
            <button class="chat-btn" onclick="ChatWizard.submitStep5()">입력 완료</button>
        `);
    },

    onForkliftTypeChange(type) {
        const heightEl = document.getElementById('chat-forklift-height');
        const astEl = document.getElementById('chat-forklift-ast');
        
        const defaults = {
            'reach':   { height: 4500, ast: 2800 },
            'counter': { height: 3300, ast: 3700 },
            'vna':     { height: 6500, ast: 1800 }
        };

        if (defaults[type]) {
            if (heightEl) heightEl.value = defaults[type].height;
            if (astEl) astEl.value = defaults[type].ast;
        }
    },

    submitStep5() {
        document.getElementById('forklift-type').value = document.getElementById('chat-forklift-type').value;
        document.getElementById('forklift-lift-height').value = document.getElementById('chat-forklift-height').value;
        document.getElementById('forklift-ast').value = document.getElementById('chat-forklift-ast').value;

        this.appendUserMsg("지게차 제원 입력 완료!");
        this.currentStep = 6;
        setTimeout(() => this.startStep6(), 800);
    },

    startStep6() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 6/7</span><br>
            <span class="small">거의 다 왔어요!<br>랙을 <b>몇 단으로 설치</b>하실지<br>알려주세요.</span>
            <div class="form-card">
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">설치 단수 (필수)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-rack-levels" class="chat-input-custom small" value="3" placeholder="예: 3단" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
                <div class="mb-2">
                    <label class="small text-secondary mb-1 fw-bold">설치 높이(mm) (선택)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-rack-height" class="chat-input-custom small" placeholder="공란시 자동 계산" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, '');">
                </div>
            </div>
            <button class="chat-btn" onclick="ChatWizard.submitStep6()">입력 완료</button>
        `);
    },

    submitStep6() {
        document.getElementById('rack-levels').value = document.getElementById('chat-rack-levels').value;
        document.getElementById('rack-height').value = document.getElementById('chat-rack-height').value;

        this.appendUserMsg("랙 희망 제원 입력 완료!");
        this.currentStep = 7;
        setTimeout(() => this.startStep7(), 800);
    },

    startStep7() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 7/7</span><br>
            <span class="small">
                수고하셨습니다! 💕<br>
                참고할 <b>도면이나 현장 사진</b>이 있다면<br>첨부해주시고, <b>[자동 배치 시작하기]</b>를 <br>눌러주세요!
            </span>
            <div class="form-card text-center py-3 mt-3 mb-2" style="cursor: pointer; border: 2px dashed #0ea5e9; border-radius: 12px; background: rgba(14, 165, 233, 0.05);" onclick="document.getElementById('file-input').click()">
                <div style="font-size:2rem; margin-bottom: 3px;">📁</div>
                <div class="fw-bold small" style="color: #0284c7;">클릭하여 파일 업로드</div>
                <small class="text-secondary">(선택사항: 이미지, PDF)</small>
            </div>
            <div id="file-preview-area" class="d-flex flex-wrap gap-2 mt-2"></div>
            <button class="chat-btn mt-3" style="background: linear-gradient(135deg, #10b981, #059669); font-size: 1rem; padding: 12px; border-radius: 25px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);" onclick="ChatWizard.submitFinal()">🚀 자동 배치 시작하기</button>
        `);
    },

    submitFinal() {
        if(typeof window.runAutoLayout === 'function') {
            window.runAutoLayout();
            
            // 배치 완료 후 채팅창 안내 메시지 출력
            setTimeout(() => {
                const vendorSlug = window.vendorUserId || 'asamiya';
                this.appendBotMsg(`
                    🎉 <b>파렛트랙 배치가 성공적으로<br>실행되었습니다!</b><br><br>
                    <span class="small">
                    • 랙을 드래그해서 원하는 위치로<br>자유롭게 이동시킬 수 있어요.<br>
                    • 리모컨을 사용해서 연장이나 회전,<br>삭제 등을 할 수 있어요! 🚀
                    </span>
                    <div class="mt-3">
                        <a href="/quote/${vendorSlug}/video" target="_blank" class="chat-btn text-decoration-none d-block text-center shadow-sm" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); font-size: 0.85rem; padding: 8px 12px;">🎮 리모콘 사용법 영상 보기</a>
                    </div>
                `);
            }, 400);
        } else {
            alert("배치 엔진을 찾을 수 없습니다.");
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    ChatWizard.init();
});

// Draggable Chat Window Logic
const chatWizardDOM = document.getElementById('chat-wizard-container');
const chatHeaderDOM = document.getElementById('chat-wizard-header');
let isChatDragging = false;
let chatStartX, chatStartY, chatInitialX, chatInitialY;

chatHeaderDOM.addEventListener('mousedown', chatDragStart);
document.addEventListener('mousemove', chatDrag);
document.addEventListener('mouseup', chatDragEnd);

function chatDragStart(e) {
    if(e.target.closest('button') || e.target.closest('a')) return;
    
    const rect = chatWizardDOM.getBoundingClientRect();
    
    // Switch from bottom/right to top/left positioning for easier dragging
    chatWizardDOM.style.bottom = 'auto';
    chatWizardDOM.style.right = 'auto';
    chatWizardDOM.style.left = rect.left + 'px';
    chatWizardDOM.style.top = rect.top + 'px';

    chatInitialX = e.clientX - rect.left;
    chatInitialY = e.clientY - rect.top;

    isChatDragging = true;
    chatWizardDOM.classList.add('dragging');
}

function chatDrag(e) {
    if (!isChatDragging) return;
    
    e.preventDefault();
    
    let currentX = e.clientX - chatInitialX;
    let currentY = e.clientY - chatInitialY;
    
    const maxX = window.innerWidth - chatWizardDOM.offsetWidth;
    const maxY = window.innerHeight - chatWizardDOM.offsetHeight;
    
    if (currentX < 0) currentX = 0;
    if (currentY < 0) currentY = 0;
    if (currentX > maxX) currentX = maxX;
    if (currentY > maxY) currentY = maxY;

    chatWizardDOM.style.left = currentX + 'px';
    chatWizardDOM.style.top = currentY + 'px';
}

function chatDragEnd(e) {
    if (!isChatDragging) return;
    isChatDragging = false;
    chatWizardDOM.classList.remove('dragging');
}
</script>
