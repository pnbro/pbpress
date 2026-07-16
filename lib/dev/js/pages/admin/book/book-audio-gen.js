/**
 * book-audio-gen 페이지 스크립트
 * @category admin/book
 */
jQuery(function(){
    "use strict";

    var $form_ = $("#pb-book-audio-gen-form");
    // TODO: 초기화 코드

function book_audio_gen_custom(data_){
    PB.post("pb-admin-book-ajax-do-gen-audio", data_, function(result_, response_json_){
        if(!result_ || response_json_.success !== true){
            PB.alert({
                title: response_json_.error_title || __("에러발생"),
                content: response_json_.error_message || __("처리 중 오류가 발생했습니다.")
            });
            return;
        }
        // TODO: 성공 처리
    }, true);
}

function book_audio_gen_load(data_){
    PB.post("pb-admin-book-ajax-audio-gen-progress", data_, function(result_, response_json_){
        if(!result_ || response_json_.success !== true){
            PB.alert({
                title: response_json_.error_title || __("에러발생"),
                content: response_json_.error_message || __("처리 중 오류가 발생했습니다.")
            });
            return;
        }
        // TODO: 성공 처리
    }, true);
}

function book_audio_gen_custom(data_){
    PB.post("pb-admin-book-ajax-retry-failed-audio", data_, function(result_, response_json_){
        if(!result_ || response_json_.success !== true){
            PB.alert({
                title: response_json_.error_title || __("에러발생"),
                content: response_json_.error_message || __("처리 중 오류가 발생했습니다.")
            });
            return;
        }
        // TODO: 성공 처리
    }, true);
}
});
