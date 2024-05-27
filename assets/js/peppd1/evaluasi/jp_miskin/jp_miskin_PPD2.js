var main = function(){
    controller = "index.php/E_jumlah_penduduk_miskin";
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
            $("p#inp_pro_text").html(pro_list);
            $("#inp_kab").val("");
            $("#map").val("");
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
        $("#s_rpjmn").change(function (e) {
            loading.show();
            satu();
            loading.hide();
        });
        function satu(){
            url = controller+"/jumlah_p_miskin";
            var provinsi = $("#inp_pro").val();
            var s_rpjmn = $("#s_rpjmn").val();
            data = "provinsi="+$("#inp_pro").val()+"&s_rpjmn="+$("#s_rpjmn").val();
            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+url, //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    var obj = jQuery.parseJSON(response);
                    //$('form#form_jpm p[name="ket"]').html(obj.ket);
                    $('form#form_jml p[name="maxpep"]').html(obj.max_pe_p);
                    $('form#form_jml_prov p[name="per_pro"]').html(obj.pe_perbandingan_pro);
                    $('form#form_pe_perk p[name="per_kab"]').html(obj.pe_perbandingan_kab);
                    $('form#form_pe_p p[name="per_p"]').html(obj.perbandingan_2th);
                    
                    var mm = Highcharts.chart('chart-container-14', {
                        chart: { type: 'column' },
                        title: { "text":obj.text, },
                        subtitle: { 
                            //"text":obj.sumber, 
                            align: 'left',
                            verticalAlign: 'bottom'
                        },
                        xAxis: { "categories":obj.categories,
                            crosshair: true
                           },
                        yAxis: {
                            //min: 0,
                            title: {"text":obj.text1,},
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  else if(this.value < 1000000)
                                      return Highcharts.numberFormat( this.value/1000, 0) + " Rb";
                                  else
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
                        series:  obj.series
                    });

                    var pro = Highcharts.chart('chart-container-1-pro', {
                        chart: { type: 'column' },
                        title: { "text":obj.text_pro, },
                        xAxis: { "categories":obj.categories_pro,
                            crosshair: true
                        },
                         credits: { enabled: false },
                        //subtitle: { "text":obj.sumber, },
                        yAxis: {
                            //min: 0,
                            title: {"text":obj.text1,},
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  else if(this.value < 1000000)
                                      return Highcharts.numberFormat( this.value/1000, 0) + " Rb";
                                  else
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
                            series: {
                              borderWidth: 0,
                              dataLabels: {
                                enabled: true,
                                rotation: 0,
                                format: "{point.y:,,.0f}",
                                style: {
                                  fontSize: "6px",
                                  fontFamily: "Verdana, sans-serif",
                                },
                              },
                            },
                          },
                        series:  obj.series_pro,
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
                
                    var radar = Highcharts.chart('container-1-rad', {
                        chart: {
                            polar: true,
                                type: "line",
                                marginTop: 10,
                                marginRight: 10,
                                marginBottom: 10,
                                marginLeft: 10,
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
//                                        yAxis: {
//                                            gridLineInterpolation: 'polygon',
//                                            lineWidth: 0,
//                                            min: 0
//                                        },
yAxis: {
                            //min: 0,
                            
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  else if(this.value < 1000000)
                                      return Highcharts.numberFormat( this.value/1000000, 1) + " Jt";
                                  else
                                    return Highcharts.numberFormat(this.value,0);
                                }                
                              }
                        },
                                tooltip: {
                                    shared: true,
                                    pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>'
                                },

                                legend: {
                                    align: 'right',
                                    verticalAlign: 'middle',
                                    layout: 'vertical'
                                },
                                series: obj.catdata_radar,
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
                        xAxis: { "categories":obj.categories_kab,
                            crosshair: true

                           },
                          yAxis: {
                            //min: 0,
                            title: {"text":obj.text1,},
                            labels: {
                                formatter: function() {
                                  if ( this.value >= 1000000 ) 
                                      return Highcharts.numberFormat( this.value/1000000, 0) + " Jt";  // maybe only switch if > 1000
                                  else if(this.value < 1000000)
                                      return Highcharts.numberFormat( this.value/1000, 0) + " Rb";
                                  else
                                    return Highcharts.numberFormat(this.value,0);
                                }                
                              }
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        plotOptions: {
                            series: {
                              borderWidth: 0,
                              dataLabels: {
                                enabled: true,
                                rotation: -0,
                                format: "{point.y:,,.0f}",
                                style: {
                                  fontSize: "6px",
                                  fontFamily: "Verdana, sans-serif",
                                },
                              },
                            },
                          },
                        series:  obj.series_kab
                    });

                    if(provinsi == '' && s_rpjmn ==''){
                        
                        $('#pe_pro_g').show('');
                        $('#pe_pro').hide('');
                        $('#pe_rad').hide('');
                        $('#pe_kab').hide('');
                    }
                    else if (provinsi != '' && s_rpjmn ==''){
                        $('#pe_pro_g').show('');
                        $('#pe_pro').show('');
                        $('#pe_rad').show('');
                        $('#pe_kab').show('');
                        
                    }
                    else if (provinsi != '' && s_rpjmn !=''){
                        $('#pe_pro_g').show('');
                        $('#pe_pro').hide('');
                        $('#pe_rad').hide('');
                        $('#pe_kab').hide('');
                    }else if (provinsi == '' && s_rpjmn !=''){
                        $('#pe_pro_g').show('');
                        $('#pe_pro').hide('');
                        $('#pe_rad').hide('');
                        $('#pe_kab').hide('');
                    }
                },
                error:function (xhr, ajaxOptions, thrownError){
                     loading.hide(); 
                     alert(thrownError);
                 }
            });
        }satu();
    };


return{
    init:function(){datatable();},
};
}();