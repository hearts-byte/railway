<?php if (boomAllow($addons['addons_access'])) { ?>
<script data-cfasync="false" type="text/javascript">
	aaGlobalNotifyOpenCompose = function() {
		$.post('addons/AA_global_notify/system/open_compose.php', {
			token: utk,
		}, function(response) {
			if (response == 0) {
				callError(system.error);
			} else {
				showEmptyModal(response, 350);
			}
		});
	}
	$(document).ready(function() {
		appInputMenu('addons/AA_global_notify/files/announce.svg', 'aaGlobalNotifyOpenCompose();');
	});
</script>
<?php } ?>
