<div class="row">
    <div class="col-12">
        <div class="page-title-box" style="padding-bottom: 0px;">
            <!-- <h2 class="page-title ml-3"><i class="icon-screen-desktop"></i> Pencapaian Indikator Makro</h2>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item"></li>

            </ol>
            <div class="clearfix"></div> -->
            <div class="card card-fill bg-primary" style="border-radius: 10px 10px 10px 10px; display: inline-block;">
                <div class="card-header bg-transparent">
                    <h4 class="card-title text-white">Pencapaian Indikator Makro</h4>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">


    <div class="row">
        <div class="col-lg-12">
            <!-- <div class="card" style="background-color: transparent;">
                <div class="card-header">
                    <h3 class="card-title"></h3>
                </div>
                <div class="card-body"></div>
            </div> -->
            <form class="form-inline" style="justify-content: center; margin-bottom: 50px;">
                <p class="text-muted m-b-15 m-t-30 font-13">
                    Pilih <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Provinsi</span> atau <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Kabupaten</span> atau <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Kota</span> untuk melihat grafik pencapaian indikator makro
                </p>
                <div class="input-group">
                    <div class="row">
                        <!-- <div class="col-3">
                                    <div class="form-group">
                                        <label class="sr-only" for="exampleInputEmail2">Pilih Provinsi</label>
                                        <input type="text" class="form-control" id="inp_pro" name="inp_pro" placeholder="Provinsi">
                                        <input type="text" id="inp_proid" name="inp_proid">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn waves-effect waves-light btn-primary plhpro"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div> -->
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_pro" name="inp_pro" placeholder="Provinsi">
                                <input type="hidden" id="inp_proid" name="inp_proid">
                                <!-- <select class="form-control select2 pilihPro">
                                            <option>Pilih Provinsi</option>
                                        </select> -->
                                <select class="selectpicker" id="pilihPro" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px">
                                    <option>Pilih Provinsi</option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-3">
                                    <div class="form-group ml-2">
                                        <label class="sr-only" for="exampleInputPassword2">Pilih Kabupaten</label>
                                        <input type="text" class="form-control" id="inp_kab" name="inp_kab" placeholder="Kabupaten">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn waves-effect waves-light btn-primary plhkab"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div> -->
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_kab" name="inp_kab" placeholder="Kabupaten">
                                <select class="selectpicker" id="pilihKab" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px" disabled>
                                    <option>Pilih Kabupaten</option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-3">
                                    <div class="form-group ml-2">
                                        <label class="sr-only" for="exampleInputPassword2">Pilih Kota</label>
                                        <input type="text" class="form-control" id="inp_kota" name="inp_kota" placeholder="Kota">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn waves-effect waves-light btn-primary plhkota"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div> -->
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_kota" name="inp_kota" placeholder="Kota">
                                <select class="selectpicker" id="pilihKota" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px" disabled>
                                    <option>Pilih Kota</option>
                                </select>
                            </div>
                        </div>
                    </div>


                </div>
            </form>
        </div>
    </div>

    <div class="row text-center">

        <div class="col-md-12">

            <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent;">

                <div class="row">

                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <!-- <h5 class="page-title btnMenu"><a>Pertumbuhan Ekonomi</a></h5> -->
                                <h3 class="panel-title"> </h3>
                            </div>
                            <div class="card-body ">
                                <div id="chart-container-1" style="height: 300px;"></div>

                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-md-12 col-xl-6">
                        <div class="card card-border" style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0" style="border-radius: 10px 10px 10px 10px;">
                                <!-- <h5 class="page-title btnMenu">PDRB per Kapita ADHB</h5> -->
                                <h3 class="panel-title"> </h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-2" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <!--                        <div class="row">
                                                     BAR Chart 
                             col 

                                                      Line Chart 
                            <div class="col-lg-6">
                                <div class="card card-border">
                                    <div class="panel-heading"> 
                                        <h3 class="panel-title"></h3> 
                                    </div> 
                                    <div class="panel-body"> 
                                       <div class="panel-body">
                                            
                                </div>
                                    </div> 
                                </div>
                            </div>  col 
                        </div>  End row-->

                <div class="row">
                    <!-- BAR Chart -->
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"> </h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-3" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div> <!-- col -->

                    <!--  Line Chart -->
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"> </h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-4" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div> <!-- End row-->

                <div class="row">
                    <!-- BAR Chart -->
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-5" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                    <!--  Line Chart -->
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"> </h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-6" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div> <!-- End row-->

                <div class="row">
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-7" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->

                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-8" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div>

                <div class="row">
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-9" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->

                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-10" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div>

                <div class="row">
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-11" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->

                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-12" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div>

                <div class="row">
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-13" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-p2" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-6 ">
                        <div class="card card-border " style="border-radius: 10px 10px 10px 10px;">
                            <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                                <h3 class="panel-title"></h3>
                            </div>
                            <div class="card-body">
                                <div id="chart-container-14" style="height: 300px;">

                                </div>
                            </div>
                        </div>
                    </div> <!-- col -->
                </div>


                <!--    </div>-->
                <!-- container-fluid -->
            </div>

        </div>

    </div>

</div>



<div id="mdlPro" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
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
                <!--                                    <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">-->
                <table id="tblPro" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-black" title="id">Id</th>
                            <th class="text-black">Nama Provinsi</th>
                            <th class="text-black" title="label">Label</th>
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

<div id="modal_kab" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Kabupaten/Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <!--                                    <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">-->
                <table id="tblKab" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-black" title="id">Id</th>
                            <th class="text-black" title="Nama Kabupaten">Nama Kabupaten</th>
                            <th class="text-black"></th>
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

<div id="modal_kota" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Kota</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value="" />
                <table id="tblKot" class="table table-small-font table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-black" title="id">Id</th>
                            <th class="text-black" title="Nama kota">Nama Kota</th>
                            <th class="text-black"></th>
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