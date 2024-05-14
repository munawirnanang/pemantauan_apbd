<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title ml-3"> </h4>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item">Data Tabel </li>
                <li class="breadcrumb-item active" aria-current="page">Data Indikator</li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title" style='font-size: 12px'></h3></div>
                <div class="card-body">

                    <form class="form-inline" id="form_cari">
                        <div class="input-group">
                            <div class="form-group">
                                    <label class="sr-only" for="exampleInputEmail2">Wilayah</label>
                                    <input type="text" class="form-control input-sm inppro" id="inp_pro" name="inp_pro" placeholder="Wilayah" readonly=''>
                                    <input type="hidden" id="inp_proid" name="inp_proid" >
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn waves-effect waves-light btn-primary plhpro"><i class="fa fa-search"></i></button>
                                    </span>
                            </div>

                            <div class="form-group ml-2">
                                <label class="sr-only" for="exampleInputPassword2">Indikator</label>
                                <input type="hidden" id="inp_idind" name="inp_idind" >
                                <input type="text" class="form-control input-sm inpkab" id="inp_kab" name="inp_kab" placeholder="Indikator" readonly=''>
                                <span class="input-group-prepend">
                                            <button type="button" class="btn waves-effect waves-light btn-primary plhkab"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                            <div class="form-group ml-2">
                                <button type="submit" class="btn btn-success waves-effect waves-light ml-2">Cari</button>
                            </div>
                            <div class="form-group ml-2">
                                <button type="button" class="btn btn-success waves-effect waves-light ml-2 btnBack">Excel</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- card-body -->
            </div>
            <!-- card -->
        </div>
        <!-- col -->
    </div>

    <div class="row _wrapper_bahan cardtabel" style="display: none;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">List Data</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <input type="hidden" id="inp_wlyh" />
                        <table class="table table-condensed table-bordered table-striped" id="t_bahan">
                            <thead>
                                <tr>
                                    <th class="" title="Wilayah" style="">Wilayah</th>
                                    <th class="" title="Indikator" style="">Indikator</th>
                                    <th class="" title="Tahun" style="">Tahun</th>
                                    <th class="text">Periode</th>
                                    <th class="text">Nilai</th>
                                    <th class="text">Nasional</th>
                                    <th class="text">Target</th>
                                    <th class="text">Target Makro RPJMN</th>
                                    <th class="text">Target RKPD</th>
                                    <th class="text">Target Kewilayahan RKP</th>
                                    <th class="text">Satuan</th>
                                    <th class="text">Versi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    
    
  
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
                                                        <input type="hidden" class="frmTrgt" value=""/>
                                    <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">
                                    <table id="tblPro" class="table table-small-font table-bordered table-striped" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase" title="id">Id</th>
                                                <th class="text-uppercase">Wilayah </th>
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
            <h5 class="modal-title">Indikator</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <input type="hidden" class="frmTrgt" value=""/>
        <table id="tblInd" class="table table-small-font table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th class="text-uppercase" title="id">Id</th>
                    <th class="text-uppercase">Nama </th>
                    <th class="text-uppercase" title="label"></th>
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
<!--
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
                            <input type="hidden" class="frmTrgt" value=""/>
        <table id="tblInd" class="table table-small-font table-bordered table-striped" style="width:100%">
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
                            <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
                        </div>
                    </div>
                </div>
</div>
-->
