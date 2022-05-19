<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data extends MX_Model {

	var $pos=[];
	var $cek_tgl=true;
	var $miti_aktual=[];
	public function __construct()
    {
        parent::__construct();
	}

	function get_detail_char(){
		switch (intval($this->pos['data']['type_chat'])){
			case 1:
				$rows = $this->detail_lap_mitigasi();
				break;
			case 2:
				$rows = $this->detail_lap_ketepatan();
				break;
			case 3:
				$rows = $this->detail_lap_komitment();
				break;
			default:
				break;
		}

		return $rows;
	}

	function detail_lap_ketepatan(){
		$owner=[];
		$rows=$this->db->select('*, 0 as target, 0 as aktual , "" as tgl_propose, "" as file  ')->where('owner_code<>','')->get(_TBL_OWNER)->result_array();
		foreach($rows as $row){
			$owner[$row['id']]=$row;
		}

		unset($this->pos['tgl1']);
		$this->filter_data();
		$this->db->where('kode_dept<>','');
		$rows=$this->db->select('owner_id as id, kode_dept as owner_code, owner_name, 0 as status')->group_by(['owner_id', 'kode_dept','owner_name'])->get(_TBL_VIEW_RCSA_APPROVAL_MITIGASI)->result_array();
		$tmp=[];
		foreach($rows as $row){
			$tmp[$row['id']]=$row;
		}
		$id=[];
		foreach($owner as $key=>$row){
			if (array_key_exists($key, $tmp)){
				unset($owner[$key]);
				if (intval($this->pos['data']['param_id'])==1){
					$id[]=$key;
				}
			}else{
				if (intval($this->pos['data']['param_id'])==0){
					$id[]=$row;
				}
			}
		}

		unset($row);
		// dumps($id);
		if (intval($this->pos['data']['param_id'])==1){
			$this->filter_data();
			if (!$id){
				$id[]=0;
			}
			$this->db->where_in('owner_id',$id);
			$rows=$this->db->select('owner_id, kode_dept as owner_code, owner_name, tgl_propose, 0 as status, 0 as target, 0 as aktual,  file_att as file  ')->get(_TBL_VIEW_RCSA_APPROVAL_MITIGASI)->result_array();
		}else{
			$rows=$owner;
		}

		$hasil['data']=$rows;

		return $hasil;
	}

	function filter_data(){
		if ($this->cek_tgl){
			if (isset($this->pos['minggu'])){
				if (intval($this->pos['minggu'])){
					$rows= $this->db->select('*')->where('id', intval($this->pos['minggu']))->get(_TBL_COMBO)->row_array();
					$this->pos['tgl1']=$rows['param_date'];
					$this->pos['tgl2']=$rows['param_date_after'];
				}
			}

			if (!isset($this->pos['tgl1'])){
				if (isset($this->pos['term'])){
					if (intval($this->pos['term'])){
						$rows= $this->db->select('*')->where('id', intval($this->pos['term']))->get(_TBL_COMBO)->row_array();
						$this->pos['tgl1']=$rows['param_date'];
						$this->pos['tgl2']=$rows['param_date_after'];
					}
				}
			}
		}

		if ($this->pos){
			if ($this->pos['owner']){
				if($this->owner_child){
					$this->db->where_in('owner_id', $this->owner_child);
				}
			}
			if ($this->pos['type_ass']){
				$this->db->where('type_ass_id', $this->pos['type_ass']);
			}
			if ($this->pos['period']){
				$this->db->where('period_id', $this->pos['period']);
			}

			if (isset($this->pos['tgl1'])){
				$this->db->where('created_at>=', $this->pos['tgl1']);
				$this->db->where('created_at<=', $this->pos['tgl2']);
			}elseif ($this->pos['minggu']){
				$this->db->where('minggu_id', $this->pos['minggu']);
			}
		}else{
			$this->db->where('period_id', _TAHUN_ID_);
			$this->db->where('term_id', _TERM_ID_);
		}
	}

	function get_data_grap(){
		$owner=[];
		$rows=$this->db->where('owner_code<>','')->get(_TBL_OWNER)->result_array();
		foreach($rows as $row){
			$owner[$row['id']]=$row;
		}

		unset($this->pos['tgl1']);
		$this->filter_data();
		$this->db->where('kode_dept<>','');
		$rows=$this->db->select('owner_id, kode_dept, owner_name, 0 as status')->group_by(['owner_id', 'kode_dept','owner_name'])->get(_TBL_VIEW_RCSA_APPROVAL_MITIGASI)->result_array();
		$tmp=[];
		foreach($rows as $row){
			$tmp[$row['owner_id']]=$row;
		}
		$ownerx=$owner;
		foreach($ownerx as $key=>&$row){
			if (array_key_exists($key, $tmp)){
				$row['status']=1;
			}else{
				$row['status']=0;
			}
		}
		unset($row);

		$hasil['tepat']['owner']=$ownerx;
		$hasil['tepat']['total']=count($owner);
		$hasil['tepat']['sudah']=count($rows);
		$hasil['tepat']['belum']=count($owner)-count($rows);
		$hasil['tepat']['sudah_persen']=number_format((count($rows)/count($owner))*100,2);
		$hasil['tepat']['belum_persen']=number_format(((count($owner)-count($rows))/count($owner))*100,2);

		return $hasil;
	}


	function get_data_lap_basic($tahun = 0, $term = 0)
    {
        if ($tahun == 0)
            $tahun = _TAHUN_ID_;
        if ($term == 0)
            $term = _TERM_ID_;

		$divisi[0] = " - All Department - ";
		// $divisi[-1] = " - All Divisi & Proyek - ";

        $data = array('owner_code<>' => '' );
        $rows = $this->db->where($data)->ORDER_BY('owner_name', 'ASC')->get(_TBL_OWNER)->result_array();
        foreach ($rows as $key => $row){
			$divisi [$row['id']] = $row['owner_code']." - ".$row['owner_name'];
		}

        return $divisi;
    }

	function get_data_lap_owner($tahun = 0, $term = 0)
    {
        if ($tahun == 0)
            $tahun = _TAHUN_ID_;
        if ($term == 0)
            $term = _TERM_ID_;

        $data = array('owner_code<>' => '' );
        $rows = $this->db->where($data)->ORDER_BY('owner_name', 'ASC')->get(_TBL_OWNER)->result_array();
       

        foreach ($rows as $key => $row) {
            $this->owner_child[] = $row['id'];
        }
    }

	function get_data_lap($tahun = 0, $term = 0, $asse = 0, $owner=0, $bulan='')
    {

        $asse_type = [68, 69];
        $this->owner_child = [];
        $this->owner_child[] = $owner;
       
        if ($owner!=0 && $owner!=-1) {
            $this->get_owner_child($owner);
        } else {
            $this->get_data_lap_owner();
        }
      
        $child_div=$this->owner_child;

       
        //$data = array('sts_lapor_kerja' => 1 );
        $parent = $this->db->where('id',$owner)->get(_TBL_OWNER)->row_array();

        $projectx = $this->db->SELECT('*')
        // ->LIKE('level_approval', 81)
        ->WHERE_IN('id', $child_div)->order_by('urut')
        // ->get_compiled_select(_TBL_OWNER);

        ->get(_TBL_OWNER)->result_array();
        

        $rows = $this->db->WHERE('period_id', $tahun)->WHERE('term_id', $term)
        ->where_in('type_ass_id',[128])
        ->WHERE_IN('owner_id', $child_div)->order_by('urut_owner')
        // ->get_compiled_select(_TBL_VIEW_RCSA);

        ->get(_TBL_VIEW_RCSA)->result_array();

      
        if ($bulan!='') {
            // $rows_before = $this->db
            // // ->WHERE('period_no', $tahun)
            // ->WHERE('tgl_mulai_term >=', "date_trunc('month', DATE '".$bulan."' - interval '1' month)", false)
            // ->WHERE('tgl_mulai_term <', "date_trunc('month', DATE '".$bulan."')", false)->where_in('type_ass',[69,68])->WHERE_IN('owner_id', $child_div)->order_by('urut_owner')
            // // ->get_compiled_select(_TBL_VIEW_RCSA);
            // ->get(_TBL_VIEW_RCSA)->result_array();
        }


        // if ($bulan!='') {

        //     $rows_loss = $this->db
        //     ->WHERE('tgl_mulai_term >=', "date_trunc('month', DATE '".$bulan."' - interval '1' month)", false)
        //     ->WHERE('tgl_mulai_term <', "date_trunc('month', DATE '".$bulan."')", false)
        //     // ->WHERE('period_no', $tahun)->WHERE('term_no', $term)
        //     ->where_in('tgl_mulai_term',[69,68])->WHERE('parent', 0)
        //     ->WHERE_IN('owner_id', $child_div)->order_by('urut_owner')->get(_TBL_VIEW_LOSS_EVENT)->result_array();
        // }
        $project=[];
        foreach($rows as $row){
            $project[$row['owner_id']]=$row;
        }
        
        // $project_before=[];
        // if ($bulan!='') {
        //     foreach($rows_before as $row){
        //         $project_before[$row['owner_id']]=$row;
        //     }
        // }

        // $loss=[];
        // if ($bulan!='') {

        //     foreach($rows_loss as $row){
        //         $loss[$row['owner_id']]=$row;
        //     }
        // }
        
        $unit = $this->db->WHERE('period_id', $tahun)->WHERE('term_id', $term)->WHERE_IN('owner_id', $child_div)->get(_TBL_VIEW_RCSA)->result_array();

        
    //     dumps($project);
    //   die();
        $proyek_all = [];
        
        
        // if ($owner != 0) {
            foreach ($projectx as $key => $val) {
                $res['status_final']=0;
                $res['status_final_mitigasi']=0;
                $res['id']=0;
                $bk1=0;
                $bk3=0;
                // $res_before['sts_final']=0;
                // $res_before['sts_final_bk3']=0;
                // $res_before['id']=0;
                // $bk1_before=0;
                // $bk3_before=0;
                // $res_loss['sts_final']=0;
                // $res_loss['id']=0;
                // $bk1_loss=0;
                if ($val['owner_code'] != "") {
                   
                  
                    if (array_key_exists($val['id'], $project)) {
                        $res = $project[$val['id']];
                        $bkx = $res['urut_owner'];
                        $tgl = $res['tgl_mulai_term'];
                        $time = strtotime($tgl);

                        $newformat = date('Y-m',$time);
                        $deadline = $newformat.'-05';
                        if ($res['status_final'] == 1) {
                            $bk1 = 1;
                        }
                        if ($res['status_final_mitigasi'] == 1) {
                            $bk3 = 1;
                        }
                    } else {
                        $res['owner_name'] = $val['owner_name'];
                        $res['kode_dept'] = $val['owner_code'];
                        $res['kategori'] = '';
                        $bkx = 1000;
                        $bk1 = '';
                        $bk3 = '';
                        $tgl = '';
                        $deadline = '';

                        
                    }

                    // if (array_key_exists($val['id'], $project_before)) {
                    //     $res_before = $project_before[$val['id']];
                    //     $bkx_before = $res_before['urut_owner'];
                    //     $tgl_before = $res_before['tgl_mulai_term'];

                    //     if ($res_before['sts_final'] == 1) {
                    //         $bk1_before = 1;
                    //     }
                    //     if ($res_before['sts_final_bk3'] == 1) {
                    //         $bk3_before = 1;
                    //     }
                    // } else {
                    //     $res_before['owner_name'] = $val['owner_name'];
                    //     $res_before['kategori'] = '';
                    //     $bkx_before = 1000;
                    //     $bk1_before = '';
                    //     $bk3_before = '';
                    //     $tgl_before = '';
                    // }

                    // if (array_key_exists($val['id'], $loss)) {
                    //     $res_loss = $loss[$val['id']];
                    //     $bkx_loss = $res_loss['urut_owner'];
                    //     $tgl_loss = $res_loss['tgl_mulai_term'];
                    //     if ($res_loss['sts_final'] == 1) {
                    //         $bk1_loss = 1;
                    //     }
                    // } else {
                    //     $res_loss['owner_name'] = $val['owner_name'];
                    //     $res_loss['kategori'] = '';
                    //     $bkx_loss = 1000;
                    //     $bk1_loss = '';
                    //     $tgl_loss = '';
                        
                    // }
    
                    $proyek_all[] = [
                        $res,
                        // $res_before,
                        // $res_loss,
                        'deadline' => $deadline,
                        'bkx' => $bkx,
                        'bk1' => $bk1,
                        'bk3' => $bk3,
                        'tgl' => $tgl,
                        // 'bkx_before' => $bkx_before,
                        // 'bk1_before' => $bk1_before,
                        // 'bk3_before' => $bk3_before,
                        // 'tgl_before' => $tgl_before,
                        // 'bkx_loss' => $bkx_loss,
                        // 'bk1_loss' => $bk1_loss,
                        // 'tgl_loss' => $tgl_loss,
                    ];
                }
            } 
        // }
        // dumps($proyek_all);
      
        // die();
        
        // ambil kolom yg dibutuhkan untuk sort
        $bkxsort = array_column($proyek_all, 'bkx');

        // sort asc by kolom bkx
        array_multisort($bkxsort, SORT_ASC, $proyek_all);

        $hasil['parent'] = $parent;
        $hasil['proyek_all'] = $proyek_all;
        $hasil['proyek'] = $project;
        $hasil['unit'] = $unit;

        return $hasil;
    }


}
/* End of file app_login_model.php */
/* Location: ./application/models/app_login_model.php */