<!--<div class="row formadd_wrapper" >
        <div class="col-md-12">
            <div class="card">
            <div class="panel panel-border panel-primary">
                <div class="panel-heading"> 
                    <h3 class="panel-title">NEW PURCHASE ORDER</h3> 
                </div>

                <form role="form" id="form_add">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>PO Number</label>
                                    <input type="text" class="form-control" name="ponumber" placeholder="">
                                </div>
                            </div>                        
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>PO Date</label>
                                    <div class='input-group date'>
                                        <input readonly="" value="<?php echo  date('d/m/Y'); ?>" type="text" class="form-control inp_dp" name="podate" placeholder="Inputkan tanggal ">
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
                                            <button type="button" class="btn waves-effect waves-light btn-default btnShwSupp" id="" data-trgt="frmAdd"><i class="fa fa-search"></i></button>
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
                        <div class="row">
                            <div class="col-md-4 col-sm-6 col-xs-12 col-lg-4">
                                <div class="form-group">
                                    <label>Attachment</label>
                                    <input type="file" name="attch" />
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
    </div>    -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" style='font-size: 12px'>Upload Data Indikator</h3>
            </div>
            <div class="card-body">

                <form id="form_cari" role="form">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-6 col-xs-12 col-lg-12">
                                <div class="form-group">
                                    <label>Attachment</label>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-6 col-xs-12 col-lg-12">
                                <div class="form-group">

                                    <input type="file" name="attch" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">

                        </div>

                        <div class="form-group ml-2">

                        </div>

                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary "><i class="fa fa-check"></i> Simpan</button>
                            <!--                                <button type="button" class="btn btn-primary waves-effect waves-light nodua" ><i class=" ion ion-md-stats "></i></button>-->
                            <!--                                                    <button type="button" class="btn btn-primary waves-effect waves-light"><i class="far fa-trash-alt"></i></button>-->
                        </div>
                    </div>


                    <!--                                            <button type="submit" class="btn btn-success waves-effect waves-light ml-2">Download PDF</button>-->
                </form>

            </div>

            <!-- card-body -->
        </div>
        <!-- card -->
    </div>
    <!-- col -->
</div>

<div class="row listdata" style="display: none;">
    <div class="col-12">
        <form class="" id="form_download">
            <input type="hidden" id="inp_wl" name="inp_wl">
            <input type="hidden" id="inp_in" name="inp_in">
            <div class="form-group ml-2">
                <button type="submit" class="btn btn-success waves-effect waves-light ml-2 btnBack">Excel</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="center" style="font-size: 12px">Tahun</th>
                        <th style="font-size: 12px">Periode</th>
                        <th style="font-size: 12px">Nilai</th>
                        <th style="font-size: 12px">Nasional</th>
                        <th style="font-size: 12px">Target</th>
                        <th class="center" style="font-size: 12px">Target Makro RPJMN</th>
                        <th style="font-size: 12px">Target RKPD</th>
                        <th class="center" style="font-size: 12px">Target Kewilayahan RKP</th>
                        <th style="font-size: 12px">Satuan</th>
                        <th style="font-size: 12px">Versi</th>
                    </tr>
                </thead>
                <tbody class="table_data">

                </tbody>
            </table>
        </div>

    </div>
</div>

<div id="mdlPro" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Wilayah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">
                    <table id="tblPro" class="table table-small-font table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-uppercase" title="id">Id</th>
                                <th class="text-uppercase">Nama </th>
                                <th class="text-uppercase" title="label"></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Batal</button>
                <!--                                                        <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup">Pilih</button>-->
                <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
            </div>
        </div>
    </div>
</div>

<div id="mdlind" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Pilih Indikator</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <table id="tblInd" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-uppercase" title="id">Id</th>
                            <th class="text-uppercase">Nama </th>
                            <th class="text-uppercase" title="label"></th>
                            <!--                                                <th></th>-->
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
            </div>
        </div>
    </div>
</div>