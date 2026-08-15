<?php if(boomAllow($addons['addons_access'])){ ?>
<script data-cfasync="false">

var snippet = 'snippet';
var youtubeLimit = 16;
var youtubeMode = 1;
var youtubeTarget = 0;

getYoutube = function(md){
	youtubeMode = md;
	if(md == 2){
		youtubeTarget = currentPrivate;
	}
	else {
		youtubeTarget = 0;
	}
	$.post('addons/youtube/system/youtube_template.php', {
		type: youtubeMode,
		}, function(response) {
			showEmptyModal(response, 400);
			youtubeTemplate = response;
	});
}
youtubeItemTemplate = function(youId, youTitle, youTumb){
	ytmp = '';
	ytmp += '<div onclick="sendYoutube(\''+youId+'\');" class="btable you_result">';
	ytmp += '<div class="bcell pad5">';
	ytmp += '<img class="you_tumb" src="'+youTumb+'" autoplay loop/>';
	ytmp += '<div class="you_text"><p class="bold">'+youTitle+'</p></div>';
	ytmp += '</div>';
	ytmp += '</div>';
	return ytmp;
}

startYoutubeSearch = function(event, item){
	var youtubeContent = $(item).val();
	if(event.keyCode == 13 && event.shiftKey == 0){
		if (/^\s+$/.test(youtubeContent) || youtubeContent == ''){
			return false;
		}
		else {
			youtubeSearch(youtubeContent);
		}
	}
}
sendYoutubeMain = function(yid){
	$.ajax({
		url: "addons/youtube/system/youtube_main.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			id: yid,
		},
		success: function(response){
			if(response.code == 1){
				appendSelfChatMessage(response.logs);
			}
			else {
				callError(system.error);
			}
		},
		error: function(){
			callError(system.error);
		}
	});
}
sendYoutubePrivate = function(yid){
	$.ajax({
		url: "addons/youtube/system/youtube_private.php",
		type: "post",
		cache: false,
		dataType: 'json',
		data: { 
			id: yid,
			target: youtubeTarget,
		},
		success: function(response){
			if(response.code == 1){
				appendSelfPrivateMessage(response.logs);
			}
			else if(response.code == 99){
				appendCannotPrivate();
			}
			else {
				callError(system.error);
			}
		},
		error: function(){
			callError(system.error);
		}
	});
}
sendYoutube = function(yid){
	hideModal();
	if(youtubeMode == 1){
		sendYoutubeMain(yid);
	}
	else if(youtubeMode == 2){
		sendYoutubePrivate(yid);
	}
}
youtubeSearch = function(event, item, t){
	if(event.keyCode == 13 && event.shiftKey == 0){
		var youtubeContent = $(item).val();
		$('#find_youtube').val('');
		if (/^\s+$/.test(youtubeContent) || youtubeContent == ''){
			return false;
		}
		else{
			$.ajax({
				url: "addons/youtube/system/youtube_search.php",
				type: "post",
				cache: false,
				dataType: 'json',
				data: { 
					type: t,
					youtube_search: youtubeContent,
				},
				success: function(data){
					if (typeof data == 'object' && data.pageInfo.totalResults > 0 && data.pageInfo.resultsPerPage > 0) {
						$('#youtube_results').html('');
						$('#youtube_results').scrollTop(0);
						for (var i = 0; i < data.items.length; i++) {
							$('#youtube_results').append(youtubeItemTemplate(data.items[i].id.videoId, data.items[i].snippet.title, data.items[i].snippet.thumbnails.medium.url));	
						}
					}
					else{
						$('#youtube_results').html(noDataTemplate());
					}
				},
			});	
		}
	}
}

$(document).ready(function(){
	appInputMenu('addons/youtube/files/youtube.svg', 'getYoutube(1);');
	appPrivInputMenu('addons/youtube/files/youtube.svg', 'getYoutube(2);');
	boomAddCss('addons/youtube/files/youtube.css');
});

</script>
<?php } ?>