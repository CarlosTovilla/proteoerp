<?php require_once(BASEPATH.'application/controllers/validaciones.php');
class sfac extends validaciones {

	function sfac(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id(103,1);
	}

	function index() {
		redirect('ventas/sfac/filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datagrid','datafilter');

		$atts = array(
			'width'      => '800',
			'height'     => '600',
			'scrollbars' => 'yes',
			'status'     => 'yes',
			'resizable'  => 'yes',
			'screenx'    => '0',
			'screeny'    => '0'
		);

		$scli=array(
		'tabla'   =>'scli',
		'columnas'=>array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'=>'Nombre',
		'contacto'=>'Contacto'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli'),
		'titulo'  =>'Buscar Cliente');
		$boton=$this->datasis->modbus($scli);

		$filter = new DataFilter('Filtro de Facturas');
		$filter->db->select(array('fecha','numero','cod_cli','nombre','totals','totalg','iva','tipo_doc','exento', 'IF(referen="C","Credito",IF(referen="E","Contado","Pendiente")) referen','IF(tipo_doc="X","N","S") nulo','almacen','vd','usuario', 'hora', 'estampa','nfiscal','cajero', 'transac','maqfiscal', 'factura' ,'id'));
		$filter->db->from('sfac');

		$filter->fechad = new dateonlyField('Desde', 'fechad','d/m/Y');
		$filter->fechad->clause  = 'where';
		$filter->fechad->db_name = 'fecha';
		$filter->fechad->insertValue = date('Y-m-d');
		$filter->fechad->size=10;
		$filter->fechad->operator='>=';
		$filter->fechad->group = '1';

		$filter->fechah = new dateonlyField('Hasta', 'fechah','d/m/Y');
		$filter->fechah->clause = 'where';
		$filter->fechah->db_name='fecha';
		$filter->fechah->insertValue = date('Y-m-d');
		$filter->fechah->size=10;
		$filter->fechah->operator='<=';
		$filter->fechah->group = '1';

		$filter->numero = new inputField('N&uacute;mero', 'numero');
		$filter->numero->size = 20;
		$filter->numero->group = '2';

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 20;
		$filter->cliente->append($boton);
		$filter->cliente->group = '2';

		$filter->buttons('reset','search');
		$filter->build("dataformfiltro");

		$uri = anchor('ventas/sfac/dataedit/show/<#id#>','<#tipo_doc#><#numero#>');
		$uri2  = anchor('ventas/sfac/dataedit/show/<#id#>',img(array('src'=>'images/editar.png','border'=>'0','alt'=>'Editar')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/ver2/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/pdf_logo.gif','border'=>'0','alt'=>'PDF')));
		$uri2 .= "&nbsp;";
		$uri2 .= anchor('formatos/verhtml/FACTURA/<#tipo_doc#>/<#numero#>',img(array('src'=>'images/html_icon.gif','border'=>'0','alt'=>'HTML')));
		$uri2 .= "&nbsp;";
		$uri2 .= img(array('src'=>'images/<#nulo#>.gif','border'=>'0','alt'=>'Estado','title'=>'Estado'));

		$uri_3  = "<a href='javascript:void(0);' onclick='javascript:nfiscal(\"<#id#>\")'>";
		$propiedad = array('src' => 'images/engrana.png', 'alt' => 'Modifica Nro de Control', 'title' => 'Modifica Nro. de Control','border'=>'0','height'=>'12');
		$uri_3 .= img($propiedad);
		$uri_3 .= "</a>";
	
		$grid = new DataGrid();
		$grid->order_by('fecha','desc');
		$grid->per_page = 50;
		
		$grid->column('Acciones',$uri2);
		$grid->column_orderby('N&uacute;mero',$uri,'numero');
		$grid->column_orderby('Fecha',    '<dbdate_to_human><#fecha#></dbdate_to_human>','fecha','align=\'center\'');
		$grid->column_orderby('Cliente',  'cod_cli',  'cod_cli');
		$grid->column_orderby('Nombre',   'nombre',   'nombre');
		$grid->column_orderby('Almacen',  'almacen',  'almacen');
		$grid->column_orderby('Sub.Total','<nformat><#totals#></nformat>','totals','align=\'right\'');
		$grid->column_orderby('IVA',      '<nformat><#iva#></nformat>'   ,'iva',   'align=\'right\'');
		$grid->column_orderby('Total',    '<nformat><#totalg#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Exento',   '<nformat><#exento#></nformat>','totalg','align=\'right\'');
		$grid->column_orderby('Tipo',     'referen',  'referen','align=\'left\'');
		$grid->column_orderby('N.Fiscal',  $uri_3.'<#nfiscal#>', 'nfiscal' );
		$grid->column_orderby('M.Fiscal', 'maqfiscal','maqfiscal','align=\'left\'');
		$grid->column_orderby('Vende',    'vd',       'vd');
		$grid->column_orderby('Cajero',   'cajero',   'cajero');
		$grid->column_orderby('Usuario',  'usuario',  'nfiscal','align=\'left\'');
		$grid->column_orderby('Hora',     'hora',     'hora',   'align=\'center\'');
		$grid->column_orderby('Transac',  'transac',  'transac','align=\'left\'');
		$grid->column_orderby('Afecta',   'factura',  'factura','align=\'left\'');
		$grid->column_orderby('I.D.',     'id',       'id',     'align=\'right\'');

		$grid->build('datagridST');
		//echo $grid->db->last_query();

// Para usar SuperTable
		$extras = '
<script type="text/javascript">
//<![CDATA[
(function() {
	var mySt = new superTable("demoTable", {
	cssSkin : "sSky",
	fixedCols : 1,
		headerRows : 1,
		onStart : function () {
		this.start = new Date();
		},
		onFinish : function () {
		document.getElementById("testDiv").innerHTML += "Finished...<br>" + ((new Date()) - this.start) + "ms.<br>";
		}
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
	width: 640px; /* Required to set */
	height: 320px; /* Required to set */
	overflow: hidden; /* Required to set */
}
</style>	
';

$script ='
<script type="text/javascript">
function nfiscal(mid){
	jPrompt("Numero de Serie","" ,"Cambio de Nro.Fiscal", function(mserie){
		if( mserie==null){
			jAlert("Cancelado","Informacion");
		} else {
			$.ajax({ url: "'.site_url().'ventas/sfac/nfiscal/"+mid+"/"+mserie,
				success: function(msg){
					jAlert("Cambio Finalizado "+msg,"Informacion");
					location.reload();
					}
			});
		}
	})
}

</script>';


$sigma = "";



		$data['content'] = $grid->output;
		$data['filtro']  = $filter->output;
		
		$data['script']  = script('jquery.js');
		$data["script"] .= script("jquery.alerts.js");
		$data['script'] .= script('superTables.js');
		$data['script'] .= $script;

		$data['style']   = $style;
		$data['style']  .= style('superTables.css');
		$data['style']	.= style("jquery.alerts.css");

		$data['extras']  = $extras;

		$data["head"]    = $this->rapyd->get_head();
		$data['title']   = heading('Facturas');
		$this->load->view('view_ventanas', $data);
	}

	//cambio del Nro Fiscal
	function nfiscal() {
		$nfiscal   = $this->uri->segment($this->uri->total_segments());
		$mid = $this->uri->segment($this->uri->total_segments()-1);
		if (!empty($nfiscal)) {
			$this->db->simple_query("UPDATE sfac SET nfiscal='$nfiscal' WHERE id='$mid'");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('SFAC',"Cambia Nro. Fiscal $mid ->  $nfiscal ");
		
	}


	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo'  =>'C&oacute;digo',
				'descrip' =>'Descripci&oacute;n',
				'precio1' =>'Precio 1',
				'precio2' =>'Precio 2',
				'precio3' =>'Precio 3',
				'existen' =>'Existencia',
				),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array(
				'codigo' =>'codigoa_<#i#>',
				'descrip'=>'desca_<#i#>',
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');
		
		$mSCLId=array(
		'tabla'   =>'scli',
		'columnas'=>array(
			'cliente' =>'C&oacute;digo Cliente',
			'nombre'=>'Nombre', 
			'cirepre'=>'Rif/Cedula',
			'dire11'=>'Direcci&oacute;n',
			'tipo'=>'Tipo'),
		'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
		'retornar'=>array('cliente'=>'cod_cli','nombre'=>'nombre','rifci'=>'rifci',
						  'dire11'=>'direc','tipo'=>'sclitipo'),
		'titulo'  =>'Buscar Cliente',
		);
		$boton =$this->datasis->modbus($mSCLId);

		$do = new DataObject('sfac');
		$do->rel_one_to_many('sitems', 'sitems', array('numero'=>'numa','tipo_doc'=>'tipoa'));
		$do->rel_one_to_many('sfpa', 'sfpa', array('numero','transac'));

		$edit = new DataDetails('Facturas', $do);
		$edit->back_url = site_url('ventas/sfac/filteredgrid');
		$edit->set_rel_title('sitems','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;
		
		$edit->tipo_doc = new  dropdownField ('Documento', 'tipo_doc');
		$edit->tipo_doc->option('F','Factura');
		$edit->tipo_doc->option('D','Devoluci&oacute;n');
		$edit->tipo_doc->style='width:200px;';
		$edit->tipo_doc->size = 5;

		$edit->vd = new  dropdownField ('Vendedor', 'vd');
		$edit->vd->options('SELECT vendedor, CONCAT(vendedor,\' \',nombre) nombre FROM vend ORDER BY vendedor');
		$edit->vd->style='width:200px;';
		$edit->vd->size = 5;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->peso = new inputField('Peso', 'peso');
		$edit->peso->css_class = 'inputnum';
		$edit->peso->readonly  = true;
		$edit->peso->size      = 10;

		$edit->cliente = new inputField('Cliente','cod_cli');
		$edit->cliente->size = 6;
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton);

		$edit->nombre = new inputField('Nombre', 'nombre');
		$edit->nombre->size = 25;
		$edit->nombre->maxlength=40;
		$edit->nombre->autocomplete=false;
		$edit->nombre->rule= 'required';

		$edit->rifci   = new inputField('RIF/CI','rifci');
		$edit->rifci->autocomplete=false;
		$edit->rifci->size = 15;

		$edit->direc = new inputField('Direcci&oacute;n','direc');
		$edit->direc->size = 40;

		//**************************
		//  Campos para el detalle 1 sitems
		//**************************
		$edit->codigoa = new inputField('C&oacute;digo <#o#>', 'codigoa_<#i#>');
		$edit->codigoa->size     = 12;
		$edit->codigoa->db_name  = 'codigoa';
		$edit->codigoa->readonly = true;
		$edit->codigoa->rel_id   = 'sitems';
		$edit->codigoa->rule     = 'required';

		$edit->desca = new inputField('Descripci&oacute;n <#o#>', 'desca_<#i#>');
		$edit->desca->size=36;
		$edit->desca->db_name='desca';
		$edit->desca->maxlength=50;
		$edit->desca->readonly  = true;
		$edit->desca->rel_id='sitems';

		$edit->cana = new inputField('Cantidad <#o#>', 'cana_<#i#>');
		$edit->cana->db_name  = 'cana';
		$edit->cana->css_class= 'inputnum';
		$edit->cana->rel_id   = 'sitems';
		$edit->cana->maxlength= 10;
		$edit->cana->size     = 6;
		$edit->cana->rule     = 'required|positive';
		$edit->cana->autocomplete=false;

		$edit->preca = new inputField('Precio <#o#>', 'preca_<#i#>');
		$edit->preca->db_name   = 'preca';
		$edit->preca->css_class = 'inputnum';
		$edit->preca->rel_id    = 'sitems';
		$edit->preca->size      = 10;
		$edit->preca->rule      = 'required|positive';
		$edit->preca->readonly  = true;

		$edit->tota = new inputField('Importe <#o#>', 'tota_<#i#>');
		$edit->tota->db_name='tota';
		$edit->tota->size=10;
		$edit->tota->css_class='inputnum';
		$edit->tota->rel_id   ='sitems';

		//**************************
		//fin de campos para detalle,inicio detalle2 sfpa
		//**************************
		$edit->tipo = new inputField('Tipo <#o#>', 'tipo_<#i#>');
		$edit->tipo->size     = 12;
		$edit->tipo->db_name  = 'tipo';
		$edit->tipo->readonly = true;
		$edit->tipo->rel_id   = 'sfpa';
		$edit->tipo->rule     = 'required';

		$edit->numref = new inputField('Numero <#o#>', 'numero_<#i#>');
		$edit->numref->size     = 12;
		$edit->numref->db_name  = 'numref';
		$edit->numref->readonly = true;
		$edit->numref->rel_id   = 'sfpa';
		$edit->numref->rule     = 'required';

		
		$edit->monto = new inputField('Monto <#o#>', 'monto_<#i#>');
		$edit->monto->db_name   = 'monto';
		$edit->monto->css_class = 'inputnum';
		$edit->monto->rel_id    = 'sfpa';
		$edit->monto->size      = 10;
		$edit->monto->rule      = 'required|positive';
		$edit->monto->readonly  = true;
		
		$edit->banco = new inputField('Banco <#o#>', 'banco_<#i#>');
		$edit->banco->size=36;
		$edit->banco->db_name='banco';
		$edit->banco->maxlength=50;
		$edit->banco->readonly  = true;
		$edit->banco->rel_id='sfpa';
		
		//**************************
		//Fin detalle 2
		//**************************

		$edit->ivat = new inputField('I.V.A', 'iva');
		$edit->ivat->css_class ='inputnum';
		$edit->ivat->readonly  =true;
		$edit->ivat->size      = 10;

		$edit->totals = new inputField('Sub-Total', 'totals');
		$edit->totals->css_class ='inputnum';
		$edit->totals->readonly  =true;
		$edit->totals->size      = 10;

		$edit->totalg = new inputField('Total', 'totalg');
		$edit->totalg->css_class ='inputnum';
		$edit->totalg->readonly  =true;
		$edit->totalg->size      = 10;

		$edit->observa   = new inputField('Observacion', 'observa');
		$edit->nfiscal   = new inputField('No.Fiscal', 'nfiscal');
		$edit->observ1   = new inputField('Observacion', 'observ1');
		$edit->zona      = new inputField('Zona', 'zona');
		$edit->ciudad    = new inputField('Ciudad', 'ciudad');
		$edit->exento    = new inputField('Exento', 'exento');
		$edit->maqfiscal = new inputField('Mq.Fiscal', 'maqfiscal');
		$edit->cajero    = new inputField('Cajero', 'cajero');
		$edit->referen   = new inputField('Referencia', 'referen');
		$edit->transac   = new inputField('Transaccion', 'transac');
		$edit->vence     = new inputField('Vence', 'vence');

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons(  'delete', 'back','add_rel');
		$edit->build();

		$style = '
<style type="text/css">
div#sfacreiva label { display:block; }
div#sfacreiva input { display:block; }
div#sfacreiva input.text { margin-bottom:12px; width:95%; padding: .4em; }
div#sfacreiva select { display:block; }
div#sfacreiva select.text { margin-bottom:12px; width:95%; padding: .4em; }
div#sfacreiva fieldset { padding:0; border:0; margin-top:20px; }
div#sfacreiva h1 { font-size: 1.2em; margin: .6em 0; }
.ui-dialog .ui-state-error { padding: .3em; }
.validateTips { border: 1px solid transparent; padding: 0.3em; }
</style>
';

	$mreiva = round($edit->ivat->value*0.75,2);
	if( $edit->_dataobject->get('reiva') > 0 )  $mreiva = $edit->_dataobject->get('reiva');
	
	$fecha = date('d/m/Y');
	if( $edit->_dataobject->get('freiva') > 0 )  $fecha = $edit->_dataobject->get('freiva');
	
	$nro = date('Ym');
	if( $edit->_dataobject->get('creiva') > 0 )  $nro = $edit->_dataobject->get('creiva');
	
	//<p class="validateTips">Registro de Retencion de IVA</p>

		$reiva = '
<div id="sfacreiva" title="Registro de IVA">
	<form>
	<fieldset>
		<label for="numero">Numero</label>
		<input type="text" size="20" value="'.$nro.'" name="numero" id="numero" class="text ui-widget-content ui-corner-all" />
		
		<label for="fecha">Fecha</label>
		<input type="text" size="10"  value="'.$fecha.'" name="fecha" id="fecha" class="text ui-widget-content ui-corner-all" />
		
		<label for="reiva">Monto</label>
		<input type="text" name="reiva" id="reiva" value="'.$mreiva.'" align="right" class="text ui-widget-content ui-corner-all" />
		
	</fieldset>
	</form>
</div>
';


		$conten['form']  =&  $edit;
		$data['content'] = $this->load->view('view_sfac', $conten,true).$reiva;

		if($edit->tipo_doc->value=='F'){$mDoc = "Factura";}
		elseif( $edit->tipo_doc->value=='D') { $mDoc = "Devolucion";}
		else { $mDoc = "Anulado";}

		$link40 = base_url()."/ventas/sfac/sfacreiva/".$edit->_dataobject->get('id');
		$script = "
<script type=\"text/javascript\" >  
<!-- All the scripts will go here  -->

var dsOption= {
	fields :[
		{name : 'codigoa'},
		{name : 'desca'  },
		{name : 'cana',		type: 'float' },
		{name : 'preca',	type: 'float' },
		{name : 'tota',		type: 'float' },
		{name : 'iva',		type: 'float' },
		{name : 'pvp',		type: 'float' },
		{name : 'descuento',	type: 'float' },
		{name : 'precio4',	type: 'float' },
		{name : 'detalle' },
		{name : 'fdespacha',	type: 'date'  },
		{name : 'udespacha' },
		{name : 'bonifica',	type: 'integer' },
		{name : 'url' }

	],
	recordType : 'object'
} 

function codigoaurl( value, record, columnObj, grid, colNo, rowNo ) {
	var no=  value;
	var url= '';
	url = '<a href=\"#\" onclick=\"window.open(\'".base_url()."inventario/sinv/dataedit/show/'+grid.getCellValue(13,rowNo)+ '\', \'_blank\', \'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'\')\"; heigth=\"600\" >';
	url = url +no+'</a>';
	return url;
}

var colsOption = [
	{id: 'codigoa',		header: 'Codigo',	width :100, frozen: true, renderer:codigoaurl },
	{id: 'desca',		header: 'Descripcion',	width :340, align: 'left' },
	{id: 'cana',		header: 'Cant',		width :60, align: 'right' },
	{id: 'preca',		header: 'Precio',	width :90, align: 'right' },
	{id: 'tota',		header: 'Total',	width :90, align: 'right' },
	{id: 'iva',		header: 'IVA',		width :50, align: 'right' },
	{id: 'pvp',		header: 'PVP',		width :80, align: 'right' },
	{id: 'descuento',	header: 'Desc%',	width :80, align: 'right' },
	{id: 'precio4',		header: 'Control',	width :80, align: 'right' },
	{id: 'detalle',		header: 'Detalle',	width :80, align: 'right' },
	{id: 'fdespacha',	header: 'Despacha',	width :80, align: 'center' },
	{id: 'udespacha',	header: 'Usuario D',	width :80, align: 'left' },
	{id: 'bonifica',	header: 'Bonifica',	width :80, align: 'right' },
	{id: 'url',	header: 'Id',	width :80, align: 'right' }
	
];

var gridOption={
	id : 'grid1',
	loadURL : '".base_url()."ventas/sfac/sfacsitems/".$edit->_dataobject->get("tipo_doc")."/".$edit->numero->value."',
	container : 'grid1_container', 
	dataset : dsOption ,
	columns : colsOption,
	allowCustomSkin: true,
	skin: 'vista',
	toolbarContent: 'pdf'	
};

var mygrid=new Sigma.Grid(gridOption);
Sigma.Util.onLoad( Sigma.Grid.render(mygrid) );


$(document).ready(function() {

	var numero = $( '#numero' ),fecha = $( '#fecha' ),reiva = $( '#reiva' );
	$( '#sfacreiva' ).dialog({
		autoOpen: false,
		height: 300,
		width: 230,
		modal: true,
		buttons: {
			'Guardar': function() {
				var bValid = true;
				//allFields.removeClass( 'ui-state-error' );
				fecha.val( function(i,v) { return v.replace(/\//g,'-') });
				if ( bValid ) {
					$.ajax({
						url: '".$link40."/'+numero.val()+'/'+fecha.val()+'/'+reiva.val(),
						success: function(msg){
							alert('Terminado: '+msg);
						}
					});
					$( this ).dialog( 'close' );
				}
			},
			Cancelar: function() {
				$( this ).dialog( 'close' );
			}
		},
		close: function() {
			allFields.val( '' ).removeClass( 'ui-state-error' );
		}
	});
});
// Descuento por Cliente
function sfacreiva(mcodigo){
	$( '#sfacreiva' ).dialog( 'open' );
};
</script>  
";

		$data['title']  = heading($mDoc." Nro. ".$edit->numero->value);
		
		$data['style']  = style("redmond/jquery-ui.css");
		$data['style'] .= style('gt_grid.css');
		$data['style']	.= $style;

		$data['script']  = script('jquery.js');
		$data['script'] .= script('jquery-ui.js');
		$data["script"]  .= script("plugins/jquery.blockUI.js");
		$data['script'] .= script('plugins/jquery.numeric.pack.js');
		$data['script'] .= phpscript('nformat.js');
		$data['script'] .= script('plugins/jquery.floatnumber.js');
		$data['script'] .= script("gt_msg_en.js");
		$data['script'] .= script("gt_grid_all.js");
		$data['script'] .= $script;
		
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}

	//********************************************
	//
	// json para llena la tabla de inventario
	//
	function sfacsitems() {
		$numa  = $this->uri->segment($this->uri->total_segments());
		$tipoa = $this->uri->segment($this->uri->total_segments()-1);
		
		$mSQL  = 'SELECT a.codigoa, a.desca, a.cana, a.preca, a.tota, a.iva, IF(a.pvp < a.preca, a.preca, a.pvp)  pvp, ROUND(100-a.preca*100/IF(a.pvp<a.preca,a.preca, a.pvp),2) descuento, ROUND(100-ROUND(a.precio4*100/(100+a.iva),2)*100/a.preca,2) precio4, a.detalle, a.fdespacha, a.udespacha, a.bonifica, b.id url ';
		$mSQL .= "FROM sitems a LEFT JOIN sinv b ON a.codigoa=b.codigo WHERE a.tipoa='$tipoa' AND a.numa='$numa' ";
		$mSQL .= "ORDER BY a.codigoa";
		

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}


	//***************************
	//
	// Recibir retencin de IVA
	//
	function sfacreiva(){
		$monto  = $this->uri->segment($this->uri->total_segments());
		$fecha  = $this->uri->segment($this->uri->total_segments()-1);
		$numero = $this->uri->segment($this->uri->total_segments()-2);
		$id     = $this->uri->segment($this->uri->total_segments()-3);
		$mdevo  = "Exito";
		
		// status de la factura
		$fecha = substr($fecha,6,4).substr($fecha,3,2).substr($fecha,0,2);
	
		$tipo_doc = $this->datasis->dameval("SELECT tipo_doc FROM sfac WHERE id=$id");
		$referen  = $this->datasis->dameval("SELECT referen  FROM sfac WHERE id=$id");
		$numfac   = $this->datasis->dameval("SELECT numero   FROM sfac WHERE id=$id");
		$cod_cli  = $this->datasis->dameval("SELECT cod_cli  FROM sfac WHERE id=$id");

		$anterior = $this->datasis->dameval("SELECT reiva FROM sfac WHERE id=$id");
		$usuario = addslashes($this->session->userdata('usuario'));
	
		if (  $anterior == 0 )  {
			$mSQL = "UPDATE sfac SET reiva=round(iva*0.75,2), creiva='$numero', freiva='$fecha' WHERE id=$id";
			$this->db->simple_query($mSQL);
			$transac = $this->datasis->prox_sql("ntransa");
		
			if ($referen == 'E') {
				$saldo = $this->datasis->dameval("SELECT referen  FROM sfac WHERE id=$id");	
			}
			if ( $tipo_doc == 'F') {
				if ($referen == 'E') { 
					// FACTURA PAGADA AL CONTADO GENERA ANTICIPO
					$mnumant = $this->datasis->prox_sql("nancli");
					$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario )
					SELECT cod_cli, nombre, 'AN' tipo_doc, LPAD('$mnumant',8,'0') numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
						CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
						curdate() estampa, curtime() hora, LPAD('$transac',8,'0') transac, '".$usuario."' usuario
						FROM sfac 
						WHERE id=$id";
					$this->db->simple_query($mSQL);
					$mdevo = "Cambios Guardados, Anticipo Generado";
					//memowrite($mSQL,"sfacreiva1");
				} elseif ($referen == 'C') {
					// Busca si esta cancelada
					$tiposfac = 'FC';
					if ( $tipo_doc == 'D') $tiposfac = 'NC';
					$mSQL = "SELECT monto-abonos saldo FROM smov WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
					$saldo = $this->datasis->dameval($mSQL);

					if ( $saldo < $monto ) {  // crea anticipo
						$mnumant = $this->datasis->prox_sql("nancli");
						$mSQL = "INSERT INTO smov  (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario )
						SELECT cod_cli, nombre, 'AN' tipo_doc, LPAD('$mnumant',8,'0') numero, freiva fecha, reiva monto, 0 impuesto, freiva vence,
							CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
							curdate() estampa, curtime() hora, LPAD('$transac',8,'0') transac, '".$usuario."' usuario
							FROM sfac 
							WHERE id=$id";
						$this->db->simple_query($mSQL);
						$mdevo = "Cambios Guardados, Anticipo Generado";
						memowrite($mSQL,"sfacreivaAN");
					} else {
						$mnumant = $this->datasis->prox_sql("nccli");
						$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, transac, usuario, codigo, descrip )
						SELECT cod_cli, nombre, 'NC' tipo_doc, LPAD('$mnumant',8,'0') numero, freiva fecha, reiva monto, 0 impuesto, reiva abonos, freiva vence,
							CONCAT('APLICACION DE RETENCION A DOC. ',tipo_doc,numero) observa1, IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref,
							curdate() estampa, curtime() hora, LPAD('$transac',8,'0') transac, '".$usuario."' usuario,
							'NOCON 'codigo, 'NOTA DE CONTABILIDAD' descrip
							FROM sfac WHERE id=$id";
						$this->db->simple_query($mSQL);
						memowrite($mSQL,"sfacreivaNC");
						
						// ABONA A LA FACTURA
						$mSQL = "UPDATE smov SET abonos=abonos+$monto WHERE numero='$numfac' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
						$this->db->simple_query($mSQL);
						
						//Crea la relacin en ccli

						$mdevo = "Cambios Guardados, Nota de Credito";
					}
				}
				$mnumant = $this->datasis->prox_sql("nancli");
				$mSQL = "INSERT INTO smov (cod_cli, nombre, tipo_doc, numero, fecha, monto, impuesto, abonos, vence, observa1, tipo_ref, num_ref, estampa, hora, usuario, transac, codigo, descrip )
				SELECT 'REIVA' cod_cli, 'RETENCION DE IVA POR COMPENSAR' nombre, 'ND' tipo_doc, LPAD('$mnumant',8,'0') numero, freiva fecha, 
					reiva monto, 0 impuesto, 0 abonos, freiva vence, 'APLICACION DE RETENCION A FACTURA ' observa1, 
					IF(tipo_doc='F','FC', 'DV' ) tipo_ref, numero num_ref, curdate() estampa, 
					curtime() hora, '".$usuario."' usuario, LPAD('$transac',8,'0') transac, 'NOCON 'codigo, 'NOTA DE CONTABILIDAD' descrip
				FROM sfac WHERE id=$id";
				$this->db->simple_query($mSQL);
				memowrite($mSQL,"sfacreivaND");
				
			} else {
				//Devoluciones
				
			}
		} else {
			$mdevo = "Retencion ya aplicada";
		}
		echo $mdevo;
	}

	// json para llena la tabla de inventario
	function sfacsig() {
		$numa  = $this->uri->segment($this->uri->total_segments());
		$tipoa = $this->uri->segment($this->uri->total_segments()-1);
		
		$mSQL  = 'SELECT a.codigoa, a.desca, a.cana, a.preca, a.tota, a.iva, IF(a.pvp < a.preca, a.preca, a.pvp)  pvp, ROUND(100-a.preca*100/IF(a.pvp<a.preca,a.preca, a.pvp),2) descuento, ROUND(100-ROUND(a.precio4*100/(100+a.iva),2)*100/a.preca,2) precio4, a.detalle, a.fdespacha, a.udespacha, a.bonifica, b.id url ';
		$mSQL .= "FROM sitems a LEFT JOIN sinv b ON a.codigoa=b.codigo WHERE a.tipoa='$tipoa' AND a.numa='$numa' ";
		$mSQL .= "ORDER BY a.codigoa";
		

		$query = $this->db->query($mSQL);

		if ($query->num_rows() > 0){
			$retArray = array();
			foreach( $query->result_array() as  $row ) {
				$retArray[] = $row;
			}
			$data = json_encode($retArray);
			$ret = "{data:" . $data .",\n";
			$ret .= "recordType : 'array'}";
		} else {
			$ret = '{data : []}';
		}
		echo $ret;
	}

	function _pre_insert($do){
		return false;
	}

	function _pre_update($do){
		return false;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo CREADO");
	}

	function chpreca($preca,$ind){
		$codigo  = $this->input->post('codigo_'.$ind);
		$precio4 = $this->datasis->dameval('SELECT base4 FROM sinv WHERE codigo='.$this->db->escape($codigo));
		if($precio4<0) $precio4=0;

		if($preca<$precio4){
			$this->validation->set_message('chpreca', 'El art&iacute;culo '.$codigo.' debe contener un precio de al menos '.nformat($precio4));
			return false;
		}else{
			return true;
		}
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('sfac',"Factura $codigo ELIMINADO");
	}
}