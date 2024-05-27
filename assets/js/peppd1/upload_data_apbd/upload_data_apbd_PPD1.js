    var main = function(){
        controller = "index.php/Upload_data_apbd";
        var datatable = function(){
            
            var form2 = $("#form_cari");
            var msg_obj=$("#msg_add");
            msg_obj.hide();
            form2.validate({
               errorElement: 'span', //default input error message container
                 errorClass: 'help-block help-block-error', // default input error message class
                 rules:{
//                   inp_pro:{required:true},
//                     inp_proid:{required:true},
//                     inp_idind:{required:true},
//                     inp_kab:{required:true}, 
                     attch:{required:false}, 
                     attch:{extension: "xlsx|csv",filesize: 30000000}, //3 mb
                 },
                 errorPlacement: function(error, element) {
                     if (element.attr("name") === "inp_proid" || element.attr("name") === "inp_kab" ) {
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
//                     var data = forml.serialize();
//                     data+="&"+csrf_name+"="+$("#csrf").val();

                var data1 = new FormData(form2[0]);
                data1.append(csrf_name, $("#csrf").val());
                var data = data1;
                
                     loading.show();
                     msg_obj.hide();
                     jQuery.ajax({
//                         type: "POST", // HTTP method POST or GET
//                         url: base_url+url, //Where to make Ajax calls
//                         dataType:"text", // Data type, HTML, json etc.
//                         data:data, //Form variables
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
                                     $('.table_data').html(obj.content);
                                     sweetAlert("Success", obj.msg, "success");
                                 //$('.listdata').show();
                                 //    forml[0].reset();
                                 }

                                 //error msg
                                 else if(obj.status === 0){
                                     sweetAlert("Error", obj.msg, "error");
                                 }
                            //     datatable.ajax.reload();
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
        

            
            $(".btnBack").click(function(e){
            e.preventDefault();
            e.iddaerah     = $(this).data("id");
//            var id      = $(this).data("id");
//            var title   = $(this).data("title");
//            var msg_obj = $("#msg");
//                       window.open(base_url+controller+"/Download_excel1?id="+e.iddaerah);
                       // ajax_url = controller+"/Download_excel";
            window.open(base_url+controller+"/Download_excel1?wl="+$("#inp_wl").val()+"&in="+$("#inp_in").val());

            });
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();

    $(document).ready(function(){
    //     Dropzone.options.myAwesomeDropzone = {
    //         // Configuration options go here
    //         url: "my-upload-url",
    //     };

        $("#myAwesomeDropzone").dropzone({
            url: "my-upload-url",
        });

    //     myAwesomeDropzone.on("complete", function(file) {
    //         myAwesomeDropzone.removeFile(file);
    //       });
    });

    // Dropzone.options.myAwesomeDropzone = {
    //     init: function () {
    //         this.on("complete", function (file) {
    //             myAwesomeDropzone.removeFile(file)
    //         });
    //     }
    // };