<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class stringGrid{
    function GetCollumned($tabArray, $textRows, $colSep, $newLine){
        
        $jml_row = count($textRows);
        $jml_col = count($tabArray['colWidth']);
        for($r=0; $r<$jml_row; $r++){
            for($c=0; $c<$jml_col; $c++){
                $str.=$this->DrawString($textRows[$r][$c], $tabArray['colWidth'][$c], $tabArray['hAlign'][$c]);
                $str.=$colSep;
                }
                $str.=$newLine;
                }
                return $str;
    }
     function DrawString($str, $width, $align){
  
  // LEFT ALIGN
  if($align==0){
   $str=str_pad($str, $width, ' ', STR_PAD_RIGHT);
  }
  // CENTER ALIGN
  elseif($align==1){
   $str=str_pad($str, $width, ' ', STR_PAD_BOTH);   
  }
  // RIGHT ALIGN
  else{
   if(is_numeric(str_replace('.','',$str))){
    $pl=' '; 
   }else{
    $pl=''; 
   }   
   $str=str_pad($str.$pl, $width, ' ', STR_PAD_LEFT);      
  }
  return $str;
 }
}
function line($width,$line){
 return str_pad('', $width, $line, STR_PAD_BOTH);
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

