<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title><?php echo $tag_title?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Responsive bootstrap 4 admin template" name="description" />
        <meta content="Coderthemes" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="<?php echo base_url("assets/images/logo_bappenas.png")?>" /> 
        <!-- Load Font Awesome Online -->
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
    />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.15.4/css/v4-shims.css"
    />

    <!-- Bootstrap CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
      integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
      crossorigin="anonymous"
    />

    <!-- Load Leaflet from CDN -->
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
      integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
      crossorigin=""
    />
    <script
      src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
      integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
      crossorigin=""
    ></script>

    <!-- Load Esri Leaflet from CDN -->
    <script
      src="https://unpkg.com/esri-leaflet@3.0.2/dist/esri-leaflet.js"
      integrity="sha512-myckXhaJsP7Q7MZva03Tfme/MSF5a6HC2xryjAM4FxPLHGqlh5VALCbywHnzs2uPoF/4G/QVXyYDDSkp5nPfig=="
      crossorigin=""
    ></script>

    <!-- Load Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Load JS Bootsrap -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns"
      crossorigin="anonymous"
    ></script>

    <style>
      body {
        margin: 0;
        padding: 0;
      }
      #map {
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        left: 0;
      }
    </style>

    <style>
      #info-pane {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 400;
        padding: 1em;
        background: white;
      }

       #source-pane {
        position: absolute;
        bottom: 10px;
        left: 10px;
        z-index: 400;
        padding: 0.5em;
        background-color: rgba(255, 255, 255, 0.7);
        /*background: white;*/
      }

      #legend {
        /* position: absolute; */
        background: 33b86c;
        /*padding: 1em;*/
      }

      #legend > hr {
        background-color: white;
      }
    </style>
  </head>
  <body>
    <div class="row m-2">
      <div class="col-sm-4 p-1">
        <div class="row">
          <div class="col-6 pr-1">
            <div
              class="card border-dark mb-2"
              style="background-color: #33b86c"
            >
              <div class="card-body" style="padding: 0.5rem;">
                <h4 class="text-white d-flex justify-content-center">
                  <i
                    class="fas fa-arrow-alt-circle-left mr-2 d-flex align-items-center"
                    style="font-size: 1.5rem"
                  ></i>
                  <a class="text-white" style="font-size: 1.5rem;" href="" onclick="window.close()"
                    >KEMBALI</a
                  >
                </h4>
              </div>
            </div>
            <div
              class="card border-dark mb-2"
              style="background-color: #33b86c; height: 145px;"
            >
              <div class="card-body text-light p-2">
                <h6 style="font-size: 0.9rem;">Jumlah Provinsi</h6>
                <h1 style="font-size: 2rem;" id="jumlah_provinsi"></h1>
                <h6 style="font-size: 0.9rem;">Provinsi</h6>
              </div>
            </div>
          </div>
          <div class="col-6 pl-1">
            <div
              class="card border-dark mb-2"
              style="background-color: #33b86c; height: 210px;"
            >
              <div class="card-body text-light p-2" style="overflow-y: auto;">
                <!-- Tampilan legenda -->
                <div style="font-size: 0.9rem;" id="legend"></div>
                <div style="font-size: 0.9rem;" id="satuan"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div
              class="card border-dark mb-2"
              style="background-color: #33b86c; height: 335px;"
            >
              <div class="card-header text-light p-2" style="border-bottom: 1px solid #f8f9fa;">
                <strong style="font-size: 0.9rem;">Deskripsi</strong>
              </div>
              <div class="card-body text-light p-2" style="overflow-y: auto;">
                <div id="description" style="font-size: 0.9rem;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-8 p-2">
        <div class="card border-dark" style="height: 550px">
          <div class="card-body">
            <!-- Tampilan Map -->
            <div id="map"></div>
            <!-- Tampilan info Provinsi -->
            <!-- <div id="info-pane" class="leaflet-bar">Hover to Inspect</div> -->
            <div id="info-pane" class="leaflet-bar"></div>
            <div><span class="badge" id="source-pane">Sumber Data : BPS</span></div>
          </div>
        </div>
      </div>
    </div>

    <script>
      // workaround for old ie
      if (!window.location.origin) {
        window.location.origin =
          window.location.protocol +
          "//" +
          window.location.hostname +
          (window.location.port ? ":" + window.location.port : "");
      }

      // Inisialisasi koordinat awal dan zoom level
      var map = L.map("map").setView([-2.416276, 117.421875], 4);

      // Inisialisasi jenis map
      L.esri.basemapLayer("Gray").addTo(map);

      function serverAuth(callback) {
        L.esri.post(
          "https://geospasial.bappenas.go.id/DotNet/proxy.ashx",
          {
            username: "dit_peppd",
            password: "p3ppdbappen4s",
            f: "json",
            expiration: 86400,
            client: "referer",
            referer: window.location.origin,
          },
          callback
        );
      }

      serverAuth(function (error, response) {
        if (error) {
          return;
        }

        //Memasukan dynamic layer arcgis yang sudah dibuat
        var dynamic_indonesia_map = L.esri
          .dynamicMapLayer({
            url: "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Ratarata_Lama_Sekolah_Provinsi/MapServer",
            opacity: 0.7,
          })
          .addTo(map);

        dynamic_indonesia_map.on("authenticationrequired", function (e) {
          serverAuth(function (error, response) {
            if (error) {
              return;
            }

            e.authenticate(response.token);
          });
        });

        //Memasukan feature layer arcgis yang sudah dibuat
        var feature_indonesia_map = L.esri
          .featureLayer({
            url: "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Ratarata_Lama_Sekolah_Provinsi/FeatureServer/0",
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

        feature_indonesia_map.on("authenticationrequired", function (e) {
          serverAuth(function (error, response) {
            if (error) {
              return;
            }

            e.authenticate(response.token);
          });
        });

        // Inisialisasi variable oldId untuk menampung id provinsi
        var oldId;

        // Apabila mouse tidak menyentuh layer maka
        feature_indonesia_map.on("mouseout", function (e) {
          // Masukan kata Hover to Inspect pada elemen dengan id info-pane
          // document.getElementById("info-pane").innerHTML = "Hover to Inspect";
          document.getElementById("info-pane").innerHTML = "";
          // Menghapus style warna layer
          feature_indonesia_map.resetFeatureStyle(oldId);
        });

        // Apabila mouse menyentuh layer maka
        feature_indonesia_map.on("mouseover", function (e) {
          console.log(e);
          // Masukan id provinsi ke var oldId
          oldId = e.layer.feature.id;
          // Masukan kata Provinsi ke elemen dengan id info-pane
          document.getElementById("info-pane").innerHTML =
            e.layer.feature.properties.wadmpr;
          // Mendefiniskan warna layer pada id provinsi
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
            "<strong>Rata - Rata Lama Sekolah</strong><hr />" +
            "<span>Provinsi : <strong>" +
            layer.feature.properties.wadmpr +
            "</strong></span><br />" +
            "<span>Capaian : <strong>" +
            layer.feature.properties.rls_2020 +
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

        feature_indonesia_map.on("load", function (e) {
          var count_provinsi = Object.keys(e.target._layers).length;
          console.log(count_provinsi);
          document.getElementById("jumlah_provinsi").innerHTML = count_provinsi;
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

        // Mengatur zoom layer pada provinsi
        function zoomTo(
          northEastlat,
          northEastlng,
          southWestlat,
          southWestlng
        ) {
          // Inisialisasi koordinat provinsi
          var latlngs = [
            [northEastlat, northEastlng],
            [southWestlat, southWestlng],
          ];

          // zoom the map to the polygon
          // Masukan koordinat provinsi untuk melakukan zoom
          map.fitBounds(latlngs);
        }
      });
    </script>

    <script>
      // Memanggil API legend
      $.getJSON(
        "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Ratarata_Lama_Sekolah_Provinsi/MapServer/legend?f=pjson",
        function (data) {
          // console.log(data.layers[0]);
          // console.log(data.layers[0].legend.length);
          // console.log(data.layers[0].legend[0]['label']);
          var html = "<strong>" + data.layers[0].layerName + "</strong><hr/>";
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
    </script>
    <script>
      // Memanggil API Description
      $.getJSON(
        "https://geospasial.bappenas.go.id/server/rest/services/Produksi/PEPPD_Ratarata_Lama_Sekolah_Provinsi/FeatureServer?f=pjson",
        function (data) {
          // console.log(data.serviceDescription);
          // let serviceDesc = data.serviceDescription;
          let serviceDesc = data.documentInfo.Comments;

          //cari nomor index kata "Interpretasi"
          let indexInterpretasi1 = serviceDesc.indexOf("Interpretasi");
          //potong(slice) dari kalimat indexInterpretasi1 pada kalimat serviceDesc
          let descSlice1 = serviceDesc.slice(indexInterpretasi1);
          //ubah(replace) dari kalimat descSlice1 menjadi "" pada kalimat serviceDesc
          let descSlice2 = serviceDesc.replace(descSlice1, "");
          //cari nomor index kata (Satuan:
          let indexSatuanStart1 = descSlice1.indexOf("(Satuan :");
          //cari nomor index kata ) dari kata indexSatuanStart1
          let indexSatuanEnd1 = descSlice1.indexOf(")", indexSatuanStart1);
          //potong(slice) dari kalimat indexSatuanStart1 sampai indexSatuanEnd1+1 pada kalimat descSlice1
          let descSatuan1 = descSlice1.slice(indexSatuanStart1, indexSatuanEnd1+1);
          //potong(slice) dari kalimat indexSatuanStart1+1 sampai indexSatuanEnd1 pada kalimat descSlice1
          let descSatuan2 = descSlice1.slice(indexSatuanStart1+1, indexSatuanEnd1);
          //cari nomor index kata Capaian Nasional:
          let indexCapaianStart1 = descSlice1.indexOf("Capaian Nasional:");
          //potong(slice) dari kalimat indexCapaianStart1 pada kalimat descSlice1
          let descCapaian1 = descSlice1.slice(indexCapaianStart1);
          //ubah(replace) dari kalimat descSatuan1 menjadi descSatuan2 pada kalimat descSlice1
          let descReplace1 = descSlice1.replace(descSatuan1, descSatuan2);
          //ubah(replace) dari kalimat descCapaian1 menjadi "" pada kalimat descSlice1
          let descReplace2 = descReplace1.replace(descCapaian1, "");
          //gabung kata descCapaian1 dengan kalimat descReplace2
          let descReplace2Capaian = descCapaian1+"\n\n"+descReplace2;
          //gabung kalimat descSlice2 dengan descReplace2Capaian
          let descAll = descSlice2+descReplace2Capaian
          //masukan(append) kata descSatuan2 ke dalam id #satuan
          $("#satuan").append("<b>"+descSatuan2+"</b><br/>");

          console.log(descSlice1);


          /* let sliceStart1 = serviceDesc.indexOf("(Satuan:");
          let sliceEnd1 = serviceDesc.indexOf(")", sliceStart1);
          let satuanLegend1 = serviceDesc.slice(sliceStart1, sliceEnd1+1);
          let satuanLegend2 = serviceDesc.slice(sliceStart1+1, sliceEnd1);
          let sliceStart2 = serviceDesc.indexOf("Capaian Nasional:");
          let capaianLegend1 = serviceDesc.slice(sliceStart2);
          let sliceStart3 = serviceDesc.indexOf("Interpretasi");
          let satuanLegend1 = serviceDesc.slice(sliceStart1, sliceEnd1+1);
          console.log(sliceStart3);
          serviceDescReplace1 = serviceDesc.replace(satuanLegend1, satuanLegend2);
          serviceDescReplace2 = serviceDescReplace1.replace(capaianLegend1, "");
          $("#satuan").append("<b>"+satuanLegend2+"</b><br/>"); */

          // console.log(data);
          // console.log(data.documentInfo);
          // console.log(data.documentInfo.Comments);
          // console.log(Object.keys(data));
          // var html = '<b>'+Object.keys(data.documentInfo)[0]+'</b> : '+data.documentInfo.Title+'<br/>';
          // html += '<b>'+Object.keys(data.documentInfo)[1]+'</b> : '+data.documentInfo.Author+'<br/>';
          // html += '<b>'+Object.keys(data.documentInfo)[3]+'</b> : '+data.documentInfo.Subject+'<br/>';
          // html += '<b>'+Object.keys(data.documentInfo)[2]+'</b> : '+data.documentInfo.Comments+'<br/>';
          // html += '<b>'+Object.keys(data.documentInfo)[5]+'</b> : '+data.documentInfo.Keywords+'<br/>';
          // html += '<b>Description</b> : '+JSON.stringify(data.serviceDescription)+'<br/>';
          var html = 'Definisi : <br/>'+descAll.replace(/\n/g, "<br />")+'<br/>';
          $("#description").append(html);
        }
      );
    </script>
  </body>
</html>
