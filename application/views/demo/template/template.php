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

         <!-- Plugins css-->
        <link href="<?php echo base_url();?>/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url();?>/assets/libs/dropzone/dropzone.min.css" rel="stylesheet" type="text/css" />
       
        <!-- Table datatable css -->
        <link href="<?php echo base_url();?>/assets/libs/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url();?>/assets/libs/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<!--        <link href="<?php echo base_url();?>/assets/libs/datatables/fixedHeader.bootstrap4.min.css" rel="stylesheet" type="text/css" />-->
        <link href="<?php echo base_url();?>/assets/libs/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url();?>/assets/libs/datatables/scroller.bootstrap4.min.css" rel="stylesheet" type="text/css" />
     <!-- App css -->
        <link href="<?php echo base_url();?>/assets/css/bootstrap.css" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
        <link href="<?php echo base_url();?>/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url();?>/assets/css/app.css" rel="stylesheet" type="text/css" id="app-stylesheet" />
        
<!--        <script src="<?php echo base_url("package/")?>js/modernizr.min.js"></script>-->
         <!-- Notification css (Toastr) -->
        <link href="<?php echo base_url();?>/assets/libs/toastr/toastr.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url();?>/assets/css/userdefined.css?v=<?php echo now("Asia/Jakarta")?>" rel="stylesheet" type="text/css" />
        
        <!-- Custom style -->
        <link href="<?php echo base_url();?>/assets/custom/css/style.css" rel="stylesheet" type="text/css" id="app-stylesheet" />

        <!-- Custom script -->
        <!-- <script src="<?php echo base_url();?>/assets/custom/js/script.js"></script> -->
        
        <script src="https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.js"></script>
        <link href="https://api.mapbox.com/mapbox-gl-js/v1.11.0/mapbox-gl.css" rel="stylesheet" />
                
        <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=fetch,requestAnimationFrame,Element.prototype.classList,URL"></script>
        <style>
/*      .gmaps {
        width: 100%;
        height:450px;
      }*/
	/* #map { position: absolute; top: 0; bottom: 0; width: 100%; height:450px;} */
	#map { top: 0; bottom: 0; width: 100%; height:350px;}
      
    </style>
    </head>

        <body class="enlarged" data-keep-enlarged="true" style="min-height: 0 !important;">
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
            <input type="hidden" id="csrf" value="<?php echo $csrf["hash"]?>"/>
        <!-- Begin page -->
        <div id="wrapper">

            
            <!-- Topbar Start -->
            <?php                
                        $this->load->view("demo/template/header_demo");
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
                                Copyright 2021 © <b>|Direktorat PEPPD - Bappenas
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
            <div id="modal_change_password" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ubah Password</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                
                    <div class="col-md-12 alert alert-info mb-0 fade show _wrapper_statement">
                        <h5 class="text-info"><h6>Password harus lebih dari 6 karakter, mengandung huruf BESAR, huruf kecil dan angka</h6><p class=""></p></h5>
                        
                    </div>
                                <div class="col-md-12">
                                     <div class="form-group">
                                         
                                         <label for="alo">Password saat ini</label>
                                                <div class="input-group">
                                                <input name="opass" class="form-control saatini" type="password" autocomplete="off"/>
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
                                                <input name="npass" class="form-control saatbaru" type="password" id="new_password"/>
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
                                                    <input name="cpass" class="form-control saatulang" type="password"/>
                                                    <div class="input-group-append">
                                                        <button class=" btn-info input-group-text  pUlang" type="button"><i class="mdi mdi-eye-minus-outline"></i></button>
                                                 </div>
                                                </div>
                                                
                                            
                                    </div>
                                </div>
                            
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal"><i class="fas fa-times"></i>&nbsp;Batal</button>
                            <button type="submit" class="btn btn-info waves-effect waves-light"><i class="fas fa-save"></i>&nbsp;Simpan</button>
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
        <script src="<?php echo base_url("assets")?>/js/vendor.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/moment/moment.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/jquery-scrollto/jquery.scrollTo.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/sweetalert2/sweetalert2.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/jquery-validation/jquery.validate.min.js"></script>
        <!-- third party js -->
        <script src="<?php echo base_url("assets")?>/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.bootstrap4.min.js"></script>
        
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/responsive.bootstrap4.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.bootstrap4.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/pdfmake/pdfmake.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/pdfmake/vfs_fonts.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.html5.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.print.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.fixedheader.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.scroller.min.js"></script>
        
        <script src="<?php echo base_url();?>/package/plugins/jquery-validation-1.15.0/dist/jquery.validate.min.js"></script>
        <script src="<?php echo base_url();?>/package/plugins/jquery-validation-1.15.0/dist/additional-methods.min.js"></script>
        <script src="<?php echo base_url();?>/package/plugins/jquery-validation-1.15.0/dist/localization/messages_id.min.js"></script>
        <!-- Datatables init -->
        <script src="<?php echo base_url("assets")?>/js/pages/datatables.init.js"></script>
        <!-- Responsive Table js -->
        <script src="<?php echo base_url("assets")?>/libs/rwd-table/rwd-table.min.js"></script>
        
        <!-- Chat app -->
        <script src="<?php echo base_url("assets")?>/js/pages/jquery.chat.js"></script>

        <!-- Todo app -->
        <script src="<?php echo base_url("assets")?>/js/pages/jquery.todo.js"></script>
