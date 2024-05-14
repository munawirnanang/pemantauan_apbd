<div class="row">    <div class="col-12">        <div class="page-title-box">            <h4 class="page-title ml-3"><img class="chart-icon" src="<?php echo base_url("assets/images/chart-line-icon.png")?>">Gini rasio</h4>            <ol class="breadcrumb p-0 m-0 mr-3">                <li class="breadcrumb-item">Evaluasi Kinerja Indikator Makro </li>                <li class="breadcrumb-item"><a class="link-back" href="#" data-target="<?php echo site_url("Gis")?>">Sistem Informasi Geografis</a></li>            </ol>            <div class="clearfix"></div>        </div>    </div></div><div class="row">        <div class="col-lg-12">        <div class="mt-3 d-flex justify-content-md-start">            <form class="form-inline">                <div class="input-group m-2">                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">                        <div class="form-group">                            <label class="sr-only" for="exampleInputEmail2"></label>                            <input type="hidden" class="form-control border-0" style="border-radius: 20px" id="inp_pro" name="inp_pro" placeholder="Plih Provinsi">                            <input type="hidden" id="inp_proid" name="inp_proid" >                            <span class="input-group-prepend">                                <button type="button" class="btn waves-effect waves-light btn-primary border-0 plhpro" title="klik untuk melihat list provinsi" style="border-radius: 20px; width: 300px; text-align: left;"><p id="inp_pro_text" style="position: absolute; margin: -2px;">Nasional</p><i class="fa fa-search" style="float: right; padding: 3px;"></i></button>                            </span>                        </div>                    </div>                </div>                <div class="input-group m-2">                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">                        <div class="form-group">                            <select class="form-control border-0 select2-hidden-accessible s_rpjmn" style="border-radius: 20px" data-toggle="select2" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true" name="s_rpjmn" id="s_rpjmn">                                <option value="" >&nbsp;Pilih RPJMN</option>                                <option value="2015" >RPJMN 2015-2019 </option>                                <option value="2020" >RPJMN 2020-2024 </option>                            </select>                        </div>                    </div>                </div>            </form>         </div>            <!-- card -->        </div>        <!-- col -->    </div><div class="row">            <div class="col-12">            <nav>                <div class="nav nav-tabs" id="nav-tab" role="tablist">                    <a class="nav-item nav-link active" id="nav-map-tab" data-toggle="tab" href="#nav-map" role="tab" aria-controls="nav-map" aria-selected="true"><i class="fa ion ion-md-desktop "></i> Map</a>                    <a class="nav-item nav-link" id="nav-graph-tab" data-toggle="tab" href="#nav-graph" role="tab" aria-controls="nav-graph" aria-selected="false"><i class=" ion ion-md-stats "></i> Graph</a>                </div>            </nav>            <div class="tab-content" id="nav-tabContent">                <div class="tab-pane fade show active" id="nav-map" role="tabpanel" aria-labelledby="nav-map-tab">                    <div id="map" class="gmaps"></div>                    <div id="state-legend" class="legend" style="">                        <h4 style="font-size: 14px";>Gini rasio</h4>                        <div><span style="background-color: #0af545"></span>     > -0.30</div>                        <div><span style="background-color: #fafa07"></span>-2.30 - -0.30</div>                        <div><span style="background-color: #ba0618"></span>     < -2.29</div>                    </div>                    <div class="map-overlay-container" id="form_nsl">                        <div class="map-overlay">                            <h4 style="font-size: 12px;" id="location-title">Periode : <a name="n_thn"> </a></h4>                            <h4 style="font-size: 12px;" id="location-title">Angka   <a name="n_nsl"> </a>%</h4>                        </div>                    </div>                </div>                                <div class="tab-pane fade" id="nav-graph" role="tabpanel" aria-labelledby="nav-graph-tab">                                            <div class="card-body overflow-auto" style="text-align: justify; padding: 0px; padding-right: 10px; background-color: #F5F5F5;"><!--                            <div class="card card-border">-->                            <div class="col-lg-12" id="pe_pro_g" >                                <div class="card">                                    <div class="panel-heading">                                         <h3 class="panel-title"></h3>                                     </div>                                    <div class="panel-body">                                        <form  id="form_pe"> <p style=" margin-left: 1rem; margin-right: 1rem;" name="ket"></p> </form>                                                                                <form id="form_pe" > <p style="margin-left: 1rem; margin-right: 1rem;"  name="maxpep"></p></form>                                                                        </div>                                    <div class="panel-body">                                        <div id="chart-container-1" style="height: 340px;"></div>                                    </div>                                </div>                            </div>                                                <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_pro">                                <div class="card">                                    <div class="panel-heading">                                         <h3 class="panel-title"></h3>                                     </div>                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >                                                                                <div class="panel-body">                                            <form id="form_pe" >                        <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_pro"></p><br/>                    </form>                                                <div id="chart-container-1-pro" style="height: 350px;"></div>                                                                                    </div>                                    </div>                                            </div>                            </div>                                                                                    <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_rad">                                <div class="card">                                    <div class="panel-heading">                                         <h3 class="panel-title"></h3>                                     </div>                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >                                                                               <div class="panel-body">                                            <form id="form_pe_p" style="display: hidden;">                                                <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_p"></p>                                            </form>                                                                                            <div id="chart-container-rad" style="height: 400px;"></div>                                                                                    </div>                                    </div>                                            </div>                            </div>                                                                                    <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_kab">                                <div class="card">                                    <div class="panel-heading">                                         <h3 class="panel-title"></h3>                                     </div>                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >                                                                                <div class="panel-body">                                             <form id="form_pe" style="display: hidden;">                        <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_kab"></p>                    </form>                                                <div id="chart-container-1-kab" style="height: 400px;"></div>                                                                                    </div>                                    </div>                                            </div>                            </div>                            <!-- End card graph -->                        </div><!--                    </div>-->                </div>            </div>        </div>            </div><!--                <div class="pageheader" id="pageheader">                    <h4><i class="mdi mdi-map-check ">Jumlah Penduduk Miskin</i>  <span></span></h4>                </div>                    <div class="card-body">                    <form class="form-inline">                        <div class="input-group">                            <div class="form-group">                                    <label class="sr-only" for="exampleInputEmail2">Email address</label>                                    <input type="text" class="form-control" id="inp_pro" name="inp_pro" placeholder="Provinsi">                                    <input type="hidden" id="inp_proid" name="inp_proid" >                                    <input type="hidden" class="form-control" id="inp_kab" name="inp_kab" placeholder="Kabupaten">                                    <span class="input-group-prepend">                                                <button type="button" class="btn waves-effect waves-light btn-primary plhpro"><i class="fa fa-search"></i></button>                                    </span>                            </div>                            <div class="input-group">                                <label class="col-md-2 col-form-label"></label>                                <select class="form-control select2-hidden-accessible s_rpjmn" data-toggle="select2" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true" name="s_rpjmn" id="s_rpjmn">                                                                                <option value="" >&nbsp;Pilih RPJMN</option>                                                                                <option value="2015" >RPJMN 2015-2019 </option>                                                                                <option value="2020" >RPJMN 2020-2024 </option>                                </select>                            </div>                            <div class="form-group ml-2">                                <button type="button" class="btn btn-primary waves-effect waves-light nosatu"><i class="fa ion ion-md-desktop "></i></button>                            </div>                            <div class="form-group ml-2">                                <button type="button" class="btn btn-primary waves-effect waves-light nodua" ><i class=" ion ion-md-stats "></i></button>                            </div>                            <div class="form-group ml-2 bg-success btnMenu" data-target="<?php echo site_url("Gis")?>">                                     <button type="button" class="btn btn-primary waves-effect waves-light " >                                    <i class="fas fa-arrow-left"></i>&nbsp;Kembali</button>                            </div>                        </div>                                        </form>                                    </div>--><div id="mdlPro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">    <div class="modal-dialog modal-full">        <div class="modal-content">            <div class="modal-header">                    <h5 class="modal-title">Pilih Propinsi</h5>                <button type="button" class="close" data-dismiss="modal" aria-label="Close">                    <span aria-hidden="true">&times;</span>                </button>            </div>                                                    <div class="modal-body">                                                        <input type="hidden" class="frmTrgt" value=""/>                                    <table id="tblSo" style="width: 100%" class="table table-bordered table-striped">                                    <table id="tblPro" class="table table-small-font table-bordered table-striped" style="width:100%">                                        <thead>                                            <tr>                                                <th class="text-uppercase" title="id">Id</th>                                                <th class="text-uppercase">Nama Provinsi</th>                                                <th class="text-uppercase" title="label">Label</th>                                                <th></th>                                            </tr>                                        </thead>                                        <tbody></tbody>                                    </table>                                                    </div>                                                    <div class="modal-footer">                                                        <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Batal</button><!--                                                        <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup">Pilih</button>-->                                                        <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>                                                    </div>        </div>    </div>                                        </div>