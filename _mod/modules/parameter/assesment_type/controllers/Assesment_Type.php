<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//NamaTbl, NmFields, NmTitle, Type, input, required, search, help, isiedit, size, label 
//$tbl, 'id', 'id', 'int', false, false, false, true, 0, 4, 'l_id'

class Assesment_Type extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	function init($action='list'){
		$this->cbomodel=$this->crud->combo_select(['id', 'data'])->combo_where('active', 1)->combo_where('kelompok','model-approval')->combo_tbl(_TBL_COMBO)->get_combo()->result_combo();
		
		$this->kelompok_id='ass-type';
		$this->set_Tbl_Master(_TBL_COMBO);
		
		$this->addField(['field'=>'id', 'type'=>'int', 'show'=>false, 'size'=>4]);
		$this->addField(['field'=>'data', 'title'=>'Assessment Type', 'required'=>true, 'search'=>true, 'size'=>100]);
		$this->addField(['field'=>'pid', 'input'=>'combo', 'values'=>$this->cbomodel, 'size'=>100, 'search'=>true]);
		$this->addField(['field'=>'kelompok', 'show'=>false, 'save'=>true, 'default'=>$this->kelompok_id]);
		$this->addField(['field'=>'active', 'input'=>'boolean', 'size'=>20]);

		$this->set_Field_Primary($this->tbl_master, 'id');
		$this->set_Where_Table(['field'=>'kelompok', 'value'=>$this->kelompok_id]);

		$this->set_Sort_Table($this->tbl_master,'data');

		$this->set_Table_List($this->tbl_master,'data');
		$this->set_Table_List($this->tbl_master,'pid');
		$this->set_Table_List($this->tbl_master,'active','',7, 'center');

		$this->set_Close_Setting();

		if (_MODE_=='add') {
			$content_title = 'Penambahan Assesment Type';
		}elseif(_MODE_=='edit'){
			$content_title = 'Perubahan Assesment Type';
		}else{
			$content_title = 'Daftar Assesment Type';
		}

		$configuration = [
			'show_title_header' => false,
			'content_title' => $content_title
		];
		return [
			'configuration'	=> $configuration
		];
	}
}