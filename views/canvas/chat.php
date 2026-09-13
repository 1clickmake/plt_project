<?php
// chat.php (AI 챗봇형 견적 마법사)
?>
<style>
/* Chat Wizard Styles (Dual Theme Adaptive: High Contrast & Modern Aesthetics) */
.chat-wizard-container {
    position: fixed;
    bottom: 30px;
    right: 400px;
    width: 440px;
    height: 700px;
    max-height: calc(100vh - 60px);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    background: rgba(15, 23, 42, 0.92);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.08);
    border-radius: 1.2rem;
    overflow: hidden;
    transition: background 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.chat-wizard-container.dragging {
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
    opacity: 0.96;
}
.chat-header {
    cursor: grab;
    background: rgba(30, 41, 59, 0.95);
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 10;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.chat-header:active {
    cursor: grabbing;
}
.chat-header h6 {
    color: #38bdf8 !important;
    font-size: 1.05rem;
}
.chat-header small {
    color: #94a3b8 !important;
    font-size: 0.8rem;
}
.asamiya-profile {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #38bdf8;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.35);
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
    max-width: 100%;
    scroll-margin-top: 20px;
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
    padding: 14px 18px;
    border-radius: 20px;
    font-size: 0.95rem;
    position: relative;
    line-height: 1.5;
}
.chat-msg.bot .chat-bubble {
    border-top-left-radius: 4px;
    background: rgba(30, 41, 59, 0.95);
    color: #f8fafc;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
}
.chat-msg.bot .chat-bubble .text-secondary,
.chat-msg.bot .chat-bubble .text-muted {
    color: #cbd5e1 !important;
}
.chat-msg.user .chat-bubble {
    border-top-right-radius: 4px;
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #ffffff;
    border: none;
    box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
}
.form-card {
    background: rgba(15, 23, 42, 0.65);
    border-radius: 12px;
    padding: 15px;
    margin-top: 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.chat-btn {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 10px 16px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s;
    width: 100%;
    margin-top: 12px;
    box-shadow: 0 4px 14px rgba(14, 165, 233, 0.35);
}
.chat-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.5);
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Input styles */
.chat-input-custom {
    background: #1e293b;
    border: 1px solid #475569;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.9rem;
    width: 100%;
    color: #f8fafc;
    transition: all 0.2s;
}
.chat-input-custom:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
}
.drag-item {
    color: #f1f5f9 !important;
    font-weight: 700 !important;
    background-color: #1e293b !important;
    border: 1px solid #475569 !important;
}
.drag-item:hover {
    background-color: #334155 !important;
    border-color: #38bdf8 !important;
}
.step-indicator {
    font-size: 0.75rem;
    color: #38bdf8;
    font-weight: 700;
    margin-bottom: 6px;
    display: inline-block;
    background: rgba(56, 189, 248, 0.2);
    padding: 2px 8px;
    border-radius: 10px;
}

/* Floating Avatar Toggle Button */
.chat-toggle-btn {
    position: fixed;
    bottom: 10px;
    right: 10px;
    z-index: 1051;
    width: 60px;
    height: 60px;
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

/* ========================================================
   Light Theme (White Canvas Background) Adaptations
   ======================================================== */
body.theme-light .chat-wizard-container {
    background: rgba(248, 250, 252, 0.96);
    border: 1px solid #cbd5e1;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22), 0 0 0 1px rgba(148, 163, 184, 0.35);
}
body.theme-light .chat-header {
    background: linear-gradient(135deg, #ffffff, #f1f5f9);
    border-bottom: 1px solid #cbd5e1;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
body.theme-light .chat-header h6 {
    color: #0284c7 !important;
}
body.theme-light .chat-header small {
    color: #475569 !important;
}
body.theme-light .asamiya-profile {
    border: 2px solid #0ea5e9;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);
}
body.theme-light .chat-msg.bot .chat-bubble {
    background: #ffffff;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
}
body.theme-light .chat-msg.bot .chat-bubble .text-secondary,
body.theme-light .chat-msg.bot .chat-bubble .text-muted {
    color: #475569 !important;
}
body.theme-light .form-card {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
}
body.theme-light .chat-input-custom {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0f172a;
}
body.theme-light .chat-input-custom:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.18);
}
body.theme-light .drag-item {
    color: #0f172a !important;
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
}
body.theme-light .drag-item:hover {
    background-color: #e2e8f0 !important;
    border-color: #0ea5e9 !important;
}
body.theme-light .step-indicator {
    color: #0284c7;
    background: rgba(14, 165, 233, 0.12);
}

/* Typing Indicator Animation */
@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}
.typing-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    background-color: #38bdf8;
    border-radius: 50%;
    margin: 0 2px;
    animation: typingBounce 1.4s infinite ease-in-out both;
}
.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }
</style>

