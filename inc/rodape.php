</main>

<footer class="text-center text-muted small py-3 border-top">
    Mini Sistema de Gestão de Produtos &middot; Trabalho de Programação Web
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($scripts)): foreach ($scripts as $js): ?>
    <script src="assets/js/<?= e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