<!-- Toastr js -->
        <script src="<?php echo base_url("assets/")?>libs/toastr/toastr.min.js"></script>
        <script src="<?php echo base_url("assets/")?>libs/bootbox/bootbox.all.min.js"></script>
        
        <!-- flot chart -->
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.time.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.tooltip.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.resize.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.pie.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.selection.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.stack.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/flot-charts/jquery.flot.crosshair.js"></script>

         <!-- third party js -->
<!--        <script src="<?php echo base_url("assets")?>/libs/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.bootstrap4.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.responsive.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/responsive.bootstrap4.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.buttons.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.bootstrap4.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/pdfmake/pdfmake.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/pdfmake/vfs_fonts.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.html5.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/buttons.print.min.js"></script>

        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.keyTable.min.js"></script>
        <script src="<?php echo base_url("assets")?>/libs/datatables/dataTables.scroller.min.js"></script>
        
         
       <script src="<?php echo base_url();?>/package/plugins/jquery-validation-1.15.0/dist/jquery.validate.min.js"></script>

        <script src="<?php echo base_url();?>/package/plugins/jquery-validation-1.15.0/dist/additional-methods.min.js"></script>-->



        <!-- Datatables init -->
<!--        <script src="<?php echo base_url("assets")?>/js/pages/datatables.init.js"></script>-->
        <!-- Responsive Table js -->
<!--        <script src="<?php echo base_url("assets")?>/libs/rwd-table/rwd-table.min.js"></script>-->

        <!-- Init js -->
<!--        <script src="<?php echo base_url("assets")?>/js/pages/responsive-table.init.js"></script>-->
        <!-- Dashboard init JS -->
<!--        <script src="<?php echo base_url("assets")?>/js/pages/dashboard.init.js"></script>-->
        
        
        <script src="<?php echo base_url("assets");?>/js/highcharts/highcharts.js"></script>
        <script src="<?php echo base_url("assets");?>/js/highcharts/modules/exporting.js"></script>
        <script src="<?php echo base_url("assets");?>/js/highcharts/modules/offline-exporting.js"></script>
<!--       <script src="<?php echo base_url("assets");?>/js/Highcharts/modules/export-data.js"></script>        -->
       
        <script src="<?php echo base_url("assets");?>/js/highcharts/highcharts-more.js"></script>
<!--        <script src="<?php echo base_url("assets");?>/js/highcharts/modules/exporting.js"></script>-->
        <script src="<?php echo base_url("assets");?>/js/highcharts/modules/accessibility.js"></script>
        
<!--<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
<script src="https://code.highcharts.com/mapdata/countries/id/id-all.js"></script>-->


        
        <script class="js_path" src="<?php echo $js_path?>"></script>
        <!-- App js -->
        <script src="<?php echo base_url("assets")?>/js/app.min.js"></script>
          <!-- Toastr js -->
        <script src="<?php echo base_url("assets/")?>libs/toastr/toastr.min.js"></script>
        <script src="<?php echo base_url("assets/")?>libs/bootbox/bootbox.all.min.js"></script>
                
                <!--USERDEFINED-->
        <script src="<?php echo base_url("assets/")?>js/universal.js?v=<?php echo now("Asia/Jakarta")?>"></script>
        
        
        <script type="text/javascript" class="js_initial">

            <?php
            if(isset($js_init))
                echo $js_init;
            ?>

        </script>
        <script type="text/javascript">            
            $(window).on('load', function(){ $(".se-pre-con").fadeOut("slow");;});
        </script>

    </body>

</html>
