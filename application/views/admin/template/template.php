<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $tag_title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Responsive bootstrap 4 admin template" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url("assets") ?>/images/favicon_bappenas_2023.ico" />

    <!-- Plugins css-->
    <link href="<?php echo base_url(); ?>/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>/assets/libs/dropzone/dropzone.min.css" rel="stylesheet" type="text/css" />

    <!-- Table datatable css -->
    <link href="<?php echo base_url(); ?>/assets/libs/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>/assets/libs/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- <link href="<?php echo base_url(); ?>/assets/libs/datatables/fixedHeader.bootstrap4.min.css" rel="stylesheet" type="text/css" /> -->
    <link href="<?php echo base_url(); ?>/assets/libs/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>/assets/libs/datatables/scroller.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="<?php echo base_url(); ?>/assets/css/bootstrap.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="<?php echo base_url(); ?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>/assets/css/app.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

    <!-- <script src="<?php echo base_url("package/") ?>js/modernizr.min.js"></script> -->
    <!-- Notification css (Toastr) -->
    <link href="<?php echo base_url(); ?>/assets/libs/toastr/toastr.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url(); ?>/assets/css/userdefined.css?v=<?php echo now("Asia/Jakarta") ?>" rel="stylesheet" type="text/css" />

    <!-- Custom style -->
    <link href="<?php echo base_url(); ?>/assets/custom/css/style.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

    <!-- Custom script -->
    <!-- <script src="<?php echo base_url(); ?>/assets/custom/js/script.js"></script> -->

    <!-- select2 style -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- bootstrap-select -->
    <!-- <link href="<?php echo base_url(); ?>/assets/css/bootstrap-select.min.css" rel="stylesheet" /> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">

    <script src="https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.css" rel="stylesheet" />

    <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=fetch,requestAnimationFrame,Element.prototype.classList,URL"></script>
    <style>
        /*      .gmaps {
        width: 100%;
        height:450px;
      }*/
        /* #map { position: absolute; top: 0; bottom: 0; width: 100%; height:450px;} */
        #map {
            top: 0;
            bottom: 0;
            width: 100%;
            height: 350px;
        }
    </style>

    <!-- <style>
        .selectpicker {
            -webkit-appearance: menulist;
            /*webkit browsers */
            -moz-appearance: none;
            /*Firefox */
            appearance: none;
            /* modern browsers */
            border-radius: 0;
        }
    </style> -->

    <style>
        .btn-custom-selectpicker {
            color: black !important;
            background-color: white;
            border-color: black;
        }

        .btn-custom-selectpicker:hover {
            color: white !important;
            background-color: #33b86c;
            border-color: white;
        }
    </style>

    <style>
        .button-export-tabel-indikator-makro {
            padding-left: 0px;
            position: fixed;
            right: 15px;
            margin-top: 0px;
        }

        .button-excel-tabel-indikator-makro {
            background-color: white;
            color: black;
            border: 1px solid black;
        }

        .button-excel-tabel-indikator-makro:hover {
            background-color: #33b86c;
            color: white;
            border: 1px solid white;
        }

        .button-export-tabel-apbd {
            padding-left: 0px;
            position: fixed;
            right: 15px;
            margin-top: 0px;
        }

        .button-excel-tabel-apbd {
            background-color: white;
            color: black;
            border: 1px solid black;
        }

        .button-excel-tabel-apbd:hover {
            background-color: #33b86c;
            color: white;
            border: 1px solid white;
        }
    </style>

    <style>
        .nav-tabs .nav-link.active {
            color: black;
            background-color: #B8A5A5;
        }

        .nav-tabs .nav-link:hover {
            color: black;
            background-color: #C5C2C2;
            border-bottom: 2px solid black;
        }

        .nav-tabs .nav-link {
            border-top-left-radius: 0rem;
            border-top-right-radius: 0rem;
        }
    </style>

    <style>
        .card-evaluasi-kinerja {
            background-color: transparent;
            border: 1px solid black;
            border-radius: 10px;
            border-style: dashed;
        }

        .card-evaluasi-kinerja:hover {
            background-color: white;
        }

        .box-form-evaluasi-kinerja {
            display: flex;
            opacity: 50%;
        }

        .box-form-evaluasi-kinerja:hover {
            opacity: 100%;
        }
    </style>
</head>

<!-- <body class="enlarged" data-keep-enlarged="true" style="min-height: 0 !important;"> -->

