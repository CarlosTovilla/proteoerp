<?php 
//require_once(BASEPATH.'application/controllers/validaciones.php');

class Scaj extends Controller {
	var $mModulo = 'SCAJ';
	var $titp    = 'CAJEROS';
	var $tits    = 'CAJEROS';
	var $url     = 'ventas/scaj/';

	function scaj(){
		parent::Controller(); 
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'SCAJ', $ventana=0 );
	}
	
	function index(){
		$this->db->simple_query('UPDATE scaj SET cajero=TRIM(cajero)');
		if ( !$this->datasis->iscampo('scaj','id') ) {
			$this->db->simple_query('ALTER TABLE scaj DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE scaj ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE scaj ADD UNIQUE INDEX cajero (cajero)');
		}
		$this->datasis->modintramenu( 800, 600, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$param['grids'][] = $grid->deploy();

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname']);

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		//$grid->wbotonadd(array("id"=>"edocta",   "img"=>"images/pdf_logo.gif",  "alt" => "Formato PDF", "label"=>"Ejemplo"));
		$WestPanel = $grid->deploywestp();

		$adic = array(
		array("id"=>"fedita",  "title"=>"Agregar/Editar Registro")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']   = $WestPanel;
		//$param['EastPanel'] = $EastPanel;
		$param['SouthPanel']  = $SouthPanel;
		$param['listados']    = $this->datasis->listados('SCAJ', 'JQ');
		$param['otros']       = $this->datasis->otros('SCAJ', 'JQ');
		$param['temas']       = array('proteo','darkness','anexos1');
		$param['bodyscript']  = $bodyscript;
		$param['tabs']        = false;
		$param['encabeza']    = $this->titp;
		$param['tamano']      = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function scajadd() {
			$.post("'.site_url('ventas/scaj/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function scajedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('ventas/scaj/dataedit/modify').'/"+id, function(data){
					$("#fedita").html(data);
					$("#fedita").dialog( "open" );
				});
			} else { $.prompt("<h1>Por favor Seleccione un Registro</h1>");}
		};';

		//Wraper de javascript
		$bodyscript .= '
		$(function() {
			$("#dialog:ui-dialog").dialog( "destroy" );
			var mId = 0;
			var montotal = 0;
			var ffecha = $("#ffecha");
			var grid = jQuery("#newapi'.$grid0.'");
			var s;
			var allFields = $( [] ).add( ffecha );
			var tips = $( ".validateTips" );
			s = grid.getGridParam(\'selarrrow\');
			';

		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 450, width: 700, modal: true,
			buttons: {
			"Guardar": function() {
				var bValid = true;
				var murl = $("#df1").attr("action");
				allFields.removeClass( "ui-state-error" );
				$.ajax({
					type: "POST", dataType: "html", async: false,
					url: murl,
					data: $("#df1").serialize(),
					success: function(r,s,x){
						if ( r.length == 0 ) {
							apprise("Registro Guardado");
							$( "#fedita" ).dialog( "close" );
							grid.trigger("reloadGrid");
							'.$this->datasis->jwinopen(site_url('formatos/ver/SCAJ').'/\'+res.id+\'/id\'').';
							return true;
						} else { 
							$("#fedita").html(r);
						}
					}
			})},
			"Cancelar": function() { $( this ).dialog( "close" ); }
			},
			close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
		});';
		$bodyscript .= '});'."\n";

		$bodyscript .= "\n</script>\n";
		$bodyscript .= "";
		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

		$grid->addField('cajero');
		$grid->label('Cajero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('clave');
		$grid->label('Clave');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:6, maxlength: 6 }',
		));


		$grid->addField('fechaa');
		$grid->label('Fechaa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('horaa');
		$grid->label('Horaa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('apertura');
		$grid->label('Apertura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('fechac');
		$grid->label('Fechac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('horac');
		$grid->label('Horac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('cierre');
		$grid->label('Cierre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:1, maxlength: 1 }',
		));


		$grid->addField('directo');
		$grid->label('Directo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:60, maxlength: 60 }',
		));


		$grid->addField('mesai');
		$grid->label('Mesai');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('mesaf');
		$grid->label('Mesaf');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('horai');
		$grid->label('Horai');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('horaf');
		$grid->label('Horaf');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('caja');
		$grid->label('Caja');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


		$grid->addField('almacen');
		$grid->label('Almacen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('vendedor');
		$grid->label('Vendedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('290');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SCAJ','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('SCAJ','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('SCAJ','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('SCAJ','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: scajadd,\n\t\teditfunc: scajedit");

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdata/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdata()
	{
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('scaj');

		$response   = $grid->getData('scaj', array(array()), array(), false, $mWHERE );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData()
	{
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$mcodp  = "??????";
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			if(false == empty($data)){
				$check = $this->datasis->dameval("SELECT count(*) FROM scaj WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('scaj', $data);
					echo "Registro Agregado";

					logusu('SCAJ',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM scaj WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM scaj WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE scaj SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("scaj", $data);
				logusu('SCAJ',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('scaj', $data);
				logusu('SCAJ',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM scaj WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM scaj WHERE id=$id ");
				logusu('SCAJ',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


/*	
	function filteredgrid(){
		$this->rapyd->load("datafilter","datagrid");
		$this->rapyd->uri->keep_persistence();

		$filter = new DataFilter("Buscar", 'scaj');
		
		$filter->cajero = new inputField('Cajero','cajero');
		$filter->cajero->size=10;
		
		$filter->nombre = new inputField('Nombre','nombre');
		$filter->nombre->size=30;
		
		$filter->buttons('reset','search');
		$filter->build();

		$uri = anchor("ventas/scaj/dataedit/show/<#cajero#>",'<#cajero#>');

		$grid = new DataGrid('Lista de Cajeros');
		$grid->order_by('nombre','asc');
		$grid->per_page = 10;

		//$grid->column_detail("C&oacute;digo","cajero", $uri, "size=14");
		$grid->column_orderby('C&oacute;digo',$uri,'cajero');
		$grid->column_orderby('Nombre','nombre','nombre');
		$grid->column_orderby('Estado','status','status');
		$grid->column_orderby('Almacen','almacen','almacen');
		$grid->column_orderby('Caja','caja','caja');

		$grid->add('ventas/scaj/dataedit/create','Agregar un cajero');
		$grid->build();
		
		$data['content'] = $filter->output.$grid->output;
		$data['title']   = '<h1>Cajeros</h1>';
		$data['head']    = $this->rapyd->get_head();
		$this->load->view('view_ventanas', $data);
	}
*/

	function dataedit(){
		$this->rapyd->load('dataedit');
		$script ='
		$(function() {
			$(".inputnum").numeric(".");
		});';

		$edit = new DataEdit('Cajeros', 'scaj');
		$edit->pre_process('delete','_pre_del');
		$edit->back_url = site_url('ventas/scaj/filteredgrid');
		$edit->script($script, 'create');
		$edit->script($script, 'modify');
		
		//$edit->pre_process("delete",'_pre_del');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->cajero = new inputField('Codigo', 'cajero');
		$edit->cajero->rule = 'trim|strtoupper|required|callback_chexiste';
		$edit->cajero->mode = 'autohide';
		$edit->cajero->maxlength=5;
		$edit->cajero->size = 6;
		
		$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->maxlength=30;
		$edit->nombre->rule="trim|strtoupper|required";
		$edit->nombre->size =30;

		$edit->clave = new inputField('Clave', 'clave');
		$edit->clave->maxlength=6;
		$edit->clave->rule="trim";
		$edit->clave->size = 7;

		$edit->status = new dropdownField('Status', 'status');
		$edit->status->rule = 'required';
		$edit->status->options(array('C'=> 'Cerrado','A'=>'Abierto'));
		$edit->status->style='width:110px';

		$edit->almacen = new dropdownField("Almac&eacute;n", "almacen");
		$edit->almacen->option('','Seleccionar');
		$edit->almacen->options("SELECT ubica, ubides FROM caub WHERE gasto='N' ORDER BY ubides");
		$edit->almacen->rule='required';
		$edit->almacen->style="width:150px";

		$edit->caja = new dropdownField("Caja", "caja");
		$edit->caja->option('','Seleccionar');
		$edit->caja->options("SELECT codbanc, concat(codbanc,' ',banco) banco FROM banc WHERE tbanco='CAJ' ORDER BY codbanc");
		$edit->caja->rule='required';
		$edit->caja->style="width:250px";

/*
		$edit->caja->size=4;
		$edit->caja->maxlength=2;
		$edit->caja->rule='trim|callback_ccaja';
*/

		$edit->directo = new inputField('Directorio','directo');
		$edit->directo->size=55;
		$edit->directo->rule='trim';
		$edit->directo->maxlength=60;

		$edit->mesai = new inputField("Mesa desde", "mesai");
		$edit->mesai->maxlength=4;
		$edit->mesai->size=6;
		$edit->mesai->rule="trim";
		$edit->mesai->group="Mesas";

		$edit->mesaf  = new inputField("Mesa hasta", "mesaf");
		$edit->mesaf->maxlength=4;
		$edit->mesaf->size=6;
		$edit->mesaf->rule="trim";
		$edit->mesaf->group="Mesas";

		$edit->horai  = new inputField("Desde", "horai");
		$edit->horai->maxlength=8;
		$edit->horai->size=10;
		$edit->horai->rule='trim|callback_chhora';
		$edit->horai->append('hh:mm:ss');
		$edit->horai->group="Hora feliz";

		$edit->horaf  = new inputField("Hasta", "horaf");
		$edit->horaf->maxlength=8;
		$edit->horaf->size=10;
		$edit->horaf->rule='trim|callback_chhora';
		$edit->horaf->append('hh:mm:ss');
		$edit->horaf->group="Hora feliz";

		$edit->fechaa = new dateonlyfield("Fecha", "fechaa");
		$edit->fechaa->maxlength=12;
		$edit->fechaa->size=12;
		$edit->fechaa->rule='chfecha';
		$edit->fechaa->group="Apertura";

		$edit->horaa  = new inputField("Hora", "horaa");
		$edit->horaa->maxlength=12;
		$edit->horaa->size=12;
		$edit->horaa->rule='trim|callback_chhora';
		$edit->horaa->append('hh:mm:ss');
		$edit->horaa->group="Apertura";

		$edit->apertura =new inputField("Monto", "apertura");
		$edit->apertura->maxlength=12;
		$edit->apertura->size=12;
		$edit->apertura->group="Apertura";
		$edit->apertura->css_class='inputnum';
		$edit->apertura->rule='numeric';

		$edit->fechac = new dateonlyfield('Fecha', 'fechac');
		$edit->fechac->maxlength=12;
		$edit->fechac->size=12;
		$edit->fechac->rule='chfecha';
		$edit->fechac->group='Apertura';

		$edit->horac  = new inputField('Hora', 'horac');
		$edit->horac->maxlength=8;
		$edit->horac->size=12;
		$edit->horac->rule='trim|callback_chhora';
		$edit->horac->append('hh:mm:ss');
		$edit->horac->group="Apertura";

		$edit->cierre   =new inputField("Monto", "cierre");
		$edit->cierre->maxlength=12;
		$edit->cierre->size=12;
		$edit->cierre->group='Apertura';
		$edit->cierre->css_class='inputnum';
		$edit->cierre->rule='trim|numeric';

		$edit->buttons('modify','save','undo','delete','back');
		$edit->build();

		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_scaj', $conten );

		//$data['content'] = $edit->output;
		//$data['title']   = "<h1>Cajeros</h1>";
		//$data["head"]    = script("jquery.pack.js").script("plugins/jquery.numeric.pack.js").script("plugins/jquery.floatnumber.js").$this->rapyd->get_head();
		//$this->load->view( 'view_scaj', $data);
	}

	function _pre_del($do) {
		$codigo=$this->db->escape($do->get('cajero'));
		$tables = $this->db->list_tables();
		$sum=0;
		if(in_array('vieite',$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM vieite WHERE cajero=$codigo");
		if(in_array('fmay'  ,$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM fmay   WHERE cajero=$codigo");
		if(in_array('sfac'  ,$tables)) $sum+=$this->datasis->dameval("SELECT COUNT(*) FROM sfac   WHERE cajero=$codigo");

		if($sum != 0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede borrar un cajero con ventas';
			return False;
		}else
			return True;
	}
	function _post_insert($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status CREADO");
	}
	function _post_update($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status MODIFICADO");
	}
	function _post_delete($do){
		$codigo=$do->get('cajero');
		$nombre=$do->get('nombre');
		$status=$do->get('status');
		logusu('scaj',"CAJERO $codigo NOMBRE $nombre STATUS $status ELIMINADO");
	}
	
	//VALIDACIONES
	function chexiste($codigo){
		$codigo=$this->input->post('cajero');
		$check=$this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero='$codigo'");
		if ($check > 0){
			$nombre=$this->datasis->dameval("SELECT nombre FROM scaj WHERE cajero='$codigo'");
			$this->validation->set_message('chexiste',"El codigo $codigo ya existe para el cajero $nombre");
			return FALSE;
		}else {
		return TRUE;
		}
	}
	
	function ccaja($caja){
		$cant=$this->datasis->dameval("SELECT COUNT(*) FROM banc WHERE codbanc='$caja'");
		//$link=anchor('','aqui');
		if($cant==0){
			$this->validation->set_message('ccaja',"El codigo de caja '$caja' no existe");
			return FALSE;
		}
		return TRUE;
	}
	
	function instalar(){
		$mSQL="CREATE TABLE IF NOT EXISTS `vieite` (
			`numero` char(8) default NULL,
			`fecha` date default '0000-00-00',
			`codigo` char(15) default NULL,
			`precio` decimal(10,2) default '0.00',
			`monto` decimal(18,2) default '0.00',
			`cantidad` decimal(12,3) default NULL,
			`impuesto` decimal(6,2) default '0.00',
			`costo` decimal(18,2) default '0.00',
			`almacen` char(4) default NULL,
			`cajero` char(5) default NULL,
			`caja` char(5) NOT NULL default '',
			`referen` char(15) default NULL,
			KEY `fecha` (`fecha`),
			KEY `codigo` (`codigo`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='ventas por articulo'";
		$this->db->simple_query($mSQL);
		$mSQL="CREATE TABLE IF NOT EXISTS `fmay` (
			`fecha` date default NULL,
			`numero` varchar(8) NOT NULL default '',
			`presup` varchar(8) default NULL,
			`almacen` varchar(4) default NULL,
			`cod_cli` varchar(5) default NULL,
			`nombre` varchar(40) default NULL,
			`vence` date default NULL,
			`vende` varchar(5) default NULL,
			`stotal` decimal(17,2) default '0.00',
			`impuesto` decimal(17,2) default '0.00',
			`gtotal` decimal(17,2) default '0.00',
			`tipo` char(1) default NULL,
			`observa1` varchar(40) default NULL,
			`observa2` varchar(40) default NULL,
			`observa3` varchar(40) default NULL,
			`porcenta` decimal(17,2) default '0.00',
			`descuento` decimal(17,2) default '0.00',
			`cajero` varchar(5) default NULL,
			`dire1` varchar(30) default NULL,
			`dire2` varchar(30) default NULL,
			`rif` varchar(15) default NULL,
			`nit` varchar(15) default NULL,
			`exento` decimal(17,2) default '0.00',
			`transac` varchar(8) default NULL,
			`estampa` date default NULL,
			`hora` varchar(5) default NULL,
			`usuario` varchar(12) default NULL,
			`nfiscal` varchar(12) NOT NULL default '0',
			`tasa` decimal(19,2) default NULL,
			`reducida` decimal(19,2) default NULL,
			`sobretasa` decimal(17,2) default NULL,
			`montasa` decimal(17,2) default NULL,
			`monredu` decimal(17,2) default NULL,
			`monadic` decimal(17,2) default NULL,
			`cedula` varchar(13) default NULL,
			`dirent1` varchar(40) default NULL,
			`dirent2` varchar(40) default NULL,
			`dirent3` varchar(40) default NULL,
			PRIMARY KEY  (`numero`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$this->db->simple_query($mSQL);
	}
	
	function grid(){
		$start   = isset($_REQUEST['start'])  ? $_REQUEST['start']   :  0;
		$limit   = isset($_REQUEST['limit'])  ? $_REQUEST['limit']   : 50;
		$sort    = isset($_REQUEST['sort'])   ? $_REQUEST['sort']    : '[{"property":"cajero","direction":"ASC"}]';
		$filters = isset($_REQUEST['filter']) ? $_REQUEST['filter']  : null;

		$where = $this->datasis->extjsfiltro($filters);

		$this->db->_protect_identifiers=false;
		$this->db->select('*');
		$this->db->from('scaj');
		if (strlen($where)>1) $this->db->where($where, NULL, FALSE); 

		$sort = json_decode($sort, true);
		for ( $i=0; $i<count($sort); $i++ ) {
			$this->db->order_by($sort[$i]['property'],$sort[$i]['direction']);
		}

		$this->db->limit($limit, $start);
		$query = $this->db->get();
		$results = $this->db->count_all('scaj');

		$arr = $this->datasis->codificautf8($query->result_array());
		echo '{success:true, message:"Loaded data", results:'. $results.', data:'.json_encode($arr).'}';
	}


	function crear(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos   = $data['data'];
		$cajero = $campos['cajero'];

		if ( !empty($cajero) ) {
			unset($campos['id']);
			// Revisa si existe ya ese contrato
			if ($this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero='$cajero'") == 0)
			{
				$mSQL = $this->db->insert_string("scaj", $campos );
				$this->db->simple_query($mSQL);
				logusu('scaj',"CAJERO $cajero CREADO");
				echo "{ success: true, message: 'Cajero Agregado'}";
			} else {
				echo "{ success: false, message: 'Ya existe un cajero con ese Codigo!!'}";
			}
			
		} else {
			echo "{ success: false, message: 'Ya existe un cajero con ese Codigo!!'}";
		}
	}

	function modificar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cajero = $campos['cajero'];
		unset($campos['cajero']);
		unset($campos['id']);

		$mSQL = $this->db->update_string("scaj", $campos,"id='".$data['data']['id']."'" );
		$this->db->simple_query($mSQL);
		logusu('scaj',"CAJERO $cajero ID ".$data['data']['id']." MODIFICADO");
		echo "{ success: true, message: 'Cajero Modificado -> ".$data['data']['cajero']."'}";
	}

	function eliminar(){
		$js= file_get_contents('php://input');
		$data= json_decode($js,true);
		$campos = $data['data'];

		$cajero = $campos['cajero'];
		$check  =  $this->datasis->dameval("SELECT COUNT(*) FROM sfac WHERE cajero='$cajero'");
		$check +=  $this->datasis->dameval("SELECT COUNT(*) FROM sfpa WHERE cobrador='$cajero'");

		if ($check > 0){
			echo "{ success: false, message: 'Cajero no puede ser Borrado'}";
		} else {
			$this->db->simple_query("DELETE FROM scaj WHERE cajero='$cajero'");
			logusu('scaj',"CAJERO $cajero ELIMINADO");
			echo "{ success: true, message: 'Cajero Eliminado'}";
		}
	}


//0414 376 0149 juan picapiedras

//****************************************************************8
//
//
//
//****************************************************************8
	function scajextjs(){
		$encabeza='CAJEROS';
		$listados= $this->datasis->listados('scaj');
		$otros=$this->datasis->otros('scaj', 'ventas/scaj');

		$mSQL = "SELECT ubica, CONCAT(ubica,' ',ubides) descrip FROM caub WHERE gasto='N' ORDER BY ubica";
		$alma = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT codbanc, CONCAT(codbanc,' ',banco) banco FROM banc WHERE tbanco='CAJ' ORDER BY codbanc";
		$cajas = $this->datasis->llenacombo($mSQL);

		$mSQL  = "SELECT vendedor, CONCAT(vendedor,' ',nombre) nombre FROM vend ORDER BY vendedor";
		$vende = $this->datasis->llenacombo($mSQL);

		$urlajax = 'ventas/scaj/';
		$variables = "";

		$funciones = "
function estado(val){
	if ( val == 'A'){ return 'Abierto';}
	else if ( val == 'C'){return  'Cerrado';}
}
";

		$valida = "
		{ type: 'length', field: 'cajero', min: 1 },
		{ type: 'length', field: 'nombre', min: 1 }
		";
		
		$columnas = "
		{ header: 'Codigo',    width:  50, sortable: true, dataIndex: 'cajero',   field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Nombre',    width: 180, sortable: true, dataIndex: 'nombre',   field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Status',    width:  70, sortable: true, dataIndex: 'status',   field: { type: 'textfield' }, filter: { type: 'string'  }, renderer: estado },
		{ header: 'Vendedor',  width:  60, sortable: true, dataIndex: 'vendedor', field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Caja',      width:  40, sortable: true, dataIndex: 'caja',     field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Apertura',  width:  80, sortable: true, dataIndex: 'fechaa',   field: { type: 'datefield' }, filter: { type: 'date'    } }, 
		{ header: 'Hora',      width:  50, sortable: true, dataIndex: 'horaa',    field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Cierre',    width:  80, sortable: true, dataIndex: 'fechaa',   field: { type: 'datefield' }, filter: { type: 'date'    } }, 
		{ header: 'Hora',      width:  50, sortable: true, dataIndex: 'horac',    field: { type: 'textfield' }, filter: { type: 'string'  } }, 
		{ header: 'Fondo',     width:  90, sortable: true, dataIndex: 'apertura', field: { type: 'numeric'   }, filter: { type: 'numeric' }, align: 'right',renderer : Ext.util.Format.numberRenderer('0,000.00') },
		{ header: 'Alamcen',   width:  60, sortable: true, dataIndex: 'almacen',  field: { type: 'testfield' }, filter: { type: 'string'  } }, 
		{ header: 'Carpeta',   width: 200, sortable: true, dataIndex: 'directo',  field: { type: 'textfield' }, filter: { type: 'string'  } }, 
	";

		$campos = "'id', 'cajero', 'nombre', 'clave', 'fechaa', 'horaa', 'apertura', 'fechac', 'horac', 'cierre', 'status', 'directo', 'mesai', 'mesaf', 'horai', 'horaf', 'caja', 'almacen', 'vendedor'";
		
		$camposforma = "
							{
							xtype:'fieldset',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:70 },
							style:'padding:4px',
							layout: 'column',
							items: [
									{ xtype: 'textfield', fieldLabel: 'Codigo',  name: 'cajero',  allowBlank: false,  width: 120, id: 'codigo' },
									{ xtype: 'combo',     fieldLabel: 'Status',  name: 'status',                      width: 130,  store: [['A','Abierto'],['C','Cerrado']], labelWidth:50},
									{ xtype: 'textfield', fieldLabel: 'Clave',   name: 'clave', allowBlank: true, width: 150, inputType: 'password', labelWidth:50 },
									{ xtype: 'textfield', fieldLabel: 'Nombre',  name: 'nombre',  allowBlank: false,  width: 400 },
									{ xtype: 'combo',     fieldLabel: 'Caja',    name: 'caja',    store: [".$cajas."], width: 400 },
									{ xtype: 'combo',     fieldLabel: 'Almacen', name: 'almacen', store: [".$alma."], width: 300 },
									{ xtype: 'combo',     fieldLabel: 'Vendedor', name: 'vendedor', store: [".$vende."], width: 400 },
									{ xtype: 'textfield', fieldLabel: 'Carpeta', name: 'directo', allowBlank: true,   width: 400 }
								]
							},{
							xtype:'fieldset',
							title: 'APERTURA/CIERRE',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { labelWidth:60 },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ xtype: 'datefield',   fieldLabel: 'Apertura', name: 'fechaa',   width:160, labelWidth:60, format: 'd/m/Y', submitFormat: 'Y-m-d' },
								{ xtype: 'textfield',   fieldLabel: 'Hora',     name: 'horaa',    width:100, labelWidth:40 },
								{ xtype: 'numberfield', fieldLabel: 'Monto',    name: 'apertura', width:140, labelWidth:50, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
								{ xtype: 'datefield',   fieldLabel: 'Cierre',   name: 'fechac',   width:160, labelWidth:60, format: 'd/m/Y', submitFormat: 'Y-m-d' },
								{ xtype: 'textfield',   fieldLabel: 'Hora',     name: 'horac',    width:100, labelWidth:40 },
								{ xtype: 'numberfield', fieldLabel: 'Monto',    name: 'cierre',   width:140, labelWidth:50, hideTrigger: true, fieldStyle: 'text-align: right',  renderer : Ext.util.Format.numberRenderer('0,000.00') },
							]
							},{
							xtype:'fieldset',
							title: 'RESTAURANTE',
							frame: false,
							border: false,
							labelAlign: 'right',
							defaults: { xtype: 'textfield', allowBlank: true },
							style:'padding:4px',
							layout: 'column',
							items: [
								{ fieldLabel: 'Mesas Validas', name: 'mesai',  width:200, labelWidth:120 },
								{ fieldLabel: 'Hasta',           name: 'mesaf',  width:140, labelWidth: 70 },
								{ fieldLabel: 'Hora Feliz',      name: 'horai',  width:200, labelWidth:120 },
								{ fieldLabel: 'Hasta',           name: 'horaf',  width:140, labelWidth: 70 },
							]
							}
		";

		$titulow = 'Cajeros';

		$dockedItems = "
				{ iconCls: 'icon-reset', itemId: 'close', text: 'Cerrar',   scope: this, handler: this.onClose },
				{ iconCls: 'icon-save',  itemId: 'save',  text: 'Guardar',  disabled: false, scope: this, handler: this.onSave }
		";

		$winwidget = "
				closable: false,
				closeAction: 'destroy',
				width: 450,
				height: 460,
				resizable: false,
				modal: true,
				items: [writeForm],
				listeners: {
					beforeshow: function() {
						var form = this.down('writerform').getForm();
						this.activeRecord = registro;
						
						if (registro) {
							form.loadRecord(registro);
						} 
					}
				}
";

		$stores = "";

		$features = "features: [ filters],";
		$filtros = "var filters = { ftype: 'filters', encode: 'json', local: false }; ";

		$data['listados']    = $listados;
		$data['otros']       = $otros;
		$data['encabeza']    = $encabeza;
		$data['urlajax']     = $urlajax;
		$data['variables']   = $variables;
		$data['funciones']   = $funciones;
		$data['valida']      = $valida;
		$data['columnas']    = $columnas;
		$data['campos']      = $campos;
		$data['stores']      = $stores;
		$data['camposforma'] = $camposforma;
		$data['titulow']     = $titulow;
		$data['dockedItems'] = $dockedItems;
		$data['winwidget']   = $winwidget;
		$data['features']    = $features;
		$data['filtros']     = $filtros;
		
		$data['title']  = heading('Cajeros');
		$this->load->view('extjs/extjsven',$data);
		
	}
	
}
?>