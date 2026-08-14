<div class="post_input_container bhover gcard select_gift gift_bg" data-img="addons/<?php echo ADDON; ?>/files/frames/<?php echo $boom['tumb']; ?>" data-price="<?php echo $boom['price']; ?>" data-id="<?php echo $boom['id']; ?>" data-method="<?php echo $boom['method']; ?>">
	<img class="gcard_img lazy" data-src="addons/<?php echo BOOM_ADDONS; ?>/files/frames/<?php echo $boom['tumb']; ?>" src="<?php echo imgLoader(); ?>">
	<div class="btable_auto gcard_price gtag input_wrap">
		<div class="bcell_mid text_small">
			<div class="btable_auto">
				<div class="bcell_mid gcard_pwrap">
					<?php if($boom['method'] == 1){ ?>
					<img src="<?php echo goldIcon(); ?>"/>
					<?php } ?>
					<?php if($boom['method'] == 2){ ?>
					<img src="<?php echo rubyIcon(); ?>"/>
					<?php } ?>
				</div>
				<div class="bcell_mid hpad3 bold">
					<?php echo $boom['price']; ?>
				</div>
			</div>
		</div>
	</div>
</div>