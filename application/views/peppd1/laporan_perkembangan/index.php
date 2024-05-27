<!-- <div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h2 class="page-title ml-3"><i class=" mdi mdi-file-table-box-multiple-outline "></i> Laporan Perkembangan</h2>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item"></li>

            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div> -->

<div class="container-fluid">
    <!-- <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header" id="div_header">
                    <h3 class="card-title" name="judul"></h3>
                </div>
                <div class="card-body">

                    <form id="form_add" class="form-inline">
                        <div class="input-group">
                            <div class="form-group">
                                <label class="sr-only" for="exampleInputEmail2">Nasional</label>
                                <input type="text" class="form-control plhpro" id="inp_pro" name="inp_pro" placeholder="Pilih Provinsi" readonly="">
                                <input type="hidden" id="inp_proid" name="inp_proid">
                                <span class="input-group-prepend">
                                    <button type="button" class="btn waves-effect waves-light btn-primary plhpro"><i class="fa fa-search"></i></button>
                                </span>
                            </div>

                            <div class="form-group ml-2">
                                <label class="sr-only" for="exampleInputPassword2">Pilih Kabupaten/Kota</label>
                                <input type="text" class="form-control plhkab" id="inp_kab" name="inp_kab" placeholder="Kabupaten/Kota" readonly="">
                                <input type="hidden" id="inp_kid" name="inp_kid">
                                <span class="input-group-prepend">
                                    <button type="button" class="btn waves-effect waves-light btn-primary plhkab" id="btnFilter"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                            <div class="form-group ml-2">
                                <button type="submit" class="btn btn-block btn-primary waves-effect waves-light nosatu" id="btnFilter"><i class="mdi mdi-file-pdf-box-outline"></i>&nbsp;PDF</button>
                            </div>
                            <div class="form-group ml-2">
                                <button type="button" class="btn btn-primary waves-effect waves-light nodua"><i class=" mdi mdi-file-word-box-outline "></i>&nbsp;WORD</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div> -->

    <div class="row" id="tab-search-laporan-perkembangan" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available; border-bottom: 2px solid #B8A5A5;">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px; text-align: left;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white;">
                    <h4 class="card-title" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem; color: black;">Laporan Perkembangan Pencapaian Indikator Makro Pembangunan</h4>
                </div>
                <!-- <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item" style="color: black;"><a href="#">Pencapaian</a></li>
                    <li class="breadcrumb-item active" style="color: black;">Laporan Perkembangan</li>
                </ol> -->
            </div>
            <form class="form" id="form_add" style="display: grid; justify-content: center; margin-bottom: 10px;">
                <div class="input-group">
                    <div class="row">
                        <div class="col-12" style="display: flex;">
                            <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Daerah yang dipilih hanya satu</span>
                                </p>
                                <!-- <select class="form-controlselect2-multiple select2-hidden-accessible" data-toggle="select2" multiple="" data-placeholder="Choose a Country..." data-select2-id="4" tabindex="-1" aria-hidden="true"> -->
                                <select class="selectpicker form-control plhpro" id="inp_proid" name="inp_proid" multiple="multiple" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="1" data-width="420px">
                                    <!-- <option>Pilih Daerah</option> -->
                                </select>

                                <button type="button" class="btn btn-primary nodua"><i class="mdi mdi-file-word-box-outline " aria-hidden="true"></i> Word</button>
                            </div>
                            <!-- <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Tahun dapat di pilih lebih dari satu</span>
                                </p>
                                <select class="selectpicker form-control" id="selectyear" name="year[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-width="420px">
                                </select>
                            </div> -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>


<div class="page-content-wrapper">
    <div class="page-content">
        <div class="container">
            <div class="row list_wrapper">
                <div class="col-md-12">
                    <div class="portlet box blue-hoki ">
                        <div class="portlet-title">
                            <div class="caption"><i class="fa "></i></div>
                            <div class="tools">
                                <a href="" class="collapse" data-original-title="" title=""> </a>
                                <a href="" class="reload" data-original-title="" title=""> </a>
                                <!--                <a href="" class="remove" data-original-title="" title=""> </a>-->
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <div class="row laporan-perkembangan-initial" style="margin-top: 230px; justify-content: center;">
                                <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%;">
                                    <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                    <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, dan Tahun pada Form diatas</strong> -</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="frmDokAdd">
    <div id="mdl_dok_add" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dokumen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-3" class="control-label">Nama Dokumen<span class="text-danger">*</span></label>
                                <span class="text-info" style='font-size: 10px'>Format: Nama Dokumen (spasi) Tahun (spasi) Daerah, </span>
                                <span class="text-info" style='font-size: 10px'>Contoh : RKPD 2020 Provinsi A</span>
                                <input type="text" class="form-control" id="field-3" name="nama" placeholder="" required="">
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-3" class="control-label">File Dokumen<span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="field-3" name="attch" placeholder="" required="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress">
                                <div class="progress-bar"></div>
                            </div>
                            <div id="uploadStatus"></div>


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


<div class="modal fade" id="mdlPro">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Provinsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <!--                    <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">-->
                <table id="tblPro" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="" title="id">Id</th>
                            <th class="">Nama Provinsi</th>
                            <th>Label</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default pull-left" type="button">Batal</button>
                <button type="button" class="btn btn-info waves-effect waves-light blue" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal_kab">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <table id="tblKab" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="" title="id">Id</th>
                            <th class="">Nama Kab/Kota</th>

                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default pull-left" type="button">Batal</button>
                <button type="button" class="btn btn-default blue" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<!--
<div class="modal fade" id="modal_kab">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
              <button aria-label="Close" data-dismiss="modal" class="close" type="button"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Pilih Kabupaten</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idmodal" value="" />
                    <div class="row">
                        <table id="tblKab" class="table table-striped table-responsive table-bordered table-hover" style="width:100%">
                            <thead>
                            <th class="text-uppercase" title="id">Id</th>
                            <th class="text-uppercase" title="Nama Kabupaten">Nama Kabupaten</th>
                                <th class="text-uppercase"></th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
            </div>
            <div class="modal-footer">
              <button data-dismiss="modal" class="btn btn-default pull-left" type="button">Close</button>
              <button type="button"        class="btn btn-default blue" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Save changes</button>
            </div>
        </div> /.modal-content 
    </div> /.modal-dialog 
</div>-->