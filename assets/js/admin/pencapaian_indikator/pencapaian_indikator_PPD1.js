var main = function(){
    controller = "index.php/Pencapaian_indikator";
    
    var datatable = function(){
        window.history.pushState(null, "", window.location.href);        
        window.onpopstate = function() {      
            window.history.pushState(null, "", window.location.href);
            //location.reload();

        };
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
                 { "targets": 3, "orderable": false },
                 { "width": "8px", "targets": 3}
             ],
             "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
             "initComplete": function(settings, json) {
                // console.log(settings);
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
                    //  Make your callback here.
                    // $("#csrf").val(json.csrf_hash);
                     return json.data;
                 }

             },
             "columnDefs": [ 
                    // { "targets": 3, "orderable": false },
                    // { "width": "8px", "targets": 3}
             ],
             "initComplete": function(settings, json) {
                // console.log(settings);
             },
             paging: true,

        });
        var table_kt = $("#tblKot");
        var datatable_kot = table_kt.DataTable({
             "processing": true,
             "serverSide": true,
             "ajax":{
                 url :base_url+controller+"/kot_datatable", // json datasource
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
                    //  Make your callback here.
                    // $("#csrf").val(json.csrf_hash);
                     return json.data;
                 }

             },
             "columnDefs": [ 
                    // { "targets": 3, "orderable": false },
                    // { "width": "8px", "targets": 3}
             ],
             "initComplete": function(settings, json) {
                // console.log(settings);
             },
             paging: true,

        });
        
        $(".plhpro").click(function(e){ $('#mdlPro').modal('show'); });

        $('#pilihPro').ready(function(){
            $.post(base_url+controller+"/pro_list", function(result) {
                var data = JSON.parse(result);
                var html = '';
                for (let i = 0; i < data.data.length; i++) {
                    html += '<option data-id="'+data.data[i][0]+'" value="'+data.data[i][1]+'">'+data.data[i][1]+'</option>';
                }
                $('#pilihPro').append(html);
                $('#pilihPro').selectpicker('refresh');
                
                $('.selectpicker ~ option').hide();
              });
        });

        function pilih_kab() {
            $('#pilihKab').empty();
            var valpro = $("#inp_proid").val();
            $.post(base_url+controller+"/kab_list", {prov_id: valpro}, function(result){
                var data = JSON.parse(result);
                var html = '';
                html = '<option>Pilih Kabupaten</option>';
                for (let i = 0; i < data.data.length; i++) {
                    html += '<option data-id="'+data.data[i][0]+'" value="'+data.data[i][1]+'">'+data.data[i][1]+'</option>';
                }
                $('#pilihKab').append(html);
                $('#pilihKab').selectpicker('refresh');
                
                $('.selectpicker ~ option').hide();
              });
        }

        function pilih_kota() {
            $('#pilihKota').empty();
            var valpro = $("#inp_proid").val();
            $.post(base_url+controller+"/kota_list", {prov_id: valpro}, function(result){
                var data = JSON.parse(result);
                var html = '';
                html = '<option>Pilih Kota</option>';
                for (let i = 0; i < data.data.length; i++) {
                    html += '<option data-id="'+data.data[i][0]+'" value="'+data.data[i][1]+'">'+data.data[i][1]+'</option>';
                }
                $('#pilihKota').append(html);
                $('#pilihKota').selectpicker('refresh');
                
                $('.selectpicker ~ option').hide();
              });
        }

        $('#pilihPro').change(function(){
            var valpro = $(this).val();
            if (valpro == 'Pilih Provinsi') {
                $("#pilihKab").attr('disabled',true) 
                $("#pilihKota").attr('disabled',true)   
            } else {
                $("#pilihKab").attr('disabled',false)  
                $("#pilihKota").attr('disabled',false)
            }
            var idpro = $(this).find(':selected').data('id');
            $("#inp_proid").val(idpro);

            loading.show();
            $("#inp_pro").val(valpro);
            $("#inp_kab").val("");
            $("#inp_kota").val("")
            loading.hide();
        });

        $('#pilihKab').change(function(){
            var valkab = $(this).val();
            // alert("value : "+valkab);
            // var kab_list = $('.radio:checked').data("id");
            // var pro_list = $('.radio:checked').data("pv");
            loading.show();
            $("#inp_kab").val(valkab);
            loading.hide();
        });

        $('#pilihKota').change(function(){
            var valkota = $(this).val();
            loading.show();
            $("#inp_kota").val(valkota);
            loading.hide();
        });

        // let valueSelectedIndicator = [];
        
        function list_display_indikator(){
            let valueSelectedIndicator = $('#selectindicator').val();
            let valueSelectedYear = $('#selectyear').val();
            let valueSelectedRegion = $('#selectregion').val();
            if ((valueSelectedRegion == '') || (valueSelectedIndicator == '') || (valueSelectedYear == '')) {
                $(".indikator-makro-initial").show();
                $(".indikator-makro-initial-graph").show();
                $(".tabel-indikator").hide();
                $(".button-export-tabel-indikator-makro").hide();
                $(".satu").hide();
                $(".dua").hide();
                $(".tiga").hide();
                $(".empat").hide();
                $(".lima").hide();
                $(".enam").hide();
                $(".tujuh").hide();
                $(".delapan").hide();
                $(".sembilan").hide();
                $(".sepuluh").hide();
                $(".sebelas").hide();
                $(".duabelas").hide();
                $(".tigabelas").hide();
                $(".p2").hide();
                $(".empatbelas").hide();
                loading.hide();
            }else{
                tabel_indikator_makro();
                $(".satu").hide();
                $(".dua").hide();
                $(".tiga").hide();
                $(".empat").hide();
                $(".lima").hide();
                $(".enam").hide();
                $(".tujuh").hide();
                $(".delapan").hide();
                $(".sembilan").hide();
                $(".sepuluh").hide();
                $(".sebelas").hide();
                $(".duabelas").hide();
                $(".tigabelas").hide();
                $(".p2").hide();
                $(".empatbelas").hide();
                valueSelectedIndicator.forEach(element => {
                    $(".indikator-makro-initial-graph").hide();
                    if (element == 1 ) { satu(); $(".satu").show(); }
                    if (element == 2 ) { dua(); $(".dua").show(); }
                    if (element == 3 ) { tiga(); $(".tiga").show(); }
                    if (element == 4 ) { empat(); $(".empat").show(); }
                    if (element == 5 ) { lima(); $(".lima").show(); }
                    if (element == 6 ) { enam(); $(".enam").show(); }
                    if (element == 7 ) { tujuh(); $(".tujuh").show(); }
                    if (element == 8 ) { delapan(); $(".delapan").show(); }
                    if (element == 9 ) { sembilan(); $(".sembilan").show(); }
                    if (element == 10 ) { sepuluh(); $(".sepuluh").show(); }
                    if (element == 11 ) { sebelas(); $(".sebelas").show(); }
                    if (element == 36 ) { duabelas(); $(".duabelas").show(); }
                    if (element == 38 ) { tigabelas(); $(".tigabelas").show(); }
                    if (element == 39 ) { p2(); $(".p2").show(); }
                    if (element == 40 ) { empatbelas(); $(".empatbelas").show(); }
                });
            }
        }

        // $('#selectindicator').change(function(){
        //     valueSelectedIndicator = $(this).val();
        //     let valueSelectedYear = $('#selectyear').val();
        //     console.log(valueSelectedYear);
        //     if ((valueSelectedIndicator == '') || (valueSelectedYear == '')) {
        //         $(".indikator-makro-initial").show();
        //         $(".indikator-makro-initial-graph").show();
        //         $(".tabel-indikator").hide();
        //         $(".satu").hide();
        //         $(".dua").hide();
        //         $(".tiga").hide();
        //         $(".empat").hide();
        //         $(".lima").hide();
        //         $(".enam").hide();
        //         $(".tujuh").hide();
        //         $(".delapan").hide();
        //         $(".sembilan").hide();
        //         $(".sepuluh").hide();
        //         $(".sebelas").hide();
        //         $(".duabelas").hide();
        //         $(".tigabelas").hide();
        //         $(".p2").hide();
        //         $(".empatbelas").hide();
        //         loading.hide();
        //     }else{ 
        //         tabel_indikator_makro();
        //         $(".satu").hide();
        //         $(".dua").hide();
        //         $(".tiga").hide();
        //         $(".empat").hide();
        //         $(".lima").hide();
        //         $(".enam").hide();
        //         $(".tujuh").hide();
        //         $(".delapan").hide();
        //         $(".sembilan").hide();
        //         $(".sepuluh").hide();
        //         $(".sebelas").hide();
        //         $(".duabelas").hide();
        //         $(".tigabelas").hide();
        //         $(".p2").hide();
        //         $(".empatbelas").hide();
        //         valueSelectedIndicator.forEach(element => {
        //             $(".indikator-makro-initial-graph").hide();
        //             if (element == 1 ) { satu(); $(".satu").show(); }
        //             if (element == 2 ) { dua(); $(".dua").show(); }
        //             if (element == 3 ) { tiga(); $(".tiga").show(); }
        //             if (element == 4 ) { empat(); $(".empat").show(); }
        //             if (element == 5 ) { lima(); $(".lima").show(); }
        //             if (element == 6 ) { enam(); $(".enam").show(); }
        //             if (element == 7 ) { tujuh(); $(".tujuh").show(); }
        //             if (element == 8 ) { delapan(); $(".delapan").show(); }
        //             if (element == 9 ) { sembilan(); $(".sembilan").show(); }
        //             if (element == 10 ) { sepuluh(); $(".sepuluh").show(); }
        //             if (element == 11 ) { sebelas(); $(".sebelas").show(); }
        //             if (element == 36 ) { duabelas(); $(".duabelas").show(); }
        //             if (element == 38 ) { tigabelas(); $(".tigabelas").show(); }
        //             if (element == 39 ) { p2(); $(".p2").show(); }
        //             if (element == 40 ) { empatbelas(); $(".empatbelas").show(); }
        //         });
        //     }
        // });

        $('#selectregion').change(function(){
            loading.show();
            list_display_indikator();
        })
    
        $('#selectindicator').change(function(){
            loading.show();
            list_display_indikator();
        })
    
        $('#selectyear').change(function(){
            loading.show();
            list_display_indikator();
        })

        
        $("#mdlPro").on('click', '#save_popup', function(e){
            var pro_id="";
            var idpro = $('.checkbox:checked').data("id");  
            $("#inp_proid").val(idpro);
            loading.show();
            var   pro_list = "";
            console.log(pro_list=$('.checkbox:checked').map(function() {
                return this.value;
            }).get().join('|'));
            $("#inp_pro").val(pro_list);
            $("#inp_kab").val("");
            $("#inp_kota").val("");
            loading.hide();
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
            var kab_list = $('.radio:checked').data("id");
            var pro_list = $('.radio:checked').data("pv");
            loading.show();
            // $("#inp_pro").val("");
            $("#inp_kab").val(kab_list);
            // $("#inp_pro").val(pro_list);
            satu();dua();tiga();empat();lima();enam();tujuh();delapan();sembilan();sepuluh();seblas();duabelas();tigabelas();empatbelas();p2();
            loading.hide();
        });
        
        $(".plhkota").click(function(e){
           e.preventDefault();
            var id      = $(this).data("id");
            $("#idmodal").val(id);
            loading.show();
            datatable_kot.ajax.reload(function(){
                loading.hide();
                $('#modal_kota').modal('show');
            });                
        });
        
        $("#modal_kota").on('click', '#save_popup', function(e){
            var kab_list = $('.radio:checked').data("id");
            // var pro_list = $('.radio:checked').data("pv");
            loading.show();
            // $("#inp_pro").val("");
            $("#inp_kota").val(kab_list);
            // $("#inp_pro").val(pro_list);
            satu();dua();tiga();empat();lima();enam();tujuh();delapan();sembilan();sepuluh();seblas();duabelas();tigabelas();empatbelas();p2();
            loading.hide();
        });
        
        function satu(){
            url = controller+"/pertumbuhan_ekomomi";
            
            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('satu');
                    var obj = jQuery.parseJSON(response);
                    var mm = Highcharts.chart('chart-container-1', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                            // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", }
                            title: { "text": obj.indikator[0].satuan, }
                        },
                        plotOptions: {
                            line: {
                                dataLabels: {
                                    enabled: true
                                },
                                enableMouseTracking: false
                            }
                        },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }satu();
        
        // perkembangan PDRB Per Kapita ADHk
        function dua(){
            url = controller+"/pdrb";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('dua');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-2', {
                        chart: { type: 'column' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis:{
                            // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                            title: { "text": obj.indikator[0].satuan, },
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  return Highcharts.numberFormat(this.value,0);
                                }                
                              }
                          },
                        tooltip: {
                            headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                         '<td style="padding:0"><b>{point.y:,.0f} </b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }dua();

        // adhk
        function tiga(){
            url = controller+"/adhk";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('tiga');
                    var obj = jQuery.parseJSON(response);
                    var mm = Highcharts.chart('chart-container-3', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis:{
                            // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                            title: { "text": obj.indikator[0].satuan, },
                            labels: {
                                formatter: function() {
                                  if ( this.value > 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  return Highcharts.numberFormat(this.value,0)+" Jt";
                                }                
                              }
                          },
                          tooltip: {
                            headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                         '<td style="padding:0"><b>{point.y:,.2f} </b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            line: { dataLabels: { enabled: true },
                                enableMouseTracking: false
                            }
                        },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }tiga();

        // jumlah pengangguran
        function empat(){
            url = controller+"/jumlah_penganggur";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('empat');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-4', {
                        chart: { type: 'column' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis:{
                            // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                            title: { "text": obj.indikator[0].satuan, },
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  else if(this.value < 1000000)
                                      return Highcharts.numberFormat( this.value/1000000,1) + " Jt";
                                    return Highcharts.numberFormat(this.value,0);
                                }                
                              }
                          },
                          tooltip: {
                            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:,,.0f} </b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }empat();

        function lima(){
            url = controller+"/pembangunan_manusia";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('lima');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-5', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }lima();

        function enam(){
            url = controller+"/tingkat_pengangguran";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('enam');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-6', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }enam();
        
        // Gini Rasio
        function tujuh(){
            url = controller+"/gini_rasio";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('tujuh');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-7', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }tujuh();

        function delapan(){
            url = controller+"/harapan_hidup";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('delapan');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-8', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }delapan();

        // Rata rata Lama Sekolah  
        function sembilan(){
            url = controller+"/rata_lama_sekolah";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('sembilan');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-9', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }sembilan();
               
        // harapan Lama Sekolah
        function sepuluh(){
            url = controller+"/harapan_lama_sekolah";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('sepuluh');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-10', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }sepuluh();

        // pengeluaran_perkapita
        function sebelas(){
            url = controller+"/pengeluaran_perkapita";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('sebelas');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-11', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                                title: { "text": obj.indikator[0].satuan, },
                                labels: {
                                    formatter: function() {
                                      if ( this.value >= 1000000 ) 
                                          return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                      return Highcharts.numberFormat(this.value,0)+" Jt";
                                    }                
                                  }
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }sebelas();

        // Tingkat Kemiskinan
        function duabelas(){
            url = controller+"/tinkat_kemiskinan";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('duabelas');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-12', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }duabelas();
        
        // Indeks Kedalaman Kemiskinan
        function p2(){
            url = controller+"/kedalaman_kemiskinan";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('p2');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-p2', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }p2();
        
        // Indeks keparahan Kemiskinan
        function tigabelas(){
            url = controller+"/keparahan_kemiskinan";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('tigabelas');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-13', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator, },
                                title: { "text": obj.indikator[0].satuan, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }tigabelas();
        
        // Jumlah Penduduk Miskin
        function empatbelas(){
            url = controller+"/penduduk_miskin";

            var valueSelectedRegion = $('#selectregion').val();
            var valueProvince = [];
            var valueCity = [];
            var valueRegion = [];
            for (let i = 0; i < valueSelectedRegion.length; i++) {
                if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                    valueProvince.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                    valueCity.push(valueSelectedRegion[i]);
                }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                    valueRegion.push(valueSelectedRegion[i]);
                }
                
            }

            var valueSelectedYear = $('#selectyear').val();

            data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&tahun="+valueSelectedYear;
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    console.log('empatbelas');
                    var obj = jQuery.parseJSON(response);
                    // console.log(obj);
                    var mm = Highcharts.chart('chart-container-14', {
                        chart: { type: 'line' },
                        title: { "text":obj.indikator[0].nama_indikator, },
                        subtitle: { 
                            "text": obj.sumber,
                            floating: true,
                            align: 'left',
                            verticalAlign: 'bottom',
                        },
                        xAxis: { 
                            "categories":
                                obj.tahun,
                            },
                        yAxis: {
                                // title: { "text": obj.indikator[0].nama_indikator+" ("+obj.indikator[0].satuan+")", },
                                title: { "text": obj.indikator[0].satuan, },
                                labels: {
                                    formatter: function() {
                                      if ( this.value >= 1000000 ) return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                      return Highcharts.numberFormat(this.value,0);
                                    }                
                                  }
                            },
                            tooltip: {
                                headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                            // '<td style="padding:0"><b>{point.y:.0f} </b></td></tr>',
                                             '<td style="padding:0"><b>{point.y:,.0f} Orang </b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true
                            },
                            plotOptions: {
                                column: {
                                    pointPadding: 0.2,
                                    borderWidth: 0
                                }
                            },
                        series:  obj.data_indikator
                    });

                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }empatbelas();

    $("#btnFilter").click(function(e){
        satu();dua();tiga();empat();lima();enam();tujuh();delapan();sembilan();sepuluh();seblas();duabelas();tigabelas();empatbelas();p2();
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
            html += '<option value="'+data.data[i]+'">'+data.data[i]+'</option>';
        }
        $('#selectregion').append(html);
        $('#selectregion').selectpicker('refresh');
                
        $('.selectpicker ~ option').hide();
      });
});

$('#selectregion').change(function(){
    var valueSelectedRegion = $(this).val();
    var valueProvince = [];
    var valueCity = [];
    var valueRegion = [];
    for (let i = 0; i < valueSelectedRegion.length; i++) {
        if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
            valueProvince.push(valueSelectedRegion[i]);
        }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
            valueCity.push(valueSelectedRegion[i]);
        }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
            valueRegion.push(valueSelectedRegion[i]);
        }
        
    }
    // console.log(valueSelectedRegion)
    // console.log("Provinsi = "+valueProvince);
    // console.log("Kota = "+valueCity);
    // console.log("kabupaten = "+valueRegion);
});

$(document).ready(function() {
    $.post(base_url+controller+"/indikator_list", function(result) {
        var data = JSON.parse(result);
        // console.log(data.data[0]);
        var html = '';
        for (let i = 0; i < data.data.length; i++) {
            html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['nama_indikator']+'</option>';
        }
        $('#selectindicator').append(html);
        $('#selectindicator').selectpicker('refresh');
                
        $('.selectpicker ~ option').hide();
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

$(document).ready(function(){
    var table = $('#datatables-indikator-makro').DataTable( {
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        fixedColumns:   true,
        fixedHeader: true,
        fixedRowsTop: 4,
        ordering: false,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
    } );
});

$('.popup').click(function (event) {
    event.preventDefault();
    window.open($(this).attr("href"), "popupWindow", "width=600,height=600,scrollbars=yes");
});

$(document).ready(function(){
    $.get(base_url+controller+"/indikator_makro_tabel", function(response){
        var obj = jQuery.parseJSON(response);
        console.log("Data: " + obj.data[0]);
    });
});

function tabel_indikator_makro() {

    if ((valueSelectedRegion != '') && (valueSelectedIndicator != '') && (valueSelectedYear != '')) {

        var valueSelectedIndicator = $('#selectindicator').val();
        var valueSelectedYear = $('#selectyear').val();
        var valueSelectedRegion = $('#selectregion').val();
        var valueProvince = [];
        var valueCity = [];
        var valueRegion = [];
        for (let i = 0; i < valueSelectedRegion.length; i++) {
            if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
                valueProvince.push(valueSelectedRegion[i]);
            }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
                valueCity.push(valueSelectedRegion[i]);
            }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
                valueRegion.push(valueSelectedRegion[i]);
            }
            
        }

        data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&indikator="+valueSelectedIndicator+"&tahun="+valueSelectedYear;

        jQuery.ajax({
            type: "POST", // HTTP method POST or GET
            url: base_url+controller+"/indikator_makro_tabel", //Where to make Ajax calls
            dataType:"text", // Data type, HTML, json etc.
            data:data, //Form variables
            success:function(response){
                var data = JSON.parse(response);
                console.log(data.html);
                if (data.html != '') {
                    $('#tabel-indikator-makro').html(data.html);
                    loading.hide();
                    $(".indikator-makro-initial").hide();
                    $(".tabel-indikator").show(); 
                    $(".button-export-tabel-indikator-makro").show(); 
                }else{
                    loading.hide();
                }
            },
            error:function (xhr, ajaxOptions, thrownError){
                loading.hide(); 
                alert(thrownError);
            }
        });

    }else{
        loading.hide(); 
    }

}tabel_indikator_makro();

$(document).on('click', '.button-excel-tabel-indikator-makro', function(){
    var valueSelectedIndicator = $('#selectindicator').val();
    var valueSelectedYear = $('#selectyear').val();
    var valueSelectedRegion = $('#selectregion').val();
    var valueProvince = [];
    var valueCity = [];
    var valueRegion = [];
    for (let i = 0; i < valueSelectedRegion.length; i++) {
        if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
            valueProvince.push(valueSelectedRegion[i]);
        }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
            valueCity.push(valueSelectedRegion[i]);
        }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
            valueRegion.push(valueSelectedRegion[i]);
        }
        
    }

    // alert(valueSelectedYear.length);
    // var valueYear = [];
    // for (let i = 0; i < valueSelectedYear.length; i++) {
    //     if (valueSelectedYear[i]) {
    //         valueYear.push(valueSelectedYear[i]);
    //     }
        
    // }

    data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&indikator="+valueSelectedIndicator+"&tahun="+valueSelectedYear;

    // jQuery.ajax({
    //     type: "POST", // HTTP method POST or GET
    //     url: base_url+controller+"/export_indikator_makro_tabel", //Where to make Ajax calls
    //     dataType:"text", // Data type, HTML, json etc.
    //     data:data, //Form variables
    //     success:function(response){
    //         // var data = JSON.parse(response);
    //         console.log(response);
    //     },
    //     error:function (xhr, ajaxOptions, thrownError){
    //          loading.hide(); 
    //          alert(thrownError);
    //     }
    // });
    window.open(base_url+controller+"/export_indikator_makro_tabel?"+data); 
});