<!-- 신규 진입 모달 - 딱 2개만 -->
<div id="first-entry-modal" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:40px; border-radius:16px; box-shadow:0 15px 50px rgba(0,0,0,0.3); z-index:9999; text-align:center; min-width: 380px;">
    <h3 style="margin-bottom:10px; color:#0f172a; font-weight:800; letter-spacing:-1px;">창고 크기만 알려주세요</h3>
    <p style="color:#64748b; font-size:0.9rem; margin-bottom:25px;">복잡한 설정은 저희가 알아서 최적화해 드립니다.</p>
    
    <div style="display:flex; justify-content:center; gap:10px; margin-bottom:25px;">
        <div>
            <label style="display:block; text-align:left; font-size:0.8rem; font-weight:bold; color:#475569; margin-bottom:5px;">가로 길이</label>
            <input type="number" id="quick-w" placeholder="예: 30" style="width:140px; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1.1rem; text-align:center; outline:none;" onfocus="this.style.borderColor='#f16819'" onblur="this.style.borderColor='#cbd5e1'">
            <span style="position:absolute; margin-left:-30px; margin-top:14px; color:#94a3b8; font-weight:bold;">m</span>
        </div>
        <div>
            <label style="display:block; text-align:left; font-size:0.8rem; font-weight:bold; color:#475569; margin-bottom:5px;">세로 길이</label>
            <input type="number" id="quick-h" placeholder="예: 18" style="width:140px; padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:1.1rem; text-align:center; outline:none;" onfocus="this.style.borderColor='#f16819'" onblur="this.style.borderColor='#cbd5e1'">
            <span style="position:absolute; margin-left:-30px; margin-top:14px; color:#94a3b8; font-weight:bold;">m</span>
        </div>
    </div>
    
    <button onclick="autoRender()" style="background:#f16819; color:white; padding:15px; font-size:1.1rem; font-weight:bold; border:none; border-radius:8px; cursor:pointer; width:100%; box-shadow: 0 4px 15px rgba(241, 104, 25, 0.3); transition: all 0.2s;">
        랙 깔아보기 <i class="fa-solid fa-arrow-right ms-1"></i>
    </button>
</div>

<script>
function autoRender() {
    const W = document.getElementById('quick-w').value || 30;
    const H = document.getElementById('quick-h').value || 18;
    
    document.getElementById('first-entry-modal').style.display = 'none';
    
    // 1. 하이디 지정 기본값 설정
    const config = {
        width: W, 
        height: H,
        level: 3, // 3단 (6 PLT)
        ast: 3500, // 통로 3500mm 고정
        pitch: 2800 // 기둥 간격 2800mm 고정
    };

    // 2. 캔버스 엔진 호출 (실제 로직 연동)
    if(typeof window.autoGenerateWarehouse === 'function') {
        window.autoGenerateWarehouse(W, H, config);
    } else {
        // Fallback: Mock render completion to show the summary immediately
        alert(`[ 렌더링 완료 (미리보기) ]\n\n가로: ${W}m / 세로: ${H}m\n\n- 적재: 3단 (6 PLT) 기본\n- 랙 타입: 단면/양면 자동\n- 통로(AST): 3500mm 고정\n- 기둥 간격: 2800mm 고정\n\n✔️ 결과: 총 기둥 48EA, 빔 96EA, 파렛트 72PLT`);
    }
}
</script>

