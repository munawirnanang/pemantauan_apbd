    var main = function(){
        controller = "index.php/Gis";
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

        var map = L.map("map").setView([-2.416276, 117.421875], 5);

      // Inisialisasi jenis map
      L.esri.basemapLayer("Gray").addTo(map);

      //Memasukan dynamic layer arcgis yang sudah dibuat
      var dynamic_indonesia_map = L.esri
        .dynamicMapLayer({
          url: "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Angka_Harapan_Hidup_KabupatenKota/MapServer",
          opacity: 0.7,
        })
        .addTo(map);

      //Memasukan feature layer arcgis yang sudah dibuat
      var feature_indonesia_map = L.esri
        .featureLayer({
          url: "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Angka_Harapan_Hidup_KabupatenKota/FeatureServer/0",
          simplifyFactor: 0.35,
          precision: 5,
          //   fields: ["FID", "ZIP", "PO_NAME"],
          //   Mendefiniskan warna layer
          style: {
            color: "#A9A9A9",
            weight: 0.1,
            fillOpacity: 0.1,
          },
        })
        .addTo(map);

      // Inisialisasi variable oldId untuk menampung id kab/kota
      var oldId;

      // Apabila mouse tidak menyentuh layer maka
      feature_indonesia_map.on("mouseout", function (e) {
        // Masukan kata Hover to Inspect pada elemen dengan id info-pane
        document.getElementById("info-pane").innerHTML = "Hover to Inspect";
        // Menghapus style warna layer
        feature_indonesia_map.resetFeatureStyle(oldId);
      });

      // Apabila mouse menyentuh layer maka
      feature_indonesia_map.on("mouseover", function (e) {
        // console.log(e);
        // Masukan id kab/kota ke var oldId
        oldId = e.layer.feature.id;
        // Masukan kata kab/kota - Provinsi ke elemen dengan id info-pane
        document.getElementById("info-pane").innerHTML =
          e.layer.feature.properties.kabkot +
          " - " +
          e.layer.feature.properties.provinsi;
        // Mendefiniskan warna layer pada id kab/kota
        feature_indonesia_map.setFeatureStyle(e.layer.feature.id, {
          color: "#9D78D2",
          weight: 3,
          opacity: 1,
        });
      });

      // Memunculkan popup
      feature_indonesia_map.bindPopup(function (layer) {
        // console.log(layer);
        return (
          "<strong>Angka Harapan Hidup 2020</strong><hr />" +
          "<span>Kab/Kota : <strong>" +
          layer.feature.properties.kabkot +
          "</strong></span><br />" +
          "<span>Provinsi : <strong>" +
          layer.feature.properties.provinsi +
          "</strong></span><br />" +
          "<span>AHH(2020) : <strong>" +
          layer.feature.properties.ahh_2020 +
          "</strong></span><br />" +
          "<br /><a href='#' onclick='zoomTo(" +
          layer._bounds._northEast.lat +
          "," +
          layer._bounds._northEast.lng +
          "," +
          layer._bounds._southWest.lat +
          "," +
          layer._bounds._southWest.lng +
          ")'>Zoom to</a>"
        );
      });

      // listen for when all features have been retrieved from the server
      /* feature_indonesia_map.on("click", function (evt) {
        // console.log(evt.layer._bounds);
        // once we've looped through all the features, zoom the map to the extent of the collection
        map.fitBounds(evt.layer._bounds);
        // feature_indonesia_map.setFeatureStyle(evt.layer.feature.id, {
        //   color: "red",
        //   weight: 3,
        //   opacity: 1,
        // });
      }); */

      // Mengatur zoom layer pada kab/kota
      function zoomTo(northEastlat, northEastlng, southWestlat, southWestlng) {
        // Inisialisasi koordinat kab/kota
        var latlngs = [
          [northEastlat, northEastlng],
          [southWestlat, southWestlng],
        ];

        // zoom the map to the polygon
        // Masukan koordinat kab/kota untuk melakukan zoom
        map.fitBounds(latlngs);
      }
       
            
      $.getJSON(
        "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Angka_Harapan_Hidup_KabupatenKota/MapServer/legend?f=pjson",
        function (data) {
          // console.log(data.layers[0]);
          // console.log(data.layers[0].legend.length);
          // console.log(data.layers[0].legend[0]['label']);
          var html = "<strong>" + data.layers[0].layerName + "</strong>";
          html += '<ul style="list-style-type:none;padding:0;">';
          for (let index = 0; index < data.layers[0].legend.length; index++) {
            html += '<li style="padding-bottom: 10px;">';
            html +=
              '<img src="data:' +
              data.layers[0].legend[index]["contentType"] +
              ";base64, " +
              data.layers[0].legend[index]["imageData"] +
              '" alt="" style="display: inline-block;vertical-align: bottom;"/>';
            html +=
              '<span style="margin-left: 5px;">' +
              data.layers[0].legend[index]["label"] +
              "</span>";
          }
          html += "</ul>";
          $("#legend").append(html);
        }
      );            
            
        };

   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();