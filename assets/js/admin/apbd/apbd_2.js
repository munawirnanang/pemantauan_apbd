var main = function(){
    controller = "index.php/Apbd_2";
    
    var datatable = function(){
        window.history.pushState(null, "", window.location.href);        

        function list_display_apbd(){
            let valueSelectedItem = $('#selectitem').val();
            let valueSelectedYear = $('#selectyear').val();
            let valueSelectedRegion = $('#selectregion').val();
            if ((valueSelectedRegion == '') || (valueSelectedItem == '') || (valueSelectedYear == '')) {
                $(".apbd-tabel-initial").show();
                $(".apbd-grafik-initial").show();
                $(".tabel-apbd").hide();
                $(".grafik-apbd").hide();
                $(".button-export-tabel-apbd").hide();
                loading.hide();
            }else{
                tabel_apbd();
                grafik_apbd();
            }
        }

        $('#selectregion').change(function(){
            loading.show();
            list_display_apbd();
        })
    
        $('#selectitem').change(function(){
            loading.show();
            list_display_apbd();
        })
    
        $('#selectyear').change(function(){
            loading.show();
            list_display_apbd();
        })
    };
   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
}();

$(document).ready(function() {
    $('.selectpicker').selectpicker({
        style: 'btn btn-custom-selectpicker btn-round',
    });
    
});

$(document).ready(function() {
    $.post(base_url+controller+"/item_list", function(result) {
        var data = JSON.parse(result);
        // console.log(data.data[3].kode);
        var html = '';
        for (let i = 0; i < data.data.length; i++) {
            list = data.data[i].kode+'. '+data.data[i].nama;
            html += '<option value="'+data.data[i].kode+'">'+list.substr(0, 60)+'</option>';
        }
        $('#selectitem').append(html);
        $('#selectitem').selectpicker('refresh');
                
        $('.selectpicker ~ option').hide();
      });
});

$(document).ready(function() {
    $.post(base_url+controller+"/years_list", function(result) {
        var data = JSON.parse(result);
        // console.log(data.data[0].tahun);
        var html = '';
        for (let i = 0; i < data.data.length; i++) {
            html += '<option value="'+data.data[i].tahun+'">'+data.data[i].tahun+'</option>';
        }
        $('#selectyear').append(html);
        $('#selectyear').selectpicker('refresh');
                
        $('.selectpicker ~ option').hide();
      });
});

