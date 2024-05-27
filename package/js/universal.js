/*
 * common properties
 */

var base_url    = window.location.origin + '/';
    base_url   += "pemantauan_1/";
var default_controller = "welcome";
var ajax_url    = "welcome/login_act";
var ajax_data   = "";
//var msg_obj     =   $("#msg");
var msg_content = "";
var loading     = $(".spinner");
var csrf_name   = "csrf_peppd";

function show_alert_ms(obj_msg,type,msg_text){
    obj_msg.hide();
    obj_msg.addClass('alert');
    if(type ===3){
        obj_msg.removeClass('alert-danger').removeClass('alert-info').removeClass('alert-warning');
        obj_msg.addClass('alert-success');
        obj_msg.empty().append("<strong>SUCCESS!</strong><br/>").append(msg_text);
    }
    else if(type === 2){
        obj_msg.removeClass('alert-danger').removeClass('alert-info').removeClass('alert-success');
        obj_msg.addClass('alert-warning');
        obj_msg.empty().append("<strong>WARNING!</strong><br/>").append(msg_text);
    }
    else if(type === 1){
        obj_msg.removeClass('alert-danger').removeClass('alert-success').removeClass('alert-warning');
        obj_msg.addClass('alert-info');
        obj_msg.empty().append("<strong>INFO!</strong><br/>").append(msg_text);
    }
    else{
        obj_msg.removeClass('alert-success').removeClass('alert-info').removeClass('alert-warning');
        obj_msg.addClass('alert-danger');
        obj_msg.empty().append("<strong>Error!</strong><br/>").append(msg_text);
    }
    obj_msg.fadeIn();
}

$(".btn_logout").click(function(e){
    e.preventDefault();
    var url = $(".btn_logout").attr("href");
    bootbox.confirm("Signout from system?", function(result){ 
        if(result) {
            window.location = url;
        }
        else{return ;}
    });
});

//CHANGE PASSWORD - e
function goToMessage(msg_obj){
    $("body, html").animate({ 
        scrollTop: msg_obj.offset().top 
    }, 600);
}
$("a.link_target").click(function(e){
    var link_target     = $(this).data("target");
    var content_wrapper = $("#content_wrapper");
    var tag_js_path     = $("script#js_path");
    var tag_js_init     = $("script#js_initial");
    var data            = csrf_name+"="+$("#csrf").val();
    var this_tag        = $(this);
    $("a.bold").removeClass("bold");
    $(this).addClass("bold");
    //alert("tess");
    var tag_bfr_id = $("a.asolole").attr("id");
    $(".se-pre-con").show();      
    loading.show();
    jQuery.ajax({
        type: "POST", // HTTP method POST or GET
        url: link_target, //Where to make Ajax calls
        dataType:"text", // Data type, HTML, json etc.
        data:data, //Form variables
        success:function(response){
            var obj = null;
            try{
                obj = $.parseJSON(response);  
            }catch(e)
            {}
            //var obj = jQuery.parseJSON(response);

            if(obj)//if json data
            {

                //success msg
                if(obj.status === 1){
                    //alert("");
                    //update csrf token value
                    $("input#csrf").val(obj.csrf_hash);
                    //load string into content
                    content_wrapper.html(obj.str);
                    //change general title
                    $(".general_title > span").html(obj.general_title);
                    
                    //re-insert new script DOM - s
                    $(".js_path").remove();
                    $(".js_initial").remove();
                    
                    var str_script = '<script type="text/javascript" src="'+obj.js_path+'" class="js_path">';
                    str_script+="</script>";
                    $("body").append(str_script);
                    
                    str_script = '<script type="text/javascript" class="js_initial">'+obj.js_initial;
                    str_script+="</script>";
                    $("body").append(str_script);
                    //re-insert new script DOM - e
                    
                    $(".se-pre-con").fadeOut("slow");
                }

                //error msg
                else if(obj.status === 0){
                    sweetAlert("Error", obj.msg, "error");
                    //update csrf token value
                    $("input#csrf").val(obj.csrf_hash);
                    $(".se-pre-con").fadeOut("slow");
//                    $(".subdrop").removeClass("subdrop");
//                    $("#"+tag_bfr_id).addClass("subdrop");
                }
                else if(obj.status === 2){
                    sweetAlert("Error", obj.msg, "warning");
                    //update csrf token value
                    $("input#csrf").val(obj.csrf_hash);
                    //window.location.href = base_url+default_controller; //redirect ke login page
                    window.setTimeout(function(){
                      //  window.location.href = ajax_url; //redirect ke login page
                        //ajax_url
                        window.location.href = base_url+default_controller; //redirect ke login page
                    }, 2000);
                }
                
                loading.hide();
            }
            else
            {
                sweetAlert("Error", response, "error");loading.hide();
            }
        },
        error:function (xhr, ajaxOptions, thrownError){
            loading.hide(); 
            sweetAlert("Error", thrownError, "warning");
            window.setTimeout(function(){
                window.location.href = base_url+"home";
            }, 2000);
            
        }
    });
    
});