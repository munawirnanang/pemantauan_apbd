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

                $('#pe_pro').show('');
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
            
            
        function empatbelas(){
            url = controller+"/jumlah_p_miskin";
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
//                   var mm = Highcharts.chart('chart-container-14', {
//                            chart: { type: 'column' },
//                            title: { "text":obj.text, },
//                            subtitle: { "text":obj.sumber, },
//                            xAxis: { 
//                                "categories":obj.categories,
//                                crosshair: true
//                               },
//                            yAxis: {
//                                min: 0,
//                                title: { "text":obj.text1, }
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
//                            series:  obj.series
//                        });

                    var mm = Highcharts.chart('chart-container-14', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Monthly Average Rainfall'
    },
    subtitle: {
        text: 'Source: WorldClimate.com'
    },
    xAxis: {
        categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ],
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Rainfall (mm)'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
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
    series: [{
        name: 'Tokyo',
        data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]

    }, {
        name: 'New York',
        data: [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]

    }, {
        name: 'London',
        data: [48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2]

    }, {
        name: 'Berlin',
        data: [42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]

    }]
});
         
                    $('form#form_jpm p[name="ket"]').html(obj.ket_pm);
                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
            
        }empatbelas();
        
        

            
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();