$(document).ready(function() {
    $.post(base_url+controller+"/daerah_list", function(result) {
        var data = JSON.parse(result);
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
});

$(document).ready(function(){
    var table = $('#datatables-apbd').DataTable( {
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

$(document).ready(function(){
    $.get(base_url+controller+"/apbd_tabel", function(response){
        var obj = jQuery.parseJSON(response);
        // console.log("Data: " + obj);
    });
});

function tabel_apbd() {

    if ((valueSelectedRegion != '') && (valueSelectedItem != '') && (valueSelectedYear != '')) {

        var valueSelectedItem = $('#selectitem').val();
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

        // data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;
        data = "daerah="+valueSelectedRegion+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;

        var inputsub= [];
        jQuery.ajax({
            type: "POST", // HTTP method POST or GET
            url: base_url+controller+"/itemdrilldown", //Where to make Ajax calls
            dataType:"text", // Data type, HTML, json etc.
            data:data, //Form variables
            success:function(response){
                var subindikator = JSON.parse(response);
                
                for (const subArray of subindikator) {
                    for (const item of subArray) {
                        inputsub.push(item.kode);
                    }
                }
                processData(inputsub);
            }
        });

        function processData(inputsub) {
    
            input = valueSelectedItem.concat(inputsub);
            let uniqueinput = [];
            input.forEach((element) => {
                if (!uniqueinput.includes(element)) {
                    uniqueinput.push(element);
                }
            });
            
            data = "daerah="+valueSelectedRegion+"&item="+uniqueinput+"&tahun="+valueSelectedYear;

            jQuery.ajax({
                type: "POST", // HTTP method POST or GET
                url: base_url+controller+"/apbd_tabel", //Where to make Ajax calls
                dataType:"text", // Data type, HTML, json etc.
                data:data, //Form variables
                success:function(response){
                    var data = JSON.parse(response);
                    var grafik_data = [];
                    var grafik_drilldown_data =[];

                    data.data_all_all_nilai_apbd_2.forEach((item) => {
                        // Check if the "kode" has 2 digits
                        if (/^\d{2}$/.test(item.kode)) {
                            var itemCopy = JSON.parse(JSON.stringify(item)); // Deep copy the item
                            grafik_data.push(itemCopy);
                        }
                    });
                    
                    data.data_all_all_nilai_apbd_2.forEach((item) => {
                        // Check if the "kode" has 4 digits
                        if (/^\d{4}$/.test(item.kode)) {
                            var itemCopy = JSON.parse(JSON.stringify(item)); // Deep copy the item
                            grafik_drilldown_data.push(itemCopy);
                        }
                    });

                    //drilldown
                    $('#html_barchart').html('');
                    var html_barchart = '';


                    for(let g = 0; g < grafik_data.length; g++){

                        var isi = grafik_data[g];
                        allsubdata = [];
                        
                        for(let u=0; u < grafik_drilldown_data.length; u++){
                            var isi2 = grafik_drilldown_data[u];
                            const indexstr = isi2.kode.slice(0,2);
                            
                            if(isi.kode==indexstr){
                                subdata = [];

                                isi2.data.forEach((item) => {
                                    item.value.forEach((valueItem) => {
                                    const id = `${valueItem.tahun}-${item.nama_daerah}`;
                                    const legenda = `${item.nama_daerah} ${valueItem.tahun}`;
                                    const data = [isi2.nama, parseFloat(valueItem.jumlah)];
                                    if (!allsubdata[id]) {
                                        allsubdata[id] = {
                                            id: id,
                                            name: legenda, // Assign legenda as the name
                                            data: [],
                                        };
                                    }
                                    allsubdata[id].data.push(data);
                                    });
                                });
                                const combinedData = Object.values(allsubdata);
                                seriessubgrafik = [];
                                seriessubgrafik.push({
                                    allowPointDrilldown: true,
                                    series: combinedData
                                });
                            }
                        }
                        
                        var chartId = 'grafik-' + (g + 1);
                        
                        html_barchart = '<div id='+chartId+' class="mb-2"></div>';
                        $('#html_barchart').append(html_barchart);
                        
                        var nama = isi.nama;
                        var kode = isi.kode;
                        var chartname = kode+'. '+nama;                   

                        var grafikmaindata = [];

                        isi.data.forEach(function (item) {
                            var dataPoints = item.value.map(function (valueItem) {
                                return {
                                    name: valueItem.tahun,
                                    y: parseInt(valueItem.jumlah),
                                    drilldown: valueItem.tahun + "-" + item.nama_daerah
                                };
                            });

                            grafikmaindata.push({
                                name: item.nama_daerah,
                                data: dataPoints
                            });
                        });

                        Highcharts.chart(chartId, {
                            chart: {
                              type: 'column'
                            },
                            title: {
                              text: chartname,
                              align: 'center'
                            },
                            plotOptions: {
                                series: {
                                pointPadding: 0.4,
                                groupPadding: 0.1
                              }
                            },	
                            tooltip: {
                                shared: true,
                            },
                            xAxis: {
                              type: 'category',
                            },
                            plotOptions: {
                              series: {
                                borderWidth: 0,
                                dataLabels: {
                                  enabled: true
                                }
                              }
                            },
                            series: grafikmaindata,
                            drilldown: seriessubgrafik[0],
                            exporting: {
                                buttons: {
                                    copyToClipboard: {
                                        text: 'Copy Chart',
                                        _titleKey: 'contextButtonTitle',
                                        align: 'right',
                                        onclick: function () {
                                            // Call the function to copy the chart to the clipboard
                                            copyHtmlToClipboard(this.container);
                                        },            
                                    }
                                }
                            }
                        });
                    }


                    if (data.html != '') {
                        $('#judul-tabel-apbd').html('');
                        $('#isi-tabel-apbd').html('');
                        var html_head = '';
                        var html_body = '';

                        html_head += '<tr>';
                        html_head += '<th width="50px" rowspan="3">';
                        html_head += '<center>Kode</center>';
                        html_head += '</th>';
                        html_head += '<th width="350px" rowspan="3">';
                        html_head += '<center>Uraian</center>';
                        html_head += '</th>';

                        var kode_wilayah = '';
                        
                        for (let j = 0; j < data.data_all_all_nilai_apbd.length; j++) {

                            if ((data.list_selected_item[0].kode == data.data_all_all_nilai_apbd[j].kode_item) && (kode_wilayah != data.data_all_all_nilai_apbd[j].wilayah)) {

                                kode_wilayah = data.data_all_all_nilai_apbd[j].wilayah;

                                html_head += '<th width="150px" colspan="'+data.selected_year.length+'">';
                                html_head += '<center>'+data.data_all_all_nilai_apbd[j].wilayah+'</center>';
                                html_head += '</th>';

                            }
                        }
                        html_head += '</tr>';
                        html_head += '<tr>';

                        var nama_daerah = '';

                        for (let j = 0; j < data.data_all_all_nilai_apbd.length; j++) {

                            if ((data.list_selected_item[0].kode == data.data_all_all_nilai_apbd[j].kode_item) && (nama_daerah != data.data_all_all_nilai_apbd[j].nama_daerah)) {

                                nama_daerah = data.data_all_all_nilai_apbd[j].nama_daerah;
                                
                                html_head += '<th colspan="'+data.selected_year.length+'">';
                                html_head += '<center>'+data.data_all_all_nilai_apbd[j].nama_daerah+'</center>';
                                html_head += '</th>';
                            }
                        }
                        html_head += '</tr>';

                        html_head += '<tr>';
                        for (let f = 0; f < data.selected_daerah.length; f++) {
                            for(let b = 0; b < data.selected_year.length; b++){
                                html_head += '<th>';
                                html_head += '<center>'+data.data_all_all_nilai_apbd[b].tahun+'</center>';
                                html_head += '</th>';
                            }
                        }
                            html_head += '</tr>';

                        $('#judul-tabel-apbd').append(html_head);

                        // item
                        for (let i = 0; i < data.data_all_all_nilai_apbd_2.length; i++) {
                            if(valueSelectedItem.includes(data.data_all_all_nilai_apbd_2[i].kode)){   
                                html_body += '<tr>';
                                html_body += '<td style="background-color: #74737a; color: white;">'+data.data_all_all_nilai_apbd_2[i].kode+'</td>';
                                html_body += '<td style="background-color: #74737a; color: white;">'+data.data_all_all_nilai_apbd_2[i].nama+'</td>';
                                //daerah
                                for (let j = 0; j < data.data_all_all_nilai_apbd_2[i].data.length; j++) {
                                    //value
                                    var shading = [];
                                    for (let k = 0; k < data.data_all_all_nilai_apbd_2[i].data[j].value.length; k++) {
                                        var num_shading = 0;
                                        for (let l = 0; l < data.data_all_all_nilai_apbd_2[i].data[j].value.length; l++) {
                                            var value_apbd_kiri = (data.data_all_all_nilai_apbd_2[i].data[j].value[k].jumlah != null ? data.data_all_all_nilai_apbd_2[i].data[j].value[k].jumlah : '0');
                                            var value_apbd_kanan = (data.data_all_all_nilai_apbd_2[i].data[j].value[l].jumlah != null ? data.data_all_all_nilai_apbd_2[i].data[j].value[l].jumlah : '0');
                                            // console.log(typeof(value_apbd_kiri));
                                            if (value_apbd_kiri > value_apbd_kanan) {
                                                num_shading += 1;
                                            }
                                        }
                                        shading.push(num_shading);   
                                    }
                                    
                                    for (let m = 0; m < data.data_all_all_nilai_apbd_2[i].data[j].value.length; m++) {
                                        if (data.data_all_all_nilai_apbd_2[i].data[j].value[m].jumlah != null) {
                                            var number = parseFloat(data.data_all_all_nilai_apbd_2[i].data[j].value[m].jumlah);
                                            var formattedNumber = number.toLocaleString();
                                            var bg_color = (shading[m] / (data.data_all_all_nilai_apbd_2[i].data[j].value.length -1));
                                            // if (j%2 != 0){
                                            //     var rgba = 'rgba(127,255,244,'+bg_color+')';
                                            // }else{
                                            //     var rgba = 'rgba(127,255,212,'+bg_color+')';
                                            // }
                                            if (j%2 != 0){
                                                var rgba = 'rgba(131,197,190,'+bg_color+')';
                                            }else{
                                                var rgba = 'rgba(131,197,190,'+bg_color+')';
                                            }
                                            html_body += '<td style="text-align: right; background-color: '+rgba+'"><center>'+formattedNumber+'</center></td>';
                                        }else{
                                            html_body += '<td style="text-align: right;"><center>-</center></td>';
                                        } 
                                    }
                                }
                                html_body += '</tr>';
                            }
                        }

                        $('#isi-tabel-apbd').append(html_body);
                        $('.tabel-apbd').show();
                        $('.barchart-apbd').show();
                        $(".button-export-tabel-apbd").show(); 
                        $(".apbd-tabel-initial").hide(); 
                        $(".apbd-barchart-initial").hide(); 
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
        };
    }else{
        loading.hide(); 
    }

};

function grafik_apbd() {

    if ((valueSelectedRegion != '') && (valueSelectedItem != '') && (valueSelectedYear != '')) {

        var valueSelectedItem = $('#selectitem').val();
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

        // data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;
        data = "daerah="+valueSelectedRegion+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;

        jQuery.ajax({
            type: "POST", // HTTP method POST or GET
            url: base_url+controller+"/apbd_grafik", //Where to make Ajax calls
            dataType:"text", // Data type, HTML, json etc.
            data:data, //Form variables
            success:function(response){
                var data = JSON.parse(response);
                console.log(data.data);
                $('#isi-grafik-apbd').html('');
                var html_grafik = '';
                for (var index = 0; index < data.data.length; index++) {

                    html_grafik += '<div class="col-6">';
                    html_grafik += '<div class="card" style="border: 1px solid black; border-radius: 30px;">';
                    // html_grafik += '<div class="card-header" style="border-radius: 10px; padding-top: 5px; padding-bottom: 0px; text-align: left; display: inline-flex; justify-content: space-between;">';
                    // html_grafik += '</div>';
                    for (var index3 = 0; index3 < data.data[index].data_level_1.length; index3++) {
                        html_grafik += '<div class="card-body" style="padding-top: 5px;">';
                        html_grafik += '<figure class="highcharts-figure">';
                        html_grafik += '<div id="container-'+index+'-'+index3+'"></div>';
                        html_grafik += '</figure>';
                        html_grafik += '</div>';
                    }
                    html_grafik += '</div>';
                    html_grafik += '</div>';

                }
                $('#isi-grafik-apbd').append(html_grafik);
                $('.grafik-apbd').show(); 
                $(".apbd-grafik-initial").hide(); 
                // loading.hide();

                for (var index2 = 0; index2 < data.data.length; index2++) {

                    for (var index4 = 0; index4 < data.data[index2].data_level_1.length; index4++) {
                        
                        const value = data.data[index2].data_level_1[index4].data_level_2;

                        // Data retrieved from https://netmarketshare.com/
                        // Make monochrome colors
                        const colors = Highcharts.getOptions().colors.map((c, i) =>
                            // Start out with a darkened base color (negative brighten), and end
                            // up with a much brighter color
                            Highcharts.color(Highcharts.getOptions().colors[0])
                                .brighten((i - 3) / 7)
                                .get()
                        );

                        Highcharts.setOptions({
                            colors: [
                                // '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4',
                                '#058dc7', '#50b432', '#ed561b', '#dddf00', '#24cbe5',
                            ]
                        });
                            
                        Highcharts.chart('container-'+index2+'-'+index4, {
                        
                            chart: {
                                height: '100%'
                            },
                        
                            // Let the center circle be transparent
                            colors: ['transparent'].concat(Highcharts.getOptions().colors),
                        
                            title: {
                                text: data.data[index2].daerah
                            },
                        
                            subtitle: {
                                text: 'Tahun '+data.data[index2].data_level_1[index4].tahun
                            },

                            series: [{
                                type: 'sunburst',
                                data: value,
                                allowDrillToNode: true,
                                cursor: 'pointer',
                                //https://api.highcharts.com/highcharts/series.sunburst.custom
                                custom: {
                                    percentage: 'parent',  //'whole' or 'parent'
                                },
                                dataLabels: {
                                    formatter: function() {
                                        const point = this.point,
                                              series = this.series,
                                              mode = series.options.custom && series.options.custom.percentage;
                        
                                        const chartTotal = series.__myTotal || (series.__myTotal = series.data.map(p => p.options.value || 0).reduce((a, b) => a + b));
                        
                                        let percentage;
                                        switch(mode) {
                                            case 'whole':
                                                percentage = point.value/chartTotal;
                                                break;
                                            case 'parent':
                                                const group = point.parent && series.chart.get(point.parent),
                                                      total = group ? group.value : chartTotal;
                                                percentage = point.value/total;
                                                break;
                                        }
                        
                                        const val = (percentage === undefined) ? point.value : (percentage * 100).toFixed(1) + '%';
                                        return point.name + '<br>' + val;
                                    },
                                },
                                levels: [{
                                            level: 1,
                                            levelIsConstant: false,
                                            dataLabels: {
                                                enabled: false,
                                                inside: true,
                                                style: {
                                                    textOverflow: 'clip',
                                                    fontSize: '8px',
                                                },
                                                filter: {
                                                    property: 'outerArcLength',
                                                    operator: '>',
                                                    value: 64
                                                }
                                            }
                                        }, {
                                            level: 2,
                                            dataLabels: {
                                                style: {
                                                    textOverflow: 'clip',
                                                    fontSize: '8px',
                                                },
                                            },
                                            // dataLabels: {
                                            //     format: '{point.name}',
                                            //     enabled: true,
                                            //     inside: true,
                                            //     style: {
                                            //         textOverflow: 'clip',
                                            //         fontSize: '8px',
                                            //     },
                                            // },
                                            colorByPoint: true
                                        },
                                        {
                                            level: 3,
                                            dataLabels: {
                                                style: {
                                                    textOverflow: 'clip',
                                                    fontSize: '8px',
                                                },
                                            },
                                            // dataLabels: {
                                            //     format: '{point.name}',
                                            //     enabled: true,
                                            //     inside: true,
                                            //     style: {
                                            //         textOverflow: 'clip',
                                            //         fontSize: '8px',
                                            //     },
                                            // },
                                            colorVariation: {
                                                key: 'brightness',
                                                to: -0.3
                                            }
                                        }, {
                                            level: 4,
                                            dataLabels: {
                                                enabled: false,
                                            },
                                            // dataLabels: {
                                            //     enabled: false,
                                            //     inside: true,
                                            //     style: {
                                            //         textOverflow: 'clip',
                                            //         fontSize: '8px',
                                            //     },
                                            //     filter: {
                                            //         property: 'outerArcLength',
                                            //         operator: '>',
                                            //         value: 64
                                            //     }
                                            // },
                                            colorVariation: {
                                                key: 'brightness',
                                                to: 0.3
                                            }
                                        }]
                            }],
                        
                            // series: [{
                            //     type: 'sunburst',
                            //     data: value,
                            //     name: 'Root',
                            //     allowDrillToNode: true,
                            //     borderRadius: 3,
                            //     cursor: 'pointer',
                            //     // colors,
                            //     custom: {
                            //         percentage: 'whole',  //'whole' or 'parent'
                            //     },
                            //     dataLabels: {
                            //         formatter: function() {
                            //             const point = this.point,
                            //                   series = this.series,
                            //                   mode = series.options.custom && series.options.custom.percentage;
                        
                            //             const chartTotal = series.__myTotal || (series.__myTotal = series.data.map(p => p.options.value || 0).reduce((a, b) => a + b));
                        
                            //             let percentage;
                            //             switch(mode) {
                            //                 case 'whole':
                            //                     percentage = point.value/chartTotal;
                            //                     break;
                            //                 case 'parent':
                            //                     const group = point.parent && series.chart.get(point.parent),
                            //                           total = group ? group.value : chartTotal;
                            //                     percentage = point.value/total;
                            //                     break;
                            //             }
                        
                            //             const val = (percentage === undefined) ? point.value : (percentage * 100).toFixed(1) + '%';
                            //             return point.name + '<br>' + val;
                            //         },
                            //         filter: {
                            //             property: 'innerArcLength',
                            //             operator: '>',
                            //             value: 16
                            //         }
                            //     },
                            //     levels: [{
                            //         level: 1,
                            //         levelIsConstant: false,
                            //         dataLabels: {
                            //             enabled: false,
                            //             inside: true,
                            //             style: {
                            //                 textOverflow: 'clip',
                            //                 fontSize: '8px',
                            //             },
                            //             filter: {
                            //                 property: 'outerArcLength',
                            //                 operator: '>',
                            //                 value: 64
                            //             }
                            //         }
                            //     }, {
                            //         level: 2,
                            //         dataLabels: {
                            //             format: '{point.name}',
                            //             enabled: true,
                            //             inside: true,
                            //             style: {
                            //                 textOverflow: 'clip',
                            //                 fontSize: '8px',
                            //             },
                            //         },
                            //         colorByPoint: true
                            //     },
                            //     {
                            //         level: 3,
                            //         dataLabels: {
                            //             format: '{point.name}',
                            //             enabled: true,
                            //             inside: true,
                            //             style: {
                            //                 textOverflow: 'clip',
                            //                 fontSize: '8px',
                            //             },
                            //         },
                            //         colorVariation: {
                            //             key: 'brightness',
                            //             to: -0.3
                            //         }
                            //     }, {
                            //         level: 4,
                            //         dataLabels: {
                            //             enabled: false,
                            //             inside: true,
                            //             style: {
                            //                 textOverflow: 'clip',
                            //                 fontSize: '8px',
                            //             },
                            //             filter: {
                            //                 property: 'outerArcLength',
                            //                 operator: '>',
                            //                 value: 64
                            //             }
                            //         },
                            //         colorVariation: {
                            //             key: 'brightness',
                            //             to: 0.3
                            //         }
                            //     }]
                        
                            // }],
                        
                            tooltip: {
                                headerFormat: '',
                                formatter: function() {
                                    const point = this.point,
                                          series = this.series,
                                          mode = series.options.custom && series.options.custom.percentage;
                    
                                    const chartTotal = series.__myTotal || (series.__myTotal = series.data.map(p => p.options.value || 0).reduce((a, b) => a + b));
                    
                                    let percentage;
                                    switch(mode) {
                                        case 'whole':
                                            percentage = point.value/chartTotal;
                                            break;
                                        case 'parent':
                                            const group = point.parent && series.chart.get(point.parent),
                                                  total = group ? group.value : chartTotal;
                                            percentage = point.value/total;
                                            break;
                                    }
                    
                                    const val = (percentage === undefined) ? point.value : (percentage * 100).toFixed(1) + '%';
                                    return point.name + ' ' + point.value + ' (' + val + ')';
                                },
                                // pointFormat: 'The population of <b>{point.name}</b> is <b>{point.value}</b>'
                                // pointFormat: 'Anggaran <b>{point.name}</b> sebesar <b>{point.value}</b>({point.percentage:.1f}%)'
                            }
                        });
                    }
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

};

$(document).on('click', '.button-excel-tabel-apbd', function(){
    
    var valueSelectedItem = $('#selectitem').val();
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

    // data = "provinsi="+valueProvince+"&kabupaten="+valueRegion+"&kota="+valueCity+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;
    data = "daerah="+valueSelectedRegion+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;

    window.open(base_url+controller+"/export_apbd_tabel?"+data); 
});

var scrollTop = 0;
$(document).ready(function() {
    $(window).on('scroll', function(){
        scrollTop = $(window).scrollTop();
        // console.log(scrollTop);
        if (scrollTop >= '35') {
            $(".button-export-tabel-apbd").css("position", "fixed");
            $(".button-export-tabel-apbd").css("margin-top", "-25px");
        }else{
            // $(".button-export-tabel-apbd").css("position", "inherit");
            $(".button-export-tabel-apbd").css("position", "fixed");
            $(".button-export-tabel-apbd").css("margin-top", "0px");

        }
    });

    //use scrollTop here...
});


var slider = document.querySelector('#tabel-apbd');
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