var scrollTop = 0;
$(document).ready(function() {
    $(window).on('scroll', function(){
        scrollTop = $(window).scrollTop();
        // console.log(scrollTop);
        if (scrollTop >= '20') {
            $(".button-export-tabel-indikator-makro").css("position", "fixed");
            $(".button-export-tabel-indikator-makro").css("margin-top", "-25px");
        }else{
            $(".button-export-tabel-indikator-makro").css("position", "fixed");
            $(".button-export-tabel-indikator-makro").css("margin-top", "0px");

        }
    });

    //use scrollTop here...
});

// $(document).ready(function() {
//     $('#selectregion').change(function(){
//         loading.show();
//         list_display_indikator();
//     })

//     $('#selectindicator').change(function(){
//         loading.show();
//         list_display_indikator();
//     })

//     $('#selectyear').change(function(){
//         loading.show();
//         list_display_indikator();
//     })
// });

var slider = document.querySelector('#tabel-indikator-makro');
var mouseDown = false;
var startX, scrollLeft;

var startDragging = function (e) {
  mouseDown = true;
  startX = e.pageX - slider.offsetLeft;
  scrollLeft = slider.scrollLeft;
};
var stopDragging = function (event) {
  mouseDown = false;
};

slider.addEventListener('mousemove', (e) => {
  e.preventDefault();
  if(!mouseDown) { return; }
  var x = e.pageX - slider.offsetLeft;
  var scroll = x - startX;
  slider.scrollLeft = scrollLeft - scroll;
});

// Add the event listeners
slider.addEventListener('mousedown', startDragging, false);
slider.addEventListener('mouseup', stopDragging, false);
slider.addEventListener('mouseleave', stopDragging, false);