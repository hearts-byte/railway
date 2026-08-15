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

// boom_chat
$chat_det      = array();
$find_chat_det = $mysqli->query("SELECT user_id FROM boom_chat");
if ($find_chat_det->num_rows > 0) {
    while ($row = mysqli_fetch_object($find_chat_det)) {
        array_push($chat_det, $row);
    }
}
mysqli_free_result($find_chat_det);


// json.
$jsonco = [
    'FrameUser' => $user_Frame,
    'chatDetails' => $chat_det
];

?>
<script data-cfasync="false" type="text/javascript">
    window.frameData = <?php echo json_encode($jsonco, JSON_HEX_TAG); ?>;
    const config2 = {
        attributes: true,
        childList: true,
        characterData: true
    };
    $(document).ready(function() {
        boomAddCss('addons/AA_photo_frame/files/frame.css?12' + Math.random());
        const target = $("#chat_right_data")[0];
        const target2 = $("#chat_logs_container")[0];
        const observerUserListFrame = new MutationObserver(function(mutations) {
            mutations.forEach((mutation) => {
                const userFrame = document.querySelectorAll('.user_item_avatar');
                userFrame.forEach((item) => {
                    let $nodes2 = $(item).parent(); // jQuery set
                    let $nodes3 = $(item); // jQuery set
                    const userID = ($nodes2.prop('onclick').toString()).split(',')[1];
                    getUserFrame(userID).forEach((frame) => {
                        if (frame.frame_name !== '') {
                            $nodes3.append('<img class="over2 ul_fr_bg" src="addons/AA_photo_frame/files/frame/' + frame.frame_name + '">');
                            $nodes3.children('img').addClass('nosex');
                            $nodes3.children('img').removeClass('boy');
                            $nodes3.children('img').removeClass('girl');
                        }
                    });

                });
            });
            $.when($.post('addons/AA_photo_frame/system/api.php', {
                token: utk
            }).then(function(response) {
                frameData = JSON.parse(response);
            }));
        });
        const observerChatNodeFrame = new MutationObserver(function(mutations) {
            mutations.forEach((mutation) => {
                const chatNodes = document.querySelectorAll('.chat_avatar');
                chatNodes.forEach((item) => {
                    const ch_node = $(item); // jQuery set
                    const user_id = ($(ch_node).prop('onclick').toString()).split(',')[1];
                    getUserFrame(user_id).forEach((node) => {
                        if (node.frame_name !== '') {
                            let $frameImg = $(ch_node).children("img");
                            if ($frameImg.find(".ch_fr_bg").is(":visible")) {
                                $frameImg.remove();
                            }
                            $(ch_node).append('<img class="over ch_fr_bg" src="addons/AA_photo_frame/files/frame/' + node.frame_name + '">');
                            $(ch_node).children('img').addClass('nosex');
                            $(ch_node).children('img').removeClass('boy');
                            $(ch_node).children('img').removeClass('girl');
                        }
                    });

                });
            });
            $.when($.post('addons/AA_photo_frame/system/api.php', {
                token: utk
            }).then(function(response) {
                frameData = JSON.parse(response);
            }));
        });
        observerUserListFrame.observe(target, config2);
        observerChatNodeFrame.observe(target2, config2);
        getUserFrame = function(userid) {
            const tmpUserFrames = [];
            const frameForUser = frameData.FrameUser.filter((item) => item.user_id === userid);
            frameForUser.forEach((item) => tmpUserFrames.push(frameData.FrameUser.find((st) => st.user_id === item.user_id)));
            return tmpUserFrames;
        };
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
                        callSaved(system.error, 3);
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
                        callSaved(system.error, 3);
                    } else {
                        showEmptyModal(response, 300);
                    }
                });
            }
        });
    </script>
<?php } ?>