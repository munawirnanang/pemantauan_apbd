    var main = function(){
        controller = "index.php/Tingkat_kemiskinan";
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
                     { "targets": 3, "orderable": false },
                     { "width": "8px", "targets": 3}
                 ],
                 "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
                 "initComplete": function(settings, json) {
     //                console.log(settings);
                 },
                 paging: true,

            });
            var table2 = $("#tblkab");
//            var datatable = table2.DataTable({
//                 "processing": true,
//                 "serverSide": true,
//                 "ajax":{
//                     url :base_url+controller+"/kab_datatable", // json datasource
//                     type: "post",  // method  , by default get
//                     error: function(){  // error handling
//                             $(".employee-grid-error").html("");
//                             $("#employee-grid").append('<tbody class="employee-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
//                             $("#employee-grid_processing").css("display","none");
//                     },
//                     data: function(d){
//                     },
//                     "dataSrc": function ( json ) {
//                         //$("#ttl_qty").val(json.ttl_qty);
//                         return json.data;
//                     }
//
//                 },
//                 "columnDefs": [                  
//                     { "targets": 3, "orderable": false },
//                     { "width": "8px", "targets": 3}
//                 ],
//                 "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 50, "All"]],
//                 "initComplete": function(settings, json) {
//     //                console.log(settings);
//                 },
//                 paging: true,
//
//            });
            mapboxgl.accessToken = 'pk.eyJ1IjoiZnJhbnNhbGFtb25kYSIsImEiOiJja2NlZ2xtMjkwMzgxMzJubm9paGJ5dmMyIn0.QJc2VJF6md9CaTilCmgYag';
            
            
            $(".plhpro").click(function(e){  $('#mdlPro').modal('show'); });
            $(".plhkab").click(function(e){  $('#tblkab').modal('show'); });
            $("#mdlPro").on('click', '#save_popup', function(e){
                var pro_id="";
                var idpro = $('.checkbox:checked').data("id");  
                $("#inp_proid").val(idpro);

                var   pro_list = "";
                console.log(pro_list=$('.checkbox:checked').map(function() {
                    return this.value;
                }).get().join('|'));
                $("#inp_pro").val(pro_list);
                $("#inp_kab").val("");
                $("#map").val("");
                peta();
                satu();
               // duabelas();empatbelas();lima();empat();tigabelas();tujuh();
                $('#pe_pro').show('');
                $('#pe_rad').show('');
                $('#pe_kab').show('');

            });
            $(".nosatu").click(function(e){  
                $('#pe_pro_p').show('');
                $('#pe_pro_d').hide('');
                $('#pe_pro_g').hide('');
            });
            
            $(".nodua").click(function(e){  
                $('#pe_pro_p').hide('');
                $('#pe_pro_d').show('');
                $('#pe_pro_g').show('');
//                $('#pe_pro').show('');
//                $('#pe_kab').show('');
            });
            function peta(){
                url = controller+"/peta";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                data1 = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
                
                jQuery.ajax({
                    type: "POST", // HTTP method POST or GET
                    url: base_url+url, //Where to make Ajax calls
                    dataType:"text", // Data type, HTML, json etc.
                    data:data1, //Form variables
                    success:function(response){
                        var obj = jQuery.parseJSON(response);
                        var zoomThreshold = 4;
                        var map = new mapboxgl.Map({
                            container: 'map',
                            style: 'mapbox://styles/mapbox/streets-v11',
                            center: obj.js_tengah,
                            //center: [119.206479, -0.320152],
                            zoom: obj.js_zoom
                        });
                        
                        map.addControl(new mapboxgl.NavigationControl());
                            map.on('load', function () {
                                map.addSource('maine', {
                                    'type': 'geojson',
                                    //'data': obj.js_geo
                                    'data': obj.peta
        //                                    'data': 'http://localhost/peppd/assets/js/geojson/indonesia-1100.geojson'
                                });
                                map.addLayer({
                                    'id': 'states-layer',
                                    'type': 'fill',
                                    'source': 'maine',
                                    'paint': {
                                            'fill-color': [
                                               'interpolate',
                                               ['linear'],
                                               ['get', 'population'],
                                            0,
                                            '#ffd4d7',
                                            2,
                                            '#bc634f',
                                            7.3,
                                            '#a53b28',
                                            12.5,
                                            '#8b0000'
                                            ],
                                            'fill-opacity': 0.8
}
                                    });
                                // Add a black outline around the polygon.
                                map.addLayer({
                                    'id': 'outline',
                                    'type': 'line',
                                    'source': 'maine',
                                    'layout': {},
                                    'paint': {
                                        'line-color': '#000',
                                        'line-width': 1.3
                                    }
                                });   
                                map.on('click', 'states-layer', function (e) {
                                    new mapboxgl.Popup()
                                    .setLngLat(e.lngLat)
                                    .setHTML( e.features[0].properties.description
                                    )

                                    .addTo(map);
                                });

                             //   var stateLegendEl = document.getElementById('state-legend');
                             //   var countyLegendEl = document.getElementById('county-legend');
       
                            });

                        $('div#div_header h3[name="judul').html(obj.judul);    
                        $('div#form_nsl a[name="n_nsl"]').html(obj.nasional);
                        $('div#form_nsl a[name="n_thn"]').html(obj.tahun_a);
                        
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }peta();
            
            function satu(){
                url = controller+"/tinkat_kemiskinan";
                var provinsi = $("#inp_pro");
                var kabupaten = $("#inp_kab");
                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
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
                            subtitle: { "text": obj.sumber, },
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
                        var pro = Highcharts.chart('chart-container-1-pro', {
                        chart: { type: 'column' },
                        title: { "text":obj.text_pro, },
                        //subtitle: { "text":obj.sumber, },
                        xAxis: { "categories":obj.categories_pro,
                            crosshair: true

                           },
                        yAxis: {
                            min: 0,
                            title: { "text":obj.text_pro, }
                        },
                        tooltip: {
                            headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                         '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
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
                        series:  obj.series_pro
                    });
                        
                        var radar = Highcharts.chart('container-1-rad', {
                                        chart: {
                                            polar: true,
                                            type: 'line'
                                        },
                                        accessibility: {
                                            description: 'A spiderweb chart compares the allocated budget against actual spending within an organization. The spider chart has six spokes. Each spoke represents one of the 6 departments within the organization: sales, marketing, development, customer support, information technology and administration. The chart is interactive, and each data point is displayed upon hovering. The chart clearly shows that 4 of the 6 departments have overspent their budget with Marketing responsible for the greatest overspend of $20,000. The allocated budget and actual spending data points for each department are as follows: Sales. Budget equals $43,000; spending equals $50,000. Marketing. Budget equals $19,000; spending equals $39,000. Development. Budget equals $60,000; spending equals $42,000. Customer support. Budget equals $35,000; spending equals $31,000. Information technology. Budget equals $17,000; spending equals $26,000. Administration. Budget equals $10,000; spending equals $14,000.'
                                        },
                                        title: {
                                            text: obj.text_radar,
                                            x: -80
                                        },
                                        pane: {
                                            size: '80%'
                                        },
                                        xAxis: {
                                            categories: obj.categories_pro,
                                            tickmarkPlacement: 'on',
                                            lineWidth: 0
                                        },
                                        yAxis: {
                                            gridLineInterpolation: 'polygon',
                                            lineWidth: 0,
                                            min: 0
                                        },
                                    tooltip: {
                                        shared: true,
                                        pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:,.2f}</b><br/>'
                                    },

                                    legend: {
                                        align: 'right',
                                        verticalAlign: 'middle',
                                        layout: 'vertical'
                                    },
                                    series: obj.catdata_radar,
//                                    series: [{
//                                        name: 'Allocated Budget',
//                                        data: [43000, 19000, 60000, 35000, 17000, 10000],
//                                        pointPlacement: 'on'
//                                    }, {
//                                        name: 'Actual Spending',
//                                        data: [50000, 39000, 42000, 31000, 26000, 14000],
//                                        pointPlacement: 'on'
//                                    }],
                                    responsive: {
                                        rules: [{
                                            condition: {
                                                maxWidth: 500
                                            },
                                            chartOptions: {
                                                legend: {
                                                    align: 'center',
                                                    verticalAlign: 'bottom',
                                                    layout: 'horizontal'
                                                },
                                                pane: {
                                                    size: '70%'
                                                }
                                            }
                                        }]
                                    }
                                });
                        
                        var kab = Highcharts.chart('chart-container-1-kab', {
                            chart: { type: 'column' },
                            title: { "text":obj.text_kab, },
                            //subtitle: { "text":obj.sumber, },
                            xAxis: { "categories":obj.categories_kab,
                                crosshair: true

                               },
                            yAxis: {
                                min: 0,
                                title: { "text":obj.text_kab, }
                            },
                            tooltip: {
                                headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                             '<td style="padding:0"><b>{point.y:.2f} </b></td></tr>',
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
                            series:  obj.series_kab
                        });
                       
                       $('form#form_ch p[name="ket"]').html(obj.ket);
                       $('form#form_pe p[name="perkpdrkp"]').html(obj.pe_rkpd_rkp);
                       $('form#form_pe p[name="maxpep"]').html(obj.max_pe_p);
                       $('form#form_pe_per p[name="per_pro"]').html(obj.pe_perbandingan_pro);
                       $('form#form_pe_perk p[name="per_kab"]').html(obj.pe_perbandingan_pro);
                       
                    },
                    error:function (xhr, ajaxOptions, thrownError){
                         loading.hide(); 
                         alert(thrownError);
                     }
                });
            }satu();
//        function duabelas(){
//            url = controller+"/tinkat_kemiskinan";
//            var provinsi = $("#inp_pro");
//            var kabupaten = $("#inp_kab");
//            data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
//            jQuery.ajax({
//                type: "POST", // HTTP method POST or GET
//                url: base_url+url, //Where to make Ajax calls
//                dataType:"text", // Data type, HTML, json etc.
//                data:data, //Form variables
//                success:function(response){
//                    var obj = jQuery.parseJSON(response);
//                    $('form#form_tk p[name="ket_tk"]').html(obj.ket_tk);
//                    
//                    var mm = Highcharts.chart('chart-container-12', {
//                        chart: { type: 'line' },
//                        title: { "text":obj.text, },
//                        subtitle: {"text":obj.sumber, },
//                        xAxis: { "categories":obj.categories, },
//                        yAxis: {
//                            title: {"text":obj.text2, },
//                        },
//                        plotOptions: {
//                            line: { dataLabels: { enabled: true },
//                                enableMouseTracking: false
//                            }
//                        },
//                        series:  obj.series
//                    });
//                    
////                    $('form#form_tk_pro p[name="tk_pro_1"]').html(obj.max_n_tk);
////                    $('form#form_tk_pro p[name="max_p_tk"]').html(obj.max_p_tk);
////                    $('form#form_tk_pro p[name="tk_rkpd_rkp"]').html(obj.tk_rkpd_rkp);
////                    
////                    var pro = Highcharts.chart('chart-container-tk-pro', {
////                        chart: { type: 'column' },
////                        title: { "text":obj.text_pro, },
////                        //subtitle: { "text":obj.sumber, },
////                        xAxis: { "categories":obj.categories_pro,
////                            crosshair: true
////
////                           },
////                        yAxis: {
////                            min: 0,
////                            title: { "text":obj.text_pro, }
////                        },
////                        tooltip: {
////                            headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
////                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
////                                         '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
////                            footerFormat: '</table>',
////                            shared: true,
////                            useHTML: true
////                        },
////                        plotOptions: {
////                            column: {
////                                pointPadding: 0.2,
////                                borderWidth: 0
////                            }
////                        },
////                        series:  obj.series_pro
////                    });
////                    $('form#form_tk_pro_a p[name="tk_perbandingan_pro"]').html(obj.tk_perbandingan_pro);
////                    
////                    var kab = Highcharts.chart('chart-container-tk-kab', {
////                            chart: { type: 'column' },
////                            title: { "text":obj.text_kab, },
////                            //subtitle: { "text":obj.sumber, },
////                            xAxis: { "categories":obj.categories_kab,
////                                crosshair: true
////
////                               },
////                            yAxis: {
////                                min: 0,
////                                title: { "text":obj.text_kab, }
////                            },
////                            tooltip: {
////                                headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
////                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
////                                             '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
////                                footerFormat: '</table>',
////                                shared: true,
////                                useHTML: true
////                            },
////                            plotOptions: {
////                                column: {
////                                    pointPadding: 0.2,
////                                    borderWidth: 0
////                                }
////                            },
////                            series:  obj.series_kab
////                        });
////                    $('form#form_tk_k p[name="tk_perbandingan_kab"]').html(obj.tk_perbandingan_kab);    
//                },
//                error:function (xhr, ajaxOptions, thrownError){
//                     loading.hide(); 
//                     alert(thrownError);
//                 }
//            });
//        }duabelas();
//            
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();