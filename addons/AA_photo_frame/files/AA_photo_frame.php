<?php
include(addonsLang('AA_photo_frame'));

$user_Frame      = array();
$find_user_Frame = $mysqli->query("SELECT photo_frames.* FROM photo_frames");
if ($find_user_Frame->num_rows > 0) {
    while ($row = mysqli_fetch_object($find_user_Frame)) {
        array_push($user_Frame, $row);
    }
}
mysqli_free_result($find_user_Frame);


// json.
$jsonco = [
    'FrameUser' => $user_Frame
];

?>
<script data-cfasync="false" type="text/javascript">
    window.frameData = <?php echo json_encode($jsonco, JSON_HEX_TAG); ?>;
    var aaFrameSaved = 'Zapisano';
    var aaFrameRemoved = 'Ramka zostala usunieta';
    var aaFrameError = 'Nie udalo sie zapisac ramki';
    var aaFrameRefreshRequest = null;
    var aaFrameRefreshTimer = null;
    var aaFrameLastRefresh = 0;
    var aaFrameMinRefresh = 60000;
    var aaFrameApplyTimer = null;
    const config3 = {
        attributes: true,
        childList: true,
        characterData: true
    };
    $(document).ready(function() {
        boomAddCss('addons/AA_photo_frame/files/frame.css?v=<?php echo time(); ?>');
        const target = $("#chat_right_data")[0];
        const target2 = $("#chat_logs_container")[0];
        getFrameUserId = function(item) {
            const node = $(item);
            const dataId = node.data('id') || node.parent().data('id');
            if(dataId !== undefined && dataId !== ''){
                return String(dataId).replace(/\D/g, '');
            }
            const clickData = node.prop('onclick');
            if(typeof clickData === 'function'){
                const userId = String(clickData).split(',')[1] || '';
                return userId.replace(/\D/g, '');
            }
            return '';
        }
        aaFrameFile = function(frame) {
            return 'addons/AA_photo_frame/files/frame/' + frame;
        }
        aaFrameSetLocal = function(userid, frame) {
            userid = String(userid);
            if(!frameData || !Array.isArray(frameData.FrameUser)){
                frameData = { FrameUser: [] };
            }
            frameData.FrameUser = frameData.FrameUser.filter((item) => String(item.user_id) !== userid);
            if(frame !== ''){
                frameData.FrameUser.push({
                    user_id: userid,
                    frame_name: frame
                });
            }
        }
        aaFrameRefresh = function(callback, force) {
            var now = Date.now();
            if(aaFrameRefreshRequest){
                if(typeof callback === 'function'){
                    callback();
                }
                return;
            }
            if(force !== true && (now - aaFrameLastRefresh) < aaFrameMinRefresh){
                if(typeof callback === 'function'){
                    callback();
                }
                return;
            }
            aaFrameLastRefresh = now;
            aaFrameRefreshRequest = $.ajax({
                url: 'addons/AA_photo_frame/system/api.php',
                type: 'post',
                cache: false,
                timeout: 8000,
                data: { token: utk }
            }).done(function(response) {
                try {
                    frameData = (typeof response === 'string') ? JSON.parse(response) : response;
                }
                catch(e) {}
                if(!frameData || !Array.isArray(frameData.FrameUser)){
                    frameData = { FrameUser: [] };
                }
                if(typeof callback === 'function'){
                    callback();
                }
            }).fail(function() {
                if(typeof callback === 'function'){
                    callback();
                }
            }).always(function(){
                aaFrameRefreshRequest = null;
            });
        }
        aaFrameApplyUserItem = function(item) {
            let node = $(item);
            const userID = getFrameUserId(item);
            node.find('.ul_fr_bg').remove();
            getUserFrame(userID).forEach((frame) => {
                if (frame.frame_name !== '') {
                    node.append('<img class="over2 ul_fr_bg" src="' + aaFrameFile(frame.frame_name) + '">');
                    node.children('img').addClass('nosex').removeClass('boy girl');
                }
            });
        }
        aaFrameApplyChatItem = function(item) {
            let node = $(item);
            const userID = getFrameUserId(item);
            node.find('.ch_fr_bg').remove();
            getUserFrame(userID).forEach((frame) => {
                if (frame.frame_name !== '') {
                    node.append('<img class="over ch_fr_bg" src="' + aaFrameFile(frame.frame_name) + '">');
                    node.children('img').addClass('nosex').removeClass('boy girl');
                }
            });
        }
        aaFrameApplyAll = function() {
            document.querySelectorAll('.user_item_avatar').forEach((item) => {
                aaFrameApplyUserItem(item);
            });
            document.querySelectorAll('.ch_logs .chat_avatar').forEach((item) => {
                aaFrameApplyChatItem(item);
            });
        }
        aaFrameScheduleApply = function() {
            clearTimeout(aaFrameApplyTimer);
            aaFrameApplyTimer = setTimeout(function(){
                aaFrameApplyAll();
            }, 120);
        }
        const observerUserListFrame = new MutationObserver(function(mutations) {
            aaFrameScheduleApply();
            clearTimeout(aaFrameRefreshTimer);
            aaFrameRefreshTimer = setTimeout(function(){
                aaFrameRefresh(aaFrameApplyAll);
            }, 1500);
        });
        const observerChatNodeFrame = new MutationObserver(function(mutations) {
            aaFrameScheduleApply();
            clearTimeout(aaFrameRefreshTimer);
            aaFrameRefreshTimer = setTimeout(function(){
                aaFrameRefresh(aaFrameApplyAll);
            }, 1500);
        });
        if(target){
            observerUserListFrame.observe(target, config3);
        }
        if(target2){
            observerChatNodeFrame.observe(target2, config3);
        }
        getUserFrame = function(userid) {
            const tmpUserFrames = [];
            if(!frameData || !Array.isArray(frameData.FrameUser)){
                frameData = { FrameUser: [] };
            }
            const frameForUser = frameData.FrameUser.filter((item) => String(item.user_id) === String(userid));
            frameForUser.forEach((item) => tmpUserFrames.push(frameData.FrameUser.find((st) => String(st.user_id) === String(item.user_id))));
            return tmpUserFrames;
        };
        aaFrameApplyAll();
    });
</script>
<?php if (boomAllow($addons['addons_access'])) { ?>
    <script data-cfasync="false" type="text/javascript">
        $(document).ready(function() {
            <?php if (boomAllow($addons['addons_access'])) { ?>
                $(".avstaff").append("<div data='' onclick='showListFramesStaff(this)' class='avset avitem rcustom'><span class='list_icon'><i class='fa fa-image bgrad27'></i></span> <?php echo $lang['open_list_frame']; ?></div>");
            <?php } ?>
            $(".avself").append("<div data='' onclick='showListFrames(this)' class='avset avitem rcustom'><span class='list_icon'><i class='fa fa-image bgrad27'></i></span> <?php echo $lang['open_list_frame']; ?></div>");
            showListFramesStaff = function(source) {
                var target = $(source).attr('data');
                $.post('addons/AA_photo_frame/system/open_frames_staff.php', {
                    target: target,
                    token: utk,
                }, function(response) {
                    if (response == 0) {
                        callSaved(aaFrameError, 3);
                    } else {
                        showEmptyModal(response, 300);
                    }
                });
            }
            showListFrames = function() {
                $.post('addons/AA_photo_frame/system/open_frames.php', {
                    token: utk,
                }, function(response) {
                    if (response == 0) {
                        callSaved(aaFrameError, 3);
                    } else {
                        showEmptyModal(response, 300);
                    }
                });
            }
        });
    </script>
<?php } ?>
