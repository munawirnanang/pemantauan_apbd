    var main = function(){
        controller = "index.php/Gis";
        var datatable = function(){
            
          $(".btnMenu").click(function(e){
              $('#mdlProKab').modal('show');
          });
          $(".btnMenuJP").click(function(e){
              $('#mdljp_ProKab').modal('show');
          });
          $(".btnMenuTPT").click(function(e){
              $('#mdltpt_ProKab').modal('show');
          });
          $(".btnMenuHLS").click(function(e){
              $('#mdlhls_ProKab').modal('show');
          });
        $(".btnMenuIPM").click(function(e){
              $('#mdlipm_ProKab').modal('show');
          });
          $(".btnMenuKK").click(function(e){
              $('#mdlkk_ProKab').modal('show');
          });
        $(".btnMenuPE").click(function(e){
              $('#mdlpe_ProKab').modal('show');
          });
        $(".btnMenuPPK").click(function(e){
              $('#mdlppk_ProKab').modal('show');
          });
          
          $(".btnMenuGR").click(function(e){
              $('#mdlgr_ProKab').modal('show');
          });
          $(".btnMenuRLS").click(function(e){
              $('#mdlrls_ProKab').modal('show');
          });
        
          $(".btnMenuADHB").click(function(e){
              $('#mdladhb_ProKab').modal('show');
          });
          $(".btnMenuKK2").click(function(e){
              $('#mdlp2_ProKab').modal('show');
          });
          
          $(".btnMenuADHK").click(function(e){
              $('#mdladhk_ProKab').modal('show');
          });
          $(".btnMenuPPM").click(function(e){
              $('#mdlppm_ProKab').modal('show');
          });
          $(".btnMenuJPM").click(function(e){
              $('#mdljpm_ProKab').modal('show');
          });
        };
        
   
    return{
        init:function(){datatable();},
       // detail:function(){chart();},
    };
    }();