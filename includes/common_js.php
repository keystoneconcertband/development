    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-EBPFTM35MW"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-EBPFTM35MW');

		// Prevent browser aria-hidden focus warnings when Bootstrap modals hide
		document.addEventListener('hide.bs.modal', function (event) {
			if (event.target && typeof event.target.contains === 'function' && event.target.contains(document.activeElement)) {
				document.activeElement.blur();
			}
		});

		// Automatically clear alerts inside modals when the modal closes
		document.addEventListener('hidden.bs.modal', function (event) {
			if (event.target && typeof event.target.querySelectorAll === 'function') {
				event.target.querySelectorAll('.alert').forEach(function (alertEl) {
					if (alertEl._hideTimeout) {
						clearTimeout(alertEl._hideTimeout);
						alertEl._hideTimeout = null;
					}
					alertEl.className = 'alert d-none';
					alertEl.innerHTML = '';
					alertEl.removeAttribute('role');
				});
			}
		});
	</script>
<?php
if (!isset($_SESSION["email"])) {
    require_once __DIR__ . '/asset.php';
    $loginIntegrity = '';
    echo '<script src="' . asset('/assets/js/memberlogin.js', $loginIntegrity) . '" integrity="' . $loginIntegrity . '" crossorigin="anonymous"></script>' . "\n";
}

