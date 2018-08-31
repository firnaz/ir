<?	
//include "function.php";
class nanaz{
	//global $konek;
	var $item = 5;
	var $pageNum = 0;
	var $jumlah = 0;
	var $nlist = 0 ;
	
	function listNavigate($page,$item,$sql,$ConStr){
		$this->item = $item;
		//echo $this->item;
		$rs=mysql_query($sql,$ConStr);
		$data=mysql_fetch_row($rs);
		$this->nlist = $page*$this->item;
		$this->jumlah = $data[0];
		$this->pageNum = ceil($this->jumlah / $this->item);
		//print_r($data);
	}
	function showList(){
		return $this->nlist;
	}
	function showItem(){
		return $this->item;
	}
	function showJumlah(){
		return $this->jumlah;
	}
	function showPageNum(){
		return $this->pageNum;
	}
	
	function showPage($halaman,$QString=NULL,$class=NULL){
		for ($i=1 ;$i <=$this->pageNum ; $i++){
			if ($halaman == $i){
				$pagenavigation .= $i." ";
			} else {
				$pagenavigation .= "<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
									"?page=".($i-1)."&halaman=".($i)."$QString' 
									class=$class>$i</a> "; 
			}
		}
		return $pagenavigation;
	}
	function showFirst($halaman,$str,$QString=NULL,$class=NULL){
		if ($halaman ==1){
			$first = $str;
		} else{
			$first="<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
					"?page=0&halaman=0".($i)."$QString' 
					class=$class>$str</a>";			
		}
		return $first;
	}
	function showBack($halaman,$str,$page,$QString=NULL,$class=NULL){
		$back= $page -= $this->item;
		if ($halaman ==1){
			$back = $str;
		} else{
			$back="<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
					"?page=".($back-$this->item)."&halaman=".($halaman-1)."$QString' 
					class=$class>$str</a>";			
		}
		return $back;
	}
	function showNext($halaman,$str,$page,$QString=NULL,$class=NULL){
		$next= $page += $this->item;
		if ($halaman==$this->pageNum || $this->jumlah==0){
			$next = $str;
		} else{
			$next="<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
					"?page=".($next)."&halaman=".($halaman+1)."$QString' 
					class=$class>$str</a>";			
		}
		return $next;
	}
	function showLast($halaman,$str,$QString=NULL,$class=NULL){
		$last=($this->item*$this->pageNum)-$this->item;
		if ($halaman==$this->pageNum || $this->jumlah==0){
			$last = $str;
		} else{
			$last="<a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].
					"?page=".($last)."&halaman=".($this->pageNum)."$QString' 
					class=$class>$str</a>";			
		}
		return $last;
	}
}
?>