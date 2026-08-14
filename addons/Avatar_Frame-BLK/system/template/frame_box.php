<div id="gift_first" class="pad15">
	<?php echo frames_box_list(); ?>
</div>
<div id="gift_second" class="hidden" data-id="" data-user="">
	<div class="modal_content">
		<div class="centered_element">
			<div class="tpad20">
				<img class="gift_selected" id="gift_selected" src="">
			</div>
			<div class="tpad20 bpad10">
				<div class="btable_auto mauto">
					<div class="gift_selicon bcell_mid">
						<img id="gift_sgold" src="<?php echo goldIcon(); ?>"/>
						<img id="gift_sruby" src="<?php echo rubyIcon(); ?>"/>
					</div>
					<div id="gift_pricing" class="gift_pricing hpad5 bcell_mid">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal_control centered_element bpad20">
		<button class="btn_frame_send reg_button theme_btn"><?php echo $lang['aply']; ?></button>
		<?php if (!empty($data['avatar_frame'])) { ?>
		<button class="btn_frame_delete reg_button delete_btn theme_btn"><?php echo $lang['delete']; ?></button>
		<?php } ?>
		<button class="btn_frame_back reg_button default_btn"><?php echo $lang['back']; ?></button>
	</div>
</div>
<script data-cfasync="false">
$(document).ready(function(){
   	$( ".btn_frame_back" ).on('click', function() {
        $( '#gift_second' ).hide();
        $( '#gift_first' ).show();
    });
   	$( ".btn_frame_send" ).on('click', function() {
        var frame = $('#gift_second').attr('data-id');
		set_frame(frame);
    });
   	$( ".btn_frame_delete" ).on('click', function() {
		unset_frame();
    });
});
</script>