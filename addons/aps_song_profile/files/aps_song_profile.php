<?php if(boomAllow($addons['addons_access'])){ 
	require(addonsLang($addons['addons']));
} ?>
<script data-cfasync="false" type="text/javascript">
$(document).ready(function() {
	<?php if(boomAllow($addons['addons_access'])){ ?>
	    appInputMenu('addons/aps_song_profile/files/icon.png', 'profileMusicBox();');
		profileMusicBox = function() {
			$.post('addons/aps_song_profile/system/action.php', {
				edit_profile_music: 1,
				token: utk,
			}, function(response) {
				showModal(response, 500);
				$("#fileuploader").uploadFile({
					url: 'addons/aps_song_profile/system/action.php',
					fileName:"file",
					multiple:false,
					dragDrop:false,
					maxFileCount:1,
					uploadStr: '<i class="fa fa-paperclip"></i> <?php echo $lang['profile_music_select']; ?>',
					formData: { 
						save_profile_music: 1, 
						token:utk 
					},
					onSubmit:function(files) {
						$(".upload_label").hide();
						$("#fileuploader").hide();
					  },
					onSuccess:function(files,data,xhr,pd){
						callSaved(system.saved, 1);
						profileMusicBox();
					},
				});
			});
		}
		deleteProfileMusic = function() {
			$.post('addons/aps_song_profile/system/action.php', {
				remove_profile_music: 1,
				token: utk,
			}, function(response) {
				callSaved(system.saved, 1);
				profileMusicBox();
			});
		}
	<?php } ?>
	
    getProfileMusic = function(t){
		if($('#profile_music').length > 0) return false;
		$.post('addons/aps_song_profile/system/action.php', {
				get_profile_music: 1,
				target: t,
				token: utk,
			}, function(response) {
				$('#probio').prepend(response); 
			});
	}

	$(document).on('click','.get_info', function(){
		var target = $(this).attr('data');
		setInterval(getProfileMusic(target), 1000);
	});
		
	$(document).on('click','.cancel_modal', function(){
		clearInterval(getProfileMusic);
	});  
    
});
</script> 