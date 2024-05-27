var main = function(){
    controller = "index.php/Upload_data";
    var datatable = function(){
//        $('.inp_dp').datepicker({
//            format: 'dd/mm/yyyy',
//            autoclose:true,
//        });
        var table = $("#data");
        var tbl_detail = $("#tbl_detail_edit");
        var tableSupplier = $("#tblSupplier");
        var tablematerial = $("#tblMaterial");
//        var datatable = table.DataTable({
//            "processing": true,
//            "serverSide": true,
//            "ajax":{
//                url :base_url+controller+"/get_datatable", // json datasource
//                type: "post",  // method  , by default get
//                error: function(){  // error handling
//                        $(".employee-grid-error").html("");
//                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
//                        $("#employee-grid_processing").css("display","none");
//
//                },
//                data: function(d){
//                },
//                "dataSrc": function ( json ) {
//                    //Make your callback here.
////                    $("#csrf").val(json.csrf_hash);
//                    return json.data;
//                }
//                
//            },
//            "columnDefs": [ 
//                { "targets": 3, "orderable": false },
//                { "targets": 3, "class": "text-right"},
//                { "width": "50px", "targets":3 }
//            ],
//            "initComplete": function(settings, json) {
////                console.log(settings);
//            },
//            paging: true,
//            
//       });
//        var datatable_detail = tbl_detail.DataTable({
//            "processing": true,
//            "serverSide": true,
//            "ajax":{
//                url :base_url+controller+"/get_datatable_detail", // json datasource
//                type: "post",  // method  , by default get
//                error: function(){  // error handling
//                        $(".employee-grid-error").html("");
//                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
//                        $("#employee-grid_processing").css("display","none");
//
//                },
//                data: function(d){
//                    d.id     =$("#detailid").val();
//                },
//                "dataSrc": function ( json ) {
//                    return json.data;
//                }
//                
//            },
//            "columnDefs": [ 
//                { "targets": 4, "orderable": false },
//                { "width": "50px", "targets": 4 }
//            ],
//            "initComplete": function(settings, json) {
////                console.log(settings);
//            },
//            paging: true,
//       });
//        
//        var dttblSupplier = tableSupplier.DataTable({
//            "processing": true,
//            "serverSide": true,
//            "ajax":{
//                url :base_url+controller+"/supplier_datatable", // json datasource
//                type: "post",  // method  , by default get
//                error: function(){  // error handling
//                        $(".employee-grid-error").html("");
//                        $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
//                        $("#employee-grid_processing").css("display","none");
//
//                },
//                data: function(d){
//                },
//                "dataSrc": function ( json ) {
//                    //Make your callback here.
////                    $("#csrf").val(json.csrf_hash);
//                    return json.data;
//                }
//                
//            },
//            "columnDefs": [ 
//                { "targets": 3, "orderable": false },
//                { "width": "20px", "targets": 3 }
//            ],
//            "initComplete": function(settings, json) {
//            },
//            paging: true,
//            
//       });
//        
//        
        var forml = $("#form_add");
        var msg_obj=$("#msg_add");
        msg_obj.hide();
        forml.validate({
            
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            rules:{
                ponumber:{required:true},
                volume:{required:true,number:true},
                unit:{required:true},
                material:{required:true},
                supplier:{required:true},
                podate:{required:true},
                attch:{extension: "pdf|doc|docx",filesize: 3000000}, //3 mb
            }
            ,
            errorPlacement: function(error, element) {
                if (element.attr("name") === "podate" || element.attr("name") === "material" || element.attr("name") === "supplier" ) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.form-group').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.has-error').removeClass('has-error'); // set error class to the control group
            },
            submitHandler: function(form) {
                var url = controller+"/add_act";
                
                var data1 = new FormData(forml[0]);
                data1.append(csrf_name, $("#csrf").val());
                var data = data1;
                
                loading.show();
                msg_obj.hide();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    data:data, //Form variables
                    mimeType: "multipart/form-data",
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(response){
                        var obj = null;
                        try{
                            obj = $.parseJSON(response);  
                        }catch(e)
                        {}
                        //var obj = jQuery.parseJSON(response);

                        if(obj)//if json data
                        {
                            loading.hide();$("#csrf").val(obj.csrf_hash);
                            //success msg
                            if(obj.status === 1){
                                $("#detailid").val(obj.id);
                                datatable_detail.ajax.reload(function(){
                                    $("#form_add :input").prop("disabled", true);
                                    $(".formedit_wrapper").hide();
                                    $(".detail_wrapper").show();
                                    sweetAlert("Success", obj.msg, "success");loading.hide();
                                });
                            }

                            //error msg
                            else if(obj.status === 0){
                                sweetAlert("Error", obj.msg, "error");
                            }
                            datatable.ajax.reload();
                        }
                        else
                        {
                            sweetAlert("Error", response, "error");
                            loading.hide();
                        }
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                        loading.hide(); 
                        sweetAlert("FATAL ERROR", thrownError, "error");
                    }
                });
                return false;
            }
        });
        $.validator.addMethod('filesize', function (value, element, param) {
            return this.optional(element) || (element.files[0].size <= param);
        }, 'Ukuran berkas maksimal 3 Mb');
      
    };
    var detail = function(){
        controller = "index.php/r_department";
        var form_edit = $("#form");
        form_edit.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            rules:{
                thn:{required:true},
                code:{required:true},
                name:{required:true},
            }
            ,
            errorPlacement: function(error, element) {
                if (element.attr("name") === "tgl_isi" || element.attr("name") === "tgl_lahir" ) {
                    error.insertAfter(element.parent());
                } 
                else if (element.attr("name") === "st_hamil" || element.attr("name") === "st_cerai" ) {
                    error.insertAfter(element.parent().children("br.error_sini"));
                } 
                else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.form-group').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.has-error').removeClass('has-error'); // set error class to the control group
            },
            submitHandler: function(form) {
                var data = form_edit.serialize();
                data+="&"+csrf_name+"="+$("#csrf").val();
                var url = controller+"/detail_act";
                loading = $(".spinner");
                loading.fadeIn();
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
                            var msg_obj=$("#msg_box_edit");
                            //success msg
                            if(obj.status === 1){
                                sweetAlert("Success", obj.msg, "success");
                                window.setTimeout(function(){
                                    msg_obj.fadeOut();
                                }, 2000);
                                $('#modal_edit').modal('hide');
                            }

                            //error msg
                            else if(obj.status === 0){
                                sweetAlert("Oops...", obj.msg, "error");
                            }
                            $("#csrf").val(obj.csrf_hash);
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

    };
    return{
        init:function(){datatable();},
        detail:function(){detail();}
    };
}();