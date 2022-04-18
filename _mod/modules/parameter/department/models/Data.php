<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data extends MX_Model {
	public function __construct()
    {
        parent::__construct();
	}
	
	function get_data_posisi_menu(){
		$this->db->select('*');
		$this->db->from(_TBL_DEPARTMENT);
		$this->db->where('active',1);
		$this->db->order_by('urut');
		$query=$this->db->get();
		$rows=$query->result_array();
		$input=[];
		foreach($rows as $row){
			$input[] = array("id" => $row['id'], "title" => $row['department'], "slug" => $row['pid'], "pid" => $row['pid'], "urut" => $row['urut'], "active" => $row['active'], "sts_distribusi" => $row['sts_distribusi'], "jml" => 0);
		}
		
		$result = _tree($input);
		return $result;
	}
	
	function simpan_data($data){
		$output_data = stripslashes($data['data']);
		$rows = json_decode($output_data);
		$type='update';
		$n = 0;
		foreach($rows as $row) { 
			$n++; 
			$n1 = 0;
			$update_id = $row->id;
			
			$this->crud->crud_table(_TBL_DEPARTMENT);
			$this->crud->crud_type('edit');
			$this->crud->crud_field('pid', 0, 'int');
			$this->crud->crud_field('urut', $n, 'int');
			$this->crud->crud_field('level', 0, 'int');
			$this->crud->crud_where(['field'=>'id', 'value'=>$row->id]);
			$this->crud->process_crud();

			if(!empty($row->children)){
			foreach ($row->children as $vchild){ 
				$n1++; 
				$n2 = 0;
				
				$this->crud->crud_table(_TBL_DEPARTMENT);
				$this->crud->crud_type('edit');
				$this->crud->crud_field('pid', $row->id, 'int');
				$this->crud->crud_field('urut', $n1, 'int');
				$this->crud->crud_field('level', 1, 'int');
				$this->crud->crud_where(['field'=>'id', 'value'=>$vchild->id]);
				$this->crud->process_crud();

				if(!empty($vchild->children)){
				foreach ($vchild->children as $vchild1){ 
					$n2++; 
					$n3 = 0;

					$this->crud->crud_table(_TBL_DEPARTMENT);
					$this->crud->crud_type('edit');
					$this->crud->crud_field('pid', $vchild->id, 'int');
					$this->crud->crud_field('urut', $n2, 'int');
					$this->crud->crud_field('level', 2, 'int');
					$this->crud->crud_where(['field'=>'id', 'value'=>$vchild1->id]);
					$this->crud->process_crud();

					if(!empty($vchild1->children)){
					foreach ($vchild1->children as $vchild2){ 
						$n3++; 
						$n4 = 0;

						$this->crud->crud_table(_TBL_DEPARTMENT);
						$this->crud->crud_type('edit');
						$this->crud->crud_field('pid', $vchild1->id, 'int');
						$this->crud->crud_field('urut', $n3, 'int');
						$this->crud->crud_field('level', 3, 'int');
						$this->crud->crud_where(['field'=>'id', 'value'=>$vchild2->id]);
						$this->crud->process_crud();

						if(!empty($vchild2->children)){
						foreach ($vchild2->children as $vchild3){ 
							$n4++;
							$n5=0;

							$this->crud->crud_table(_TBL_DEPARTMENT);
							$this->crud->crud_type('edit');
							$this->crud->crud_field('pid', $vchild2->id, 'int');
							$this->crud->crud_field('urut', $n4, 'int');
							$this->crud->crud_field('level', 4, 'int');
							$this->crud->crud_where(['field'=>'id', 'value'=>$vchild3->id]);
							$this->crud->process_crud();

							if(!empty($vchild3->children)){
							foreach ($vchild3->children as $vchild4){ 
								$n5++;
								$n6=0;

								$this->crud->crud_table(_TBL_DEPARTMENT);
								$this->crud->crud_type('edit');
								$this->crud->crud_field('pid', $vchild3->id, 'int');
								$this->crud->crud_field('urut', $n5, 'int');
								$this->crud->crud_field('level', 5, 'int');
								$this->crud->crud_where(['field'=>'id', 'value'=>$vchild4->id]);
								$this->crud->process_crud();

							}
							}
						}
						}
					}
					}
				}
				}
			}
			}
		}

		return TRUE ;
	}
}
/* End of file app_login_model.php */
/* Location: ./application/models/app_login_model.php */