/**
 * Smart Canvas Remote Control Interactive Tutorial (Intro.js)
 * Created by Asamiya 💕
 */

// Inject Intro.js base CSS & dark theme glassmorphism CSS dynamically if not present
(function injectIntroStyles() {
    if (!document.getElementById('introjs-cdn-css')) {
        const link = document.createElement('link');
        link.id = 'introjs-cdn-css';
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css';
        document.head.appendChild(link);
    }

    if (!document.getElementById('introjs-custom-style')) {
        const style = document.createElement('style');
        style.id = 'introjs-custom-style';
        style.innerHTML = `
            .introjs-tooltip {
                background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.98)) !important;
                border: 1px solid rgba(56, 189, 248, 0.4) !important;
                border-radius: 16px !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.7), 0 0 20px rgba(56, 189, 248, 0.2) !important;
                color: #f8fafc !important;
                font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
                padding: 16px 20px !important;
                min-width: 260px !important;
                max-width: 320px !important;
                backdrop-filter: blur(16px) !important;
            }
            .introjs-tooltip-header {
                font-weight: 700 !important;
                font-size: 1.05rem !important;
                color: #38bdf8 !important;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
                padding-bottom: 8px !important;
                margin-bottom: 10px !important;
            }
            .introjs-tooltiptext {
                font-size: 0.88rem !important;
                line-height: 1.55 !important;
                color: #cbd5e1 !important;
            }
            .introjs-arrow.top { border-bottom-color: rgba(30, 41, 59, 0.98) !important; }
            .introjs-arrow.bottom { border-top-color: rgba(30, 41, 59, 0.98) !important; }
            .introjs-arrow.left { border-right-color: rgba(30, 41, 59, 0.98) !important; }
            .introjs-arrow.right { border-left-color: rgba(30, 41, 59, 0.98) !important; }
            
            /* 툴팁 잘림 방지를 위해 왼쪽 배치 시 툴팁을 위로 살짝(25%) 끌어올리고 화살표 위치 보정 */
            .introjs-tooltip.introjs-left {
                transform: translateY(-25%) !important;
            }
            .introjs-tooltip.introjs-left .introjs-arrow.right {
                top: calc(25% + 10px) !important;
                bottom: auto !important;
            }
            
            .introjs-button {
                background: rgba(56, 189, 248, 0.15) !important;
                color: #38bdf8 !important;
                border: 1px solid rgba(56, 189, 248, 0.3) !important;
                text-shadow: none !important;
                border-radius: 8px !important;
                font-weight: 600 !important;
                font-size: 0.82rem !important;
                padding: 6px 14px !important;
                transition: all 0.2s ease !important;
            }
            .introjs-button:hover {
                background: rgba(56, 189, 248, 0.35) !important;
                color: #ffffff !important;
                border-color: #38bdf8 !important;
                box-shadow: 0 0 10px rgba(56, 189, 248, 0.4) !important;
            }
            .introjs-disabled, .introjs-disabled:hover {
                opacity: 0.4 !important;
                background: rgba(148, 163, 184, 0.1) !important;
                color: #64748b !important;
                border-color: rgba(148, 163, 184, 0.2) !important;
                cursor: not-allowed !important;
            }
            .introjs-helperLayer {
                background: rgba(56, 189, 248, 0.08) !important;
                border: 2px solid #38bdf8 !important;
                box-shadow: 0 0 25px rgba(56, 189, 248, 0.5) !important;
                border-radius: 14px !important;
            }
            .introjs-bullets ul li a.active {
                background: #38bdf8 !important;
            }
        `;
        document.head.appendChild(style);
    }
})();

