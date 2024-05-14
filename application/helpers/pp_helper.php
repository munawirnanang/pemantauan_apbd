<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_encrypt_text'))
{
    function get_encrypt_text(){
        $crypt_text = "dfuzzy";
        return $crypt_text;
    }
}

//===============================================================================================
//Encrypt text
//===============================================================================================
if (!function_exists('encrypt_text'))
{
    function encrypt_text($text){

        $crypt_text = get_encrypt_text();

        if (!$text && $text != "0") return false;
        if (!$crypt_text) return false;

        $key_val = key_value($crypt_text);
        $estr = "";

        for ($i=0; $i<strlen($text); $i++) {
            $chr = ord(substr($text, $i, 1));
            $chr = $chr + $key_val[1];
            $chr = $chr * $key_val[2];
            (double)microtime()*1000000;
            $rstr = chr(rand(97, 122));
            $estr .= "$rstr$chr";
        }

        return $estr;
    }
}

//===============================================================================================
//Decrypt text
//===============================================================================================
if (!function_exists('decrypt_text'))
{
    function decrypt_text($text){
        
        $crypt_text = get_encrypt_text();

        if (!$text && $text != "0") return false;
        if (!$crypt_text) return false;

        $key_val = key_value($crypt_text);
        $estr = "";
        $tmp = "";

        for ($i=0; $i<strlen($text); $i++) {
            if ( ord(substr($text, $i, 1)) > 96 && ord(substr($text, $i, 1)) < 123 ) {
                if ($tmp != "") {
                    $tmp = $tmp / $key_val[2];
                    $tmp = $tmp - $key_val[1];
                    $estr .= chr($tmp);
                    $tmp = "";
                }
            }
            else {
                $tmp .= substr($text, $i, 1);
            }
        }

        $tmp = $tmp / $key_val[2];
        $tmp = $tmp - $key_val[1];
        $estr .= chr($tmp);

        return $estr;
    }
}

//===============================================================================================
//For encryption-decrypt key value
//===============================================================================================

if (!function_exists('key_value'))
{
    function key_value($crypt_text){
        $key_val = "";
        $key_val[1] = "0";
        $key_val[2] = "0";
        for ($i=1; $i<strlen($crypt_text); $i++) {
            $cur_char = ord(substr($crypt_text, $i, 1));
            $key_val[1] = $key_val[1] + $cur_char;
            $key_val[2] = strlen($crypt_text);
        }
        return $key_val;
    }
}

//===============================================================================================
//author    : ilham
//date      : 8-2-2017
//purpose   : clean special characters from a string
//===============================================================================================

