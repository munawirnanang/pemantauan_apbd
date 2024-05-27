<div class="container-fluid">

    <div class="row" id="tab-search-apbd" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available; border-bottom: 2px solid #B8A5A5;">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white">
                    <h4 class="card-title text-black" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem;">Evaluasi Kinerja Indikator Makro Pembangunan</h4>
                </div>
                <!-- <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item" style="color: black;"><a href="#">Data Tabel</a></li>
                    <li class="breadcrumb-item active" style="color: black;">Upload Data Indikator</li>
                </ol> -->
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12" style="margin-top: 110px;">
            <div class="card card-evaluasi-kinerja">
                <div class="card-body">
                    <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
                        <div class="input-group">
                            <div class="row">
                                <div class="col-12 box-form-evaluasi-kinerja">
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu daerah yang dapat dipilih</span>
                                        </p>
                                        <!-- <select class="form-controlselect2-multiple select2-hidden-accessible" data-toggle="select2" multiple="" data-placeholder="Choose a Country..." data-select2-id="4" tabindex="-1" aria-hidden="true"> -->
                                        <select class="selectpicker selectregion form-control" id="idregion1" name="region" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="1" data-width="190px">
                                            <!-- <option>Pilih Daerah</option> -->
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Indikator <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator dapat di pilih lebih max dua</span>
                                        </p>
                                        <select class="selectpicker selectindicator form-control" id="idindicator1" name="indicator[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="2" data-width="190px">
                                            <!-- <option>Pilih Item</option> -->
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu tahun yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker selectyear form-control" id="idyear1" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="190px">
                                            <!-- <option>Pilih Tahun</option> -->
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Data <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu data yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker selectdata form-control" id="iddata1" name="data" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="190px">
                                            <option value="pencapaian">Pencapaian</option>
                                            <option value="t_m_rpjmn">t_m_rpjmn</option>
                                            <option value="t_rkpd">t_rkpd</option>
                                            <option value="t_k_rkp">t_k_rkp</option>
                                        </select>
                                    </div>
                                    <div class="form-group mx-2" style="align-self: end;">
                                        <button type="button" id="button-submit-form-evaluasi-kinerja" class="btn btn-primary waves-effect waves-light" style="border: 1px solid black;">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <hr />
            <script src="https://code.highcharts.com/highcharts.js"></script>
            <script src="https://code.highcharts.com/highcharts-more.js"></script>
            <script src="https://code.highcharts.com/modules/exporting.js"></script>
            <script src="https://code.highcharts.com/modules/export-data.js"></script>
            <script src="https://code.highcharts.com/modules/accessibility.js"></script>
            <div class="col-evaluasi-kinerja"></div>
            <!-- <div class="card card-evaluasi-kinerja">
                <div class="card-body">
                    <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
                        <div class="input-group">
                            <div class="row">
                                <div class="col-12 box-form-evaluasi-kinerja">
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu daerah yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="region" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Indikator <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator dapat di pilih lebih max dua</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="item[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="2" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu tahun yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Data <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu data yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div class="row">
                        <div class="col-8">
                            <div class="card" style="border-radius: 10px;">
                                <div class="card-body">
                                    <script src="https://code.highcharts.com/highcharts.js"></script>
                                    <script src="https://code.highcharts.com/maps/modules/map.js"></script>
                                    <script src="https://code.highcharts.com/maps/modules/data.js"></script>
                                    <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
                                    <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
                                    <script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
                                    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

                                    <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
                                    <link href="https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet" type="text/css" />

                                    <div id="container3">
                                        <div class="loading">
                                            <i class="icon-spinner icon-spin icon-large"></i>
                                            Loading data from Google Spreadsheets...
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card" style="border-radius: 10px; height: 450px;">
                                <div class="card-header" style="border-radius: 10px;">
                                    <h3 class="card-title">Keterangan</h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card card-evaluasi-kinerja">
                <div class="card-body">
                    <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
                        <div class="input-group">
                            <div class="row">
                                <div class="col-12 box-form-evaluasi-kinerja">
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu daerah yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="region" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Indikator <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator dapat di pilih lebih max dua</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="indicator[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="2" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu tahun yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Data <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu data yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="data" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div class="row">
                        <div class="col-8">
                            <div class="card" style="border-radius: 10px;">
                                <div class="card-header" style="border-radius: 10px 10px 0px 0px; border-bottom: 1px solid rgba(0, 0, 0, 0.1);">
                                    <div class="btn-group" style="float: right; padding-buttom: 3px;">
                                        <button style="margin-right: 2px; font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-area-chart" aria-hidden="true"></i> Grafik</button>
                                        <button style="font-size: 12px; padding-bottom: 1px; padding-top: 1px; border: 1px solid black;"><i class="fa fa-table" aria-hidden="true"></i> Table</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <script src="https://code.highcharts.com/highcharts.js"></script>
                                    <script src="https://code.highcharts.com/highcharts-more.js"></script>
                                    <script src="https://code.highcharts.com/modules/exporting.js"></script>
                                    <script src="https://code.highcharts.com/modules/export-data.js"></script>
                                    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

                                    <figure class="highcharts-figure">
                                        <div id="container"></div>
                                    </figure>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card" style="border-radius: 10px; height: 515px;">
                                <div class="card-header" style="border-radius: 10px;">
                                    <h3 class="card-title">Keterangan</h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card card-evaluasi-kinerja">
                <div class="card-body">
                    <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
                        <div class="input-group">
                            <div class="row">
                                <div class="col-12 box-form-evaluasi-kinerja">
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Daerah <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu daerah yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="region" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Indikator <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Indikator dapat di pilih lebih max dua</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="item[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-max-options="2" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu tahun yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                    <div class="form-group mx-2">
                                        <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                            Pilih Data <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Hanya satu data yang dapat dipilih</span>
                                        </p>
                                        <select class="selectpicker form-control" id="" name="year" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-max-options="1" data-width="200px">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <hr>
                    <div class="row">
                        <div class="col-8">
                            <div class="card" style="border-radius: 10px;">
                                <div class="card-body">
                                    <script src="https://code.highcharts.com/highcharts.js"></script>
                                    <script src="https://code.highcharts.com/modules/exporting.js"></script>
                                    <script src="https://code.highcharts.com/modules/export-data.js"></script>
                                    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

                                    <figure class="highcharts-figure">
                                        <div id="container2"></div>
                                    </figure>

                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card" style="border-radius: 10px; height: 450px;">
                                <div class="card-header" style="border-radius: 10px;">
                                    <h3 class="card-title">Keterangan</h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div> -->
        </div>
    </div>

</div>