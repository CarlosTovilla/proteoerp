<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class Banc extends Validaciones {

	function banc() {
		parent::Controller();
		//$this->load->helper('form');
		//$this->load->helper('url');
		//$this->load->helper('text');
		$this->load->library('rapyd');
	}

	function index(){
		$this->datasis->modulo_id(512,1);
		redirect('finanzas/banc/filteredgrid');
	}

	function filteredgrid() {
		$this->rapyd->load('datafilter','datagrid');
		$filter = new DataFilter('Filtro de Bancos y cajas', 'banc');

		$filter->codbanc = new inputField('C&oacute;digo', 'codbanc');
		$filter->codbanc->size=12;

		$filter->banco = new dropdownField('Banco', 'tbanco');
		$filter->banco->option('','Todos');
		$filter->banco->options("SELECT TRIM(cod_banc),TRIM(nomb_banc) FROM tban ORDER BY nomb_banc");
		$filter->banco->style ='width:200px;';

		$filter->numcuenta = new inputField('Nro. de Cuenta', 'numcuent');
		$filter->numcuenta->size = 20;

		$filter->buttons('reset','search');
		$filter->build('dataformfiltro');

		$uri = anchor('finanzas/banc/dataedit/show/<#codbanc#>','<#codbanc#>');

		function pinta($activo,$palabra){
			if($activo=='N'){
				return "<b style='color:#ff0000;'>$palabra</b>";
			}
			return $palabra;
		}
		$grid = new DataGrid('Bancos y cajas');
		$grid->per_page = 20;
		$grid->use_function('pinta');
		$grid->column_orderby('C&oacute;digo',$uri,'codbanc');
		$grid->column_orderby('Tipo','<pinta><#activo#>|<#tbanco#></pinta>','tbanco');
		$grid->column_orderby('Banco','<pinta><#activo#>|<#banco#></pinta>','banco');
		$grid->column_orderby('Nro Cuenta','<pinta><#activo#>|<#numcuent#></pinta>','numcuent');
		$grid->column_orderby('Saldo','<pinta><#activo#>|<nformat><#saldo#></nformat></pinta>','saldo','align=right');
		$grid->column_orderby('Contable','<pinta><#activo#>|<#cuenta#></pinta>','cuenta');
		$grid->column('Telefono','telefono');
		$grid->column('Nombre','nombre');
		$grid->column('Activo','activo','activo');
		$grid->add('finanzas/banc/dataedit/create','Agregar');
		$grid->build('datagridST');
		//echo $grid->db->last_query();

//************ SUPER TABLE ************* 
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
	headerRows : 1,
	onStart : function () {	this.start = new Date();},
	onFinish : function () {document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";}
	});
})();
//]]>
</script>
';
		$style ='
