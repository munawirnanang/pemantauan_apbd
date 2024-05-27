<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h2 class="page-title ml-3"><i class="icon-screen-desktop"></i> 
Download Evaluasi Kinerja Indikator Makro
</h2>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item"></li>
                
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            
        <div class="card-body">

            <form class="form-inline" id="form_add">
                <div class="form-group">
                    <label class="sr-only" for="exampleInputEmail2">Provinsi</label>
                    <div class="input-group">

                            <input type="text"  id="inp_pro" name="inp_pro" class="form-control" placeholder="Pilih Provinsi">
                            <input type="hidden" id="inp_proid" name="inp_proid" >
                            <span class="input-group-prepend">
                                <button type="button" class="btn waves-effect waves-light btn-primary plhpro" ><i class="fa fa-search"></i></button>
                                </span>
                        </div>
                </div>
                <div class="form-group" style="display: none;">
                    <label class="sr-only" for="exampleInputEmail2">RPJMN</label>
                    <div class="input-group">
                        <label class="col-md-2 col-form-label"></label>
                        <select class="form-control select2-hidden-accessible" data-toggle="select2" data-placeholder="" data-select2-id="1" tabindex="-1" aria-hidden="true" name="rpjmn" id="rpjmn">
                                                        <option value="#" >&nbsp;Pilih RPJM</option>
                                                        <option value="2019" >RPJMN 2014-2019 </option>
                                                        <option value="2024" >RPJMN 2020-2024 </option>
                                                        
                                                    </select>


                        </div>
                </div>
                
                <!-- <button type="submit" class="btn btn-success waves-effect waves-light ml-2" id="btnFilter"> PDF</button> -->
                
                <div class="form-group ml-2">
                                <button type="button" class="btn btn-primary waves-effect waves-light nodua" ><i class=" mdi mdi-file-word-box-outline "></i>&nbsp;WORD</button>
                </div>
            </form>
        </div>
        <!-- card-body -->
    </div>
    <!-- card -->
    </div>
</div>


<div id="mdlPro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title">Pilih Provinsi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="frmTrgt" value=""/>
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
                <button data-dismiss="modal" class="btn btn-secondary waves-effect" type="button">Batal</button>
                    <button type="button"        class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-save"></i>&nbsp;Pilih</button>
            </div>
        </div>
    </div>
</div>