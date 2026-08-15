<?php
$load_addons = 'AA_photo_frame';
require_once('../../../system/config_addons.php');
if (!boomAllow($addons['addons_access'])) {
	die();
}
?>
<div class="brow">
	<div class="bcell border_bottom">
		<div class="modal_top_menu">
			<div class="bcell_mid hpad15">
				<p class="label"><i class='fa fa-image bgrad27'></i> <?php echo $lang['open_list_frame']; ?></p>
			</div>
			<div class="modal_top_menu_empty">
			</div>
			<div class="cancel_modal cover_text modal_top_item">
				<i class="fa fa-times"></i>
			</div>
		</div>
	</div>
</div>
<div class="pad10">
	<p class="label"><?php echo $lang['choose_frame']; ?></p>
	<div style="padding: 0;" class="centered_element ulist_container">
		<div class="gift-responsive" onclick="delPhotoFrame()" value="n-fr-mlky">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/em.png" />
				</div>
				<div style="background: red;" class="gift-desc"><?php echo $lang['delete_frame']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('dragon.webp')" value="dragon">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/dragon.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr-mlky.webp')" value="n-fr-mlky">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr-mlky.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr1.webp')" value="n-fr1">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr1.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr2.webp')" value="n-fr2">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr2.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr3.webp')" value="n-fr3">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr3.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr4.webp')" value="n-fr4">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr4.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr5.webp')" value="n-fr5">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr5.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr6.webp')" value="n-fr6">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr6.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr7.webp')" value="n-fr7">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr7.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr8.webp')" value="n-fr8">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr8.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr9.webp')" value="n-fr9">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr9.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr10.webp')" value="n-fr10">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr10.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr11.webp')" value="n-fr11">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr11.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr13.webp')" value="n-fr13">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr13.webp" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr14.gif')" value="n-fr14">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr14.gif" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr12.png')" value="n-fr12">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr12.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr17.png')" value="n-fr17">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr17.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr19.png')" value="n-fr19">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr19.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr24.png')" value="n-fr24">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr24.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr27.png')" value="n-fr27">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr27.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('n-fr28.png')" value="n-fr28">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/n-fr28.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame1.png')" value="frame1">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame1.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame2.png')" value="frame2">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame2.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame33.png')" value="frame33">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame33.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame4.png')" value="frame4">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame4.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame5.png')" value="frame5">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame5.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame6.png')" value="frame6">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame6.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame7.png')" value="frame7">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame7.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame8.png')" value="frame8">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame8.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame9.png')" value="frame9">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame9.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame10.png')" value="frame10">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame10.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame11.png')" value="frame11">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame11.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('pink-frame.png')" value="pink-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/pink-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame120.png')" value="frame120">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame120.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame130.png')" value="frame130">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame130.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame1400.png')" value="frame1400">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame1400.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame1500.png')" value="frame1500">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame1500.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame160.png')" value="frame160">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame160.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame170.png')" value="frame170">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame170.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame1810.png')" value="frame1810">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame1810.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame190.png')" value="frame190">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame190.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame200.png')" value="frame200">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame200.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('frame2110.png')" value="frame2110">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/frame2110.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('flower-frame.png')" value="flower-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/flower-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('caty-frame.png')" value="caty-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/caty-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('hat-frame.png')" value="hat-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/hat-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('star-frame.png')" value="star-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/star-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
		<div class="gift-responsive" onclick="savePhotoFrame('santa-frame.png')" value="santa-frame">
			<div class="gift-container">
				<div style="height:60px;">
					<img style="height: 55px;width: 50px;padding: 0;" src="addons/AA_photo_frame/files/frame/santa-frame.png" />
				</div>
				<div class="gift-desc"><?php echo $lang['click_to_use']; ?></div>
			</div>
		</div>
	</div>
</div>
<script data-cfasync="false" type="text/javascript">
	savePhotoFrame = function(frame) {
		$.post('addons/AA_photo_frame/system/action.php', {
			set_photo_frame: frame,
			token: utk
		}, function(response) {
			if (response == 1) {
				aaFrameSetLocal(user_id, frame);
				aaFrameRefresh(aaFrameApplyAll);
				callSaved(aaFrameSaved, 1);
				hideModal();
			} else {
				callSaved(aaFrameError, 3);
			}
		});
	}
	delPhotoFrame = function(frame) {
		$.post('addons/AA_photo_frame/system/action.php', {
			del_frame_target: 1,
			token: utk
		}, function(response) {
			if (response == 1) {
				aaFrameSetLocal(user_id, '');
				aaFrameRefresh(aaFrameApplyAll);
				callSaved(aaFrameRemoved, 1);
				hideModal();
			} else if (response == 2) {
				aaFrameSetLocal(user_id, '');
				aaFrameApplyAll();
				callSaved(aaFrameRemoved, 1);
				hideModal();
			} else {
				callSaved(aaFrameError, 3);
			}
		});
	}
</script>
