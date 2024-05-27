<div class="container-fluid">

    <div class="row" id="tab-search-apbd" style="background-color: white; margin-bottom: 20px; margin-left: -39px; margin-right: 0px; position: fixed; z-index: 99; width: -webkit-fill-available; border-bottom: 2px solid #B8A5A5;">
        <div class="col-lg-12">
            <div class="page-title-box" style="padding-bottom: 0px; margin-left: 0px; margin-right: 0px;">
                <div class="clearfix"></div>
                <div class="card card-fill" style="border: 1px solid #000000; border-radius: 20px; display: inline-block; background-color: white">
                    <h4 class="card-title text-black" style="font-family: 'Montserrat', sans-serif; padding: 0.5rem 0.75rem;">Anggaran Pendapatan dan Belanja Daerah</h4>
                </div>
                <ol class="breadcrumb p-0 m-0" style="margin-top: 5px !important;">
                    <li class="breadcrumb-item active" style="color: black;">APBD</li>
                </ol>
            </div>
            <form class="form" style="display: grid; justify-content: center; margin-bottom: 10px;">
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
                                    Pilih Item <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Item dapat di pilih lebih dari satu</span>
                                </p>
                                <select class="selectpicker form-control" id="selectitem" name="item[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="5" data-selected-text-format="count > 3" data-width="275px">
                                    <!-- <option>Pilih Item</option> -->
                                </select>
                            </div>
                            <div class="form-group mx-2">
                                <p class="text-muted m-t-30 font-13 mb-2" style="justify-self: center;">
                                    Pilih Tahun <span class="badge" style="background-color: rgba(108, 117, 125, 0.7);">Tahun dapat di pilih lebih dari satu</span>
                                </p>
                                <select class="selectpicker form-control" id="selectyear" name="year[]" multiple="multiple" data-actions-box="true" data-live-search="true" data-dropup-auto="false" data-size="8" data-selected-text-format="count > 6" data-width="275px">
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
                        <span class="d-none d-sm-block">Sunburst Chart</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="barchart-b2-tab" data-toggle="tab" href="#barchart-b2" role="tab" aria-controls="barchart-b2" aria-selected="false">
                        <span class="d-block d-sm-none"><i class="mdi mdi-email-outline font-18"></i></span>
                        <span class="d-none d-sm-block">Bar Chart</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabel-b2-tab" data-toggle="tab" href="#tabel-b2" role="tab" aria-controls="tabel-b2" aria-selected="false">
                        <span class="d-block d-sm-none"><i class="mdi mdi-home-variant-outline font-18"></i></span>
                        <span class="d-none d-sm-block">Tabel</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row text-center">
        <div class="col-md-12">

            <div class="tab-content">

                <div class="tab-pane show active" id="grafik-b2" role="tabpanel" aria-labelledby="grafik-b2-tab">
                    <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent; margin-top: 230px;">
                        <div class="row apbd-grafik-initial" style="justify-content: center;">
                            <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%; margin-bottom: -2%;">
                                <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, Item, dan Tahun pada form diatas</strong> -</h5>
                            </div>
                        </div>
                        <div class="row grafik-apbd" style="display: none; justify-content: space-between;">
                            <div class="tab-content" style="width: 100%;">
                                <div class="tab-pane show active" id="sunburst-b2" role="tabpanel" aria-labelledby="sunburst-b2-tab" style="width: 100%;">
                                    <div class="col-12" id="isi-grafik-apbd" style="display: ruby-text;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tabel-b2" role="tabpanel" aria-labelledby="tabel-b2-tab">
                    <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent; margin-top: 230px;">
                        <div class="row apbd-tabel-initial" style="justify-content: center;">
                            <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%; margin-bottom: -2%;">
                                <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, Item, dan Tahun pada Form diatas</strong> -</h5>
                            </div>
                        </div>
                        <div class="row tabel-apbd" style="display: none;">
                            <div style="padding-right: 0px; width: 96%;">
                                <div class="table-responsive" id="tabel-apbd">
                                    <table class="table table-bordered" style="font-size: 10px; cursor: grab; border: 1px solid #e3e0e0;">
                                        <thead id="judul-tabel-apbd" style="background-color: #645A82; color: white;">
                                        </thead>
                                        <tbody id="isi-tabel-apbd" style="text-align: left;">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-1 button-export-tabel-apbd" style="width: 4%; display: none;">
                                <div class="btn-group-vertical m-b-10">
                                    <button type="button" class="btn btn-default waves-effect button-excel-tabel-apbd" style="writing-mode: vertical-rl;text-orientation: mixed;padding: 5px; padding-top: 8px; padding-bottom: 8px;">Export to Excel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="barchart-b2" role="tabpanel" aria-labelledby="barchart-b2-tab">
                    <div class="card" style="background-color: transparent; box-shadow: 0px 0px 0px 0px transparent; margin-top: 230px;">
                        <div class="row apbd-barchart-initial" style="justify-content: center;">
                            <div class="not-found-img" style="display: grid; justify-items: center; margin: 1%; margin-bottom: -2%;">
                                <img src="<?= base_url() ?>assets/images/Searching.png" alt="Searching" width="400" height="168">
                                <h5 style="font-family: \'Hind Madurai\', sans-serif; text-align: center;">- <strong style="color: #000;">Pilih Daerah, Item, dan Tahun pada form diatas</strong> -</h5>
                            </div>
                        </div>
                        <div class="row barchart-apbd" style="display: none; justify-content: space-between;">
                            <div class="tab-content" style="width: 100%;">
                                <div class="tab-pane show active" id="sunburst-b2" role="tabpanel" aria-labelledby="sunburst-b2-tab" style="width: 100%;">
                                    <div class="col-12" id="isi-barchart-apbd" style="display: contents;">
                                        <div id="html_barchart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>