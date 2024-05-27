var main = function(){
  controller = "index.php/E_tingkat_pengangguran_terbuka";
  var datatable = function(){
      mapboxgl.accessToken = 'pk.eyJ1IjoiZnJhbnNhbGFtb25kYSIsImEiOiJja2NlZ2xtMjkwMzgxMzJubm9paGJ5dmMyIn0.QJc2VJF6md9CaTilCmgYag';
    
      
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
               data: function(d){},
               "dataSrc": function ( json ) { return json.data;}
           },
           "columnDefs": [                  
               { "targets": 3, "orderable": false },
               { "width": "8px", "targets": 3}
           ],
           "lengthMenu": [[5, 20, 25, 50, -1], [5, 20, 25, 34]],
           "initComplete": function(settings, json) {
//                console.log(settings);
           },
           paging: true,

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
      $("#s_rpjmn").change(function (e) {
        loading.show();
        satu();
        loading.hide();
    });
      
      function satu(){
          url = controller+"/tingkat_pengangguran_terbuka";
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
                  var mm = Highcharts.chart('chart-container-1', {
                      chart: { type: 'line' },
                      title: { "text":obj.text, },
                      subtitle: { "text": obj.sumber, },
                      xAxis: { "categories":obj.categories, },
                      yAxis: { title: { "text": obj.text2, } },
                      plotOptions: {
                          line: {
                              dataLabels: {
                                  enabled: true,
                                  format: "{point.y:.2f}",
                                  style: {
                                      fontSize: "9px",
                                      fontFamily: "Verdana, sans-serif",
                                  },
                              },
                              enableMouseTracking: false
                          }
                      },
                      series:  obj.series
                  });
                  
                  var pro = Highcharts.chart('chart-container-1-pro', {
                          chart: { type: "column",
                                      marginTop: 80,
                                      marginRight: 50,
                                      marginBottom: 100,
                                      marginLeft: 100, },
                          title: { "text":obj.text_pro, },
                          xAxis: { "categories":obj.categories_pro, crosshair: true},
                          yAxis: { min: 0, title: { "text":obj.text_pro, } },
                          tooltip: {
                              headerFormat:
                                '<span style="font-size:10px">{point.key}</span><table>',
                              pointFormat:
                                '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.2f}%</b></td></tr>',
                              footerFormat: "</table>",
                              shared: true,
                              useHTML: true,
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
                      
                      series:  obj.series_pro
                  });
              
                    var radar = Highcharts.chart("chart-container-rad", {
                      chart: {
                          polar: true,
                              type: "line",
                              marginTop: 1,
                              marginRight: 1,
                              marginBottom: 1,
                              marginLeft: 1,
                        },
                      title: {
                        text: obj.text_radar
                      },
                      pane: {
                        size: "70%",
                      },
                      xAxis: {
                        categories: obj.categories_pro,
                        tickmarkPlacement: "on",
                        lineWidth: 0,
                      },
                      yAxis: {
                        gridLineInterpolation: "polygon",
                        lineWidth: 0,
                        min: 0,
                      },
                      tooltip: {
                        shared: true,
                        pointFormat:
                          '<span style="color:{series.color}">{series.name}: <b>{point.y:,.2f}</b><br/>',
                      },

                      legend: {
                        align: "right",
                        verticalAlign: "middle",
                        layout: "vertical",
                      },
                      series: obj.catdata_radar,
                      responsive: {
                        rules: [
                          {
                            condition: {
                              maxWidth: 500,
                            },
                            chartOptions: {
                              legend: {
                                align: "center",
                                verticalAlign: "bottom",
                                layout: "horizontal",
                              },
                              pane: {
                                size: "70%",
                              },
                            },
                          },
                        ],
                      },
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
                          pointFormat: '<tr><td style="color:{series.color};padding:0">{""}: Capaian :</td>' +
                                       '<td style="padding:0"><b>{point.y:,.2f}%</b></td></tr>',
                          footerFormat: '</table>',
                          shared: true,
                          useHTML: true
                      },
//                            tooltip: {
//                                          shared: true,
//                                          pointFormat:
//                                            '<span style="color:{series.color}">{"Nilai :"}: <b>{point.y:,.2f}%</b><br/>',
//                                        },
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
                      series:  obj.series_kab
                  });
                 
                 $('form#form_pe p[name="maxpep"]').html(obj.max_pe_p);
                 $('form#form_pe p[name="perkpdrkp"]').html(obj.pe_rkpd_rkp);
                 
                 $('form#form_pe p[name="per_pro"]').html(obj.pe_perbandingan_pro);
                 $('form#form_pe_p p[name="per_p"]').html(obj.perbandingan_radar);
                 $('form#form_pe p[name="per_kab"]').html(obj.perbandingan_kab);
                 if(s_rpjmn == '' && provinsi =='' ){
                      $('#pe_pro_g').show('');
                      $('#pe_pro').hide('');
                      $('#pe_rad').hide('');
                      $('#pe_kab').hide('');
                      $('#pe_pro_p').hide('');
                      $('#pe_pro_d').hide('');
                } 
                else if(s_rpjmn == '' && provinsi !='' ){
                      $('#pe_pro').show('');
                      $('#pe_rad').show('');
                      $('#pe_kab').show('');
                      $('#pe_pro_p').show('');
                      $('#pe_pro_d').show('');
                }
                else if(s_rpjmn != '' && provinsi ==''){
                  $('#pe_pro_g').show('');
                      $('#pe_pro').hide('');
                      $('#pe_rad').hide('');
                      $('#pe_kab').hide('');
                      $('#pe_pro_p').hide('');
                      $('#pe_pro_d').hide('');
                }
                else if(s_rpjmn != '' && provinsi !=''){
                  $('#pe_pro_g').show('');
                      $('#pe_pro').hide('');
                      $('#pe_rad').hide('');
                      $('#pe_kab').hide('');
                      $('#pe_pro_p').hide('');
                      $('#pe_pro_d').hide('');
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
 // detail:function(){chart();},
};
}();