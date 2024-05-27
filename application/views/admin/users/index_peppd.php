<div class="container-fluid">
    <div class="row" id="tab-search-user-peppd" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available; border-bottom: 2px solid #B8A5A5;">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white">
                    <h4 class="card-title text-black" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem;">Tim PEPPD</h4>
                </div>
                <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item" style="color: black;"><a href="#">Manajemen User</a></li>
                    <li class="breadcrumb-item active" style="color: black;">Tim PEPPD</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row _list_user">

        <div class="col-lg-12" style="margin-top: 100px;">
            <a href="#" id="modal_add_show" class="btn btn-primary waves-effect waves-light my-1"><i class="fa fa-plus"></i> User Baru</a>
            <div class="card" style="border: 1px solid black; box-shadow: 0px 0px 0px 0px transparent;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive isitable">
                                <table id="dataUser" class="table table-small-font table-bordered table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 13px">Id </th>
                                            <th style="font-size: 13px">Nama</th>
                                            <!-- <th style="font-size: 13px">Email</th> -->
                                            <th style="font-size: 13px">Group User</th>
                                            <th style="font-size: 13px">Status</th>
                                            <!-- <th style="font-size: 13px">Last Access</th> -->
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row _edituser" style="display: none;">

        <div class="col-lg-12" style="margin-top: 100px;">
            <div class="card">
                <form id="form_edit">
                    <div class="card-header">
                        <h3 class="card-title">Profil</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Id User</label>
                                    <input type="hidden" class="form-control" id="iduser" name="iduser" placeholder="">
                                    <input type="text" class="form-control input-sm" id="userid" name="userid" placeholder="username" readonly="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nama</label>
                                    <input type="text" class="form-control input-sm" id="nama" name="nama" placeholder="name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" class="form-control input-sm" id="email" name="email" placeholder="email">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Status </label>
                                    <select class="form-control" id="stts" name="stts">
                                        <option value=""> - Pilih - </option>
                                        <option value="Y"> Active </option>
                                        <option value="N"> Not Active </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" style="display: flex; justify-content: space-between;">
                        <!-- <button class="btn btn-warning btnShwHd"  data-show="._list_user" data-hide="._edituser" data-hdrhide=".lbl_hdr_indi,.lbl_hdr_krit"><i class="fas fa-arrow-left"></i>&nbsp;Kembali</button> -->
                        <a class="btn btn-outline-primary btnShwHd" data-show="._list_user" data-hide="._edituser" data-hdrhide=".lbl_hdr_nmwlyh" data-reload="DUser"><i class="fas fa-arrow-left"></i>&nbsp;Kembali</a>
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Edit</button>
                    </div>
                </form>
            </div>
            <!-- card-body -->
        </div>
    </div>

</div>


</div>

<form id="form_add">
    <div id="modal_add" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="field-3" class="control-label">User Id</label>
                                <input type="text" class="form-control" id="field-1" name="code" placeholder="username" required="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Nama</label>
                                <input type="text" class="form-control" id="field-2" placeholder="name" required="" name="name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Email</label>
                                <input type="email" class="form-control" id="field-3" placeholder="email" required="" name="email">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal"><i class="fas fa-times"></i>&nbsp;Batal</button> -->
                    <!-- <a class="btn btn-warning btnShwHd" data-show="._list_user" data-hide="._edituser" data-hdrhide=".lbl_hdr_nmwlyh" data-reload="DUser"><i class="fas fa-arrow-left"></i>&nbsp;Kembali</a> -->
                    <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fas fa-save"></i>&nbsp;Simpan</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
</form>

<form id="form_wil">
    <div id="modal_wil" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Provinsi yang Akan Dinilai</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12 col-lg-12">
                            <div class="form-group">
                                <input type="" class="form-control" name="iduserr" placeholder=''>
                                <label>Provinsi</label>
                                <select class="form-control" name="prov">
                                    <option value=""> - Pilih - </option>
                                    <?php
                                    //  foreach ($list_prov->result() as $v) {
                                    ?>
                                    <option value="<?php //echo encrypt_text($v->id);
                                                    ?>"><?php //echo $v->id_kode
                                                        ?>-<?php //echo $v->nama_provinsi
                                                            ?></option>
                                    <?php
                                    // }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">

                    <button class="btn btn-info waves-effect waves-light pull-left" type="submit">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>








<!--//list kabupaten-->
<div id="list_kab" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive isitable">
                            <table id="dataKab" class="table table-small-font table-bordered table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="font-size: 13px">No </th>
                                        <th style="font-size: 13px">Kabupaten</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup">Simpan</button>
            </div>
        </div>
    </div>
</div>