var login = function(){
    var login_init = function(){
        controller = "Welcome";
        msg_obj = $("#msg");
        var forml = $("#frm_login");
        forml.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error text-left', // default input error message class
            rules:{
                userid:{required:true},
                pass:{required:true},
                captcha:{required:true},
            }
            ,
            highlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.input_wrapper').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.has-error').removeClass('has-error'); // set error class to the control group
            },
            submitHandler: function(form) {
                // some other code
                // maybe disabling submit button
                // then:
                var url = controller+"/login_act";
                var data = forml.serialize();
                loading.show();
                msg_obj.hide();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
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
                                sweetAlert("Well Done", obj.msg, "success");
                                window.location.href = base_url+"home";
                            }

                            //error msg
                            else if(obj.status === 0){
                                sweetAlert("Caution", obj.msg, "error");;
                                $("#csrf").val(obj.csrf_hash);
                                $("#captcha_wrapper").html(obj.captcha_img);
                                $("#captcha").val('');
                            }
                            loading.hide();
                        }
                        else
                        {
                            show_alert_ms(msg_obj,99,response);loading.hide();
                        }
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        loading.hide(); 
                        alert(thrownError);
                    }
                });
                return false;
            }
        });
        
        $("#captcha_wrapper").click(function(e){
            e.preventDefault();
            var url = controller+"/refresh_captcha";
                var data = forml.serialize();
                loading.show();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
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
                                $("#csrf").val(obj.csrf_hash);
                                $("#captcha_wrapper").html(obj.captcha_img);
                                $("#captcha").val('');
                            }

                            //error msg
                            else if(obj.status === 0){
                                sweetAlert("Caution", obj.msg, "error");;
                                $("#csrf").val(obj.csrf_hash);
                                $("#captcha_wrapper").html(obj.captcha_img);
                                $("#captcha").val('');
                            }
                            loading.hide();
                        }
                        else
                        {
                            show_alert_ms(msg_obj,99,response);loading.hide();
                        }
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        loading.hide(); 
                        alert(thrownError);
                    }
                });
        });
    };
    return{
        init:function(){login_init();}
    };
}();