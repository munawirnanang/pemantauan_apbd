<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of M_master
 *
 * @author ilham.dinov
 */
class M_Master extends CI_Model {
    var $table      = "";
    var $primary    = "";
    
    function setTableName($tableName){
        return $this->table = $tableName;
    }
    function setTablePrimary($PrimaryKey){
        return $this->primary = $PrimaryKey;
    }
    
    function save($isi){
        return $this->db->insert($this->table,$isi);
    }
    function delete($condition){
        $this->db->where($condition);
        return $this->db->delete($this->table);
    }
    function update($condition,$isi){
        $this->db->where($condition);
        return $this->db->update($this->table, $isi);  
    }
    function get_by_condition($select=array(),$condition=array()){
        $this->db->select($select);
        $this->db->where($condition);
        return $this->db->get($this->table);
    }
}
