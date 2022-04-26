<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Map
{
	private $_ci;
    private $preference=array();
    private $impact=[];
    private $like=[];
    private $level=[];
    private $_data=[];
    private $_param=[];
    private $total_nilai=0;
    private $jmlstatus=[];

	function __construct()
	{
		$this->_ci =& get_instance();

		if ($x=$this->_ci->session->userdata('preference')){
			$this->preference=$this->_ci->session->userdata('preference');
        }

        $this->like = $this->_ci->db->where('category', 'likelihood')->order_by('urut', 'desc')->get(_TBL_LEVEL)->result_array();
        $this->impact = $this->_ci->db->where('category', 'impact')->order_by('urut')->get(_TBL_LEVEL)->result_array();
        $this->level = $this->_ci->db->order_by('urut')->get(_TBL_LEVEL_COLOR)->result_array();
        $this->_clear();

	}

	function initialize($config = array())
	{

    }

    function _clear(){
        $rows = $this->_ci->db->get(_TBL_VIEW_MATRIK_RCSA)->result_array();
        $this->_data=[];
        foreach($rows  as $key=>$row){
            $this->_data[$row['id']]=$row;
        }
        $this->_param=[];
    }

    function set_data($data=[]){
        if ($data){
            foreach($data as $row){
                if (array_key_exists($row['id'],$this->_data)){
                    $this->_data[$row['id']]['nilai'] = $row['nilai'];
                    if (array_key_exists('level_color', $data)) {
                        $this->_data[$row['id']]['level_color'] = $row['level_color'];
                        $this->_data[$row['id']]['level_color_residual'] = $row['level_color_residual'];
                        $this->_data[$row['id']]['level_color_target'] = $row['level_color_target'];
                    }
                }
            }
        }
        return $this;
    }

    function set_data_profile($data=[], $post){
      
        if ($data){
            $no = 0;

            foreach($data as $row){
                if (array_key_exists($row['id'],$this->_data)){
                    if($post['term_mulai']>0){
                        if($post['term_mulai']==$row['minggu_id']){
                            $this->_data[$row['id']]['mulai']['nilai'] = ++$no;
                            if (array_key_exists('level_color', $data)) {
                                $this->_data[$row['id']]['mulai']['level_color'] = $row['level_color'];
                                $this->_data[$row['id']]['mulai']['level_color_residual'] = $row['level_color_residual'];
                                $this->_data[$row['id']]['mulai']['level_color_target'] = $row['level_color_target'];
                            }
                        }
                    }
                    
                }
            }
            $no = 0;

            foreach($data as $row){
                if (array_key_exists($row['id'],$this->_data)){
                    if($post['term_akhir']>0){
                        if($post['term_akhir']==$row['minggu_id']){
                            $this->_data[$row['id']]['akhir']['nilai'] = ++$no;
                            if (array_key_exists('level_color', $data)) {
                                $this->_data[$row['id']]['akhir']['level_color'] = $row['level_color'];
                                $this->_data[$row['id']]['akhir']['level_color_residual'] = $row['level_color_residual'];
                                $this->_data[$row['id']]['akhir']['level_color_target'] = $row['level_color_target'];
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    function set_param($params=[]){
        if (is_array($params)){
            foreach($params as $key=>$row){
                $this->_param[$key]=$row;
            }
        }
        return $this;
    }

    function draw(){
       
        // var_dump($this->_data);
        $this->total_nilai=0;
        $this->jmlstatus=[];
        $content = '<table style="text-align:center;" border="1" width="100%">';
        $content .= '<tr><td colspan="2" rowspan="3" width="25%"><strong>PERINGKAT<br/>KEMUNGKINAN<br/>RISIKO</strong></td>';
        $content .= '<td colspan="5"><strong>PERINGKAT DAMPAK RISIKO</strong></td></tr>';
        foreach ($this->impact as $key => $row) {
            $content .= '<td class="text-center" style="padding:5px;" width="15%">' . $row['level'] . '</td>';
        }
        $content .= '</tr><tr>';
        foreach ($this->impact as $key => $row) {
            $content .= '<td style="padding:5px;">' . $row['urut'] . '</td>';
        }
        $no = 0;
        $noTd = 5;
        $nourut = 0;
        $arrBorder=[];
        $key=0;
        foreach ($this->_data as $keys => $row) {
            $icon='&nbsp;&nbsp;';
            if (!empty($row['icon'])){
                $icon=show_image($row['icon'], 0, 10, 'slide', 0, 'pull-right');
            }

            $apetite = ' <i class="fa fa-minus-circle pull-right text-primary"></i> ';

            $icon='&nbsp;&nbsp;';
            ++$no;
            $nilai = (!empty($row['nilai'])) ? $row['nilai'] : "";
            
            if ($this->_param['tipe'] == 'angka') {
                $nilaiket = $nilai;
            } else {
                $nilaiket = (!empty($row['nilai'])) ? '<i class="icon-checkmark-circle" style="color:' . $row['warna_txt'] . '"></i>' : "";
            }

            $this->jmlstatus[] = ['nilai'=>intval($nilai), 'tingkat'=>$row['tingkat']];
            $this->total_nilai+=intval($nilai);
            if ($key == 0) {
                $content .= '<tr><td class="text-center" width="15%" style="padding:5px;">' . $this->like[$nourut]['level'] . '</td><td style="padding:5px;" width="5%">' . $this->like[$nourut]['urut'] . '</td>';
                ++$nourut;
            }

            $notif = '<strong>' . $row['tingkat'] . '</strong><br/>Standar Nilai :<br/>Impact: [ >' . $row['bawah_impact'] . ' s.d <=' . $row['atas_impact'] . ']<br/>Likelihood: [ >' . $row['bawah_like'] . ' s.d <=' . $row['atas_like'] . ']';

            $content .= '<td data-level="'.$this->_param['level'].'" data-id="' . $row['id'] . '" class="pointer detail-peta" style="background-color:' . $row['warna_bg'] . ';color:' . $row['warna_txt'] . ';padding:5px;border:solid 1px black; font-size:16px; font-weight:bold;height:30px !important;" data-trigger="hover" data-toggle = "popover" data-placement="top" data-html="true" data-content="'.$notif.'" data-nilai="' . $nilai . '" ><div class="containingBlock">' . $nilaiket.'</div></td>';
            if ($no % 5 == 0 && $key < 24) {
                --$noTd;
                $content .= '</tr><tr><td width="15%" class="td-nomor-v" style="padding:5px;text-align:center;">' . $this->like[$nourut]['level'] . '</td><td width="5%" style="padding:5px;">' . $this->like[$nourut]['urut'] . '</td>';
                ++$nourut;
            }
            ++$key;
        }
        
        $content .= '</tr>';
        $content .= '</table ><br/>&nbsp;';
       
        $this->_clear();
        return $content;
    }

    function draw_profile(){
       
        // dumps($this->_data);
        // die();
        $this->total_nilai=0;
        $this->jmlstatus=[];
        $content = '<table style="text-align:center;" border="1" width="100%">';
        $content .= '<tr><td colspan="2" rowspan="3" width="25%"><strong>PERINGKAT<br/>KEMUNGKINAN<br/>RISIKO</strong></td>';
        $content .= '<td colspan="5"><strong>PERINGKAT DAMPAK RISIKO</strong></td></tr>';
        foreach ($this->impact as $key => $row) {
            $content .= '<td class="text-center" style="padding:5px;" width="15%">' . $row['level'] . '</td>';
        }
        $content .= '</tr><tr>';
        foreach ($this->impact as $key => $row) {
            $content .= '<td style="padding:5px;">' . $row['urut'] . '</td>';
        }
        $no = 0;
        $noTd = 5;
        $nourut = 0;
        $arrBorder=[];
        $key=0;
        foreach ($this->_data as $keys => $row) {
            $icon='&nbsp;&nbsp;';
            if (!empty($row['icon'])){
                $icon=show_image($row['icon'], 0, 10, 'slide', 0, 'pull-right');
            }

            $apetite = ' <i class="fa fa-minus-circle pull-right text-primary"></i> ';

            $icon='&nbsp;&nbsp;';
            ++$no;
            $nilai = (!empty($row['mulai']['nilai'])) ? $row['mulai']['nilai'] : "";
            $nilaiakhir = (!empty($row['akhir']['nilai'])) ? $row['akhir']['nilai'] : "";
            
            if ($this->_param['tipe'] == 'angka') {
                $nilaiket = (!empty($nilai)) ? '<span class="badge bg-primary badge-pill badge-sm"> '.$nilai.'</span>':$nilai;
                $nilaiketakhir = (!empty($nilaiakhir)) ? '<span class="badge bg-info badge-pill badge-sm"> '.$nilaiakhir.'</span>':$nilaiakhir;
            } else {
                $nilaiket = (!empty($row['mulai']['nilai'])) ? '<i class="icon-checkmark-circle" style="color:' . $row['warna_txt'] . '"></i>' : "";
                $nilaiketakhir = (!empty($row['akhir']['nilai'])) ? '<i class="icon-checkmark-circle" style="color:' . $row['warna_txt'] . '"></i>' : "";
            }

            $this->jmlstatus[] = ['nilai'=>intval($nilai), 'tingkat'=>$row['tingkat']];
            $this->total_nilai+=intval($nilai);
            if ($key == 0) {
                $content .= '<tr><td class="text-center" width="15%" style="padding:5px;">' . $this->like[$nourut]['level'] . '</td><td style="padding:5px;" width="5%">' . $this->like[$nourut]['urut'] . '</td>';
                ++$nourut;
            }

            $notif = '<strong>' . $row['tingkat'] . '</strong><br/>Standar Nilai :<br/>Impact: [ >' . $row['bawah_impact'] . ' s.d <=' . $row['atas_impact'] . ']<br/>Likelihood: [ >' . $row['bawah_like'] . ' s.d <=' . $row['atas_like'] . ']';

            $content .= '<td data-level="'.$this->_param['level'].'" data-id="' . $row['id'] . '" class="pointer detail-peta" style="background-color:' . $row['warna_bg'] . ';color:' . $row['warna_txt'] . ';padding:5px;border:solid 1px black; font-size:16px; font-weight:bold;height:30px !important;" data-trigger="hover" data-toggle = "popover" data-placement="top" data-html="true" data-content="'.$notif.'" data-nilai="' . $nilai . '" ><div class="containingBlock">' . $nilaiket.'<br>'.$nilaiketakhir.'</div></td>';
            if ($no % 5 == 0 && $key < 24) {
                --$noTd;
                $content .= '</tr><tr><td width="15%" class="td-nomor-v" style="padding:5px;text-align:center;">' . $this->like[$nourut]['level'] . '</td><td width="5%" style="padding:5px;">' . $this->like[$nourut]['urut'] . '</td>';
                ++$nourut;
            }
            ++$key;
        }
        
        $content .= '</tr>';
        $content .= '</table ><br/>&nbsp;';
       
        $this->_clear();
        return $content;
    }

    function get_total_nilai(){
        return $this->total_nilai;
    }

    function get_jumlah_status(){
        $content = "";
        $status = [];
        $total = 0;
        $totpersentase = 0;
        foreach ($this->jmlstatus as $keys => $row) {
            $status[$row['tingkat']] = 0;
        }
        foreach ($this->jmlstatus as $keys => $row) {
            $status[$row['tingkat']] += $row['nilai'];
        }
     
        foreach ($this->level as $keys => $row) {
            $total += $status[$row['level_color']];
        }
        $content .= '<table style="text-align:center;" border="1" width="40%">';
        $content .= '<tr><td>Status Risiko</td><td>Jumlah</td><td>Persentase</td></tr>';
        foreach ($this->level as $keys => $row) {
            $persentase = ($total>0)?round(($status[$row['level_color']]/$total)*100, 1):0;
            $content .= '<tr><td style="background-color:' . $row['color'] . ';color:' . $row['color_text'].'">'.$row['level_color'].'</td><td>'.$status[$row['level_color']].'</td><td>'.$persentase.'%</td></tr>';
            $totpersentase += $persentase;

        }
        $content .= '<tr><td>Total Risiko</td><td>'.$total.'</td><td>'.round($totpersentase).'%</td></tr>';
        $content .= '</table><br/>&nbsp;';
        return $content;
    }

	
}

// END Template class