
<div class="page-head">
    <div class="container">

        <!-- BEGIN PAGE TITLE -->
        <div class="page-title">
            <h1>Indikator 
                <small></small>
            </h1>
        </div>
        <!-- END PAGE TITLE -->
        <!-- BEGIN PAGE TOOLBAR -->
        <div class="page-toolbar">
            <!-- BEGIN THEME PANEL -->
            <div class="btn-group btn-theme-panel">
                <a href="javascript:;" class="btn dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-settings"></i>
                </a>
                <div class="dropdown-menu theme-panel pull-right dropdown-custom hold-on-click">
                    
                </div>
            </div>
            <!-- END THEME PANEL -->
        </div>
        <!-- END PAGE TOOLBAR -->
    </div>
</div>

<div class="page-content-wrapper">
    <!-- BEGIN CONTENT BODY -->
    <!-- BEGIN PAGE HEAD-->
    <!-- END PAGE HEAD-->
    <!-- BEGIN PAGE CONTENT BODY -->
    <div class="page-content">
        <div class="container">
            <!-- BEGIN PAGE BREADCRUMBS -->
            <ul class="page-breadcrumb breadcrumb">
<!--                            <li>
                    <a href="index.html">Home</a>
                    <i class="fa fa-circle"></i>
                </li>
                <li>
                    <span>Dashboard</span>
                </li>-->
            </ul>
            <!-- END PAGE BREADCRUMBS -->
            <!-- BEGIN PAGE CONTENT INNER -->
           <div class="row list_wrapper">

                <div class="col-md-12">
                    <div class="panel panel-border panel-primary">
                        <div class="panel-heading"> 
                            <h3 class="panel-title"> </h3> 
                        </div> 
                        <div class="panel-body">
                            <table id="data" class="table table-striped table-responsive table-bordered table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase" title="PO No"> No</th>
                                        <th class="text-uppercase">Sasaran Pokok</th>
                                        <th class="text-uppercase">Jenis</th>
                                        <th class="text-uppercase">Chart</th>
                                        <th class="text-uppercase">Satuan</th>
                                        <th class="text-uppercase"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="panel-footer" style="padding-left: 20px">

<!--                            <a href="#" id="addBtn" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> New Data</a>-->

                            <a href="#" class="btn btn-default btn-sm btnRefresh"><i class="fa fa-refresh"></i> Reload</a>

                        </div>

                    </div>
                </div>

            </div>
            <!-- END PAGE CONTENT INNER -->

            <div class="row formadd_wrapper" style="display: none">
                <div class="col-md-12">
                    <div class="panel panel-border panel-primary">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">Indikator </h3> 
                        </div>

                        <form role="form" id="form_add">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Sasaran Pokok</label>
                                            <input type="text" class="form-control" name="judul" placeholder="">
                                        </div>
                                    </div>                        
                                    <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Group Indikator</label>
                                                    <div class='input-group date'>
                                                        <input type="text" id="inp_ind" name="inp_ind" readonly="" class="form-control" placeholder="Search">
                                                            <input type="hidden" id="inp_kid" name="soid" >
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn waves-effect waves-light btn-default plhindk" id="" data-trgt="frmAdd"><i class="fa fa-search"></i></button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                    <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Nama Table</label>
                                                    <div class='input-group date'>
                                                        <input type="text" id="inp_kab" name="inp_kab" readonly="" class="form-control" placeholder="Search">
                                                            <input type="hidden" id="inp_kid" name="soid" >
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn waves-effect waves-light btn-default plhkab" id="" data-trgt="frmAdd"><i class="fa fa-search"></i></button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                    <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Chart</label>
                                                    <div class='input-group date'>
                                                        <input type="text" id="inp_kab" name="inp_kab" readonly="" class="form-control" placeholder="Search">
                                                            <input type="hidden" id="inp_kid" name="soid" >
                                                            <span class="input-group-btn">
                                                                <button type="button" class="btn waves-effect waves-light btn-default plhkab" id="" data-trgt="frmAdd"><i class="fa fa-search"></i></button>
                                                            </span>
                                                    </div>
                                                </div>
                                            </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Satuan</label>
                                            <div class='input-group'>
                                            <div class="">
                                                    <select class="form-control">
                                                        <option value="">Select</option>
                                                            <option value="%">%</option>
                                                            <option value="Rp">Rp</option>
                                                            <option value="Orang">Orang</option>
                                                            <option value="Tahun">Tahun</option>
                                                            <option value="Ton">Ton</option>
                                                            <option value="Industri">Industri</option>
                                                    </select>
                                                    <span class="help-block"></span>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>PPD</label>
                                            <div class='input-group'>
                                            <div class="">
                                                    <select class="form-control">
                                                            <option value="">Ya</option>
                                                            <option value="">Tidak</option>
                                                    </select>
                                                    <span class="help-block"></span>
                                            </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                    <!--            <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" class="form-control" name="ket" placeholder="">
                                        </div>
                                    </div>       
                                </div>
                                <div class="row">
                                    <div class="col-md-4 col-sm-6 col-xs-12 col-lg-4">
                                        <div class="form-group">
                                            <label>Attachment</label>
                                            <input type="file" name="attch" />
                                        </div>
                                    </div>
                                </div>-->
                            </div>
                            <div class="panel-footer">
