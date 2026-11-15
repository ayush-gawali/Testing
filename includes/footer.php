</main>
<footer class="footer"><div class="container">
  &copy; <?= date('Y') ?> CoviCare - BE Computer Engineering Mini Project. For awareness only; not medical advice.
</div></footer>
<script>const RULES = <?= json_encode(covicare_rules()) ?>;</script>
<script src="<?= BASE_URL ?>js/validation.js"></script>
</body>
</html>
