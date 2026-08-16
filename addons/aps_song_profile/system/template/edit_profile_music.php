<?php global $addons; global $data; ?>
<div class="form_full pad15">
	<div class="setting_element">
		<div style="width:100%;display: block;" class="input_item main_item base_main">
		    <?php if(!empty($data['profile_music'])){ ?>
			   <audio style="width: 240px;height: 30px;" controls="" autoplay="" loop=""><source src="upload/upload/<?php echo $data['profile_music']; ?>" type="audio/mpeg"></audio>
			  <br>
			  <button onclick="deleteProfileMusic();" type="button" class="clear_top reg_button delete_btn">
			  <i class="fa fa-trash"></i>  <?php echo $lang['delete']; ?> </button>	
			  <?php } else { ?>
			        <div id="fileuploader"> <?php echo $lang['profile_music_select']; ?></div>
			  <?php } ?>
		</div>
	</div>
</div>
<link href="addons/<?php echo $addons['addons']; ?>/files/<?php echo $addons['addons']; ?>.css" rel="stylesheet">
<script src="addons/<?php echo $addons['addons']; ?>/files/<?php echo $addons['addons']; ?>.js"></script>
