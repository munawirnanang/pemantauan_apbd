var main = function(){
    controller = "index.php/Pdrb_sektoral";
    
    var datatable = function(){
        
        $("a.link-back").click(function(e){
            var link_target     = $(this).data("target");
            var content_wrapper = $("#content_wrapper");
            var tag_js_path     = $("script#js_path");
            var tag_js_init     = $("script#js_initial");
            var data            = csrf_name+"="+$("#csrf").val();
            var this_tag        = $(this);
            $("a.bold").removeClass("bold");
            $(this).addClass("bold");
            var tag_bfr_id = $("a.asolole").attr("id");
            $(".se-pre-con").show();    
            loading.show();
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: link_target, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    var obj = null;
                    try{
                        obj = $.parseJSON(response);  
                    }catch(e)
                    {}
                    if(obj)//if json data
                    {
                        //success msg
                        if(obj.status === 1){
                            //update csrf token value
                            $("input#csrf").val(obj.csrf_hash);
                            //load string into content
                            content_wrapper.html(obj.str);
                            //change general title
                            $(".general_title > span").html(obj.general_title);

                            //re-insert new script DOM - s
                            $(".js_path").remove();
                            $(".js_initial").remove();

                            var str_script = '<script type="text/javascript" src="'+obj.js_path+'" class="js_path">';
                            str_script+="</script>";
                            $("body").append(str_script);

                            str_script = '<script type="text/javascript" class="js_initial">'+obj.js_initial;
                            str_script+="</script>";
                            $("body").append(str_script);
                            //re-insert new script DOM - e

                            $(".se-pre-con").fadeOut("slow");
                        }

                        //error msg
                        else if(obj.status === 0){
                            sweetAlert("Error", obj.msg, "error");
                            //update csrf token value
                            $("input#csrf").val(obj.csrf_hash);
                            $(".se-pre-con").fadeOut("slow");
                        }
                        else if(obj.status === 2){
                            sweetAlert("Error", obj.msg, "warning");
                            //update csrf token value
                            $("input#csrf").val(obj.csrf_hash);
                            //window.location.href = base_url+default_controller; //redirect ke login page
                            window.setTimeout(function(){
                              //  window.location.href = ajax_url; //redirect ke login page
                                //ajax_url
                                window.location.href = base_url+default_controller; //redirect ke login page
                            }, 2000);
                        }

                        loading.hide();
                    }
                    else
                    {
                        sweetAlert("Error", response, "error");loading.hide();
                    }
                },
                error:function (xhr, ajaxOptions, thrownError){
                    loading.hide(); 
                    sweetAlert("Error", thrownError, "warning");
                    window.setTimeout(function(){
        //                window.location.href = base_url+"home";
                    }, 2000);

                }
            });

        });
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
        $(".plhpro").click(function(e){ 
           // alert("");
            $('#mdlPro').modal('show'); 
        });
        
       $("#mdlPro").on('click', '#save_popup', function(e){
            var pro_id="";
            var idpro = $('.checkbox:checked').data("id");  
            $("#inp_proid").val(idpro);

            var   pro_list = "";
            console.log(pro_list=$('.checkbox:checked').map(function() {
                return this.value;
            }).get().join('|'));
            $("#inp_pro").val(pro_list);
            $("p#inp_pro_text").html(pro_list);
            struktur();
        });
        
  
     //Struktur PDRB
    function struktur(){
        url = controller+"/struktur_pdrb";
        var provinsi = $("#inp_pro");
        var kabupaten = $("#inp_kab");
        data = "provinsi="+$("#inp_pro").val();
        jQuery.ajax({
            type: "POST", // HTTP method POST or GET
            url: base_url+url, //Where to make Ajax calls
            dataType:"text", // Data type, HTML, json etc.
            data:data, //Form variables
            success:function(response){
                var obj = jQuery.parseJSON(response);
                var mm = Highcharts.chart('chart-container-s', {
                    chart: {
                        type: 'bar'
                    },
                    title: {
                        text: 'Struktur PDRB'
                    },
                    subtitle: {
                        text: null
                    },
                    xAxis: {
                        //categories: ['1. Pertanian, Kehutanan, dan Perikanan', '2. Pertambangan dan Penggalian', '3. Industri Pengolahan', '4. Pengadaan Listrik dan Gas', '5. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang'],
                        categories :obj.categories,
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        //min: 0,
                        title: {
                            text: null,
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ' millions'
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor:
                            Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                        shadow: true
                    },
                    credits: {
                        enabled: false
                    },
//                        series: [{
//                            name: 'TW 1 2020',
//                            data: [23.71, 2.16, 5.22, 0.10, 0.41]
//                        }]
                    series:  obj.series
                });
                
                var pertumbuhan = Highcharts.chart('chart-container-p', {
                    chart: { type: 'bar' },
                    title: {
                        text: 'Pertumbuhan PDRB Sektoral'
                    },
                    subtitle: {
                        text: null
                    },
                    xAxis: {
                     //   categories: ['1. Pertanian, Kehutanan, dan Perikanan', '2. Pertambangan dan Penggalian', '3. Industri Pengolahan', '4. Pengadaan Listrik dan Gas', '5. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang'],
                       categories :obj.categories, 
                        title: {
                            text: null
                        }
                    },
                    yAxis: {
                        //min: 0,
                        title: {
                            text: null,
                            align: 'high'
                        },
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    tooltip: {
                        valueSuffix: ' millions'
                    },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    legend: {
                        layout: 'vertical',
                        align: 'right',
                        verticalAlign: 'top',
                        x: -40,
                        y: 80,
                        floating: true,
                        borderWidth: 1,
                        backgroundColor:
                            Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                        shadow: true
                    },
                    credits: {
                        enabled: false
                    },
//                        series: [{
//                            name: 'TW 1 2020',
//                            data: [107, 31, 635, 203, 2]
//                        }]
                        series:  obj.series2
                });
                
                var mm = Highcharts.chart('chart-container-pdrb', {
                    chart: { type: 'column' },
                    title: { "text":obj.text, },
                    subtitle: { "text":obj.sumber, },
                    xAxis: { 
                        "categories":obj.categories,
                        //crosshair: true
                        labels: {
                            rotation: -90,
                            style: {
                                fontSize: '9px',
                                fontFamily: 'Verdana, sans-serif'
                            }
                        }
                       },
                    yAxis: {
//                            min: 0,
                        title: {
                            "text":obj.text1,
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                        footerFormat: '</table>',
                        shared: true,
                        useHTML: true
                    },
                    plotOptions: {
                            series: {
                              borderWidth: 0,
                              dataLabels: {
                                enabled: true,
                                rotation: 0,
                                format: "{point.y:.2f}",
                                style: {
                                  fontSize: "6px",
                                  fontFamily: "Verdana, sans-serif",
                                },
                              },
                            },
                          },
                    series:  obj.series_2
                });

                $('form#form_tk p[name="ket_tk"]').html(obj.ket_tk);
            },
            error:function (xhr, ajaxOptions, thrownError){
                 loading.hide(); 
                 alert(thrownError);
             }
        });
    }struktur();
    //PDRB
//        function pdrb(){
//            url = controller+"/struktur_pdrb";
//            var provinsi = $("#inp_pro");
//            var kabupaten = $("#inp_kab");
//            var categories = [ '18. Produk Domestik Regional Bruto dengan migas',
//    '17. Jasa Lainnya', '16. Jasa Kesehatan dan Kegiatan Sosial', '15. Jasa Pendidikan', '14. Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib', '13. Jasa Perusahaan',
//    '12. Real Estat', '11. Jasa Keuangan dan Asuransi', '10. Informasi dan Komunikasi', '9. Penyediaan Akomodasi dan Makan Minum', '8. Transportasi dan Pergudangan',
//    '7. Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor', '6. Konstruksi', '5. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang', '4. Pengadaan Listrik dan Gas', '3. Industri Pengolahan',
//    '2. Pertambangan dan Penggalian', '1. Pertanian, Kehutanan, dan Perikanan '
//];
//            data = "provinsi="+$("#inp_pro").val();
//            jQuery.ajax({
//                type: "POST", // HTTP method POST or GET
//                url: base_url+url, //Where to make Ajax calls
//                dataType:"text", // Data type, HTML, json etc.
//                data:data, //Form variables
//                success:function(response){
//                    var obj = jQuery.parseJSON(response);
//                    var mm = Highcharts.chart('chart-container-pdrb', {
//                        chart: {
//                            type: 'bar'
//                        },
//                        title: {
//                            text: 'Struktur dan Pertumbuhan PDRB Sektoral'
//                        },
//                        subtitle: {
//                           // text: 'Source: <a href="http://populationpyramid.net/germany/2018/">Population Pyramids of the World from 1950 to 2100</a>'
//                            text: ''
//                        },
//                        accessibility: {
//                            point: {
//                                valueDescriptionFormat: '{index}. Age {xDescription}, {value}%.'
//                            }
//                        },
//                        xAxis: [{
//                            categories: categories,
//                            reversed: false,
//                            labels: {
//                                step: 1
//                            },
//                            accessibility: {
//                                description: 'Age (male)'
//                            }
//                        }, { // mirror axis on right side
//                            opposite: true,
//                            reversed: false,
//                            categories: categories,
//                            linkedTo: 0,
//                            labels: {
//                                step: 1
//                            },
//                            accessibility: {
//                                description: 'Age (female)'
//                            }
//                        }],
//                        yAxis: {
//                            title: {
//                                text: null
//                            },
//                            labels: {
//                                formatter: function () {
//                                    return Math.abs(this.value) + '%';
//                                }
//                            },
//                            accessibility: {
//                                description: 'Percentage population',
//                                rangeDescription: 'Range: 0 to 5%'
//                            }
//                        },
//
//                        plotOptions: {
//                            series: {
//                                stacking: 'normal'
//                            }
//                        },
//
//                        tooltip: {
//                            formatter: function () {
//                                return '<b>' + this.series.name + ', age ' + this.point.category + '</b><br/>' +
//                                    'Population: ' + Highcharts.numberFormat(Math.abs(this.point.y), 1) + '%';
//                            }
//                        },
//
//                        series: [{
//                            name: 'Struktur PDRB TW 1 2020',
//                            data: [
//                                 -2.4,
//                                -2.7, -3.0, -3.3, -3.2,
//                                -2.9, -3.5, -4.4, -4.1,
//                                -3.4, -2.7, -2.3, -2.2,
//                                -1.6, -0.6, -0.3, -5.05,
//                                -32.00
//                            ]
//                        }, {
//                            name: 'Laju Pertumbuhan PDRB TW 1 2020',
//                            data: [
//                                 2.3, 2.6,
//                                2.9, 3.2, 3.1, 2.9, 3.4,
//                                4.3, 4.0, 3.5, 2.9, 2.5,
//                                2.7, 2.2, 1.1, 0.6, 5.05,
//                                32.00
//                            ]
//                        }]
//                    });
//
//
//                    $('form#form_tk p[name="ket_tk"]').html(obj.ket_tk);
//                },
//                error:function (xhr, ajaxOptions, thrownError){
//                     loading.hide(); 
//                     alert(thrownError);
//                 }
//            });
//        }pdrb();
    
   
   
};
//     var chart     = function(){
//     };

return{
    init:function(){datatable();},
   // detail:function(){chart();},
};
}();