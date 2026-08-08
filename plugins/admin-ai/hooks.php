<?php

/**
 * Admin AI Plugin Hooks
 */

// 1. Add AI Assistant CSS
add_action('admin_header_head', function() {
    echo '
    <style>
        .ai-assistant-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .ai-input-wrapper {
            position: relative;
            display: flex;
            gap: 10px;
        }
        .ai-input {
            flex: 1;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .ai-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
        }
        .ai-submit-btn {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 0 1.5rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .ai-submit-btn:hover {
            transform: scale(1.05);
        }
        .ai-result-area {
            margin-top: 2rem;
            display: none;
            animation: fadeIn 0.5s ease;
        }
        .ai-chart-container {
            height: 300px;
            margin-top: 1rem;
        }
        .ai-explanation {
            font-size: 0.9rem;
            color: #ccc;
            margin-top: 1rem;
            font-style: italic;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
});

// 2. Add AI Assistant Search Bar to Dashboard
add_action('admin_dashboard_before_grid', function() {
    ?>
    <div class="ai-assistant-card">
        <h3 class="mb-3 d-flex align-items-center">
            <i class="fa-solid fa-robot me-2 text-primary"></i> Admin AI Assistant
        </h3>
        <p class="text-muted-small mb-4">관리 기능을 질문하세요. "2레벨 회원 엑셀 다운로드해줘", "최근 가입 현황 그래프", "1주일간 접속 통계" 등</p>
        
        <div class="ai-input-wrapper">
            <input type="text" id="ai-query-input" class="ai-input" placeholder="질문을 입력하세요...">
            <button type="button" id="ai-query-btn" class="ai-submit-btn">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>

        <div id="ai-result-area" class="ai-result-area">
            <div id="ai-content-body"></div>
            <div id="ai-explanation" class="ai-explanation"></div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let currentChart = null;

        $('#ai-query-btn').on('click', function() {
            submitAiQuery();
        });

        $('#ai-query-input').on('keypress', function(e) {
            if(e.which == 13) submitAiQuery();
        });

        function submitAiQuery() {
            const query = $('#ai-query-input').val().trim();
            if (!query) return;

            const $btn = $('#ai-query-btn');
            const $resultArea = $('#ai-result-area');
            const $contentBody = $('#ai-content-body');
            const $explanation = $('#ai-explanation');

            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
            $resultArea.hide();
            
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }

            $.ajax({
                url: '/admin/ai/query',
                method: 'POST',
                data: {
                    prompt: query,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        renderAiResult(data);
                        $resultArea.fadeIn();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('서버 오류가 발생했습니다.');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane"></i>');
                }
            });
        }

        function renderAiResult(res) {
            const $body = $('#ai-content-body');
            const $exp = $('#ai-explanation');
            $body.empty();
            $exp.text(res.explanation || '');

            switch (res.type) {
                case 'CHART':
                    $body.append(`<h4>${res.title}</h4>`);
                    $body.append('<div class="ai-chart-container"><canvas id="ai-chart-canvas"></canvas></div>');
                    const ctx = document.getElementById('ai-chart-canvas').getContext('2d');
                    currentChart = new Chart(ctx, {
                        type: res.chartType || 'line',
                        data: {
                            labels: res.labels,
                            datasets: [{
                                label: res.title,
                                data: res.data,
                                backgroundColor: 'rgba(99, 102, 241, 0.5)',
                                borderColor: 'rgba(99, 102, 241, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: 'white' } }
                            },
                            scales: {
                                y: { ticks: { color: 'white' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                                x: { ticks: { color: 'white' }, grid: { color: 'rgba(255,255,255,0.1)' } }
                            }
                        }
                    });
                    break;

                case 'DOWNLOAD':
                    $body.append(`
                        <div class="glass-card mb-0" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                            <h4 class="text-success"><i class="fa-solid fa-file-excel me-2"></i> ${res.message}</h4>
                            <p>${res.explanation}</p>
                            <a href="${res.downloadUrl}" class="btn btn-primary mt-2">
                                <i class="fa-solid fa-download me-1"></i> 파일 다운로드
                            </a>
                        </div>
                    `);
                    break;

                case 'LIST':
                    let table = `<h4 class="mb-3">${res.title}</h4><div class="admin-table-wrapper"><table class="admin-table"><thead><tr>`;
                    res.headers.forEach(h => table += `<th>${h}</th>`);
                    table += '</tr></thead><tbody>';
                    res.rows.forEach(row => {
                        table += '<tr>';
                        row.forEach(cell => table += `<td>${cell}</td>`);
                        table += '</tr>';
                    });
                    table += '</tbody></table></div>';
                    $body.append(table);
                    break;

                case 'TEXT':
                default:
                    $body.append(`<div class="ai-text-response">${res.content}</div>`);
                    break;
            }
        }
    });
    </script>
    <?php
});
