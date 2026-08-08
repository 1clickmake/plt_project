<?php include_header('Register', $siteConfig); ?>

<div class="container auth-container-register">
    <div class="glass-card">
        <h2 class="auth-title">Join Us</h2>
        
        <?php if (isset($error)): ?>
            <div class="auth-alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="country" value="<?= $_SESSION['visitor_country'] ?? 'Unknown' ?>">
            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="user_id" id="user_id" class="form-control" required>
                <div id="user_id_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
                <div id="username_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required>
                <div id="email_feedback" class="feedback-text"></div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-auth-submit">Create Account</button>
        </form>
        
        <p class="auth-footer-text">
            Already have an account? <a href="/login" class="auth-link">Login here</a>
        </p>
    </div>
</div>


<?php include_footer($siteConfig); ?>

<!-- Success Modal -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div id="successModal" class="modal-overlay">
    <div class="modal-content glass-card success-box">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3>Welcome Aboard!</h3>
        <p>회원가입이 완료 되었습니다.<br>잠시 후 로그인 페이지로 이동합니다.</p>
        
        <div class="timer-bar">
            <div id="timerProgress" class="progress"></div>
        </div>

        <div class="timer-link-container">
            <a href="/login" class="btn btn-primary">지금 바로 로그인</a>
            <span class="timer-text">
                <span id="countdown">3</span>초 후 자동 이동
            </span>
        </div>
    </div>
</div>

<?php endif; ?>
