<div class="container-fluid">


    <div class="row" id="tab-search-indikator-makro" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px; text-align: left;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white;">
                    <h4 class="card-title" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem; color: black;">Pencapaian Indikator Makro Pembangunan</h4>
                </div>
                <!-- <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item" style="color: black;"><a href="#">Pencapaian</a></li>
                    <li class="breadcrumb-item active" style="color: black;">Pencapaian Indikator Makro Berdasarkan Daerah, Indikator, dan tahun</li>
                </ol> -->
            </div>
            <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
                <!-- <p class="text-muted m-b-15 m-t-30 font-13 mb-2" style="justify-self: center;">
                    Pilih <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">daerah</span> dan <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator</span> untuk melihat grafik pencapaian indikator makro
                </p> -->
                <!-- <div class="input-group">
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_pro" name="inp_pro" placeholder="Provinsi">
                                <input type="hidden" id="inp_proid" name="inp_proid">
                                <select class="selectpicker" id="pilihPro" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px">
                                    <option>Pilih Provinsi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_kab" name="inp_kab" placeholder="Kabupaten">
                                <select class="selectpicker" id="pilihKab" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px" disabled>
                                    <option>Pilih Kabupaten</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="inp_kota" name="inp_kota" placeholder="Kota">
                                <select class="selectpicker" id="pilihKota" data-live-search="true" data-dropup-auto="false" data-size="5" data-width="300px" disabled>
                                    <option>Pilih Kota</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="input-group">
                    <div class="row">
                        <div class="col-12" style="display: flex;">
                            <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Daerah dapat di pilih lebih dari satu</span>
                                </p>
                                <!-- <select class="form-controlselect2-multiple select2-hidden-accessible" data-toggle="select2" multiple="" data-placeholder="Choose a Country..." data-select2-id="4" tabindex="-1" aria-hidden="true"> -->
                                <select class="selectpicker form-control" id="selectregion" name="region[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-width="275px">
                                    <!-- <option>Pilih Daerah</option> -->
                                </select>
                            </div>
                            <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Indikator <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator dapat di pilih lebih dari satu</span>
                                </p>
                                <select class="selectpicker form-control" id="selectindicator" name="indicator[]" multiple="multiple" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="5" data-width="275px">
                                    <!-- <option>Pilih Indikator</option> -->
                                </select>
                            </div>
                            <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Tahun dapat di pilih lebih dari satu</span>
                                </p>
                                <select class="selectpicker form-control" id="selectyear" name="year[]" multiple="multiple" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="5" data-width="275px">
                                    <!-- <option>Pilih Tahun</option> -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-12" style="background-color: #D9D9D9; padding: 0px;">
            <ul class="nav nav-tabs tabs-bordered nav-justified" role="tablist" style="box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 1rem !important;">
                <li class="nav-item">
                    <a class="nav-link active" id="grafik-b2-tab" data-toggle="tab" href="#grafik-b2" role="tab" aria-controls="grafik-b2" aria-selected="true">
                        <span class="d-block d-sm-none"><i class="mdi mdi-account-outline font-18"></i></span>
                        <span class="d-none d-sm-block">Grafik</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabel-b2-tab" data-toggle="tab" href="#tabel-b2" role="tab" aria-controls="tabel-b2" aria-selected="false">
                        <span class="d-block d-sm-none"><i class="mdi mdi-home-variant-outline font-18"></i></span>
                        <span class="d-none d-sm-block">Tabel</span>
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" id="peta-b2-tab" data-toggle="tab" href="#peta-b2" role="tab" aria-controls="peta-b2" aria-selected="false">
                        <span class="d-block d-sm-none"><i class="mdi mdi-email-outline font-18"></i></span>
                        <span class="d-none d-sm-block">Peta</span>
                    </a>
                </li> -->
            </ul>
        </div>
    </div>

    <div class="row text-center">

        <div class="col-md-12">

            <div class="tab-content">

                <div class="tab-pane show active" id="grafik-b2" role="tabpanel" aria-labelledby="grafik-b2-tab">

                    <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent; margin-top: 230px;">

                        <div class="row indikator-makro-initial-graph" style="justify-content: center;">
                            <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%; margin-bottom: -2%;">
                                <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, Indikator, dan Tahun pada Form diatas</strong> -</h5>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-12 col-xl-6 satu" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;"> -->
                                    <!-- <h5 class="page-title btnMenu"><a>Pertumbuhan Ekonomi</a></h5> -->
                                    <!-- <h3 class="panel-title"> </h3> -->
                                    <!-- </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-1" style="height: 300px;"></div>

                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-md-12 col-xl-6 dua" style="display: none;">
                                <div class="card card-border" style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0" style="border-radius: 10px 10px 10px 10px;">
                    <h5 class="page-title btnMenu">PDRB per Kapita ADHB</h5>
                    <h3 class="panel-title"> </h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-2" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BAR Chart -->
                            <div class="col-md-12 col-xl-6 tiga" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"> </h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-3" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <!--  Line Chart -->
                            <div class="col-md-12 col-xl-6 empat" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"> </h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-4" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <!-- BAR Chart -->
                            <div class="col-md-12 col-xl-6 lima" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-5" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->
                            <!--  Line Chart -->
                            <div class="col-md-12 col-xl-6 enam" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"> </h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-6" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 tujuh" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-7" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 delapan" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-8" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 sembilan" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-9" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 sepuluh" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-10" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 sebelas" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-11" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 duabelas" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-12" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 tigabelas" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-13" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div> <!-- col -->

                            <div class="col-md-12 col-xl-6 empatbelas" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
                                    <div class="card-body">
                                        <div id="chart-container-p2" style="height: 300px;">

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-xl-6 p2" style="display: none;">
                                <div class="card card-border " style="border-radius: 10px 10px 10px 10px; border: 1px solid black; padding: 10px 10px 10px 0px;">
                                    <!-- <div class="card-header border-primary bg-transparent p-0 " style="border-radius: 10px 10px 10px 10px;">
                    <h3 class="panel-title"></h3>
                </div> -->
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

                <div class="tab-pane" id="tabel-b2" role="tabpanel" aria-labelledby="tabel-b2-tab">
                    <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent; margin-top: 230px;">
                        <div class="row indikator-makro-initial" style="justify-content: center;">
                            <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%; margin-bottom: -2%;">
                                <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, Indikator, dan Tahun pada Form diatas</strong> -</h5>
                            </div>
                        </div>
                        <div class="row tabel-indikator" style="display: none;">
                            <div style="padding-right: 0px; width: 96%;">
                                <div class="table-responsive" id="tabel-indikator-makro">
                                </div>
                            </div>
                            <div class="col-1 button-export-tabel-indikator-makro" style="display: none; width: 4%;">
                                <div class="btn-group-vertical m-b-10">
                                    <button type="button" class="btn btn-default waves-effect button-excel-tabel-indikator-makro" style="writing-mode: vertical-rl;text-orientation: mixed;padding: 5px; padding-top: 8px; padding-bottom: 8px;">Export to Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="peta-b2" role="tabpanel" aria-labelledby="peta-b2-tab">
                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim.</p>
                    <p class="mb-0">Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt.Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim.</p>
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