<style type="text/css">
.fakeContainer { /* The parent container */
    margin: 5px;
    padding: 0px;
    border: none;
    width: 740px; /* Required to set */
    height: 320px; /* Required to set */
    overflow: hidden; /* Required to set */
}
</style>	
';
//****************************************

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');

		$data['extras']  = $extras;		

		$data['script']  = script('jquery.js');
		$data["script"] .= script('superTables.js');

		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;

		$data['title']   = '<h1>Bancos y Cajas</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->load('dataedit');

		$atts = array(
			'width'     =>'800',
			'height'    =>'600',
			'scrollbars'=>'yes',
			'status'    =>'yes',
			'resizable' =>'yes',
			'screenx'   =>'5',
			'screeny'   =>'5');

		$qformato=$this->qformato=$this->datasis->formato_cpla();

		$mCPLA=array(
			'tabla'   =>'cpla',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo'=>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'cuenta'),
			'titulo'  =>'Buscar Cuenta',
			'where'=>"codigo LIKE \"$qformato\"",
			);

		$bcpla =$this->datasis->modbus($mCPLA);

		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
			'proveed' =>'C&oacute;digo Proveedor',
			'nombre'=>'Nombre',
			'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'codprv'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

		$mTBAN=array(
			'tabla'   =>'tban',
			'columnas'=>array(
				'cod_banc' =>'C&oacute;digo',
				'nomb_banc'=>'Banco'),
			'filtro'  =>array('cod_banc'=>'C&oacute;digo','nomb_banc'=>'Banco'),
			'retornar'=>array('cod_banc'=>'tbanco','nomb_banc'=>'banco'),
			'titulo'  =>'Buscar Banco'
			);

		$bTBAN =$this->datasis->modbus($mTBAN);

		$link=site_url('finanzas/banc/ubanc');
		$script ='
		function gasto(){
			a=parseInt(dbporcen.value);
			if(a>0 && a<100){
				$("#tr_gastoidb").show();
			}else{
				$("#tr_gastoidb").hide();
			}
		}

		function ultimo(){
			$.ajax({
				url: "'.$link.'",
				success: function(msg){
				  alert( "El ultimo codigo ingresado fue: " + msg );
				}
			});
		}
		$(function() {
			gasto();
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Bancos y cajas', 'banc');
		$edit->back_url = site_url('finanzas/banc/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$lultimo='<a href="javascript:ultimo();" title="Consultar ultimo codigo ingresado" onclick="">Consultar ultimo codigo</a>';
		$edit->codbanc = new inputField('C&oacute;digo', 'codbanc');
		$edit->codbanc->rule = 'trim|required|callback_chexiste';
		$edit->codbanc->mode="autohide";
		$edit->codbanc->maxlength=2;
		$edit->codbanc->size =3;
		$edit->codbanc->append($lultimo);

		$edit->activo = new dropdownField('Activo', 'activo');
		$edit->activo->style ='width:50px;';
		$edit->activo->rule='required';
		$edit->activo->options(array('S'=>'Si','N'=>'No' ));

		$edit->tbanco = new inputField('Caja/Banco', 'tbanco');
		$edit->tbanco->size =12;
		$edit->tbanco->maxlength =3;
		$edit->tbanco->rule='trim|required';
		$edit->tbanco->readonly=true;
		$edit->tbanco->append($bTBAN);
		$edit->tbanco->style ='width:80px;';

		$edit->banco = new inputField('Nombre', 'banco');
		$edit->banco->size =25;
		$edit->banco->maxlength=30;
		//$edit->banco->readonly=true;

		$edit->numcuent = new inputField('Nro. de Cuenta', 'numcuent');
		$edit->numcuent->rule='trim';
		$edit->numcuent->size = 25;
		$edit->numcuent->maxlength=25;
		
		$edit->dire1 = new inputField('Direcci&oacute;n', 'dire1');
		$edit->dire1->rule='trim';
		$edit->dire1->size =45;
		$edit->dire1->maxlength=40;

		$edit->dire2 = new inputField('', 'dire2');
		$edit->dire2->rule='trim';
		$edit->dire2->size =45;
		$edit->dire2->maxlength=40;

		$edit->telefono = new inputField('Tel&eacute;fono', 'telefono');
		$edit->telefono->rule='trim';
		$edit->telefono->size =45;
		$edit->telefono->maxlength=40;

		$edit->nombre = new inputField('Gerente', 'nombre');
		$edit->nombre->rule='trim';
		$edit->nombre->size =45;
		$edit->nombre->maxlength=40;

		$edit->moneda = new dropdownField('Moneda','moneda');
		$edit->moneda->options("SELECT moneda, descrip FROM mone ORDER BY moneda");
		$edit->moneda->style ="width:100px;";

		$edit->tipocta = new dropdownField('Cuenta Tipo', 'tipocta');
		$edit->tipocta->style ="width:100px;";
		$edit->tipocta->options(array("K"=>"Caja","C"=>"Corriente","A" =>"Ahorros","P"=>"Plazo Fijo" ));

		$edit->proxch = new inputField('Proximo Doc.', 'proxch');
		$edit->proxch->rule='trim';
		$edit->proxch->size =12;
		$edit->proxch->maxlength=12;

		$edit->saldo = new inputField('Saldo Actual','saldo');
		$edit->saldo->mode ='autohide';
		$edit->saldo->size = 12;
		$edit->saldo->when=array('show');
		$edit->saldo->css_class='inputnum';
		$edit->saldo->readonly=true;

		$edit->dbporcen = new inputField('Debito %','dbporcen');
		$edit->dbporcen->rule='trim';
		$edit->dbporcen->size =12;
		$edit->dbporcen->maxlength=5;
		$edit->dbporcen->css_class='inputnum';
		$edit->dbporcen->rule = 'callback_chporcent';
		$edit->dbporcen->onchange='gasto()';

		$lcuent=anchor_popup('/contabilidad/cpla/dataedit/create','Agregar Cuenta Contable',$atts);
		$edit->cuenta = new inputField('Cuenta. Contable', 'cuenta');
		$edit->cuenta->rule='trim|callback_chcuentac';
		$edit->cuenta->size =12;
		$edit->cuenta->append($bcpla);
		$edit->cuenta->append($lcuent);

		$lsprv=anchor_popup('/compras/sprv/dataedit/create','Agregar Proveedor',$atts);
		$edit->codprv = new inputField('Proveedor', 'codprv');
		$edit->codprv->rule= 'condi_required|callback_chiscaja|trim';
		$edit->codprv->append($boton);
		$edit->codprv->append($lsprv);
		$edit->codprv->append(' Solo bancos');
		$edit->codprv->size = 12;

		$edit->depto = new dropdownField('Departamento', 'depto');
		$edit->depto->option('','Seleccionar');
		$edit->depto->options("SELECT depto, descrip FROM dpto ORDER BY descrip");
		$edit->depto->rule='required';
		$edit->depto->style ='width:225px;';

		$edit->sucur = new dropdownField('Sucursal', 'sucur');
		$edit->sucur->option('','Ninguna');
		$edit->sucur->options("SELECT codigo, TRIM(sucursal) FROM sucu ORDER BY sucursal");
		$edit->sucur->style ='width:80px;';

		$mSQL="SELECT codigo, CONCAT_WS('-',TRIM(descrip),TRIM(codigo)) AS descrip FROM mgas ORDER BY descrip";
		$edit->gastoidb = new dropdownField('Gasto I.D.B.','gastoidb');
		$edit->gastoidb->option('','Seleccionar');
		$edit->gastoidb->rule= 'condi_required|callback_chisidb';
		$edit->gastoidb->options($mSQL);
		$edit->gastoidb->style ='width:350px;';

		$edit->gastocom = new dropdownField('Gasto Comisi&oacute;n', 'gastocom');
		$edit->gastocom->rule= 'condi_required|callback_chiscaja|trim';
		$edit->gastocom->option('','Seleccionar');
		$edit->gastocom->options($mSQL);
		$edit->gastocom->style ='width:350px;';
		$edit->gastocom->append('Solo bancos');

		$edit->buttons('modify', 'save', 'undo', 'delete', 'back');
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_banc', $conten,true);

		//$data['content'] = $edit->output;
		$data['title']   = '<h1>Bancos y cajas</h1>';
		$data['head']    = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	function _post_insert($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre  MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('codbanc');
		$nombre=$do->get('banco');
		logusu('banc',"BANCO $codigo NOMBRE  $nombre  ELIMINADO ");
	}

	function chexiste($codigo){
		//$codigo=$this->input->post('codbanc');
		$chek=$this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE codbanc='$codigo'");
		if ($chek > 0){
			$banco=$this->datasis->dameval("SELECT banco FROM grup WHERE codbanc='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el banco $banco");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function chiscaja($proveed){
		$tbanco=$this->input->post('tbanco');
		if ($tbanco!='CAJ' AND strlen(trim($proveed))==0){
			$this->validation->set_message('chiscaja',"El campo '%s' es obligatorio cuando el registro no es una caja");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function chisidb($gastoidb){
		$dbporcen=$this->input->post('dbporcen');
		if ($dbporcen>0 AND strlen(trim($gastoidb))==0){
			$this->validation->set_message('chisidb',"El campo '%s' es obligatorio cuando existe porcentaje de d&eacute;bito");
			return FALSE;
		}else {
			return TRUE;
		}
	}

	function banco_delete($llave) {
		return false;
	}

	function ubanc(){
		$consul=$this->datasis->dameval("SELECT codbanc FROM banc ORDER BY codbanc DESC");
		echo $consul;
	}

	function consulta(){  
		$this->rapyd->load("datagrid");
		$fields = $this->db->field_data('banc');
		$url_pk = $this->uri->segment_array();
		$coun=0; $pk=array();
		foreach ($fields as $field){
			if($field->primary_key==1){
				$coun++;
				$pk[]=$field->name;
			}
		}
		$values=array_slice($url_pk,-$coun);
		$claves=array_combine (array_reverse($pk) ,$values );

		$grid = new DataGrid('Movimientos ultimos 30 dias');
		$grid->db->select( array('a.fecha', 'a.tipo_op','a.numero','CONCAT(a.concepto," ",a.concep2) concepto', 'a.monto') );
		$grid->db->from('bmov a');
		$grid->db->where('a.codbanc', $claves['codbanc'] );
		$grid->db->where('a.fecha > SUBDATE(curdate(),90)' );
		$grid->db->orderby('fecha DESC');
		//$grid->db->limit();
			
		$grid->column("Fecha"   ,"fecha" );
		$grid->column("Tipo"   ,"tipo_op" );
		$grid->column("Numero" ,"numero");
		$grid->column("Concepto"   ,"concepto" );
		//$grid->column("Nombre"  ,"nombre");
		$grid->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
		$grid->build();
		//echo $grid->db->last_query();
/*
		$grid1 = new DataGrid('Totales por Mes');
		$grid1->db->select( array('a.fecha', 'a.descrip', 'a.proveed', 'b.nombre', 'sum(a.precio) monto', 'a.iva', 'a.importe') );
		$grid1->db->from('gitser a');
		$grid1->db->join('sprv b','a.proveed=b.proveed');
		$grid1->db->where('a.codigo', $claves['codigo'] );
		$grid1->db->groupby('fecha DESC ');
		$grid1->db->limit(6);
			
		$grid1->column("Fecha"   ,"fecha" );
		$grid1->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
			
		$grid1->build();

		$grid2 = new DataGrid('Totales por Proveedor');
		$grid2->db->select( array('a.fecha', 'a.proveed', 'b.nombre', 'sum(a.precio) monto') );
		$grid2->db->from('gitser a');
		$grid2->db->join('sprv b','a.proveed=b.proveed');
		$grid2->db->where('a.codigo', $claves['codigo'] );
		$grid2->db->groupby('a.proveed');
		$grid2->db->orderby('monto DESC ');
		$grid2->db->limit(6);
			
		$grid2->column("Proveed" ,"proveed");
		$grid2->column("Nombre"  ,"nombre");
		$grid2->column("Monto"   ,"<nformat><#monto#></nformat>",'align="RIGHT"');
		
		$grid2->build();
*/
		$descrip = $this->datasis->dameval("SELECT CONCAT(banco, '', cuenta) cuenta FROM banc WHERE codbanc='".$claves['codbanc']."'");
		$data['content'] = "
		<table width='100%'>
			<tr>
				<td valign='top'>
					<div style='border: 2px outset #EFEFEF;background: #EFEFFF '>".
					$grid->output."
					</div>".
				"</td>
			</tr>
		</table>";
		$data["head"]     = script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		$data['title']    = '<h1>Consulta de Banco</h1>';
		$data["subtitle"] = "<div align='center' style='border: 2px outset #EFEFEF;background: #EFEFEF '><a href='javascript:javascript:history.go(-1)'>(".$claves['codbanc'].") ".$descrip."</a></div>";
		$this->load->view('view_ventanas', $data);
		
	}


}