<!-- 기존 채팅 7단계 전체 숨김 -->
<div id="old-chat-guide" style="display:none;">
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
    <input type="number" id="rack-beam-length" value="2585">
    <input type="number" id="rack-depth" value="1000">
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
        setTimeout(() => {
            if (typeof window.RAW_RESTORE_DATA !== 'undefined' && window.RAW_RESTORE_DATA) {
                this.currentStep = 7;
                this.appendBotMsg(`
                    <p class="mb-2"><strong>🎉 도면이 성공적으로 복원되었습니다! 💕</strong></p>
                    <p class="text-secondary small mb-2">이전에 작업하시던 상태 그대로 불러왔어요.<br>도면을 수정 후 도면 저장을 클릭하세요.</p>
                    <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.82rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                        • 랙을 클릭하여 원하는 위치로 자유롭게 이동할 수 있어요.<br>
                        • 리모컨 버튼(회전, 연장, 복사 등)을 눌러 편집해보세요!
                    </div>
                    <div class="mt-3">
                        <button onclick="openQuoteRequestModal()" class="chat-btn w-100 fw-bold shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669); font-size: 0.95rem; padding: 10px 14px; border-radius: 20px;">💾 도면 저장</button>
                    </div>
                `, true);
            } else {
                this.startStep1();
            }
        }, 500);
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
        if (typeof draw === 'function') draw();
    },

    appendBotMsg(html, fullWidth = false, onComplete = null) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg bot';
        if (fullWidth) msgDiv.style.width = '100%';
        const bubbleStyle = fullWidth ? 'flex: 1; width: 100%;' : '';
        
        // Step 1/7 (첫 시작)일 때만 채팅(타이핑) 느낌을 주고, 나머지는 즉시 로드하여 눈 피로 방지
        const isStep1 = html.includes('Step 1/7');

        if (isStep1) {
            // 초기에는 타이핑 효과 점 3개만 표시 (생각하는 느낌)
            msgDiv.innerHTML = `
                <img src="/asamiya_profile.png" alt="Asamiya" class="asamiya-profile" style="width: 38px; height: 38px; margin-top: 4px;" onerror="this.src='https://ui-avatars.com/api/?name=Asamiya&background=0ea5e9&color=fff'">
                <div class="chat-bubble" style="${bubbleStyle} padding: 8px 12px;">
                    <div class="typing-indicator">
                        <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
                    </div>
                </div>
            `;
            this.body.appendChild(msgDiv);
            this.scrollToBottom();

            // 600ms 후에 실제 텍스트를 한 글자씩 타자치는 효과 시작
            setTimeout(() => {
                if (msgDiv) {
                    const bubble = msgDiv.querySelector('.chat-bubble');
                    if (bubble) {
                        bubble.style.padding = ''; // 원래 패딩 복구
                        this.typeWriterHTML(html, bubble, 25, onComplete); // 25ms 간격으로 타이핑
                    }
                }
            }, 600);
        } else {
            // 타이핑 효과 없이 즉시 표시
            msgDiv.innerHTML = `
                <img src="/asamiya_profile.png" alt="Asamiya" class="asamiya-profile" style="width: 38px; height: 38px; margin-top: 4px;" onerror="this.src='https://ui-avatars.com/api/?name=Asamiya&background=0ea5e9&color=fff'">
                <div class="chat-bubble" style="${bubbleStyle}">
                    ${html}
                </div>
            `;
            this.body.appendChild(msgDiv);
            this.scrollToBottom();
            if (onComplete) onComplete();
        }
    },

    typeWriterHTML(html, targetElement, speed, onComplete) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        targetElement.innerHTML = '';
        
        const typeNode = (node, parentTarget, callback) => {
            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent;
                let i = 0;
                
                // 공백/줄바꿈만 있는 텍스트 노드는 타이핑 딜레이 없이 즉시 출력
                if (text.trim() === '') {
                    parentTarget.appendChild(document.createTextNode(text));
                    callback();
                    return;
                }

                const typeChar = () => {
                    if (i < text.length) {
                        parentTarget.appendChild(document.createTextNode(text.charAt(i)));
                        i++;
                        if (i % 4 === 0) ChatWizard.scrollToBottom(); // 부하 방지를 위해 4글자마다 스크롤
                        setTimeout(typeChar, speed);
                    } else {
                        callback();
                    }
                };
                typeChar();
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const clone = node.cloneNode(false);
                parentTarget.appendChild(clone);
                
                const children = Array.from(node.childNodes);
                let childIdx = 0;
                const processNextChild = () => {
                    if (childIdx < children.length) {
                        typeNode(children[childIdx], clone, () => {
                            childIdx++;
                            processNextChild();
                        });
                    } else {
                        callback();
                    }
                };
                processNextChild();
            } else {
                callback();
            }
        };

        const rootChildren = Array.from(tempDiv.childNodes);
        let rootIdx = 0;
        const processRootChild = () => {
            if (rootIdx < rootChildren.length) {
                typeNode(rootChildren[rootIdx], targetElement, () => {
                    rootIdx++;
                    processRootChild();
                });
            } else {
                ChatWizard.scrollToBottom();
                if (onComplete) onComplete();
            }
        };
        processRootChild();
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
            스마트 창고 배치를 도와드리는 AI <b>아사미야</b> 입니다.<br>
            천천히 따라오시면 멋진 도면을 만드실 수 있어요.💕<br><br>
            먼저 바탕화면의 넓은 도화지(캔버스)에<br>마우스로 점을 찍어<br><b>창고 외곽선(모양)</b>을 직접 그려주세요!<br>
            </span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 도면 선이 조금 삐뚤어져도 걱정하지 마세요!<br>대략적인 형태만 그려주시면 다음 단계에서 <b>자동으로 반듯하게 정렬</b>됩니다. 편하게 그려주세요!
            </div>
            <div style="text-align:center; margin-top:10px;"><img src="/assets/images/storage.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>
        `);
    },

    resetForNewFloor(floorName) {
        this.currentStep = 1;
        this.step2Html = '';
        if (this.body) {
            this.body.innerHTML = '';
            this.appendBotMsg(`
                <span class="step-indicator">Step 1/7</span><br>
                📍 <b>[${floorName}]</b> 도면 작성을 시작합니다! 💕<br>
                <span class="small">
                바탕화면의 넓은 도화지(캔버스)에<br>마우스로 점을 찍어<br><b>[${floorName}] 외곽선(모양)</b>을 직접 그려주세요!<br>
                </span>
                <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                    💡 <b>Tip:</b> 도면 선이 조금 삐뚤어져도 걱정하지 마세요!<br>대략적인 형태만 그려주시면 다음 단계에서 <b>자동으로 반듯하게 정렬</b>됩니다. 편하게 그려주세요!
                </div>
                <div style="text-align:center; margin-top:10px;"><img src="/assets/images/storage.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>
            `);
        }
    },

    // Called externally when polygon is closed by canvas-interactions.js
    onPolygonClosed() {
        this.appendUserMsg("도면 그리기 완료!");
        this.currentStep = 2;
        setTimeout(() => this.startStep2(), 500);
    },

    startStep2() {
        let inputsHtml = this.step2Html || `<div class="text-center text-secondary small py-2">도면을 닫아주세요.</div>`;
        // Step 2 form is dynamically generated based on segments
        this.appendBotMsg(`
            <span class="step-indicator">Step 2/7</span><br>
            <span class="small">
                와! 도면을 잘 그려주셨네요. 👏<br>
                이제 각 벽면의 <b>실제 길이(mm)</b>를<br>아래 폼에 입력해주세요.<br>
            </span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 숫자만 입력 후 <b>탭(Tab)키</b>를 누르면<br>편하게 다음 칸으로 이동할 수 있어요!
            </div>
            
            <div class="form-card" id="chat-inputs-container">
                ${inputsHtml}
            </div>
            
            <button class="chat-btn" onclick="ChatWizard.submitStep2(this)">입력 완료</button>
        `);
    },

    submitStep2(btn) {
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.6';
        }
        this.appendUserMsg("벽면 길이 입력 완료!");
        
        // Trigger actual calculation update
        if(typeof window.updateRealLengths === 'function') {
            window.updateRealLengths(); 
        }
        
        this.currentStep = 3;
        setTimeout(() => this.startStep3(), 600);
    },

    startStep3() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 3/7</span><br>
            <span class="small">
                완벽해요! 💕<br>
                이번엔 <b>출입문이나 기둥 같은 장애물</b>을<br>도면 위로 끌어다 놓아주세요.<br>
                그리고 각 장애물의 사이즈(mm)를 입력하세요!<br>
            </span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 장애물을 끌어다 놓은 후,<br>아이콘 위에 마우스를 올려놓고 <b>드래그하면<br>이동</b>할 수 있어요!
            </div>
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
            <span class="small">이제 보관하실 <b>파렛트의 사이즈</b>를 알려주세요.</span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 파렛트 사이즈를 정확히 알려주시면<br>아사미야가 <b>랙 규격을 자동으로 추천</b>해 드립니다!
            </div>
            <div class="form-card">
                <div class="mb-3">
                    <label class="small text-secondary mb-1 fw-bold">가로(W) / 세로(D) mm</label>
                    <div class="d-flex gap-2">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-w" class="chat-input-custom small" value="1100" placeholder="W" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); ChatWizard.updateRackSpecOptions();">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" id="chat-pallet-d" class="chat-input-custom small" value="1100" placeholder="D" onclick="this.select()" oninput="this.value=this.value.replace(/[^0-9]/g, ''); ChatWizard.updateRackSpecOptions();">
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
                            <input class="form-check-input" type="radio" name="chatForkDirection" id="chatForkW" value="W" checked onchange="ChatWizard.updateRackSpecOptions()">
                            <label class="form-check-label small text-secondary fw-bold" for="chatForkW">가로(W)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="chatForkDirection" id="chatForkD" value="D" onchange="ChatWizard.updateRackSpecOptions()">
                            <label class="form-check-label small text-secondary fw-bold" for="chatForkD">세로(D)</label>
                        </div>
                    </div>
                </div>

                <!-- 💡 스마트 추천 랙 규격 섹션 -->
                <div class="p-2 rounded bg-dark bg-opacity-50 border border-secondary border-opacity-50 mt-2" id="chat-rack-spec-recommendation-box">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-25">
                        <span class="small fw-bold text-info" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> 아사미야 추천 랙 규격
                        </span>
                        <span class="badge bg-secondary bg-opacity-50 text-light" style="font-size:0.65rem;">기성품 최적화</span>
                    </div>

                    <!-- 가로 로드빔 길이 -->
                    <div class="mb-2">
                        <label class="small text-secondary d-block mb-1 fw-bold" style="font-size: 0.73rem;">가로 로드빔 길이 (W)</label>
                        <div id="chat-beam-options-container" class="d-flex flex-column gap-1"></div>
                    </div>

                    <!-- 세로 랙 깊이 (프레임) -->
                    <div class="mb-1">
                        <label class="small text-secondary d-block mb-1 fw-bold" style="font-size: 0.73rem;">세로 랙 깊이 (D)</label>
                        <div id="chat-depth-options-container" class="d-flex flex-column gap-1"></div>
                    </div>
                </div>
            </div>
            <button class="chat-btn mt-2" onclick="ChatWizard.submitStep4()">입력 완료</button>
        `, false, () => {
            this.updateRackSpecOptions();
        });
    },

    updateRackSpecOptions() {
        const pwInput = document.getElementById('chat-pallet-w');
        const pdInput = document.getElementById('chat-pallet-d');
        if (!pwInput || !pdInput) return;

        let pw = parseInt(pwInput.value) || 1100;
        let pd = parseInt(pdInput.value) || 1100;

        const forkDir = document.querySelector('input[name="chatForkDirection"]:checked')?.value || 'W';
        if (forkDir === 'D') {
            const temp = pw;
            pw = pd;
            pd = temp;
        }

        const beamContainer = document.getElementById('chat-beam-options-container');
        const depthContainer = document.getElementById('chat-depth-options-container');
        if (!beamContainer || !depthContainer) return;

        // 1. 로드빔 가로(W) 후보
        let beamOptions = [];
        if (pw <= 1100) {
            beamOptions = [
                { val: 2585, label: '2,585mm (국내 표준 기성품 ⭐)', isRec: true },
                { val: 2385, label: '2,385mm (초슬림 밀착형)', isRec: false }
            ];
        } else if (pw === 1200) {
            beamOptions = [
                { val: 2585, label: '2,585mm (대중적 기성품·공간절약 ⭐)', isRec: true },
                { val: 2785, label: '2,785mm (광폭 여유형 95mm 유격)', isRec: false }
            ];
        } else if (pw === 1300) {
            beamOptions = [
                { val: 2785, label: '2,785mm (기성품·공간절약 ⭐)', isRec: true },
                { val: 2985, label: '2,985mm (광폭 여유형 95mm 유격)', isRec: false }
            ];
        } else if (pw === 1400) {
            beamOptions = [
                { val: 2985, label: '2,985mm (기성품 표준·공간절약 ⭐)', isRec: true },
                { val: 3185, label: '3,185mm (광폭 여유형 95mm 유격)', isRec: false }
            ];
        } else {
            const compactVal = (pw * 2) + 185;
            const standardVal = (pw * 2) + 385;
            beamOptions = [
                { val: compactVal, label: `${compactVal.toLocaleString()}mm (알뜰 밀착형 ⭐)`, isRec: true },
                { val: standardVal, label: `${standardVal.toLocaleString()}mm (표준 여유형)`, isRec: false }
            ];
        }

        const currentSelectedBeam = document.querySelector('input[name="chatRackBeam"]:checked')?.value || (beamOptions[0] ? beamOptions[0].val : 2585);

        let beamHtml = '';
        beamOptions.forEach((opt, idx) => {
            const isChecked = String(opt.val) === String(currentSelectedBeam) || (idx === 0 && !currentSelectedBeam);
            beamHtml += `
                <div class="form-check" style="font-size:0.75rem; margin-bottom:2px;">
                    <input class="form-check-input" type="radio" name="chatRackBeam" id="chatBeam_${opt.val}" value="${opt.val}" ${isChecked ? 'checked' : ''} onchange="ChatWizard.syncSelectedSpecs()">
                    <label class="form-check-label ${opt.isRec ? 'text-warning fw-bold' : 'text-light'}" for="chatBeam_${opt.val}">
                        ${opt.label}
                    </label>
                </div>
            `;
        });
        beamHtml += `
            <div class="d-flex align-items-center gap-1 mt-1" style="font-size:0.75rem;">
                <input class="form-check-input" type="radio" name="chatRackBeam" id="chatBeam_custom" value="custom" onchange="ChatWizard.syncSelectedSpecs()">
                <label class="form-check-label text-secondary me-1" for="chatBeam_custom">직접입력:</label>
                <input type="text" inputmode="numeric" id="chat-beam-custom-input" class="form-control form-control-sm bg-dark text-light border-secondary p-1" style="width:75px; font-size:0.75rem; height:24px;" placeholder="mm" onfocus="document.getElementById('chatBeam_custom').checked=true" oninput="this.value=this.value.replace(/[^0-9]/g, ''); document.getElementById('chatBeam_custom').checked=true; ChatWizard.syncSelectedSpecs()">
            </div>
        `;
        beamContainer.innerHTML = beamHtml;

        // 2. 랙 깊이(D) 후보
        let depthOptions = [];
        if (pd <= 1100) {
            depthOptions = [
                { val: 1000, label: '1,000mm (국내 90% 표준 기성품 ⭐)', isRec: true },
                { val: 900,  label: '900mm (소형 기성품)', isRec: false }
            ];
        } else if (pd === 1200) {
            depthOptions = [
                { val: 1000, label: '1,000mm (표준 기성품 + 타이바 권장 ⭐)', isRec: true },
                { val: 1100, label: '1,100mm (100mm 여유형)', isRec: false }
            ];
        } else if (pd === 1300) {
            depthOptions = [
                { val: 1000, label: '1,000mm (표준 기성품 + 타이바 적용 ⭐)', isRec: true },
                { val: 1200, label: '1,200mm (안정형 기성품)', isRec: false }
            ];
        } else if (pd >= 1400) {
            depthOptions = [
                { val: 1000, label: '1,000mm (국내 표준 기성품 + 타이바 ⭐최저가)', isRec: true },
                { val: 1200, label: '1,200mm (안정형 기성품)', isRec: false },
                { val: 1300, label: '1,300mm (주문제작)', isRec: false }
            ];
        } else {
            depthOptions = [
                { val: 1000, label: '1,000mm (국내 표준 기성품 ⭐)', isRec: true },
                { val: pd - 100, label: `${pd - 100}mm (파렛트 - 100mm)`, isRec: false }
            ];
        }

        const currentSelectedDepth = document.querySelector('input[name="chatRackDepth"]:checked')?.value || (depthOptions[0] ? depthOptions[0].val : 1000);

        let depthHtml = '';
        depthOptions.forEach((opt, idx) => {
            const isChecked = String(opt.val) === String(currentSelectedDepth) || (idx === 0 && !currentSelectedDepth);
            depthHtml += `
                <div class="form-check" style="font-size:0.75rem; margin-bottom:2px;">
                    <input class="form-check-input" type="radio" name="chatRackDepth" id="chatDepth_${opt.val}" value="${opt.val}" ${isChecked ? 'checked' : ''} onchange="ChatWizard.syncSelectedSpecs()">
                    <label class="form-check-label ${opt.isRec ? 'text-warning fw-bold' : 'text-light'}" for="chatDepth_${opt.val}">
                        ${opt.label}
                    </label>
                </div>
            `;
        });
        depthHtml += `
            <div class="d-flex align-items-center gap-1 mt-1" style="font-size:0.75rem;">
                <input class="form-check-input" type="radio" name="chatRackDepth" id="chatDepth_custom" value="custom" onchange="ChatWizard.syncSelectedSpecs()">
                <label class="form-check-label text-secondary me-1" for="chatDepth_custom">직접입력:</label>
                <input type="text" inputmode="numeric" id="chat-depth-custom-input" class="form-control form-control-sm bg-dark text-light border-secondary p-1" style="width:75px; font-size:0.75rem; height:24px;" placeholder="mm" onfocus="document.getElementById('chatDepth_custom').checked=true" oninput="this.value=this.value.replace(/[^0-9]/g, ''); document.getElementById('chatDepth_custom').checked=true; ChatWizard.syncSelectedSpecs()">
            </div>
        `;
        depthContainer.innerHTML = depthHtml;

        this.syncSelectedSpecs();
    },

    syncSelectedSpecs() {
        const selectedBeam = document.querySelector('input[name="chatRackBeam"]:checked')?.value;
        const selectedDepth = document.querySelector('input[name="chatRackDepth"]:checked')?.value;

        let finalBeam = parseInt(selectedBeam) || 2585;
        let finalDepth = parseInt(selectedDepth) || 1000;

        if (selectedBeam === 'custom') {
            finalBeam = parseInt(document.getElementById('chat-beam-custom-input')?.value) || finalBeam;
        }
        if (selectedDepth === 'custom') {
            finalDepth = parseInt(document.getElementById('chat-depth-custom-input')?.value) || finalDepth;
        }

        const hBeam = document.getElementById('rack-beam-length');
        const hDepth = document.getElementById('rack-depth');
        if (hBeam) hBeam.value = finalBeam;
        if (hDepth) hDepth.value = finalDepth;

        window.rackSpecs = window.rackSpecs || {};
        window.rackSpecs.beamLength = finalBeam;
        window.rackSpecs.rackDepth = finalDepth;
    },

    submitStep4() {
        this.syncSelectedSpecs();

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

        const bLen = document.getElementById('rack-beam-length')?.value || 2585;
        const rDepth = document.getElementById('rack-depth')?.value || 1000;

        this.appendUserMsg(`파렛트(${document.getElementById('pallet-w').value}×${document.getElementById('pallet-d').value}) 및 랙규격(${bLen}×${rDepth}) 선택 완료!`);
        this.currentStep = 5;
        setTimeout(() => this.startStep5(), 600);
    },

    startStep5() {
        this.appendBotMsg(`
            <span class="step-indicator">Step 5/7</span><br>
            <span class="small">다음은 창고에서 사용하실 <b>지게차 정보</b>를<br>선택해주세요! 🚜</span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 정확한 인상높이나 통로 폭을 모르셔도 <b>지게차 종류(입승식/좌승식 등)만 선택</b>하시면 아사미야가 평균적인 값을 알아서 입력해 드려요!
            </div>
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
            <span class="small">거의 다 왔어요!<br>랙을 <b>몇 단으로 설치</b>하실지 알려주세요.</span>
            <div class="mt-2 p-2 rounded text-start" style="background:rgba(56,189,248,0.1); font-size:0.85rem; border:1px dashed rgba(56,189,248,0.5); line-height:1.4;">
                💡 <b>Tip:</b> 설치 높이를 비워두시면, <b>파렛트 제원과 단수에 맞춰 아사미야가 자동으로 계산</b>해 드려요!
            </div>
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
                참고할 <b>도면이나 현장 사진</b>이 있다면 첨부해주시고,<br><b>[자동 배치 시작하기]</b>를  눌러주세요!
            </span>
            <div class="form-card text-center py-3 mt-3 mb-2" style="cursor: pointer; border: 2px dashed #0ea5e9; border-radius: 12px; background: rgba(14, 165, 233, 0.05);" onclick="document.getElementById('file-input').click()">
                <div style="font-size:2rem; margin-bottom: 3px;">📁</div>
                <div class="fw-bold small" style="color: #0284c7;">클릭하여 파일 업로드</div>
                <small class="text-secondary">(선택사항: 이미지, PDF)</small>
            </div>
            <div id="file-preview-area" class="d-flex flex-wrap gap-2 mt-2"></div>
            <button id="final-submit-btn" class="chat-btn mt-3" style="background: linear-gradient(135deg, #10b981, #059669); font-size: 1rem; padding: 12px; border-radius: 25px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);" onclick="ChatWizard.submitFinal()">🚀 자동 배치 시작하기</button>
        `, true);
    },

    submitFinal() {
        const btn = document.getElementById('final-submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '✅ 배치 완료';
            btn.style.background = '#64748b';
            btn.style.boxShadow = 'none';
            btn.style.cursor = 'not-allowed';
        }

        if(typeof window.runAutoLayout === 'function') {
            window.runAutoLayout();
            
            // 배치 완료 후 채팅창 안내 메시지 출력
            setTimeout(() => {
                const vendorSlug = window.vendorUserId || 'asamiya';
                this.appendBotMsg(`
                    🎉 <b>파렛트랙 배치가 성공적으로<br>실행되었습니다!</b><br><br>
                    <span class="small">
                    • <b>마우스 휠</b>을 굴리면 <b>확대/축소</b>가 가능해요.<br>
                    • 랙을 드래그해서 원하는 위치로<br>자유롭게 이동시킬 수 있어요.<br>
                    &nbsp;&nbsp;<span class="text-primary">(💡 <b>Shift 키</b>를 누른 채로 드래그하면<br>&nbsp;&nbsp;더욱 부드럽고 미세하게 이동해요!)</span><br>
                    • 리모컨을 사용해서 연장이나 회전,<br>삭제 등을 할 수 있어요! 🚀<br>
                    &nbsp;&nbsp;<span class="text-primary">(💡 리모컨 <b>상단(CANVAS)을 드래그</b>하면<br>&nbsp;&nbsp;작업하기 편한 위치로 옮길 수 있어요!)</span><br>
                    <span class="text-danger fw-bold">※ 주의:</span> 랙을 드래그해서 이동할 때에는<br>리모컨의 모든 버튼이 꺼져 있는지 꼭 확인하세요!
                    </span>
                    <div class="mt-3">
                        <button onclick="if(window.startRemoteControlTutorial) window.startRemoteControlTutorial();" class="chat-btn border-0 w-100 text-decoration-none d-block text-center shadow-sm text-white fw-bold" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); font-size: 0.85rem; padding: 8px 12px; cursor: pointer;">🎮 리모콘 사용법 (대화형 가이드)</button>
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

// 📑 멀티 플로어 전환/추가 챗봇 연동 훅
window.onFloorSwitched = function(floorName) {
    if (typeof ChatWizard !== 'undefined' && typeof ChatWizard.appendBotMsg === 'function') {
        ChatWizard.appendBotMsg(`
            📍 <b>[${floorName}]</b> 구역으로 화면을 전환했어요!<br>
            <span class="small text-secondary">이 층/구역의 치수와 배치된 랙을 확인 및 수정하실 수 있습니다 💕</span>
        `);
    }
};

window.onFloorAdded = function(floorName) {
    // ChatWizard.resetForNewFloor에 의해 Step 1 안내가 자동으로 렌더링됩니다.
};
</script>
