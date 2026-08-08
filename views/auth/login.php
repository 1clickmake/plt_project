<?php include_header('Login', $siteConfig); ?>

<div class="container auth-container">
    <div class="glass-card">
        <h2 class="auth-title">Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="auth-alert-error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group mb-3">
                <label>User ID</label>
                <input type="text" name="user_id" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-auth-submit">Sign In</button>
        </form>
        
        <p class="auth-footer-text">
            Don't have an account? <a href="/register" class="auth-link">Register here</a>
        </p>
    </div>
</div>

<?php include_footer($siteConfig); ?>
