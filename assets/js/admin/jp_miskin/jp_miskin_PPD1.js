    var main = function(){
        controller = "index.php/Jp_miskin";
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
            mapboxgl.accessToken = 'pk.eyJ1IjoiZnJhbnNhbGFtb25kYSIsImEiOiJja2NlZ2xtMjkwMzgxMzJubm9paGJ5dmMyIn0.QJc2VJF6md9CaTilCmgYag';
            
            
            $(".plhpro").click(function(e){  $('#mdlPro').modal('show'); });
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
                
               // duabelas();empatbelas();lima();empat();tigabelas();tujuh();
                $('#pe_pro').show('');
                $('#pe_kab').show('');
                $('#pe_pro_p').show('');
                $('#pe_pro_d').hide('');
                $('#pe_pro_g').hide('');
                peta();
                satu();
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
                        var prp = obj.prop;
                        
                        var zoomThreshold = 4;
                        var map = new mapboxgl.Map({
                            container: 'map',
                            style: 'mapbox://styles/mapbox/streets-v11',
    //                        center: [109.390350, -7.303822 ],
                            center: [119.206479, -0.320152],
                            zoom: obj.js_zoom
                        });
                        
                        map.addControl(new mapboxgl.NavigationControl());
                            map.on('load', function () {
                                map.addSource('maine', {
                                    'type': 'geojson',
                                    //'data': obj.js_geo,
                                    'data': obj.peta,
        //                                    'data': 'http://localhost/peppd/assets/js/geojson/indonesia-1100.geojson'
                                });
                                  //  var nilai= obj.nilai_pro;
                                    map.addLayer({
                                    'id': 'states-layer',
                                    'type': 'fill',
                                    'source': 'maine',
                                    'paint': {
                                            'fill-color': [
                                               'interpolate',
                                               ['linear'],
                                               ['get', 'population'],
                                         // [obj.id_pro, obj.nilai_d],
                                        //  [ obj.nilai_d],
                                            0,
                                            '#eff3ff',
                                            276670,
                                            '#aac8e6',
                                            652800,
                                            '#E6B71E',
                                            1407250,
                                            '#669cce',
                                            5070980,
                                            '#2171b5'
                                            ],
                                            'fill-opacity': 0.75
}
                                    });


                                map.on('click', 'maine', function (e) {
                                    new mapboxgl.Popup()
                                    .setLngLat(e.lngLat)
                                    .setHTML(
                                        e.features[0].properties.NAME_2
                                    )

                                    .addTo(map);
                                });

                                var stateLegendEl = document.getElementById('state-legend');
                                var countyLegendEl = document.getElementById('county-legend');
       
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
            
//            function peta1(){
//                url = controller+"/peta";
//                var provinsi = $("#inp_pro");
//                var kabupaten = $("#inp_kab");
//                data1 = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
//                
//                jQuery.ajax({
//                    type: "POST", // HTTP method POST or GET
//                    url: base_url+url, //Where to make Ajax calls
//                    dataType:"text", // Data type, HTML, json etc.
//                    data:data1, //Form variables
//                    success:function(response){
//                        var obj = jQuery.parseJSON(response);
//                        var prp = obj.prop;
//           //             var data2 =obj.js_geo;
//                        //alert(data2);
//                        var data = [
//    ['id-3700', 0],
//    ['id-ac', 1],
//    ['id-jt', 2],
//    ['id-be', 3],
//    ['id-bt', 4],
//    ['id-kb', 5],
//    ['id-bb', 6],
//    ['id-ba', 7],
//    ['id-ji', 8],
//    ['id-ks', 9],
//    ['id-nt', 10],
//    ['id-se', 11],
//    ['id-kr', 12],
//    ['id-ib', 13],
//    ['id-su', 14],
//    ['id-ri', 15],
//    ['id-sw', 16],
//    ['id-ku', 17],
//    ['id-la', 18],
//    ['id-sb', 19],
//    ['id-ma', 20],
//    ['id-nb', 21],
//    ['id-sg', 22],
//    ['id-st', 23],
//    ['id-pa', 24],
//    ['id-jr', 25],
//    ['id-ki', 26],
//    ['id-1024', 27],
//    ['id-jk', 28],
//    ['id-go', 29],
//    ['id-yo', 30],
//    ['id-sl', 31],
//    ['id-sr', 32],
//    ['id-ja', 33],
//    ['id-kt', 34]
//];
//
//// Create the chart
//                       var mm = Highcharts.mapChart('map', {
//    chart: {
//        map: 'countries/id/id-all'
//    },
//
//    title: {
//        text: 'Highmaps basic demo'
//    },
//
//    subtitle: {
//        text: 'Source map: <a href="http://code.highcharts.com/mapdata/countries/id/id-all.js">Indonesia</a>'
//    },
//
//    mapNavigation: {
//        enabled: true,
//        buttonOptions: {
//            verticalAlign: 'bottom'
//        }
//    },
//
//    colorAxis: {
//        min: 0
//    },
//
//    series: [{
//        data: data,
//        name: 'Random data',
//        states: {
//            hover: {
//                color: '#BADA55'
//            }
//        },
//        dataLabels: {
//            enabled: true,
//            format: '{point.name}'
//        }
//    }]
//});
//                        $('div#div_header h3[name="judul').html(obj.judul);    
//                        $('div#form_nsl a[name="n_nsl"]').html(obj.nasional);
//                        $('div#form_nsl a[name="n_thn"]').html(obj.tahun_a);
//                        
//                    },
//                    error:function (xhr, ajaxOptions, thrownError){
//                         loading.hide(); 
//                         alert(thrownError);
//                     }
//                });
//            }peta1();
            //ok

            
//            function satu(){
//                url = controller+"/pertumbuhan_ekomomi";
//                var provinsi = $("#inp_pro");
//                var kabupaten = $("#inp_kab");
//                data = "provinsi="+$("#inp_pro").val()+"&kabupaten="+$("#inp_kab").val();
//                jQuery.ajax({
//                    type: "POST", // HTTP method POST or GET
//                    url: base_url+url, //Where to make Ajax calls
//                    dataType:"text", // Data type, HTML, json etc.
//                    data:data, //Form variables
//                    success:function(response){
//                        var obj = jQuery.parseJSON(response);
//                        var mm = Highcharts.chart('chart-container-1', {
//                            chart: { type: 'line' },
//                            title: { "text":obj.text, },
//                            subtitle: { "text": obj.sumber, },
//                            xAxis: { "categories":obj.categories, },
//                            yAxis: {
//                                title: { "text": obj.text2, }
//                            },
//                            plotOptions: {
//                                line: {
//                                    dataLabels: {
//                                        enabled: true
//                                    },
//                                    enableMouseTracking: false
//                                }
//                            },
//                            series:  obj.series
//                        });
//                        var pro = Highcharts.chart('chart-container-1-pro', {
//                        chart: { type: 'column' },
//                        title: { "text":obj.text_pro, },
//                        //subtitle: { "text":obj.sumber, },
//                        xAxis: { "categories":obj.categories_pro,
//                            crosshair: true
//
//                           },
//                        yAxis: {
//                            min: 0,
//                            title: { "text":obj.text_pro, }
//                        },
//                        tooltip: {
//                            headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
//                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
//                                         '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
//                            footerFormat: '</table>',
//                            shared: true,
//                            useHTML: true
//                        },
//                        plotOptions: {
//                            column: {
//                                pointPadding: 0.2,
//                                borderWidth: 0
//                            }
//                        },
//                        series:  obj.series_pro
//                    });
//                        var kab = Highcharts.chart('chart-container-1-kab', {
//                            chart: { type: 'column' },
//                            title: { "text":obj.text_kab, },
//                            //subtitle: { "text":obj.sumber, },
//                            xAxis: { "categories":obj.categories_kab,
//                                crosshair: true
//
//                               },
//                            yAxis: {
//                                min: 0,
//                                title: { "text":obj.text_kab, }
//                            },
//                            tooltip: {
//                                headerFormat:'<span style="font-size:10px">{point.key}</span><table>',
//                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
//                                             '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
//                                footerFormat: '</table>',
//                                shared: true,
//                                useHTML: true
//                            },
//                            plotOptions: {
//                                column: {
//                                    pointPadding: 0.2,
//                                    borderWidth: 0
//                                }
//                            },
//                            series:  obj.series_kab
//                        });
//                       
//                       $('form#form_ch p[name="ket"]').html(obj.ket);
//                       $('form#form_pe p[name="perkpdrkp"]').html(obj.pe_rkpd_rkp);
//                       $('form#form_pe p[name="maxpep"]').html(obj.max_pe_p);
//                       $('form#form_pe_per p[name="per_pro"]').html(obj.pe_perbandingan_pro);
//                       $('form#form_pe_perk p[name="per_kab"]').html(obj.pe_perbandingan_pro);
//                       
//                    },
//                    error:function (xhr, ajaxOptions, thrownError){
//                         loading.hide(); 
//                         alert(thrownError);
//                     }
//                });
//            }satu();
//            
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();