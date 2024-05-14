    var main = function(){
        controller = "index.php/Laporan_2";
        
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

            
            $(".plhpro").click(function(e){ $('#mdlPro').modal('show'); });
            
            $("#mdlPro").on('click', '#save_popup', function(e){
                var idgi = $('.radio:checked').data("id");
                var idsa = $('.radio:checked').data("gi");
                $("#inp_proid").val(idgi);
                $("#inp_pro").val(idsa);
                $("#inp_kid").val("");
              //  $("#inp_kab").val("");
            });
        
            
            
        var forml = $("#form_add");
         forml.validate({
            errorElement: 'span', //default input error message container
           errorClass: 'help-block help-block-error', // default input error message class
            rules:{
                inp_pro:{required:false},
                rpjmn:{required:false},
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
                alert();
                loading.show();
                window.open(base_url+"Laporan_2/download_act?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#inp_kid").val());
                    loading.hide();
            }
        });
        
            
            $(".nodua").click(function(e){
                if($("#inp_proid").val() == '' ){
                toastr["warning"]('Silakan di pilih provinsi atau Kab/Kota yang akan di unduh ');
                toastr.options = {
                  "closeButton": false,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": false,
                  "positionClass": "toast-bottom-right",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                };
            }
            else{
            loading.show();
                //window.open(base_url+"Laporan_makro/download_word?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#rpjmn").val());
                window.open(base_url+"Laporan_word_e/download_kinerja?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#rpjmn").val());
                    loading.hide();
        }
           
            });
       
   };
   
   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();