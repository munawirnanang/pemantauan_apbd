<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title ml-3"><img class="chart-icon" src="<?php echo base_url("assets/images/chart-line-icon.png")?>"> Jumlah Penduduk Miskin</h4>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item"><a class="link-back" href="#" data-target="<?php echo site_url("Gis")?>">Evaluasi Kinerja Indikator Makro</a></li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<div class="row">
        <div class="col-lg-12">

        <div class="mt-3 d-flex justify-content-center-md-start">
            <form class="form-inline">
                <div class="input-group m-2">
                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail2"></label>
                            <input type="hidden" class="form-control border-0" style="border-radius: 20px" id="inp_pro" name="inp_pro" placeholder="Plih Provinsi">
                            <input type="hidden" id="inp_proid" name="inp_proid" >
                            <span class="input-group-prepend">
                                <button type="button" class="btn waves-effect waves-light btn-primary border-0 plhpro" title="klik untuk melihat list provinsi" style="border-radius: 20px; width: 300px; text-align: left;"><p id="inp_pro_text" style="position: absolute; margin: -2px;">Cari Provinsi</p><i class="fa fa-search" style="float: right; padding: 3px;"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="input-group m-2">
                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">
                        <div class="form-group">
                            <select class="form-control border-0 select2-hidden-accessible s_rpjmn" style="border-radius: 20px" data-toggle="select2" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true" name="s_rpjmn" id="s_rpjmn">
                                <option value="" >&nbsp;Pilih RPJMN</option>
                                <option value="2015" >RPJMN 2015-2019 </option>
                                <option value="2020" >RPJMN 2020-2024 </option>
                            </select>
                        </div>
                    </div>
                </div>
            </form> 
        </div>

            <!-- card -->
        </div>
        <!-- col -->
    </div>
<div class="row">

        
    <div class="col-12">
            <div class="tab-content" id="nav-tabContent">
          
                
                <div class="tab-pane fade show active" id="nav-graph" role="tabpanel" aria-labelledby="nav-graph-tab">
                    
                    <div class="col-lg-12 col-md-12 col-sm-12 col-12" id="pe_pro_g">

                            <div class="card card-box" style="border: 2px solid black;">
                                
                                <div class="card-body" >
                                    <div id="chart-container-14" style="height: 340px;"></div>
                                </div>

                                <div class="card-body " style="">
                                    <form id="form_jml" > <p style="margin-left: 1rem; margin-right: 1rem;"  name="maxpep"></p></form>
                                </div>

                            </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-12" style="display: none; padding: 0px;" id="pe_pro">
                        
                        <div class="card card-box" style="border: 2px solid black;">

                            <div class="card-body" >
                                <div id="chart-container-1-pro" style="height: 340px;"></div>
                            </div>

                            <div class="card-body" >
                            <form id="form_jml_prov" >
                                                <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_pro"></p><br/>
                                            </form>
                            </div>        

                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-12" style="display: none; padding: 0px;" id="pe_rad">
                                <div class="card"  style="border: 2px solid black;">
                                <div class="card-body" >
                                    <div id="container-1-rad" style="height: 340px;"></div>
                                </div>
                                <div class="card-body " style="">
                                <form id="form_pe_p">
                                                    <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_p"></p>
                                                </form>
                                </div>
                                             
                                </div>
                            </div>
                            
                            <div class="col-lg-12 col-md-12 col-sm-12 col-12" style="display: none; padding: 0px;" id="pe_kab">
                                <div class="card"  style="border: 2px solid black;">
                                    <div class="card-body" >
                                        <div id="chart-container-1-kab" style="height: 400px;"></div>
                                    </div>
                                    <div class="card-body" >
                                    <form id="form_pe_perk">
                                                <p style=" margin-left: 1rem; margin-right: 1rem;" name="per_kab"></p>
                                            </form>
                                    </div>            
                                </div>
                            </div>

                           

                
                          
            </div>
        </div>    
</div>


<div id="mdlPro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title">Pilih Propinsi</h5>
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
                                                <th class="text-uppercase">Nama Provinsi</th>
                                                <th class="text-uppercase" title="label">Label</th>
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