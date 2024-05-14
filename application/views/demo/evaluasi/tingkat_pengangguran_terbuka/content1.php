<div class="row">
    <div class="col-12">
        <!-- <div class="page-title-box" style="border-bottom: 1px solid; border-color: #e0e0e0;"> -->
        <div class="page-title-box">
            <h4 class="page-title ml-3"><img class="chart-icon" src="<?php echo base_url("assets/images/chart-line-icon.png")?>"> Tingkat Pengangguran Terbuka </h4>
            <ol class="breadcrumb p-0 m-0 mr-3">
                <li class="breadcrumb-item">Evaluasi Kinerja Indikator Makro </li>
                <li class="breadcrumb-item"><a class="link-back" href="#" data-target="<?php echo site_url("Gis")?>">Sistem Informasi Geografis</a></li>
<!--                <li class="breadcrumb-item active" aria-current="page"><b>Tingkat Pengangguran Terbuka</b></li>-->
            </ol>
            <div class="clearfix"></div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-12">
        
        <div class="mt-2 d-flex justify-content-md-start">
            <form class="form-inline">
                <div class="input-group m-2">
                    <div class="p-1 bg-primary rounded rounded-pill shadow-sm mb-4">
                        <div class="form-group">
                            <label class="sr-only" for="exampleInputEmail2">c</label>
                            <input type="hidden" class="form-control border-0" style="border-radius: 20px" id="inp_pro" name="inp_pro" placeholder="Plih Provinsi">
                            <input type="hidden" id="inp_proid" name="inp_proid" >
                            <input type="hidden" class="form-control" id="inp_kab" name="inp_kab" placeholder="Kabupaten">
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
        
    </div>    
        <!-- col -->
    </div>

<div class="row">
<!--        <div class="col-5">
            <div class="card" style="height: 405px;">
                <div class="card-header">
                    
                </div>
                <div class="card-body overflow-auto" style="text-align: justify;">
                    <form class="form_ch" id="form_ch">
                        <p class="card-text" name="ket"></p><br/>
                    </form>
                    <form id="form_pe" style="display: none;">
                        <p class="card-text" name="maxpep"></p><br/>
                         <p class="card-text" name="perkpdrkp"></p> 
                    </form>
                    <form id="form_pe_per" style="display: none;">
                        <p class="card-text" name="per_pro"></p><br/>
                    </form>
                </div>
            </div>
        </div>-->
        <div class="col-12">
            
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-map" role="tabpanel" aria-labelledby="nav-map-tab">
                    <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-map-tab" data-toggle="tab" href="#nav-map" role="tab" aria-controls="nav-map" aria-selected="true"><i class="fa ion ion-md-desktop "></i> Map</a>
                    <a class="nav-item nav-link" id="nav-graph-tab" data-toggle="tab" href="#nav-graph" role="tab" aria-controls="nav-graph" aria-selected="false"><i class=" ion ion-md-stats "></i> Graph</a>
                </div>
            </nav>
                    <div id="map" class="gmaps">
                        
                    </div>
                 
                    <div id="state-legend" class="legend">
                        <h4 style="font-size: 14px";>Tingkat Pengangguran Terbuka</h4>
                        <div><span style="background-color: #e20039"></span>7.57 - 10.95</div>
                        <div><span style="background-color: #f5da4b"></span>5.51  - 7.57</div>
                        <div><span style="background-color: #4bd712"></span>3.32  - 5.51</div>
<!--                        <div><span style="background-color: #ffd4d7"></span>0.00  - 2.00</div>-->
                    </div>
                    <div class="map-overlay-container" id="form_nsl">
                        <div class="map-overlay">
                            <h4 style="font-size: 12px;" id="location-title">Periode : <a name="n_thn"> </a></h4>
                            <h4 style="font-size: 12px;" id="location-title">Angka   <a name="n_nsl"> </a>%</h4>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="nav-graph" role="tabpanel" aria-labelledby="nav-graph-tab">
                    <!-- <div id="chart-container-1" style="height: 350px;"></div> -->
<!--                    <div class="card shadow-none" style="height: 375px;">-->
                        <div class="card-body overflow-auto" style="text-align: justify; padding: 0px; padding-right: 10px; background-color: #F5F5F5;">
                            <!-- Card graph -->
                            <div class="col-lg-12" id="pe_pro_g" style="padding: 0px;">
                                <div class="card">
                                    <div class="panel-heading"> 
                                        <h3 class="panel-title"></h3> 
                                    </div>
                                    <div class="panel-body">
                                        <form class="form_ch" id="form_ch">
                                            <p name="ket" style="font-size: 12px; margin-left: 1rem; margin-right: 1rem;"></p>
                                        </form>
                                        <form class="" id="form_pe">
                                            <p name="maxpep" style="font-size: 12px; margin-left: 1rem; margin-right: 1rem;"></p>
                                            <p name="perkpdrkp" style="font-size: 12px"></p>
                                        </form>
                                    </div>
                                    <div class="panel-body"> 
                                        <div class="panel-body">
                                        <div id="chart-container-1" style="height: 340px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_pro">
                                <div class="card">
                                    <div class="panel-heading"> 
                                        <h3 class="panel-title"></h3> 
                                    </div>
                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                                         <div class="card-body">
                                            <form class="" id="form_pe_per">
                                                <p name="per_pro" style="font-size: 12px"></p>
                                            </form>
                                        </div> 
                                        <div class="panel-body"> 
                                            <div class="panel-body">
                                                <div id="chart-container-1-pro" style="height: 350px;"></div>
                                            </div>
                                        </div>
                                    </div>            
                                </div>
                            </div>

                            <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_rad">
                                <div class="card">
                                    <div class="panel-heading"> 
                                        <h3 class="panel-title"></h3> 
                                    </div>
                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                                         <div class="card-body">
                                            <form class="" id="form_pe_perk">
                                                <p name="rad_kab"></p>
                                            </form>
                                        </div> 
                                        <div class="panel-body"> 
                                            <div class="panel-body">
                                                <div id="container-1-rad" style="height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>            
                                </div>
                            </div>

                            <div class="col-lg-12" style="display: none; padding: 0px;" id="pe_kab">
                                <div class="card">
                                    <div class="panel-heading"> 
                                        <h3 class="panel-title"></h3> 
                                    </div>
                                    <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                                         <div class="card-body">
                                            <form class="" id="form_pe_perk" >
                                                <p name="per_kab" style="font-size: 12px"></p>
                                            </form>
                                        </div> 
                                        <div class="panel-body"> 
                                            <div class="panel-body">
                                                <div id="chart-container-1-kab" style="height: 400px;"></div>
                                            </div>
                                        </div>
                                    </div>            
                                </div>
                            </div>
                            <!-- End card graph -->
                        </div>
