</main>

<footer>
    <div class="footer-content">
        <p>&copy; <?= date('Y') ?> Le Grand Miam - Projet Creative Yumland (CY Tech)</p>
        <div class="footer-links">
            <a href="/public/html/mentions.php">Mentions Légales</a> |
            <a href="/public/html/inscription.php">Devenir Membre</a>
        </div>
    </div>
</footer>

<script src="/public/js/script.js"></script>
<?php if (isset($additionalJs)): ?>
    <?php foreach ($additionalJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>