function startRemoteControlTutorial() {
    // 1. Ensure canvas-remote-ctrl is visible
    const remoteCtrl = document.getElementById('canvas-remote-ctrl');
    if (remoteCtrl) {
        remoteCtrl.classList.remove('d-none');
    }

    if (typeof introJs === 'undefined') {
        setTimeout(startRemoteControlTutorial, 300);
        return;
    }

    const intro = introJs();
    intro.setOptions({
        nextLabel: '다음 ▶',
        prevLabel: '◀ 이전',
        doneLabel: '완료 🎉',
        exitOnEsc: true,
        exitOnOverlayClick: true,
        showStepNumbers: false,
        showBullets: true,
        positionPrecedence: ['left', 'bottom', 'top', 'right'],
        steps: [
            {
                element: '#canvas-remote-ctrl',
                title: '🎮 스마트 캔버스 리모컨',
                intro: '만나서 반갑습니다! 💕 캔버스 오른쪽 상단에 위치한 리모컨으로 파렛트랙 배치와 편집을 아주 손쉽게 조작할 수 있어요!',
                position: 'left'
            },
            {
                element: '#remote-drag-rack',
                title: '🟦 랙 추가하기 (드래그앤드롭)',
                intro: '이 버튼을 마우스로 꾹 누른 채 캔버스 안으로 끌고 가면(Drag & Drop) 원하는 위치에 파렛트랙을 즉시 생성할 수 있습니다.<br><br><div style="text-align:center;"><img src="/assets/images/drag.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#remote-align-btn',
                title: '🎛️ 도면 자동 정렬',
                intro: '배치된 랙들을 최적의 통로폭과 격자에 맞춰 깔끔하게 자동 정렬해 주는 스마트 기능입니다.',
                position: 'left'
            },
            {
                element: '#mode-btn-rotate',
                title: '↻ 회전 모드',
                intro: '버튼을 켠 후 캔버스 위의 랙을 클릭하면 90도씩 회전시킬 수 있습니다.<br><br><div style="text-align:center;"><img src="/assets/images/rotate.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#mode-btn-extend',
                title: '⬌ 연장 편집 모드',
                intro: '랙의 연결형(연결 칸) 개수를 늘리거나 줄일 수 있습니다.<br><br><div style="text-align:center;"><img src="/assets/images/extend.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#mode-btn-copy',
                title: '❐ 복사 편집 모드',
                intro: '이미 배치된 랙과 동일한 스펙의 랙을 복사하여 빠르게 추가 배치합니다.<br><br><div style="text-align:center;"><img src="/assets/images/copy.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#mode-btn-bypass',
                title: '🚗 바이패스 모드 (Bypass)',
                intro: '단수가 높은 랙 아래로 지게차가 통과할 수 있도록 1단 랙을 비우는 바이패스 통로를 설정합니다.<br><br><div style="text-align:center;"><img src="/assets/images/bypass.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#mode-btn-delete',
                title: '✕ 삭제 모드',
                intro: '잘못 배치했거나 불필요한 랙을 클릭하여 즉시 삭제합니다.<br><br><div style="text-align:center;"><img src="/assets/images/delete.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#mode-btn-levels',
                title: '☰ 단수 편집 모드',
                intro: '랙의 적재 단수(예: 3단, 4단 등)를 클릭하여 손쉽게 변경할 수 있습니다.<br><br><div style="text-align:center;"><img src="/assets/images/levels.gif" style="width:100%; border-radius:8px; border:1px solid rgba(56,189,248,0.3); margin-top:5px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);"></div>',
                position: 'left'
            },
            {
                element: '#remote-quote-btn',
                title: '🔴 견적 요청',
                intro: '도면 배치가 완성되면 이 버튼을 눌러 공급사에게 즉시 맞춤 견적을 요청해 보세요!',
                position: 'left'
            }
        ]
    });

    // 도면(랙)이 없는데 리모컨만 켜져있는 상태면 튜토리얼 종료 시 리모컨 닫기
    const handleTutorialExit = () => {
        const rackCount = typeof window.getRackCount === 'function' ? window.getRackCount() : 0;
        if (remoteCtrl && rackCount === 0) {
            remoteCtrl.classList.add('d-none');
        }
    };

    intro.onexit(handleTutorialExit);
    intro.oncomplete(handleTutorialExit);

    intro.start();
}

window.startRemoteControlTutorial = startRemoteControlTutorial;

// Auto-start if URL has ?tutorial=1
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tutorial')) {
        setTimeout(() => {
            startRemoteControlTutorial();
        }, 1000);
    }
});
