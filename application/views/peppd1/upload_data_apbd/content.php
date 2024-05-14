<div class="container-fluid">

    <div class="row" id="tab-search-apbd" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available; border-bottom: 2px solid #B8A5A5;">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white">
                    <h4 class="card-title text-black" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem;">Upload Data APBD</h4>
                </div>
                <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item" style="color: black;"><a href="#">Data Tabel</a></li>
                    <li class="breadcrumb-item active" style="color: black;">Upload Data APBD</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-12" style="margin-top: 140px;">
            <!-- <form action="./my-upload-url" class="dropzone dz-clickable" id="myAwesomeDropzone">
                <div class="dz-message needsclick">
                    <i class="h1 text-muted fas fa-upload mb-4"></i>
                    <h4>Drop files here or click to upload.</h4>
                    <span class="text-muted font-13">
                        (This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)
                    </span>
                </div>
            </form> -->
            <!-- card -->

            <a href="<?= base_url(); ?>attachments_apbd/contohtemplate_apbd/contoh_template_apbd.xlsx" target='_blank' class="btn btn-primary mb-1 mr-1"><i class="fa fa-download" aria-hidden="true"></i> Download File Template APBD</a>

            <div class="card" style="border: 1px solid black; border-style: dashed">
                <div class="card-body" style="text-align-last: center;">

                    <form id="form_cari" role="form">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>File APBD</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input type="file" name="attch" />
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>atau Drop File disini</label>
                                    </div>
                                </div>
                            </div> -->

                            <div class="panel-footer">
                                <button type="submit" class="btn btn-primary "><i class="fa fa-check"></i> Simpan</button>
                            </div>
                        </div>
                    </form>

                </div>

                <!-- card-body -->
            </div>
            <!-- /card -->
        </div>
        <!-- col -->
    </div>

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