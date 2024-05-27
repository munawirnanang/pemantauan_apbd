    var main = function(){
        controller = "index.php/Api_List_indikator";
        var datatable = function(){
            //Disable disBtn
            $('.btnBack').prop('disabled', false);

            var table = $("#tblPro");
            var tableInd = $("#tblInd");
            
            var datatable = table.DataTable({
                 "processing": true,
                 "serverSide": true,
                 "ajax":{
                     url :base_url+controller+"/pro_datatable", // json datasource
                     type: "post",  // method  , by default get
                     error: function(){  // error handling
                             $(".employee-grid-error").html("");
                             $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                             $("#employee-grid_processing").css("display","none");
                     },
                     data: function(d){
                     },
                     "dataSrc": function ( json ) {
                         //$("#ttl_qty").val(json.ttl_qty);
                         return json.data;
                     }

                 },
                 "columnDefs": [                  
                     { "targets": 3, "orderable": false },
                     { "width": "8px", "targets": 3}
                 ],
                 "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
                 "initComplete": function(settings, json) {
     //                console.log(settings);
                 },
                 paging: true,

            });
            
            var datatable = tableInd.DataTable({
                "processing": true,
                 "serverSide": true,
                 "ajax":{
                     url :base_url+controller+"/kab_datatable", // json datasource
                     type: "post",  // method  , by default get
                     error: function(){  // error handling
                             $(".employee-grid-error").html("");
                             $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                             $("#employee-grid_processing").css("display","none");
                     },
                     data: function(d){
                     },
                     "dataSrc": function ( json ) {
                         //$("#ttl_qty").val(json.ttl_qty);
                         return json.data;
                     }

                 },
                 "columnDefs": [                  
                     { "targets": 2, "orderable": false },
                     { "width": "8px", "targets": 2}
                 ],
                 "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
                 "initComplete": function(settings, json) {
     //                console.log(settings);
                 },
                 paging: true,
            });
            
            function g_listdata(){
             loading.show();
             ajax_url = controller+"/g_list_data";
             ajax_data="id="+$("#inp_proid").val()+"&indk="+$("#inp_idind").val();
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
                             $("#t_bahan > tbody").html(obj.str);
//                             $("._wrapper_wlyh").hide();
//                             $("._wrapper_info").show();
//                             $("._wrapper_bahan").show();
//                             $(".list_kabko").fadeIn();
                             loading.hide();
                         }
                         else if(obj.status === 0){
                             loading.hide();
                             sweetAlert("Error", obj.msg, "error");
                         }
                         else if(obj.status === 2){
                             sweetAlert("Caution", obj.msg, "warning");
//                             window.setTimeout(function(){
//                                 window.location.href = base_url+"welcome";
//                             }, 2000);
                         }

                     }
                     else{
                         sweetAlert("Caution", response, "error");
                         loading.hide();
//                         window.setTimeout(function(){
//                             window.location.href = base_url+"welcome";
//                         }, 2000);
//                         return false;
                     }
                 },
                 error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                     return false;
                 }
             });
         }g_listdata();
            
            
            
            
            $(".plhpro").click(function(e){  $('#mdlPro').modal('show'); });
            $("#mdlPro").on('click', '#save_popup', function(e){
                var pro_id="";
                var idpro = $('.checkbox:checked').data("id");
                var idwil = $('.checkbox:checked').data("wil");
                //alert(idpro);
                $("#inp_proid").val(idpro);
                $("#inp_pro").val(idwil);
                $("#inp_wl").val(idpro);
               $('.listdata').hide();
            });
            
            $(".plhkab").click(function(e){  $('#mdlind').modal('show'); });
            $("#mdlind").on('click', '#save_popup', function(e){
                var pro_id="";
                var idind = $('.checkbox:checked').data("id");
                var ind = $('.checkbox:checked').data("in");
                $("#inp_idind").val(idind);
                $("#inp_kab").val(ind);
                $("#inp_in").val(idind);
               $('.listdata').hide();
            });
            
        $(".btnShwHd").click(function(){
            var _self = $(this);
           g_listdata();
        });
//            
//            
//            
//            var forml = $("#form_cari");
//            var msg_obj=$("#msg_add");
//            msg_obj.hide();
//            forml.validate({
//               errorElement: 'span', //default input error message container
//                 errorClass: 'help-block help-block-error', // default input error message class
//                 rules:{
//                   inp_pro:{required:true},
//                     inp_proid:{required:true},
//                     inp_idind:{required:true},
//                     inp_kab:{required:true},  
//                 },
//                 errorPlacement: function(error, element) {
//                     if (element.attr("name") === "tgl_isi" || element.attr("name") === "tgl_lahir" ) {
//                         error.insertAfter(element.parent());
//                     } else {
//                         error.insertAfter(element);
//                     }
//                 },
//                 highlight: function (element) { // hightlight error inputs
//                     $(element)
//                     .closest('.form-group').addClass('has-error'); // set error class to the control group
//                 },
//                 unhighlight: function (element) { // hightlight error inputs
//                     $(element)
//                     .closest('.has-error').removeClass('has-error'); // set error class to the control group
//                 },
//                 submitHandler: function(form) {
//                     var url = controller+"/add_act";
//                     var data = forml.serialize();
//                     data+="&"+csrf_name+"="+$("#csrf").val();                
//                     loading.show();
//                     msg_obj.hide();
//                     jQuery.ajax({
//                         type: "POST", // HTTP method POST or GET
//                         url: base_url+url, //Where to make Ajax calls
//                         dataType:"text", // Data type, HTML, json etc.
//                         data:data, //Form variables
//                         success:function(response){
//                             var obj = null;
//                             try{
//                                 obj = $.parseJSON(response);  
//                             }catch(e)
//                             {}
//                             //var obj = jQuery.parseJSON(response);
//
//                             if(obj)//if json data
//                             {
//                                 loading.hide();$("#csrf").val(obj.csrf_hash);
//                                 //success msg
//                                 if(obj.status === 1){
//                                     $('.table_data').html(obj.content);
//                                     sweetAlert("Success", obj.msg, "success");
//                                 $('.listdata').show();
//                                 //    forml[0].reset();
//                                 }
//
//                                 //error msg
//                                 else if(obj.status === 0){
//                                     sweetAlert("Error", obj.msg, "error");
//                                 }
//                                 datatable.ajax.reload();
//                             }
//                             else
//                             {
//                                 sweetAlert("Error", response, "error");
//                                 loading.hide();
//                             }
//                         },
//                         error:function (xhr, ajaxOptions, thrownError){
//                             loading.hide(); 
//                             sweetAlert("FATAL ERROR", thrownError, "error");
//                         }
//                     });
//                     return false;
//                 }
//            });
//
//            
//            $(".btnBack").click(function(e){
//            e.preventDefault();
//            e.iddaerah     = $(this).data("id");
//            window.open(base_url+controller+"/Download_excel1?wl="+$("#inp_wl").val()+"&in="+$("#inp_in").val());
//
//            });
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();