<!--                    </div>-->
                </div>
            </div>
        </div>

    </div>



<!-- <div class="row">
    
    <div class="col-lg-12">   
        
        <div id="" style="overflow-y:scroll; overflow-x:hidden; height:100vw;">
            
            <div class="col-md-12" id="pe_pro_p">

                <div class="row">
                    <div class="col-lg-12">
                         <div class="panel panel-default">
                             <div class="panel-heading"> 
                                 <h3 class="panel-title"></h3> 
                             </div> 
                             <div class="panel-body"> 

                                 <div id="map" class="gmaps">
                                     
                                 </div>
                                 <div id="state-legend" class="legend" style="">
                                 
                                <h4 style="font-size: 14px";>Tingkat Kemiskinan</h4>
                                <div><span style="background-color: #fb030f"></span>12.50 - 17.60</div>
                                <div><span style="background-color: #f5da4b"></span>7.30  - 12.40</div>
                                <div><span style="background-color: #4bd712"></span>2.00  - 7.20</div>
                                <div><span style="background-color: #ffd4d7"></span>0.00  - 2.00</div>
                               </div>

                                    <div class="map-overlay-container" id="form_nsl">
                                        <div class="map-overlay">
                                    <h4 style="font-size: 12px"; id="location-title">Periode          : <a name="n_thn"> </a></h4>
                                    <h4 style="font-size: 12px"; id="location-title">Angka   <a name="n_nsl"> </a>%</h4>
                                </div>
                                    </div>
                             </div> 
                         </div>
                    </div>
                 </div>

            </div>
            
            <div class="col-lg-12" style="display: none;" id="pe_pro_d">
                
                <div class="col-lg-12" >
                     <div class="card">
                         <div class="card-header">
                             <h3 class="card-title"> Tingkat Kemiskinan</h3>
                        </div>
                         <div class="card-body">
                             <form class="form_ch" id="form_ch">
                             <p name="ket" style="font-size: 12px"></p>
                             </form>
                             <form class="" id="form_pe">
                                <p name="maxpep" style="font-size: 12px"></p>
                            <p name="perkpdrkp" style="font-size: 12px"></p>
                            </form>
                         </div>
                     </div>
                 </div>

                <div class="col-lg-12"style="display: none;" id="pe_pro_g">
                    <div class="card">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">   </h3> 
                        </div>

                        <div class="panel-body"> 
                            <div class="panel-body">
                            <div id="chart-container-1" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div> 

                <div class="col-lg-12" style="display: none;" id="pe_pro">
                    <div class="card">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">   </h3> 
                        </div>

                        <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                        <div class="card-body">
                            <form class="" id="form_pe_per">
                                <p name="per_pro" style="font-size: 12px"></p>
                            </form>
                            <p></p>
                        </div>
                        <div class="panel-body"> 
                            <div class="panel-body">
                            <div id="chart-container-1-pro" style="height: 300px;"></div>
                            </div>
                        </div>
                        </div>            
                    </div>
                </div>
                <div class="col-lg-12"   style="display: none;" id="pe_rad">
                    <div class="card">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">   </h3> 
                        </div>

                        <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                        <div class="card-body">
                            <form class="" id="form_pe_perk">
                                <p name="rad_kab"></p>
                            </form>
                            <p></p>
                        </div>
                        <div class="panel-body"> 
                            <div class="panel-body">
                            <div id="container-1-rad" style="height: 500px;"></div>
                            </div>
                        </div>
                        </div>            
                    </div>
                </div>

                <div class="col-lg-12"  id="pe_kab" style="display: none;">
                    <div class="card">
                        <div class="panel-heading"> 
                            <h3 class="panel-title">   </h3> 
                        </div>

                        <div  class="pepro" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
                        <div class="card-body">
                            <form class="" id="form_pe_perk" >
                                <p name="per_kab" style="font-size: 12px"></p>
                            </form>
                            <p></p>
                        </div>
                        <div class="panel-body"> 
                            <div class="panel-body">
                            <div id="chart-container-1-kab" style="height: 300px;"></div>
                            </div>
                        </div>
                        </div>            
                    </div>
                </div>
                
            </div>
        
        </div>
        
    </div>

</div> -->

<div id="mdlPro" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
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
                <button type="button" class="btn btn-info waves-effect waves-light" id="save_popup" data-dismiss="modal"><i class="fa fa-search"></i>&nbsp;Pilih</button>
            </div>
        </div>
    </div>
</div>