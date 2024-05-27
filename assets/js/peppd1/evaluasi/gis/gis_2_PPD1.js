    var main = function(){
        controller = "index.php/Gis_2";
        var datatable = function(){
            
          $(".btnMenu").click(function(e){
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
                        //var obj = jQuery.parseJSON(response);

                        if(obj)//if json data
                        {

                            //success msg
                            if(obj.status === 1){
                                //alert("");
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
            //                    $(".subdrop").removeClass("subdrop");
            //                    $("#"+tag_bfr_id).addClass("subdrop");
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
        list_daerah();
        list_indicator();
        list_year();
    });

    function list_daerah() {
        $.post(base_url+controller+"/daerah_list", function(result) {
            var data = JSON.parse(result);
            // console.log(data.data[0]);
            var html = '';
            for (let i = 0; i < data.data.length; i++) {
                html += '<option value="'+data.data[i]+'">'+data.data[i]+'</option>';
            }
            $('.selectregion').append(html);
            $('.selectregion').selectpicker('refresh');
                    
            $('.selectpicker ~ option').hide();
        });
    }
    

    function list_indicator() {
        $.post(base_url+controller+"/indikator_list", function(result) {
            var data = JSON.parse(result);
            // console.log(data.data[0]);
            var html = '';
            for (let i = 0; i < data.data.length; i++) {
                html += '<option value="'+data.data[i]['id']+'">'+data.data[i]['nama_indikator']+'</option>';
            }
            $('.selectindicator').append(html);
            $('.selectindicator').selectpicker('refresh');
                    
            $('.selectpicker ~ option').hide();
        });
    }

    function list_year() {
        $.post(base_url+controller+"/years_list", function(result) {
            var data = JSON.parse(result);
            // console.log(data.data[0]['tahun']);
            var html = '';
            for (let i = 0; i < data.data.length; i++) {
                html += '<option value="'+data.data[i]['tahun']+'">'+data.data[i]['tahun']+'</option>';
            }
            $('.selectyear').append(html);
            $('.selectyear').selectpicker('refresh');
                    
            $('.selectpicker ~ option').hide();
        });
    }

    // $(document).ready(function() {
    //     var html = '';
    //     html += '<option value="pencapaian">Pencapaian</option>';
    //     html += '<option value="t_m_rpjmn">t_m_rpjmn</option>';
    //     html += '<option value="t_rkpd">t_rkpd</option>';
    //     html += '<option value="t_k_rkp">t_k_rkp</option>';
    //     $('#selectdata').append(html);
    //     $('#selectdata').selectpicker('refresh');

    //     $('.selectpicker ~ option').hide();
    // });

    // $('#selectregion').change(function(){
    //     var regional = $('#selectregion').val();
    //     console.log(regional);
    // });

    // $('#selectindicator').change(function(){
    //     var indicator = $('#selectindicator').val();
    //     console.log(indicator);
    // });

    // $('#selectyear').change(function(){
    //     var year = $('#selectyear').val();
    //     console.log(year);
    // });

    // $('#selectdata').change(function(){
    //     var data = $('#selectdata').val();
    //     console.log(data);
    // });

    data_evaluasi_kinerja = {};

    data_all_evaluasi_kinerja = {};

    data_all_evaluasi_kinerja['data'] = [];

    data_evaluasi_kinerja['region'] = [];
    data_evaluasi_kinerja['indicator'] = [];
    data_evaluasi_kinerja['year'] = [];
    data_evaluasi_kinerja['data'] = [];

    $('#button-submit-form-evaluasi-kinerja').click(function(){

        var valueSelectedRegion = $('#idregion1').val();
        var valueSelectedIndicator = $('#idindicator1').val();
        var valueSelectedYear = $('#idyear1').val();
        var valueSelectedData = $('#iddata1').val();

        data_evaluasi_kinerja['region'].push(valueSelectedRegion);
        data_evaluasi_kinerja['indicator'].push(valueSelectedIndicator);
        data_evaluasi_kinerja['year'].push(valueSelectedYear);
        data_evaluasi_kinerja['data'].push(valueSelectedData);

        data_all_evaluasi_kinerja['data'].push(data_evaluasi_kinerja);

        // console.log("regional = "+data_evaluasi_kinerja['region']);
        // console.log("indicator = "+data_evaluasi_kinerja['indicator']);
        // console.log("year = "+data_evaluasi_kinerja['year']);
        // console.log("data = "+data_evaluasi_kinerja['data']);

        console.log(data_all_evaluasi_kinerja);

        // var valueProvince = [];
        // var valueCity = [];
        // var valueRegion = [];
        // for (let i = 0; i < valueSelectedRegion.length; i++) {
        //     if (valueSelectedRegion[i].indexOf("Provinsi") != -1) {
        //         valueProvince.push(valueSelectedRegion[i]);
        //     }else if(valueSelectedRegion[i].indexOf("Kota") != -1) {
        //         valueCity.push(valueSelectedRegion[i]);
        //     }else if(valueSelectedRegion[i].indexOf("Kabupaten") != -1) {
        //         valueRegion.push(valueSelectedRegion[i]);
        //     }
            
        // }

        // data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&indicator="+valueSelectedIndicator+"&year="+valueSelectedYear+"&data="+valueSelectedData;
        // data = "region="+valueSelectedRegion+"&indicator="+valueSelectedIndicator+"&year="+valueSelectedYear+"&data="+valueSelectedData+"&numberofgraph="+numberofgraph;
        data = data_all_evaluasi_kinerja;

        jQuery.ajax({
            type: "POST", // HTTP method POST or GET
            url: base_url+controller+"/evaluasi_kinerja", //Where to make Ajax calls
            dataType:"text", // Data type, HTML, json etc.
            data:data, //Form variables
            success:function(response){
                var data = JSON.parse(response);
                console.log(data);
                console.log(data.indicator[0][0][0]['nama_indikator']);
                // console.log(data.data[0].region[0][0]);
                $(".selectregion").val('default');
                $(".selectindicator").val('default');
                $(".selectyear").val('default');
                $(".selectdata").val('default');
                
                $(".selectregion").selectpicker("refresh");
                $(".selectindicator").selectpicker("refresh");
                $(".selectyear").selectpicker("refresh");
                $(".selectdata").selectpicker("refresh");
                if (data != '') {
                    var html = '';
                    for (let index = 0; index < data.data.length; index++) {
                        html += '<div class="card card-evaluasi-kinerja">';
                            
                            html += '<div class="row" style="margin-top: 15px; margin-left: 10px; margin-right: 10px;">';
                                html += '<div class="col-8">';
                                    html += '<div class="card" style="border-radius: 10px;">';
                                        html += '<div class="card-header" style="border-radius: 10px 10px 0px 0px; border-bottom: 1px solid rgba(0, 0, 0, 0.1);">';
                                            html += '<div class="btn-group" style="float: right; padding-buttom: 3px;">';
                                                html += '<button id="graph-tab" onclick="changeChart(graph-'+index+')" style="margin-right: 2px; font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-area-chart" aria-hidden="true"></i> Grafik</button>';
                                                html += '<button id="table-tab" onclick="changeChart(table-'+index+')" style="font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-table" aria-hidden="true"></i> Table</button>';
                                            html += '</div>';
                                        html += '</div>';
                                        html += '<div class="card-body">';
                                                html += '<div id="graphid-'+index+'" style="display: block;">';
                                                    html += '<figure class="highcharts-figure">';
                                                        html += '<div id="container-'+index+'"></div>';
                                                    html += '</figure>';
                                                html += '</div>';
                                                html += '<div id="tableid-'+index+'" style="display: none;">';
                                                    html += '<table class="table mb-0">';
                                                        html += '<thead>';
                                                            html += '<tr>';
                                                                html += '<th>#</th>';
                                                                html += '<th>First Name</th>';
                                                                html += '<th>Last Name</th>';
                                                                html += '<th>Username</th>';
                                                                html += '<th>Age</th>';
                                                                html += '<th>City</th>';
                                                            html += '</tr>';
                                                        html += '</thead>';
                                                        html += '<tbody>';
                                                            html += '<tr>';
                                                                html += '<td>1</td>';
                                                                html += '<td>Mark</td>';
                                                                html += '<td>Otto</td>';
                                                                html += '<td>@mdo</td>';
                                                                html += '<td>20</td>';
                                                                html += '<td>Cityname</td>';
                                                            html += '</tr>';
                                                            html += '<tr>';
                                                                html += '<td>2</td>';
                                                                html += '<td>Jacob</td>';
                                                                html += '<td>Thornton</td>';
                                                                html += '<td>@fat</td>';
                                                                html += '<td>20</td>';
                                                                html += '<td>Cityname</td>';
                                                            html += '</tr>';
                                                            html += '<tr>';
                                                                html += '<td>3</td>';
                                                                html += '<td>Larry</td>';
                                                                html += '<td>the Bird</td>';
                                                                html += '<td>@twitter</td>';
                                                                html += '<td>20</td>';
                                                                html += '<td>Cityname</td>';
                                                            html += '</tr>';
                                                            html += '<tr>';
                                                                html += '<td>4</td>';
                                                                html += '<td>Steve</td>';
                                                                html += '<td>Mac Queen</td>';
                                                                html += '<td>@steve</td>';
                                                                html += '<td>20</td>';
                                                                html += '<td>Cityname</td>';
                                                            html += '</tr>';

                                                        html += '</tbody>';
                                                    html += '</table>';

                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                html += '</div>';
                                html += '<div class="col-4">';
                                    html += '<div class="card" style="border-radius: 10px; height: 515px;">';
                                        html += '<div class="card-header" style="border-radius: 10px;">';
                                            html += '<h3 class="card-title">Keterangan</h3>';
                                        html += '</div>';
                                        html += '<div class="card-body">';
                                            html += '<p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
                                        html += '</div>';
                                    html += '</div>';
                                html += '</div>';
                            html += '</div>';
                            if ((data.indicator[index][0][0]['id'] == 1) || (data.indicator[index][0][0]['id'] == 2) || (data.indicator[index][0][0]['id'] == 3) || (data.indicator[index][0][0]['id'] == 5) || (data.indicator[index][0][0]['id'] == 8) || (data.indicator[index][0][0]['id'] == 9) || (data.indicator[index][0][0]['id'] == 10) || (data.indicator[index][0][0]['id'] == 11)) {
                                html += '<div class="row" style="margin-top: 15px; margin-left: 10px; margin-right: 10px;">';
                                    html += '<div class="col-8">';
                                        html += '<div class="card" style="border-radius: 10px;">';
                                            html += '<div class="card-header" style="border-radius: 10px 10px 0px 0px; border-bottom: 1px solid rgba(0, 0, 0, 0.1);">';
                                                html += '<div class="btn-group" style="float: right; padding-buttom: 3px;">';
                                                    html += '<button class="nav-link active" id="graph-perbandingan-tab" data-toggle="tab" href="#graphidperbandingan" role="tab" aria-controls="graph" aria-selected="false" style="margin-right: 2px; font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-area-chart" aria-hidden="true"></i> Grafik</button>';
                                                    html += '<button class="nav-link" id="table-perbandingan-tab" data-toggle="tab" href="#tableidperbandingan" role="tab" aria-controls="table" aria-selected="false" style="font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-table" aria-hidden="true"></i> Table</button>';
                                                html += '</div>';
                                            html += '</div>';
                                            html += '<div class="card-body">';
                                                html += '<div class="tab-content">';
                                                    html += '<div class="tab-pane show active" id="graphidperbandingan" role="tabpanel" aria-labelledby="graph-perbandingan-tab">';
                                                        html += '<figure class="highcharts-figure">';
                                                            html += '<div id="container-perbandingan-daerah-'+index+'"></div>';
                                                        html += '</figure>';
                                                    html += '</div>';
                                                    html += '<div class="tab-pane" id="tableidperbandingan" role="tabpanel" aria-labelledby="table-perbandingan-tab">';
                                                        html += '<table class="table mb-0">';
                                                            html += '<thead>';
                                                                html += '<tr>';
                                                                    html += '<th>#</th>';
                                                                    html += '<th>First Name</th>';
                                                                    html += '<th>Last Name</th>';
                                                                    html += '<th>Username</th>';
                                                                    html += '<th>Age</th>';
                                                                    html += '<th>City</th>';
                                                                html += '</tr>';
                                                            html += '</thead>';
                                                            html += '<tbody>';
                                                                html += '<tr>';
                                                                    html += '<td>1</td>';
                                                                    html += '<td>Mark</td>';
                                                                    html += '<td>Otto</td>';
                                                                    html += '<td>@mdo</td>';
                                                                    html += '<td>20</td>';
                                                                    html += '<td>Cityname</td>';
                                                                html += '</tr>';
                                                                html += '<tr>';
                                                                    html += '<td>2</td>';
                                                                    html += '<td>Jacob</td>';
                                                                    html += '<td>Thornton</td>';
                                                                    html += '<td>@fat</td>';
                                                                    html += '<td>20</td>';
                                                                    html += '<td>Cityname</td>';
                                                                html += '</tr>';
                                                                html += '<tr>';
                                                                    html += '<td>3</td>';
                                                                    html += '<td>Larry</td>';
                                                                    html += '<td>the Bird</td>';
                                                                    html += '<td>@twitter</td>';
                                                                    html += '<td>20</td>';
                                                                    html += '<td>Cityname</td>';
                                                                html += '</tr>';
                                                                html += '<tr>';
                                                                    html += '<td>4</td>';
                                                                    html += '<td>Steve</td>';
                                                                    html += '<td>Mac Queen</td>';
                                                                    html += '<td>@steve</td>';
                                                                    html += '<td>20</td>';
                                                                    html += '<td>Cityname</td>';
                                                                html += '</tr>';

                                                            html += '</tbody>';
                                                        html += '</table>';

                                                    html += '</div>';
                                                html += '</div>';
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                    html += '<div class="col-4">';
                                        html += '<div class="card" style="border-radius: 10px; height: 515px;">';
                                            html += '<div class="card-header" style="border-radius: 10px;">';
                                                html += '<h3 class="card-title">Keterangan</h3>';
                                            html += '</div>';
                                            html += '<div class="card-body">';
                                                html += '<p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                html += '</div>';

                                html += '<div class="row" style="margin-top: 15px; margin-left: 10px; margin-right: 10px;">';
                                    html += '<div class="col-8">';
                                        html += '<div class="card" style="border-radius: 10px;">';
                                            html += '<div class="card-header" style="border-radius: 10px 10px 0px 0px; border-bottom: 1px solid rgba(0, 0, 0, 0.1);">';
                                                html += '<div class="btn-group" style="float: right; padding-buttom: 3px;">';
                                                    html += '<button style="margin-right: 2px; font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-area-chart" aria-hidden="true"></i> Grafik</button>';
                                                    html += '<button style="font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-table" aria-hidden="true"></i> Table</button>';
                                                html += '</div>';
                                            html += '</div>';
                                            html += '<div class="card-body">';
                                                html += '<figure class="highcharts-figure">';
                                                    html += '<div id="container-perbandingan-daerah-dari-tahun-sebelumnya-'+index+'"></div>';
                                                html += '</figure>';
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                    html += '<div class="col-4">';
                                        html += '<div class="card" style="border-radius: 10px; height: 515px;">';
                                            html += '<div class="card-header" style="border-radius: 10px;">';
                                                html += '<h3 class="card-title">Keterangan</h3>';
                                            html += '</div>';
                                            html += '<div class="card-body">';
                                                html += '<p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</div>';
                                html += '</div>';
                            }

                        html += '</div>';
                    }

                    $(".col-evaluasi-kinerja").html(html);
                    
                    $('.selectpicker').selectpicker({
                        style: 'btn btn-custom-selectpicker btn-round',
                    });

                    for (let index2 = 0; index2 < data.data.length; index2++) {
                        if (data.data[index2].indicator[index2].length == 2) {
                            dataeval = [];
                            dataevaltot = [];
                            // for (let no1 = 0; no1 < data.data_evaluasi_db[index2][0].length; no1++) {
                            //     let text = data.data_evaluasi_db[index2][1][no1]['nama_daerah'];
                            //     const myArray = text.split(" ");
                            //     var acronym = ''; 
                            //     for (let index=0; index < myArray.length; index++) {
                            //         acronym += myArray[index].charAt(0);
                            //     }
                            //     var graphval = { x: data.data_evaluasi_db[index2][0][no1]['nilai'], y: data.data_evaluasi_db[index2][1][no1]['nilai'], z: 5, name: acronym, country: data.data_evaluasi_db[index2][0][no1]['nama_daerah'] };
                            //     dataeval.push(graphval);
                            // }
                            // for (let no1 = 0; no1 < data.data_regional_db[0][0].length; no1++) {
                            for (let no1 = 0; no1 < data.data_regional_db[0][0].length; no1++) {
                                let text = data.data_regional_db[0][1][no1][0]['nama_daerah'];
                                const myArray = text.split(" ");
                                var acronym = ''; 
                                for (let index=0; index < myArray.length; index++) {
                                    acronym += myArray[index].charAt(0);
                                }
                                var graphval = { x: parseFloat(data.data_regional_db[0][0][no1][0]['nilai']), y: parseFloat(data.data_regional_db[0][1][no1][0]['nilai']), z: 1, name: acronym, country: data.data_regional_db[0][0][no1][0]['nama_daerah'] };
                                dataeval.push(graphval);
                            }
                            dataevaltot.push(dataeval);
                            console.log(dataeval);

                            // console.log(data.data_regional_db[0][1][2][0]['nama_daerah']);
                            Highcharts.chart('container-'+index2, {
                
                                chart: {
                                    type: 'bubble',
                                    plotBorderWidth: 1,
                                    zoomType: 'xy'
                                },
                            
                                legend: {
                                    enabled: false
                                },
                            
                                title: {
                                    text: 'Grafik bubble indikator '+data.indicator[index2][0][0]['nama_indikator']+' dan '+data.indicator[index2][1][0]['nama_indikator']+' di '+data.data[index2].region[index2][0]+' pada tahun '+data.data[index2].year[index2][0]
                                },
                                subtitle: {
                                    text: 'Sumber : Data BPS, diolah'
                                },
                            
                                accessibility: {
                                    point: {
                                        valueDescriptionFormat: '{index}. {point.name}, fat: {point.x}g, sugar: {point.y}g, obesity: {point.z}%.'
                                    }
                                },
                            
                                xAxis: {
                                    gridLineWidth: 1,
                                    title: {
                                        text: data.indicator[index2][0][0]['nama_indikator']
                                    },
                                    labels: {
                                        format: '{value} '+data.data_regional_db[0][0][0][0]['satuan']
                                    },
                                    plotLines: [{
                                        color: 'black',
                                        dashStyle: 'dot',
                                        width: 2,
                                        value: 0,
                                        label: {
                                            rotation: 0,
                                            y: 15,
                                            style: {
                                                fontStyle: 'italic'
                                            },
                                            text: 'Safe line '+data.indicator[index2][0][0]['nama_indikator']
                                        },
                                        zIndex: 3
                                    }],
                                    accessibility: {
                                        rangeDescription: 'Range: 60 to 100 grams.'
                                    }
                                },
                            
                                yAxis: {
                                    startOnTick: false,
                                    endOnTick: false,
                                    title: {
                                        text: data.indicator[index2][1][0]['nama_indikator'],
                                    },
                                    labels: {
                                        format: '{value} '+data.data_regional_db[0][1][0][0]['satuan'],
                                    },
                                    maxPadding: 0.2,
                                    plotLines: [{
                                        color: 'black',
                                        dashStyle: 'dot',
                                        width: 2,
                                        value: 50,
                                        label: {
                                            align: 'right',
                                            style: {
                                                fontStyle: 'italic'
                                            },
                                            text: 'Safe line '+data.indicator[index2][1][0]['nama_indikator'],
                                            x: -10
                                        },
                                        zIndex: 3
                                    }],
                                    accessibility: {
                                        rangeDescription: 'Range: 0 to 160 grams.'
                                    }
                                },
                            
                                tooltip: {
                                    useHTML: true,
                                    headerFormat: '<table>',
                                    pointFormat: '<tr><th colspan="2"><h3>{point.country}</h3></th></tr>' +
                                        '<tr><th>'+data.indicator[index2][0][0]['nama_indikator']+':</th><td>{point.x} '+data.data_regional_db[0][0][0][0]['satuan']+'</td></tr>' +
                                        '<tr><th>'+data.indicator[index2][1][0]['nama_indikator']+':</th><td>{point.y} '+data.data_regional_db[0][0][1][0]['satuan']+'</td></tr>',
                                    footerFormat: '</table>',
                                    followPointer: true
                                },
                            
                                plotOptions: {
                                    series: {
                                        dataLabels: {
                                            enabled: true,
                                            format: '{point.name}'
                                        }
                                    }
                                },
                            
                                series: [{
                                        data: dataeval,
                                        colorByPoint: true
                                }]
                            
                            });
                        }else{
                            dataperiod = [];
                            datanas = [];
                            if (data.data_nasional_db[index2].length == 1) {
                                for (let indexnas = 0; indexnas < data.data_nasional_db[index2][0].length; indexnas++) {
                                    let sbstrperiodtahun = data.data_nasional_db[index2][0][indexnas]['id_periode'].substr(0, 4);
                                    let sbstrperiodbulan = data.data_nasional_db[index2][0][indexnas]['id_periode'].substr(4, 5);
                                    bulan = {'0' : '', '00' : '', '000' : '', '01' : 'Jan', '02' : 'Feb', '03' : 'Mar', '04' : 'Apr', '05' : 'Mei', '06' : 'Jun', '07' : 'Jul', '08' : 'Ags', '09' : 'Sep', '10' : 'Okt', '11' : 'Nov', '12' : 'Des'};
                                    if (sbstrperiodbulan == '0' || sbstrperiodbulan == '00' || sbstrperiodbulan == '000') {
                                        dataperiod.push(sbstrperiodtahun);
                                    }else{
                                        dataperiod.push(bulan[sbstrperiodbulan]+'-'+sbstrperiodtahun);
                                    }
                                    datanas.push(parseFloat(data.data_nasional_db[index2][0][indexnas]['nilai']));
                                }
                            }

                            dataeval = []; 
                            data_t_k_rkp = [];
                            data_t_m_rpjmn = [];
                            data_t_rkpd = [];
                            if (data.data_evaluasi_db[index2].length == 1) {
                                indexperiodnas = 0;
                                while (indexperiodnas < data.data_nasional_db[index2][0].length) {
                                    indexperiodeval = 0;
                                    indexnull = 0;
                                    while (indexperiodeval < data.data_evaluasi_db[index2][0].length) {
                                        if (data.data_nasional_db[index2][0][indexperiodnas]['id_periode'] == data.data_evaluasi_db[index2][0][indexperiodeval]['id_periode']) {
                                            dataeval.push(parseFloat(data.data_evaluasi_db[index2][0][indexperiodeval]['nilai']));
                                            data_t_k_rkp.push(parseFloat(data.data_evaluasi_db[index2][0][indexperiodeval]['t_k_rkp']));
                                            data_t_m_rpjmn.push(parseFloat(data.data_evaluasi_db[index2][0][indexperiodeval]['t_m_rpjmn']));
                                            data_t_rkpd.push(parseFloat(data.data_evaluasi_db[index2][0][indexperiodeval]['t_rkpd']));
                                            indexnull = indexnull + 1;
                                        }
                                        indexperiodeval = indexperiodeval + 1;
                                    }
                                    if (indexnull == 0) {
                                        dataeval.push(null);
                                        data_t_k_rkp.push(null);
                                        data_t_m_rpjmn.push(null);
                                        data_t_rkpd.push(null);
                                    }
                                    indexperiodnas = indexperiodnas + 1;
                                }
                            }
                            
                            // console.log(dataeval);
                            Highcharts.chart('container-'+index2, {
                                chart: {
                                    type: 'line'
                                },
                                title: {
                                    // text: 'Grafik garis perbandingan indikator '+data.indicator[index2][0][0]['nama_indikator']+' di '+data.data[index2].region[index2][0]+' dengan Nasional pada tahun '+data.data[index2].year[index2][0],
                                    text: 'Perbandingan indikator '+data.indicator[index2][0][0]['nama_indikator']+' '+data.data[index2].region[index2][0]+' dengan Nasional',
                                    align: 'center'
                                },
                                subtitle: {
                                    text:'Sumber: data BPS, diolah',
                                    align: 'center'
                                },
                                xAxis: {
                                    categories: dataperiod,
                                    crosshair: true,
                                    accessibility: {
                                        description: 'Periode'
                                    }
                                },
                                yAxis: {
                                    title: {
                                        text: data.data_evaluasi_db[index2][0][0]['satuan']
                                    }
                                },
                                tooltip: {
                                    valueSuffix: ' '+data.data_evaluasi_db[index2][0][0]['satuan']
                                },
                                plotOptions: {
                                    series: {
                                        connectNulls: true
                                    }
                                },
                                series: [
                                    {
                                        name: 'Nasional',
                                        data: datanas,
                                    },
                                    {
                                        name: data.data[index2].region[index2][0],
                                        data: dataeval,
                                    },
                                    {
                                        name: 'Target Makro RPJMN',
                                        data: data_t_m_rpjmn,
                                    },
                                    {
                                        name: 'Target RKPD',
                                        data: data_t_rkpd,
                                    },
                                    {
                                        name: 'Target Kewilayahan RKP',
                                        data: data_t_k_rkp,
                                    },
                                ]
                            });

                            if ((data.indicator[index2][0][0]['id'] == 1) || (data.indicator[index2][0][0]['id'] == 2) || (data.indicator[index2][0][0]['id'] == 3) || (data.indicator[index2][0][0]['id'] == 5) || (data.indicator[index2][0][0]['id'] == 8) || (data.indicator[index2][0][0]['id'] == 9) || (data.indicator[index2][0][0]['id'] == 10) || (data.indicator[index2][0][0]['id'] == 11)) {
                                datagraph = [];
                                datagraphmin1 = [];
                                dataregcat = [];
                                dataregnil = [];
                                dataregnilmin1 = [];
                                datanasnil = [];
                                datacapital = [];
                                for (let indexcat = 0; indexcat < data.data_regional_db[index2][0].length; indexcat++) {
                                    for (let indexcat2 = 0; indexcat2 < data.data_regional_db[index2][0][indexcat].length; indexcat2++) {
                                        console.log(data.data_regional_db[index2][0][indexcat][0]['nilai']);
                                        dataregcat.push(data.data_regional_db[index2][0][indexcat][0]['nama_daerah']);
                                        dataregnil.push(parseFloat(data.data_regional_db[index2][0][indexcat][0]['nilai']));
                                        dataregnilmin1.push(parseFloat(data.data_regional_min_1_db[index2][0][indexcat][0]['nilai']));
                                        datanasnil.push(parseFloat(data.data_regional_db[index2][0][indexcat][0]['nasional']));
                                        if (data.region[index2][0]['prov_id'] != null) {
                                            datacapital.push(parseFloat(data.capital[index2][0][0]['nilai']));
                                        }
                                    }
                                }

                                if (data.region[index2][0]['prov_id'] != null) {
                                    //column graph
                                    var graphnas = {type: 'line', name: 'Nasional', data: datanasnil};
                                    var graphcap = {type: 'line', name: data.capital[index2][0][0]['nama_daerah'], data: datacapital};
                                    var graphreg = {type: 'column', name: data.data[index2].year[index2][0], data: dataregnil};
                                    datagraph.push(graphnas);
                                    datagraph.push(graphcap);
                                    datagraph.push(graphreg);

                                    //spider graph
                                    var spidergraphnas = {name: 'Nasional', data: datanasnil, pointPlacement: 'on'};
                                    var spidergraphcap = {name: data.capital[index2][0][0]['nama_daerah'], data: datacapital, pointPlacement: 'on'};
                                    var spidergraphreg = {name: data.data[index2].year[index2][0], data: dataregnil, pointPlacement: 'on'};
                                    var spidergraphregmin1 = {name: (data.data[index2].year[index2][0] - 1), data: dataregnilmin1, pointPlacement: 'on'};
                                    datagraphmin1.push(spidergraphnas);
                                    datagraphmin1.push(spidergraphcap);
                                    datagraphmin1.push(spidergraphreg);
                                    datagraphmin1.push(spidergraphregmin1);
                                }else{
                                    //column graph
                                    var graphnas = {type: 'line', name: 'Nasional', data: datanasnil};
                                    var graphreg = {type: 'column', name: data.data[index2].year[index2][0], data: dataregnil};
                                    datagraph.push(graphnas);
                                    datagraph.push(graphreg);

                                    //spider graph
                                    var spidergraphnas = {name: 'Nasional', data: datanasnil, pointPlacement: 'on'};
                                    var spidergraphreg = {name: data.data[index2].year[index2][0], data: dataregnil, pointPlacement: 'on'};
                                    var spidergraphregmin1 = {name: (data.data[index2].year[index2][0] - 1), data: dataregnilmin1, pointPlacement: 'on'};
                                    datagraphmin1.push(spidergraphnas);
                                    datagraphmin1.push(spidergraphreg);
                                    datagraphmin1.push(spidergraphregmin1);
                                }

                                Highcharts.chart('container-perbandingan-daerah-'+index2, {
                                    chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        // text: 'Grafik garis perbandingan indikator '+data.indicator[index2][0][0]['nama_indikator']+' di '+data.data[index2].region[index2][0]+' dengan Nasional pada tahun '+data.data[index2].year[index2][0],
                                        text: 'Perbandingan indikator '+data.indicator[index2][0][0]['nama_indikator']+' antar daerah tahun '+data.data[index2].year[index2][0],
                                        align: 'center'
                                    },
                                    subtitle: {
                                        text:'Sumber: data BPS, diolah',
                                        align: 'center'
                                    },
                                    xAxis: {
                                        categories: dataregcat,
                                        crosshair: true,
                                        accessibility: {
                                            description: 'Periode'
                                        }
                                    },
                                    yAxis: {
                                        title: {
                                            text: data.data_regional_db[index2][0][0]['satuan']
                                        }
                                    },
                                    tooltip: {
                                        valueSuffix: ' '+data.data_regional_db[index2][0][0]['satuan']
                                    },
                                    plotOptions: {
                                        series: {
                                            connectNulls: true
                                        }
                                    },
                                    series: datagraph,
                                });

                                Highcharts.chart('container-perbandingan-daerah-dari-tahun-sebelumnya-'+index2, {

                                    chart: {
                                        polar: true,
                                        type: 'line'
                                    },
                                
                                    title: {
                                        text: 'Perbandingan indikator '+data.indicator[index2][0][0]['nama_indikator']+' antar daerah dari tahun '+(data.data[index2].year[index2][0] - 1) + 'dengan tahun '+data.data[index2].year[index2][0],
                                        x: -80
                                    },
                                
                                    pane: {
                                        size: '80%'
                                    },
                                
                                    xAxis: {
                                        categories: dataregcat,
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
                                        pointFormat: '<span style="color:{series.color}">{series.name}: <b>${point.y:,.0f}</b><br/>'
                                    },
                                
                                    legend: {
                                        align: 'right',
                                        verticalAlign: 'middle',
                                        layout: 'vertical'
                                    },
                                
                                    series: datagraphmin1,
                                
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
                            }
                            
                        }

                        function changeChart(chartType) {
                            if (chartType == 'graph-'+index2) {
                                $("#graphid-"+index2).show();
                                $("#tableid-"+index2).hide();
                            } else if (chartType == 'table-'+index2) {
                                $("#graphid-"+index2).hide();
                                $("#tableid-"+index2).show();
                            }
                        }
                    }

                    loading.hide();
                }else{
                    loading.hide();
                }
            },
            error:function (xhr, ajaxOptions, thrownError){
                loading.hide(); 
                alert(thrownError);
            }
        });
    });

    function graph_evaluasi_kinerja() {
        Highcharts.chart('container', {
    
            chart: {
                type: 'bubble',
                plotBorderWidth: 1,
                zoomType: 'xy'
            },
        
            legend: {
                enabled: false
            },
        
            title: {
                text: 'Sugar and fat intake per country'
            },
        
            subtitle: {
                text: 'Source: <a href="http://www.euromonitor.com/">Euromonitor</a> and <a href="https://data.oecd.org/">OECD</a>'
            },
        
            accessibility: {
                point: {
                    valueDescriptionFormat: '{index}. {point.name}, fat: {point.x}g, sugar: {point.y}g, obesity: {point.z}%.'
                }
            },
        
            xAxis: {
                gridLineWidth: 1,
                title: {
                    text: 'Daily fat intake'
                },
                labels: {
                    format: '{value} gr'
                },
                plotLines: [{
                    color: 'black',
                    dashStyle: 'dot',
                    width: 2,
                    value: 65,
                    label: {
                        rotation: 0,
                        y: 15,
                        style: {
                            fontStyle: 'italic'
                        },
                        text: 'Safe fat intake 65g/day'
                    },
                    zIndex: 3
                }],
                accessibility: {
                    rangeDescription: 'Range: 60 to 100 grams.'
                }
            },
        
            yAxis: {
                startOnTick: false,
                endOnTick: false,
                title: {
                    text: 'Daily sugar intake'
                },
                labels: {
                    format: '{value} gr'
                },
                maxPadding: 0.2,
                plotLines: [{
                    color: 'black',
                    dashStyle: 'dot',
                    width: 2,
                    value: 50,
                    label: {
                        align: 'right',
                        style: {
                            fontStyle: 'italic'
                        },
                        text: 'Safe sugar intake 50g/day',
                        x: -10
                    },
                    zIndex: 3
                }],
                accessibility: {
                    rangeDescription: 'Range: 0 to 160 grams.'
                }
            },
        
            tooltip: {
                useHTML: true,
                headerFormat: '<table>',
                pointFormat: '<tr><th colspan="2"><h3>{point.country}</h3></th></tr>' +
                    '<tr><th>Fat intake:</th><td>{point.x}g</td></tr>' +
                    '<tr><th>Sugar intake:</th><td>{point.y}g</td></tr>' +
                    '<tr><th>Obesity (adults):</th><td>{point.z}%</td></tr>',
                footerFormat: '</table>',
                followPointer: true
            },
        
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}'
                    }
                }
            },
        
            series: [{
                data: [
                    { x: 95, y: 95, z: 13.8, name: 'BE', country: 'Belgium' },
                    { x: 86.5, y: 102.9, z: 14.7, name: 'DE', country: 'Germany' },
                    { x: 80.8, y: 91.5, z: 15.8, name: 'FI', country: 'Finland' },
                    { x: 80.4, y: 102.5, z: 12, name: 'NL', country: 'Netherlands' },
                    { x: 80.3, y: 86.1, z: 11.8, name: 'SE', country: 'Sweden' },
                    { x: 78.4, y: 70.1, z: 16.6, name: 'ES', country: 'Spain' },
                    { x: 74.2, y: 68.5, z: 14.5, name: 'FR', country: 'France' },
                    { x: 73.5, y: 83.1, z: 10, name: 'NO', country: 'Norway' },
                    { x: 71, y: 93.2, z: 24.7, name: 'UK', country: 'United Kingdom' },
                    { x: 69.2, y: 57.6, z: 10.4, name: 'IT', country: 'Italy' },
                    { x: 68.6, y: 20, z: 16, name: 'RU', country: 'Russia' },
                    { x: 65.5, y: 126.4, z: 35.3, name: 'US', country: 'United States' },
                    { x: 65.4, y: 50.8, z: 28.5, name: 'HU', country: 'Hungary' },
                    { x: 63.4, y: 51.8, z: 15.4, name: 'PT', country: 'Portugal' },
                    { x: 64, y: 82.9, z: 31.3, name: 'NZ', country: 'New Zealand' }
                ],
                colorByPoint: true
            }]
        
        });
    }
    