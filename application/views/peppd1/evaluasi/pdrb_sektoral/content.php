<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title ml-3"><img class="chart-icon" src="<?php echo base_url("assets/images/chart-line-icon.png")?>"> Struktur & Pertumbuhan PDRB Sektoral</h4>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item"><a class="link-back" href="#" data-target="<?php echo site_url("Gis")?>">Evaluasi Kinerja Indikator Makro</a></li>
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="col-lg-12">

        <div class="mt-3 d-flex justify-content-md-start">
            <form class="form-inline">
                <div class="input-group m-2">
                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail2"></label>
                            <input type="hidden" class="form-control border-0" style="border-radius: 20px" id="inp_pro" name="inp_pro" placeholder="Plih Provinsi">
                            <input type="hidden" id="inp_proid" name="inp_proid" >
                            <span class="input-group-prepend">
                                <button type="button" class="btn waves-effect waves-light btn-primary border-0 plhpro" title="klik untuk melihat list provinsi" style="border-radius: 20px; width: 300px; text-align: left;"><p id="inp_pro_text" style="position: absolute; margin: -2px;">Nasional</p><i class="fa fa-search" style="float: right; padding: 3px;"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
               
            </form> 
        </div>

            <!-- card -->
        </div>
    
</div>

<div class="container-fluid">
    <div class="row _wrapper _wrapper_indi">
        <div class="col-lg-12">
            <div class="card">
              
                <div class="card-body" style="border: 2px solid black;">
                    <div id="chart-container-s" style="height: 400px;"></div>
                    
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="border: 2px solid black;">
                    
                    <div id="chart-container-p" style="height: 400px;"></div>
                    <form class="form_tk" id="form_tk">
                        <p name="ket_tk" style="font-size: 12px"></p>
                    </form>
                </div>
              
            </div>

            <div class="card"> 
            <div class="card-body" style="border: 2px solid black;">
                <div id="chart-container-pdrb" style="height: 500px;"></div>
                </div>
            </div>

                   
        </div>
    </div>
    
</div>

                    <!-- end container-fluid -->    
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