<body class="sidebar-enable" data-keep-enlarged="true" style="min-height: 0 !important;">
    <style>
        /*        .legend {
        background-color: #fff;
        border-radius: 3px;
        bottom: 30px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
        padding: 10px;
        position: absolute;
        right: 10px;
        z-index: 1;
        }*/
        .legend {
            position: absolute;
            width: 20%;
            top: 250px;
            /* right: 10px; */
            padding: 5px;
            z-index: 1;
            background-color: #fff;
        }

        .legend h4 {
            margin: 0 0 10px;
        }

        .legend div span {
            border-radius: 50%;
            display: inline-block;
            height: 10px;
            margin-right: 5px;
            width: 10px;
        }

        .mapLinkContainer {
            display: block;
            position: relative;
            margin: 0px auto;
            width: 50%;
            height: 40px;
            padding: 10px;
            border: none;
            border-radius: 3px;
            font-size: 12px;
            text-align: center;
            color: #fff;
            background: #ee8a65;

        }

        .map-overlay-container {
            position: absolute;
            width: 20%;
            top: 60px;
            right: 55px;
            padding: 5px;
            z-index: 1;
            opacity: 0.6;
        }

        .map-overlay {
            font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
            background-color: #fff;
            border-radius: 3px;
            padding: 10px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .map-overlay h2,
        .map-overlay p {
            margin: 0 0 10px;
        }
    </style>

    <div class="se-pre-con"></div>
    <div class="spinner">

        <div class="rect1"></div>

        <div class="rect2"></div>

        <div class="rect3"></div>

        <div class="rect4"></div>

        <div class="rect5"></div>

    </div>
    <input type="hidden" id="csrf" value="<?php echo $csrf["hash"] ?>" />
    <!-- Begin page -->
    <div id="wrapper">


        <!-- Topbar Start -->
        <?php
        $this->load->view("admin/template/header");
        ?>
        <!-- end Topbar -->

        <!-- ========== Left Sidebar Start ========== -->
        <?php $this->load->view($sidebar); ?>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">

                <!-- Start Content-->
                <div class="container-fluid">

                    <!--Widget-4 -->
                    <div id="content_wrapper">
                        <?php
                        $this->load->view($main_content);
                        ?>
                        <!-- end row -->
                    </div>

                </div>
                <!-- end container-fluid -->

            </div>
            <!-- end content -->

            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            Copyright 2023 © <b>|Direktorat PEPPD - Bappenas
                        </div>
                    </div>
                </div>
            </footer>
            <!-- end Footer -->

        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->





    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!--        <a href="javascript:void(0);" class="right-bar-toggle demos-show-btn">
            <i class="mdi mdi-settings-outline mdi-spin"></i> &nbsp;Choose Demos
        </a>-->
    <!--CHANGE PASSWORD MODAL s-->
    <form id="frmChaPass">
        <div id="modal_change_password" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="card card-color mb-0">
                        <div class="card-header bg-primary">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                            <h3 class="card-title text-white mt-1 mb-0">Ubah Password</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-12 mb-3 alert alert-info mb-0" style="border-radius: 0px;">
                                    <h6 class="text-muted" style="text-align: center;">Password harus lebih dari <b style="color: #8d8d8d;">6 karakter</b>, mengandung huruf <b style="color: #8d8d8d;">BESAR</b>, huruf <b style="color: #8d8d8d;">kecil</b> dan <b style="color: #8d8d8d;">angka</b></h6>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">

                                        <label for="alo">Password saat ini</label>
                                        <div class="input-group">
                                            <input name="opass" class="form-control saatini" type="password" autocomplete="off" />
                                            <div class="input-group-append">
                                                <button class=" btn-info input-group-text  pIni" type="button"><i class="mdi mdi-eye-minus-outline"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">

                                        <label for="alo">Password baru</label>
                                        <div class="input-group">
                                            <input name="npass" class="form-control saatbaru" type="password" id="new_password" placeholder="Password harus lebih dari 6 karakter, mengandung huruf BESAR, huruf kecil dan angka" />
                                            <div class="input-group-append">
                                                <button class=" btn-info input-group-text  pBaru" type="button"><i class="mdi mdi-eye-minus-outline"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">

                                        <label for="alo">Ulangi Password baru </label>
                                        <div class="input-group">
                                            <input name="cpass" class="form-control saatulang" type="password" placeholder="Password harus lebih dari 6 karakter, mengandung huruf BESAR, huruf kecil dan angka" />
                                            <div class="input-group-append">
                                                <button class=" btn-info input-group-text  pUlang" type="button"><i class="mdi mdi-eye-minus-outline"></i></button>
                                            </div>
                                        </div>


                                    </div>
                                </div>

                            </div>


                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal" style="border-radius: 0px; padding-left: 10px; padding-right: 10px;"><i class="fas fa-times"></i>&nbsp;Batal</button>
                            <button type="submit" class="btn btn-info waves-effect waves-light" style="border-radius: 0px; padding-left: 10px; padding-right: 10px; float: right;"><i class="fas fa-save"></i>&nbsp;Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.modal -->
    </form>
    <!--CHANGE PASSWORD MODAL e-->

    <script>
        var resizefunc = [];
    </script>
    <!-- Vendor js -->
    <script src="<?php echo base_url("assets") ?>/js/vendor.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/moment/moment.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/sweetalert2/sweetalert2.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/jquery-validation/jquery.validate.min.js"></script>
    <!-- third party js -->
    <script src="<?php echo base_url("assets") ?>/libs/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/responsive.bootstrap4.min.js"></script>

    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/buttons.bootstrap4.min.js"></script>

    <script src="<?php echo base_url("assets") ?>/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/pdfmake/pdfmake.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/pdfmake/vfs_fonts.js"></script>

    <script src="<?php echo base_url("assets") ?>/libs/datatables/buttons.html5.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/buttons.print.min.js"></script>

    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.fixedheader.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.keyTable.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/datatables/dataTables.scroller.min.js"></script>

    <script src="<?php echo base_url(); ?>/package/plugins/jquery-validation-1.15.0/dist/jquery.validate.min.js"></script>
    <script src="<?php echo base_url(); ?>/package/plugins/jquery-validation-1.15.0/dist/additional-methods.min.js"></script>
    <script src="<?php echo base_url(); ?>/package/plugins/jquery-validation-1.15.0/dist/localization/messages_id.min.js"></script>
    <!-- Datatables init -->
    <script src="<?php echo base_url("assets") ?>/js/pages/datatables.init.js"></script>
    <!-- Responsive Table js -->
    <script src="<?php echo base_url("assets") ?>/libs/rwd-table/rwd-table.min.js"></script>

    <!-- Chat app -->
    <script src="<?php echo base_url("assets") ?>/js/pages/jquery.chat.js"></script>

    <!-- Todo app -->
    <script src="<?php echo base_url("assets") ?>/js/pages/jquery.todo.js"></script>
    <!-- Toastr js -->
    <script src="<?php echo base_url("assets/") ?>libs/toastr/toastr.min.js"></script>
    <script src="<?php echo base_url("assets/") ?>libs/bootbox/bootbox.all.min.js"></script>

    <!-- flot chart -->
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.time.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.tooltip.min.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.resize.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.pie.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.selection.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.stack.js"></script>
    <script src="<?php echo base_url("assets") ?>/libs/flot-charts/jquery.flot.crosshair.js"></script>


    <script src="<?php echo base_url("assets"); ?>/js/highcharts/highcharts.js"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/exporting.js?v=<?php echo now("Asia/Jakarta") ?>"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/export-data.js?v=<?php echo now("Asia/Jakarta") ?>"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/offline-exporting.js"></script>


    <script src="<?php echo base_url("assets"); ?>/js/highcharts/highcharts-more.js"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/accessibility.js"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/annotations.js"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/drilldown.js"></script>
    <script src="<?php echo base_url("assets"); ?>/js/highcharts/modules/sunburst.js"></script>



    <script class="js_path" src="<?php echo $js_path ?>"></script>
    <!-- App js -->
    <script src="<?php echo base_url("assets") ?>/js/app.min.js"></script>
    <!-- Toastr js -->
    <script src="<?php echo base_url("assets/") ?>libs/toastr/toastr.min.js"></script>
    <script src="<?php echo base_url("assets/") ?>libs/bootbox/bootbox.all.min.js"></script>

    <!-- select2 js -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- bootstrap-select -->
    <!-- <script src="<?php echo base_url("assets") ?>/js/pages/bootstrap-select.min.js"></script> -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>

    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/i18n/defaults-*.min.js"></script>

    <!--USERDEFINED-->
    <script src="<?php echo base_url("assets/") ?>js/universal.js?v=<?php echo now("Asia/Jakarta") ?>"></script>

    <!-- new javascript added -->
    <script src="https://cdn.datatables.net/fixedcolumns/4.2.2/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.3.2/js/dataTables.fixedHeader.min.js"></script>
    <script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"></script>


    <script type="text/javascript" class="js_initial">
        <?php
        if (isset($js_init))
            echo $js_init;
        ?>
    </script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $(".se-pre-con").fadeOut("slow");;
        });
    </script>

</body>

</html>