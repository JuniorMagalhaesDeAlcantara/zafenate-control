    </div><!-- /.zf-content -->
    </div><!-- /.zf-main -->
</div><!-- /.zf-layout -->

<script>
// Auto-fecha alertas flash após 4s
document.querySelectorAll('.zf-alert[data-auto-close]').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity 0.4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, 4000);
});

// Confirmar ações destrutivas
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm)) {
            e.preventDefault();
        }
    });
});
</script>

<?php if (!empty($extraScripts)): ?>
    <?php foreach ($extraScripts as $script): ?>
        <script src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
