    var main = function(){
        controller = "index.php/Laporan_3";
        
        var datatable = function(){            
            var table = $("#tblPro");
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
                     { "targets": 2, "orderable": false },
                     { "width": "8px", "targets": 2}
                 ],
                 "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
                 "initComplete": function(settings, json) {
     //                console.log(settings);
                 },
                 paging: true,

            });

//            var table_k = $("#tblKab");
//            var datatable_kab = table_k.DataTable({
//                 "processing": true,
//                 "serverSide": true,
//                 "ajax":{
//                     url :base_url+controller+"/kab_datatable", // json datasource
//                     type: "post",  // method  , by default get
//                     error: function(){  // error handling
//                             $(".employee-grid-error").html("");
//                             $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
//                             $("#employee-grid_processing").css("display","none");
//
//                     },
//                     data: function(d){
//                         d.id  =$("#inp_proid").val();
//                     },
//                     "dataSrc": function ( json ) {
//                         //Make your callback here.
//     //                    $("#csrf").val(json.csrf_hash);
//                         return json.data;
//                     }
//
//                 },
//                 "columnDefs": [ 
////                     { "targets": 3, "orderable": false },
////                     { "width": "8px", "targets": 3}
//                 ],
//                 "initComplete": function(settings, json) {
//     //                console.log(settings);
//                 },
//                 paging: true,
//
//            });
            
            $(".plhpro").click(function(e){ $('#mdlPro').modal('show'); });
            
            $("#mdlPro").on('click', '#save_popup', function(e){
                var idgi = $('.radio:checked').data("id");
                var idsa = $('.radio:checked').data("gi");
                //alert(idgi);
                $("#inp_proid").val(idgi);
                $("#inp_pro").val(idsa);
                $("#inp_kid").val("");
              //  $("#inp_kab").val("");
            });
        
            
//            $(".plhkab").click(function(e){
//                    e.preventDefault();
//                     var id      = $(this).data("id");
//                     $("#idmodal").val(id);
//                     loading.show();
//                     datatable_kab.ajax.reload(function(){
//                         loading.hide();
//                         $('#modal_kab').modal('show');
//                     });
//             });
//        
//            $("#modal_kab").on('click', '#save_popup', function(e){
//                var idkp = $('.radio:checked').data("id");
//                var nmkp = $('.radio:checked').data("kp");
//                var idpr = $('.radio:checked').data("idp");
//                var nmpr = $('.radio:checked').data("pr");
//                $("#inp_kid").val(idkp);
//                $("#inp_kab").val(nmkp);
//                $("#inp_proid").val(idpr);
//                $("#inp_pro").val(nmpr);
//            });
            
        var forml = $("#form_add");
         forml.validate({
            errorElement: 'span', //default input error message container
           errorClass: 'help-block help-block-error', // default input error message class
            rules:{
                inp_pro:{required:false},
                inp_sp:{required:false},
                //tahun:{required:false},
            },
            highlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.form-group').addClass('has-error'); // set error class to the control group
            },
            unhighlight: function (element) { // hightlight error inputs
                $(element)
                .closest('.has-error').removeClass('has-error'); // set error class to the control group
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") === "grdate" ) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                //alert("");
                loading.show();
                window.open(base_url+"Laporan_3/download_act?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#inp_kid").val());
                    loading.hide();
            }
        });

       
   };
   
   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();