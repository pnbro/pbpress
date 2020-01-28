(function ($) {
    'use strict';

    $.trumbowyg.defaultOptions['autogrow'] = true;
    $.trumbowyg.defaultOptions['btns'] = [
        ['undo', 'redo'],
        ['formatting'],
        ['strong', 'em', 'del'],
        ['superscript', 'subscript'],
        ['link', 'pb_image_upload'],
        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
        ['unorderedList', 'orderedList'],
        ['horizontalRule'],
        ['removeformat'],
    ];

    $.extend(true, $.trumbowyg, {
        
        langs: {
            en: {
                pb_image_upload: 'Insert Image',
                pb_image_upload_dropzone_placeholder : "Upload Image"
            },
            ko: {
                pb_image_upload: '이미지 추가',
                pb_image_upload_dropzone_placeholder : "이미지 업로드"
            },
        },
        plugins: {
            resizimg: {
                minSize: 64,
                step: 16,
            },
            pb_image_upload: {
                init: function(trumbowyg_){

                    trumbowyg_._pb_image_upload_modal_uid = PB.random_string(5);

                    var modal_html_ = '<div class="pb-imageupload-dropzone-modal modal" id="pb-image-uploader-modal-'+trumbowyg_._pb_image_upload_modal_uid+'">' +
                        '<div class="modal-dialog">' +
                            '<div class="modal-content">' +
                                '<div class="modal-body">' +
                                    '<input id="pb-image-uploader-modal-input-'+trumbowyg_._pb_image_upload_modal_uid+'" type="file" name="files[]" accept="image/*" multiple>' +
                                '</div>' +
                                '<div class="modal-footer">' +
                                    '<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                    trumbowyg_._pb_image_uploader_modal = $(modal_html_);
                    trumbowyg_._pb_image_uploader_modal.appendTo("body");
                    trumbowyg_._pb_image_uploader_modal.find("#pb-image-uploader-modal-input-"+trumbowyg_._pb_image_upload_modal_uid).pb_fileupload_btn({
                        upload_url : PB.file.upload_url(),
                        label : trumbowyg_.lang.pb_image_upload_dropzone_placeholder,
                        button_class : "btn-default btn-sm",
                        dropzone : trumbowyg_._pb_image_uploader_modal,
                        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                        autoupload : true,
                        limit : 10,
                        done : $.proxy(function(files_){
                            var trumbowyg_ = this;
                            $.each(files_, function(){
                                trumbowyg_.execCmd('insertImage', PBVAR['home_url']+"uploads/"+this['upload_path']+this['r_name'], false, true);
                            });
                            
                            trumbowyg_._pb_image_uploader_modal.modal("hide");
                        }, trumbowyg_)
                    });

                    // console.log(trumbowyg_._pb_image_uploader_modal);
                    
                    trumbowyg_.addBtnDef('pb_image_upload', {
                        ico : "insert-image",
                        fn : $.proxy(function(){
                            this._pb_image_uploader_modal.modal("show");
                        },trumbowyg_)
                    });
                },
                destroy: function(trumbowyg_){
                    this._pb_image_uploader_modal.remove();
                    delete this._pb_image_uploader_modal;
                    delete this._pb_image_upload_modal_uid;
                }
            }
        }
    });

})(jQuery);