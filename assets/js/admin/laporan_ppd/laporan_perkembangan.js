    var main = function(){
        controller = "index.php/Laporan_perkembangan";
        
        var datatable = function(){     
		window.history.pushState(null, "", window.location.href);        
            window.onpopstate = function() {      
                window.history.pushState(null, "", window.location.href);
                //location.reload();

            };
            $('.nosatu').prop('disabled', true);
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

            var table_k = $("#tblKab");
            var datatable_kab = table_k.DataTable({
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
                         d.id  =$("#inp_proid").val();
                     },
                     "dataSrc": function ( json ) {
                         //Make your callback here.
     //                    $("#csrf").val(json.csrf_hash);
                         return json.data;
                     }

                 },
                 "columnDefs": [ 
//                     { "targets": 3, "orderable": false },
//                     { "width": "8px", "targets": 3}
                 ],
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
                $("#inp_kab").val("");
            });
            
            $(".plhkab").click(function(e){
                    e.preventDefault();
                     var id      = $(this).data("id");
                     $("#idmodal").val(id);
                     loading.show();
                     datatable_kab.ajax.reload(function(){
                         loading.hide();
                         $('#modal_kab').modal('show');
                     });
             });

            $("#modal_kab").on('click', '#save_popup', function(e){
//                var kab_list = "";
//                console.log(kab_list=$('.checkboxx:checked').map(function(){
//                  return this.value;  
//                }).get().join('|'));
//                $("#inp_kab").val(kab_list);
                var idkp = $('.radio:checked').data("id");
                var nmkp = $('.radio:checked').data("kp");
                var idpr = $('.radio:checked').data("idp");
                var nmpr = $('.radio:checked').data("pr");
                $("#inp_kid").val(idkp);
                $("#inp_kab").val(nmkp);
                $("#inp_proid").val(idpr);
                $("#inp_pro").val(nmpr);
            });
            
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
                window.open(base_url+"Laporan_pdf/download_act?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#inp_kid").val());
                    loading.hide();
            }
        });

       $(".nosatu").click(function(e){  

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
            }else{

                loading.show();
                var valueSelectedRegion = $('#inp_proid').val();
                var stringValueSelectedRegion = valueSelectedRegion.toString();
                var dataIdProvince = stringValueSelectedRegion.substr(0, 4);
                var dataRegion = stringValueSelectedRegion.substr(5);

                console.log(stringValueSelectedRegion);

                if (dataIdProvince == 'null') {
                    window.open(base_url+"Laporan_word_e/download_word?inp_pro="+dataRegion+"&inp_sp=");
                }else{
                    window.open(base_url+"Laporan_word_e/download_word?inp_pro="+dataIdProvince+"&inp_sp="+dataRegion);
                }
                // window.open(base_url+"Laporan_word_e/download_word?inp_pro="+$("#inp_proid").val()+"&inp_sp="+$("#inp_kid").val());
                // window.open(base_url+"Laporan_word_e/download_word?inp_pro="+$("#inp_proid").val());
                loading.hide();
            }
           
        });
       
   };
   
   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();

    $(document).ready(function() {
        // $('.select2').select2();
        // $('.pilihPro').selectpicker();
        $('.selectpicker').selectpicker({
            style: 'btn btn-custom-selectpicker btn-round',
        });
        
    });

    $(document).ready(function() {
        $.post(base_url+controller+"/daerah_list", function(result) {
            var data = JSON.parse(result);
            // console.log(data.data[0]);
            var html = '';
            for (let i = 0; i < data.data.length; i++) {
                html += '<option value="'+data.data[i].id_provinsi+'-'+data.data[i].id+'">'+data.data[i].nama+'</option>';
            }
            $('#inp_proid').append(html);
            $('#inp_proid').selectpicker('refresh');
                    
            $('.inp_proid ~ option').hide();
          });
    });

    $(document).ready(function() {
        $.post(base_url+controller+"/years_list", function(result) {
            var data = JSON.parse(result);
            // console.log(data.data[0]['tahun']);
            var html = '';
            for (let i = 0; i < data.data.length; i++) {
                html += '<option value="'+data.data[i]['tahun']+'">'+data.data[i]['tahun']+'</option>';
            }
            $('#selectyear').append(html);
            $('#selectyear').selectpicker('refresh');
                    
            $('.selectpicker ~ option').hide();
          });
    });