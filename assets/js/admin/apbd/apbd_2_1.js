var main = function(){
    controller = "index.php/Apbd_2";
    
    var datatable = function(){
        window.history.pushState(null, "", window.location.href);        

        function list_display_apbd(){
            let valueSelectedItem = $('#selectitem').val();
            let valueSelectedYear = $('#selectyear').val();
            let valueSelectedRegion = $('#selectregion').val();
            if ((valueSelectedRegion == '') || (valueSelectedItem == '') || (valueSelectedYear == '')) {
                $(".apbd-initial").show();
                $(".tabel-apbd").hide();
                $(".button-export-tabel-apbd").hide();
                loading.hide();
            }else{
                tabel_apbd();
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
        console.log("Data: " + obj);
    });
});

function grafik_apbd(){
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

        data = "daerah="+valueSelectedRegion+"&item="+valueSelectedItem+"&tahun="+valueSelectedYear;
        // console.log(data);
    }
}

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

                    $('#html_grafik').html('');
                    var html_grafik = '';


                    for(let g = 0; g < grafik_data.length; g++){

                        var isi = grafik_data[g];
                        allsubdata = [];
                        
                        for(let u=0; u < grafik_drilldown_data.length; u++){
                            var isi2 = grafik_drilldown_data[u];
                            const indexstr = isi2.kode.slice(0,2);
                            
                            if(isi.kode==indexstr){
                                console.log(isi2);
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
                        var chartIdBar = 'bar-grafik-' + (g + 1);
                        var chartIdSun = 'sun-burst-grafik-' + (g + 1);

                        // html_grafik = '<div class="col-md-12">';
                        // html_grafik += '<div class="card card-border" style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">';
                        // html_grafik += '<div class="card-body" style="padding-top: 10px; padding-bottom: 0px;">';
                        // html_grafik += '<div class="button-years" style="float: left">';
                        // html_grafik += '<button type="button" class="btn-xs" style="margin-right: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">2017</button>';
                        // html_grafik += '<button type="button" class="btn-xs" style="margin-right: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">2018</button>';
                        // html_grafik += '<button type="button" class="btn-xs" style="margin-right: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">2019</button>';
                        // html_grafik += '<button type="button" class="btn-xs" style="margin-right: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">2020</button>';
                        // html_grafik += '</div>';
                        // html_grafik += '<div class="button-graft" style="float: right">';
                        // html_grafik += '<button type="button" class="btn-xs changeGraf" data-chart="bar" data-id="'+chartId+'" style="margin-left: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">Column Graft</button>';
                        // html_grafik += '<button type="button" class="btn-xs changeGraf" data-chart="sun-burst" data-id="'+chartId+'" style="margin-left: 2px; padding: 0px; padding-left: 5px; padding-right: 5px; font-size: .65rem;">Sun Burst Graft</button>';
                        // html_grafik += '</div>';
                        // html_grafik += '<div id="'+chartIdBar+'" class="mb-2" style="height: 300px; margin-top: 50px;"></div>';
                        // // html_grafik += '<div id="'+chartIdSun+'" class="mb-2" style="height: 300px; margin-top: 50px; display: none;"></div>';
                        // html_grafik += '</div>';
                        // html_grafik += '</div>';
                        // html_grafik += '</div>';
                        
                        html_grafik = '<div class="grafik-box col-md-12">';
                        html_grafik += '<div class="card" style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px; width: 38vw;">';
                        html_grafik += '<div class="card-body">';
                        html_grafik += '<div id='+chartId+' class="mb-2" style="height: 300px;"></div>';
                        html_grafik += '</div>';
                        html_grafik += '</div>';
                        html_grafik += '</div>';
                        $('#html_grafik').append(html_grafik);
                        
                        var nama = isi.nama;
                        var kode = isi.kode;
                        var chartname = kode+'. '+nama+' (2023)';                   

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

                        console.log(grafikmaindata);
                        console.log(seriessubgrafik[0]);

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
                            drilldown: seriessubgrafik[0]
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
                        $('.grafik-apbd').show();
                        $(".button-export-tabel-apbd").show(); 
                        $(".apbd-initial").hide(); 
                        $(".apbd-grafik-initial").hide(); 
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
        }

    }else{
        loading.hide(); 
    }

}tabel_apbd();

$(document).on('click', '.changeGraf', function(){
    var dataId = $(this).attr("data-id");
    var dataChart = $(this).attr("data-chart");
    if (dataChart == 'bar'){
        $("#bar-"+dataId).show();
        $("#sun-burst-"+dataId).hide();
        console.log('bar');
    }else{
        $("#bar-"+dataId).hide();
        $("#sun-burst-"+dataId).show();
        console.log('sun-burst');
    }
});


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