<!--                                <a href="#" class="btn btn-warning btnBack"><i class="fa fa-arrow-left"></i> Go Back</a>
                                <button type="submit" class="btn btn-primary "><i class="fa fa-check"></i> Save Changes</button>-->
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="modal_indk">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                          <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                          <h4 class="modal-title">Group Indikator</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="idmodal" value="" />
                                
                                    <table id="tblIndikator" class="table table-striped table-responsive table-bordered table-hover" style="width:100%">
                                        <thead>
                                        <th class="text-uppercase" title="id">Id</th>
                                        <th class="text-uppercase" title="Nama Kabupaten">Group</th>
                                            <th class="text-uppercase"></th>
                                        </thead>
                                        <tbody> </tbody>
                                    </table>
                                
                        </div>
                        <div class="modal-footer">
                          <button data-dismiss="modal" class="btn btn-default pull-left" type="button">Close</button>
                          <button type="button"        class="btn btn-default blue" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Save changes</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>

             <div class="row formedit_wrapper" style="display: none">
                <div class="col-md-12">
                    <div class="panel panel-border panel-primary">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">DETAIL PEDOMAN BARU</h3> 
                        </div>
                        <form role="form" id="form_edit">
                            <div class="panel-body">

                                <div class="row">

                                    <div class="col-md-8">

                                        <div class="form-group">

                                            <label>Judul</label>

                                            <input type="text" class="form-control" name="ponumber" readonly="" disabled="">

                                            <input type="hidden" name="id" >

                                        </div>

                                    </div>                        

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>PO Date</label>
                                            <div class='input-group date'>
                                                <input readonly="" value="<?php echo  date('d/m/Y');?>" type="text" class="form-control inp_dp" name="podate" placeholder="Inputkan tanggal ">
                                                <span class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>                        
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-sm-6 col-xs-12 col-lg-4">
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <div class="input-group">
                                                <input type="text" id="inp_supplier" name="supplier" readonly="" class="form-control" placeholder="Search">

                                                <input type="hidden" id="inp_supplierid" name="supplierid" readonly="" class="form-control" placeholder="Search">

                                                <span class="input-group-btn">

                                                    <button type="button" class="btn waves-effect waves-light btn-default btnShwSupp" data-trgt="frmEdit"><i class="fa fa-search"></i></button>

                                                </span>



                                            </div>

                                        </div>

                                    </div>                   

                                    <div class="col-md-8 col-sm-6 col-xs-12 col-lg-8">

                                        <div class="form-group">

                                            <label>Address</label>

                                            <input type="text" class="form-control" id="inp_address" name="address" readonly="" placeholder="">

                                        </div>

                                    </div>           

                                </div>

                            </div>

                            <div class="panel-footer">

                                <a href="#" class="btn btn-warning btnBack"><i class="fa fa-arrow-left"></i> Go Back</a>

                                <button type="submit" class="btn btn-primary "><i class="fa fa-check"></i> Save Changes</button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT BODY -->
    <!-- END CONTENT BODY -->
</div>
            <!-- END CONTENT -->
            <!-- BEGIN QUICK SIDEBAR -->
<a href="javascript:;" class="page-quick-sidebar-toggler">
    <i class="icon-login"></i>
</a>