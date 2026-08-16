<?php

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat

require(addonsLang('roll_the_dice'));

if($data['user_roomid'] == $addons['custom1']){ ?>
	
<script data-cfasync="false">

// Roll the dice
// Custom plugin by JOOJ
// for Cody chat


rollTheDice = function(item){
	var user = $(item).attr('data');
	$.post('addons/roll_the_dice/system/action.php', { 
		user: user,
		token: utk,
		}, function(response) {
			if(response == 1){
				callSaved("This user is not in not in your room!", 3);
			}
	});
}
	
	
$(document).ready(function(){
		var diceButton = '<div data="" value="" data-av="" class="avset avitem" style="vertical-align:middle;display:flex;justify-content:center;align-items:center;" onclick="rollTheDice(this);"><img src="addons/roll_the_dice/files/images/dice.png" height="16" width="16"> <?php echo $lang['roll_the_dice']; ?></div>';
		$('.avother').append(diceButton);
		$('.avstaff').append(diceButton);
});

</script>

<?php } ?>








