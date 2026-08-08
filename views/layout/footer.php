
    
    <!-- 템플릿 전용 JS 동적 로드 -->
    <?= load_template_scripts($siteConfig ?? []) ?>

    <script src="/js/app.js"></script> <!-- 통합 스크립트 -->
    <?php do_action('public_footer'); ?>
</body>
</html>
