    var main = function(){
        controller = "Api_domain_ind";
        var datatable = function(){
            //Disable disBtn
        $("#select_ind").change(function(e){
                e.preventDefault();
                var _self = $(this);
                var id          = _self.val();
                $("#inp_ind").val(id);
                s_turvar_label();
            });
        function s_turvar_label(){
                loading.show();
                ajax_url = controller+"/s_turvar_label";
                ajax_data="id="+$("#inp_ind").val();
                ajax_data+="&"+csrf_name+"="+$("#csrf").val();
                jQuery.ajax({
                    type: "POST",
                    url: base_url+ajax_url,
                    dataType:"text",
                    data:ajax_data,
                    success:function(response){
                        var obj = null;
                        try
                        {
                            obj = $.parseJSON(response);  
                        }catch(e)
                        {}
                        if(obj)
                        {
                            $("#csrf").val(obj.csrf_hash);
                            if(obj.status === 1){
                                $('form#commentForm select[name="select_tl"]').html(obj.str);
                                $('form#commentForm select[name="select_turth"]').html(obj.str_t);
                                loading.hide();
                            }
                            else if(obj.status === 0){
                                loading.hide();
                                sweetAlert("Error", obj.msg, "error");
                            }
                            else if(obj.status === 2){
                                sweetAlert("Caution", obj.msg, "warning");
                                window.setTimeout(function(){
                                    window.location.href = base_url+"welcome";
                                }, 2000);
                            }

                        }
                        else{
                            sweetAlert("Caution", response, "error");
                            loading.hide();
                            window.setTimeout(function(){
                                window.location.href = base_url+"welcome";
                            }, 2000);
                            return false;
                        }
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        loading.hide(); 
                        alert(thrownError);
                        return false;
                    }
                });
            }
        
        $(".btnDown").click(function(e){
            e.preventDefault();
            loading.show();
            ajax_url = controller+"/a_nilai";
                ajax_data="id="+$("#inp_ind").val()+"&select_tl="+$("#select_tl").val()+"&select_vervar="+$("#select_vervar").val()+"&tahun="+$("#tahun").val()+"&select_turth="+$("#select_turth").val();
                ajax_data+="&"+csrf_name+"="+$("#csrf").val();
            jQuery.ajax({
                    type: "POST",
                    url: base_url+ajax_url,
                    dataType:"text",
                    data:ajax_data,
                    success:function(response){
                        var obj = null;
                        try
                        {
                            obj = $.parseJSON(response);  
                        }catch(e)
                        {}
                        if(obj)
                        {
                            $("#csrf").val(obj.csrf_hash);
                            if(obj.status === 1){
                                loading.hide();
                                sweetAlert("Success", obj.msg, "Success");
                            }
                            else if(obj.status === 0){
                                loading.hide();
                                sweetAlert("Error", obj.msg, "error");
                            }
                            else if(obj.status === 2){
                                sweetAlert("Caution", obj.msg, "warning");
                                window.setTimeout(function(){
                                    window.location.href = base_url+"welcome";
                                }, 2000);
                            }

                        }
                        else{
                            sweetAlert("Caution", response, "error");
                            loading.hide();
                            window.setTimeout(function(){
                                window.location.href = base_url+"welcome";
                            }, 2000);
                            return false;
                        }
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        loading.hide(); 
                        alert(thrownError);
                        return false;
                    }
                });

        });
        
        
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();