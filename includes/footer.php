<?php
/** footer.php — closes the layout opened in header.php. */
$user = currentUser();
?>
  </main>
<?php if ($user): ?>
</div>
<?php endif; ?>
</body>
</html>