if (!function_exists('clean_string'))
{
    function clean_string($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}

//===============================================================================================
//author    : ilham
//date      : 9-3-2017
//purpose   : check user previlege each menu
//===============================================================================================

if (!function_exists('check_access_rights'))
{
    function check_access_rights($roleid,$menuid) {
        
        $CI =& get_instance();
        $sql = "SELECT id FROM tbl_otoritas WHERE id_role=? AND id_menu=?";
        $list = $CI->db->query($sql,array($roleid,$menuid));
        $status = true;
        $msg = "";
        if($list->num_rows()==0)
            $status = FALSE;
        
        $out = array(
            "status"    =>  $status,
            "msg"       =>  $msg,
        );
        return $out; // Removes special chars.
    }
}

//===============================================================================================
//author    : ilham
//date      : 15-2-2018
//purpose   : generate string inside TD tag table in bukti pendukung menu
//===============================================================================================

if (!function_exists('generate_link_bukti'))
{
    function generate_link_bukti($link=NULL) {
        $str = "";
        if($link!=NULL)
            $str='[ <a class="btn-link" href="'.$link.'" target="_blank" title="Klik untuk melihat detail">Klik</a> ]';
        return $str;
    }
}

//===============================================================================================
//author    : ilham
//date      : 28-2-2018
//purpose   : generate string inside TD tag table in permaslahan menu
//===============================================================================================

if (!function_exists('generate_link_permasalahan'))
{
    function generate_link_permasalahan($idmasalah=NULL) {
        $str = "";
        if($idmasalah!=NULL)
            $str='<a class="btn-xs btn-link detail" data-id="'.$idmasalah.'" title="Klik untuk melihat detail"><i class="fa fa-eye"></i></a>';
        return $str;
    }
}

//===============================================================================================
//author    : ilham
//date      : 14-2-2018
//purpose   : generate progress bar 
//===============================================================================================

if (!function_exists('generate_str_progressbar'))
{
    function generate_str_strip($type="progress-bar-success",$value=0) {
        $str = '<div class="progress progress-sm">
                                                <div class="progress-bar '.$type.' progress-bar-striped active" role="progressbar" aria-valuenow="'.$value.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$value.'%;">
                                                    <span class="sr-only">'.$value.'% Complete</span>
                                                </div>
                                            </div>';
        return $str;
    }
}

////===============================================================================================
//author    : ilham
//date      : 20-4-2018
//purpose   : generate output td 
//===============================================================================================

if (!function_exists('generate_str_output_laporan_digital'))
{
    function generate_str_output_laporan_digital($inp=array(),$bulan=0,$arr_notif=array(),$arr_msl=array()) {
        $r_ang      = $inp["r_ang"];
        $r_fisik    = $inp["r_fisik"];
        $r_notif    = $inp["r_notif"];
        $r_msl      = $inp["r_msl"];
        $r_msl_dsc  = $inp["r_msl_dsc"];
        $r_ntf_dsc  = $inp["r_ntf_dsc"];
        $pagu_ang   = $inp["pagu_ang"];
        $vol = $inp["vol"];
        $percentage_ang = $pagu_ang==NULL||$r_ang==""||$pagu_ang==0?0:(number_format($r_ang/$pagu_ang,2)*100);
        $percentage_fisik =$vol==NULL||$r_fisik==""||$r_fisik==0||$vol==0?0:(number_format($r_fisik/$vol,3)*100);
        $kinerja_ttl = $r_notif==""?"":(($percentage_fisik+$percentage_ang)/2)." %";
        
        $str = "";
        $class = " triw_odd";
        if($bulan%2==0)
            $class="";
        //anggaran
        $tmp = $r_ang==""?'':number_format($r_ang);
        $str .= "<td class='text-right ".$class."'>".$tmp."</td>";
        
        //%
        $tmp = $r_notif==""?"":$percentage_ang." %";
        $str .= "<td class='text-right ".$class."'>".$tmp." </td>";
        
        //fisik
        $tmp = $r_fisik==""?'':number_format($r_fisik);
        $str .= "<td class='text-right ".$class."'>".$tmp."</td>";
        
        //%
        $tmp = $r_notif==""?"":$percentage_fisik." %";
        $str .= "<td class='text-right ".$class."'>".$tmp."</td>";
        
        //kinerja total
        $str .= "<td class='text-right ".$class."'>".$kinerja_ttl."</td>";
        //notifikasi
        $tmp = $r_fisik==""?'':$arr_notif[$r_notif];
        $str .= "<td class='text-left ".$class."'>".$tmp."</td>";
        //notifikasi remark
        $tmp = $r_ntf_dsc==""?'':$r_ntf_dsc;
        $str .= "<td class='text-left ".$class."'>".wordwrap($tmp,45,"<br/>")."</td>";
        //permasalahan
        $tmp = "";
        if($r_fisik!=""){
            $tmp_msl = explode(',',$r_msl);
            foreach ($tmp_msl as $v) {
                $tmp.=$arr_msl[$v].', ';
            }
        }
        $str .= "<td class='text-left ".$class."'>".$tmp."</td>";
        
        //permasalahan desc
        $tmp = $r_fisik==""?'':$r_msl_dsc;
        $str .= "<td class='text-left ".$class."'>".wordwrap($tmp, 45, "<br/>")."</td>";
        return $str;
    }
}

//===============================================================================================
//author    : ilham
//date      : 21-9-2018
//purpose   : generate home dashboard icon status
//===============================================================================================

if (!function_exists('generate_str_dashboard_status'))
{
    function generate_str_dashboard_status($percentage) {
        $str = "<br><i class='fa fa-check-circle-o fa-2x text-success'></i>"
                . "<br><br><b>".$percentage."</b> %";
        if($percentage<100)
            $str = "<br><i class='fa fa-exclamation fa-2x text-danger'></i>"
                . "<br><br>".$percentage." %";
        return $str;
    }
}

if (!function_exists('decrypt_base64'))
{
    function decrypt_base64($id) {
        $_id=   base64_decode($id);
        $out=   openssl_decrypt($_id,"AES-128-ECB",ENCRYPT_PASS);
        return $out; 
    }
}