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
                satu();dua();tiga();empat();lima();enam();tujuh();delapan();sembilan();sepuluh();seblas();duabelas();tigabelas();empatbelas();p2();
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
                //$("#inp_pro").val("");
                $("#inp_kab").val(kab_list);
                //$("#inp_pro").val(pro_list);
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
                //var pro_list = $('.radio:checked').data("pv");
                loading.show();
                //$("#inp_pro").val("");
                $("#inp_kota").val(kab_list);
                //$("#inp_pro").val(pro_list);
                satu();dua();tiga();empat();lima();enam();tujuh();delapan();sembilan();sepuluh();seblas();duabelas();tigabelas();empatbelas();p2();
                loading.hide();
            });
//            $(".btnMenu").click(function(e){
//                var provinsi = $(".inp_proid");
//                var kabupaten = $("#inp_kab");
//                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
//                alert(data); 
//                //window.open(base_url+controller+"/indikator?inp_pro="+$(".inp_proid").val()+"&inp_sp="+$(".inp_kid").val());
//                window.open(base_url+controller+"/indikator?"+data);
//                
//               //
//            });
            
            function satu(){
                url = controller+"/pertumbuhan_ekomomi";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-1', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            //title: { "text":null, },
                            subtitle: { 
                                "text": obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom',
                            },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: { "text": obj.text2, }
                            },
                            plotOptions: {
                                line: {
                                    dataLabels: {
                                        enabled: true
                                    },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });

                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }satu();
            
            //perkembangan PDRB Per Kapita ADHk
            function dua(){
                url = controller+"/pdrb";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-2', {
                            chart: { type: 'column' },
                            title: { "text":obj.text, },
                            //title: { "text":null, },
                            subtitle: { "text":obj.sumber, 
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom',},
                            xAxis: { "categories":obj.categories,
                                crosshair: true
    //                            ,
    //                            labels: {
    //                                format: '{value} km'
    //                            }
                               },
//                            yAxis: {
//                                min: 0,
//                                title: {
//                                    "text":obj.text1,
//                                }
//                            },
                            yAxis:{
                                title: {"text":obj.text1, },
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
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }dua();
            //adhk
            function tiga(){
                url = controller+"/adhk";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-3', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom',},
                            xAxis: { "categories":obj.categories, },
//                            yAxis: {
//                                title: {"text":obj.text2, },
//                            },
                            yAxis:{
                                title: {"text":obj.text2, },
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
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }tiga();
        //jumlah pengangguran
            function empat(){
                url = controller+"/jumlah_penganggur";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-4', {
                            chart: { type: 'column' },
                            title: { "text":obj.text, },
                            subtitle: { "text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories,
                                crosshair: true
                               },
//                            yAxis: {
//                                min: 0,
//                                title: {
//                                    "text":obj.text1,
//                                }
//                            },
                            yAxis:{
                                title: {"text":obj.text1, },
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
//                                    '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
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
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }empat();

            function lima(){
                url = controller+"/tingkat_pengangguran";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-5', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }lima();

             function enam(){
                url = controller+"/pembangunan_manusia";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-6', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }enam();
            //Gini Rasio
            function tujuh(){
                url = controller+"/gini_rasio";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-7', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
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
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    var obj = jQuery.parseJSON(response);
                    var mm = Highcharts.chart('chart-container-8', {
                        chart: { type: 'line' },
                        title: { "text":obj.text, },
                        subtitle: {"text":obj.sumber,
                            floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                        xAxis: { "categories":obj.categories, },
                        yAxis: {
                            title: {"text":obj.text2, },
                        },
                        plotOptions: {
                            line: { dataLabels: { enabled: true },
                                enableMouseTracking: false
                            }
                        },
                        series:  obj.series
                    });
                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
            }delapan();
            //Rata rata Lama Sekolah  
            function sembilan(){
                url = controller+"/rata_lama_sekolah";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-9', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }sembilan();          
            //harapan Lama Sekolah
            function sepuluh(){
                url = controller+"/harapan_lama_sekolah";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-10', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }sepuluh();
            //pengeluaran_perkapita
            function seblas(){
                url = controller+"/pengeluaran_perkapita";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-11', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
//                            yAxis: {
//                                title: {"text":obj.text2, },
//                            },
yAxis:{
                                title: {"text":obj.text2, },
//                                labels: {
//                                    formatter: function() {
//                                      if ( this.value > 1000000 ) return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
//                                      return Highcharts.numberFormat(this.value,0);
//                                    }                
//                                  }
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
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }seblas();
            //Tingkat Kemiskinan
            function duabelas(){
                url = controller+"/tinkat_kemiskinan";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-12', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }duabelas();
            //Indeks Kedalaman Kemiskinan
            function tigabelas(){
                url = controller+"/kedalaman_kemiskinan";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-13', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }tigabelas();
            //Indeks keparahan Kemiskinan
            function p2(){
                url = controller+"/keparahan_kemiskinan";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-p2', {
                            chart: { type: 'line' },
                            title: { "text":obj.text, },
                            subtitle: {"text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories, },
                            yAxis: {
                                title: {"text":obj.text2, },
                            },
                            plotOptions: {
                                line: { dataLabels: { enabled: true },
                                    enableMouseTracking: false
                                }
                            },
                            series:  obj.series
                        });
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }p2();
            //Jumlah Penduduk Miskin
            function empatbelas(){
                url = controller+"/penduduk_miskin";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                var kota = $("#inp_kota");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val()+"&kota="+$("#inp_kota").val();
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var mm = Highcharts.chart('chart-container-14', {
                            chart: { type: 'column' },
                            title: { "text":obj.text, },
                            
                            subtitle: { "text":obj.sumber,
                                floating: true,
                                align: 'left',
                                verticalAlign: 'bottom', },
                            xAxis: { "categories":obj.categories,
                                crosshair: true
    //                            ,
    //                            labels: {
    //                                format: '{value} km'
    //                            }
                               },
                            yAxis:{
                                title: {"text":obj.text1, },
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
                            series:  obj.series
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