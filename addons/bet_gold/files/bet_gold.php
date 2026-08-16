


<?php if(boomAllow($addons['addons_access'])){ ?>
<script data-cfasync="false">

getbet_goldr = function(){
    $.post('addons/bet_gold/system/bet_gold.php', { 
        token: utk,
    }, function(response) {
        showModal(response, 400);
    });
}

function flipGoldGame() {
    var bet_amount = $('#changegoldme').val();
    $.post('addons/bet_gold/system/bet_game.php', {
        bet_amount: bet_amount,
        token: utk
    })
    .done(function(response) {
        console.log("Raw Response:", response);

       
        if (response) { 
           
            if (response.new_gold !== undefined) {
                var resultMessage = (response.result === 'win') 
                    ? 'You won and earned ' + bet_amount + ' gold! ' 
                    : 'Unfortunately, you lost. ';
                var balanceMessage = 'Your current balance: ' + response.new_gold;
                var finalMessage = resultMessage + balanceMessage; 

                callSuccess(finalMessage); 
            } else {
            
                callError('Insufficient balance for betting.'); 
            }
        } else {
            callError('An error occurred. Please try again or contact support.'); 
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX Error:", textStatus, errorThrown);
        callError('Network error. Please check your internet connection and try again.'); 
    });
}

$(document).ready(function(){
    appInputMenu('addons/bet_gold/files/bet_gold.png', 'getbet_goldr();');
});
$(document).ready(function(){
    boomAddCss('addons/bet_gold/files/bet_gold.css');
});

</script>
<?php } ?>