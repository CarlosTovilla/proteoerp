<?php
/**
 * ProteoERP
 *
 * @autor    Andres Hocevar
 * @license  GNU GPL v3
*/
class Conv extends Controller {
	var $mModulo = 'CONV';
	var $titp    = 'Conversiones de Inventario';
	var $tits    = 'Conversiones de Inventario';
	var $url     = 'inventario/conv/';
	var $cpeso   = array();

	function Conv(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'CONV', $ventana=0 );
	}

	function index(){
		$this->instalar();
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

		$grid1   = $this->defgridit();
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 165, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'boton1',  'img'=>'assets/default/images/print.png','alt' => 'Formato PDF',      'label'=>'Reimprimir Documento'));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = 'radicional', $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );


		$adic = array(
			array('id'=>'fedita', 'title'=>'Agregar Conversi&oacute;n'),
			array('id'=>'fshow' , 'title'=>'Ver Conversi&oacute;n')
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('CONV', 'JQ');
		$param['otros']        = $this->datasis->otros('CONV', 'JQ');
		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$param['tamano']       = $this->datasis->getintramenu( substr($this->url,0,-1) );
		$this->load->view('jqgrid/crud2',$param);

	}


	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function convshow() {
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				$.post("'.site_url($this->url.'dataedit/show').'/"+id,
					function(data){
						$("#fshow").html(data);
						$("#fshow").dialog( "open" );
					});
			} else {
				$.prompt("<h1>Por favor Seleccione un registro</h1>");
			}
		};';

		$bodyscript .= '
		function convadd() {
			$.post("'.site_url('inventario/conv/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function convdel() {
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				if(confirm("Seguro desea eliminar el registro?")){
					var ret    = jQuery("#newapi'. $grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url($this->url.'dataedit/do_delete').'/"+id, function(data){
						$("#fedita").html("");
						try{
							var json = JSON.parse(data);
							if (json.status == "A"){
								$.prompt("<h1>Registro eliminado</h1>");
								jQuery("#newapi'. $grid0.'").trigger("reloadGrid");
							}else{
								$.prompt("<h1>Registro no se puede eliminado</h1>");
							}
						}catch(e){
							$("#fshow").html(data);
							$("#fshow").dialog({
								autoOpen: false, height: 350, width: 450, modal: true,
								buttons: {"Aceptar": function() {
										$(this).dialog("close");
										jQuery("#newapi'. $grid0.'").trigger("reloadGrid");
									}
								}
							});
						}
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione un Registro</h1>");
			}
		};';

		$bodyscript .= '
		function convedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('inventario/conv/dataedit/modify').'/"+id, function(data){
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
		jQuery("#boton1").click( function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if(id){
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				window.open(\''.site_url('formatos/ver/CONV').'/\'+id, \'_blank\', \'width=900,height=800,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-400)\');
			} else { $.prompt("<h1>Por favor Seleccione una Factura</h1>");}
		});';


		$bodyscript .= '
		$("#fedita").dialog({
			autoOpen: false, height: 550, width: 700, modal: true,
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

							try{
								var json = JSON.parse(r);
								if (json.status == "A"){
									$.prompt("Registro Guardado");
									$( "#fedita" ).dialog( "close" );
									grid.trigger("reloadGrid");
									'.$this->datasis->jwinopen(site_url('formatos/ver/CONV').'/\'+json.pk.id+\'/id\'').';
									return true;
								} else {
									$.prompt(json.mensaje);
								}
							}catch(e){
								$("#fedita").html(r);
							}
						}
				})},
				"Cancelar": function() {
					$("#fedita").html("");
					$( this ).dialog( "close" );
				}
			},
			close: function(){
				$("#fedita").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';

		$bodyscript .= '
		$("#fshow").dialog({
			autoOpen: false, height: 500, width: 700, modal: true,
			buttons: {
				"Aceptar": function() {
					$( this ).dialog( "close" );
				},
			},
			close: function() {
				$("#fshow").html("");
				allFields.val( "" ).removeClass( "ui-state-error" );
			}
		});';

		$bodyscript .= '});';
		$bodyscript .= '</script>';

		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('fecha');
		$grid->label('Fecha');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('observ1');
		$grid->label('Observaciones 1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:35, maxlength: 35 }',
		));


		$grid->addField('observ2');
		$grid->label('Observaciones 2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:35, maxlength: 35 }',
		));


		$grid->addField('almacen');
		$grid->label('Almac&eacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 45,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:4, maxlength: 4 }',
		));


		$grid->addField('transac');
		$grid->label('Transaci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));


		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		/*$grid->addField('id');
		$grid->label('Id');
		$grid->params(array(
			'align'         => "'center'",
			'frozen'        => 'true',
			'width'         => 40,
			'editable'      => 'false',
			'search'        => 'false'
		));*/


		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('190');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
				if (id){
					jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}'
		);

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('CONV','INCLUIR%' ));
		$grid->setEdit(  false);
		$grid->setDelete( $this->datasis->sidapuede('CONV','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('CONV','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');
		$grid->setOndblClickRow('');

		$grid->setBarOptions('addfunc: convadd,editfunc: convedit,viewfunc: convshow,delfunc: convdel');

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
	function getdata(){
		$grid       = $this->jqdatagrid;

		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE = $grid->geneTopWhere('conv');

		$response   = $grid->getData('conv', array(array()), array(), false, $mWHERE, 'id', 'desc' );
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData(){
		echo 'Deshabilitado';
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = 'false';

		$grid  = new $this->jqdatagrid;

/*
		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));
*/

		$grid->addField('codigo');
		$grid->label('C&oacute;digo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 150,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descripci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('salida');
		$grid->label('Salida');
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


		$grid->addField('entrada');
		$grid->label('Entrada');
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

/*
		$grid->addField('transac');
		$grid->label('Transaci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));

		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:12, maxlength: 12 }',
		));
*/

		$grid->addField('costo');
		$grid->label('Costo');
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

/*
		$grid->addField('modificado');
		$grid->label('Modificado');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));
*/

		$grid->showpager(true);
		$grid->setWidth('');
		$grid->setHeight('140');
		//$grid->setTitle($this->titp);
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(   false ); // $this->datasis->sidapuede('ITCONV','INCLUIR%' ));
		$grid->setEdit(  false ); // $this->datasis->sidapuede('ITCONV','MODIFICA%'));
		$grid->setDelete(false ); // $this->datasis->sidapuede('ITCONV','BORR_REG%'));
		$grid->setSearch(false ); // $this->datasis->sidapuede('ITCONV','BUSQUEDA%'));
		$grid->setRowNum(60);
		$grid->setShrinkToFit('false');
		$grid->setOndblClickRow('');

		#Set url
		//$grid->setUrlput(site_url($this->url.'setdata/'));

		#GET url
		$grid->setUrlget(site_url($this->url.'getdatait/'));

		if ($deployed) {
			return $grid->deploy();
		} else {
			return $grid;
		}
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function getdatait($id = 0){
		if($id === 0){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM conv");
		}
		$id = intval($id);
		if(empty($id)) return '';
		$numero   = $this->datasis->dameval("SELECT numero FROM conv WHERE id=${id}");
		$dbnumero = $this->db->escape($numero);

		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itconv WHERE numero=${dbnumero} ORDER BY descrip ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait(){
		echo 'Deshabilitado';
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
				'codigo' =>'codigo_<#i#>',
				'descrip'=>'descrip_<#i#>',
				'ultimo' =>'costo_<#i#>',
				'peso' =>'sinvpeso_<#i#>'
			),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Articulo',
			'where'   => '`tipo` = "Articulo"',
			'script'  => array('post_modbus_sinv(<#i#>)')
		);
		$btn=$this->datasis->p_modbus($modbus,'<#i#>');

		$do = new DataObject('conv');
		$do->rel_one_to_many('itconv', 'itconv', 'numero');
		$do->rel_pointer('itconv','sinv','itconv.codigo=sinv.codigo','sinv.descrip AS sinvdescrip','sinv.ultimo AS sinvultimo','sinv.peso AS sinvpeso');

		$edit = new DataDetails('Conversiones', $do);
		$edit->on_save_redirect=false;
		$edit->set_rel_title('itconv','Producto <#o#>');

		//$edit->script($script,'create');
		//$edit->script($script,'modify');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->pre_process('delete','_pre_delete');
		$edit->post_process('insert','_post_insert');
		$edit->post_process('delete','_post_delete');
		$edit->post_process('update','_post_update');

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->rule = 'required';
		$edit->fecha->mode = 'autohide';
		$edit->fecha->size = 10;
		$edit->fecha->calendar=false;

		$edit->numero = new inputField('N&uacute;mero', 'numero');
		$edit->numero->size = 10;
		$edit->numero->mode='autohide';
		$edit->numero->maxlength=8;
		$edit->numero->apply_rules=false; //necesario cuando el campo es clave y no se pide al usuario
		$edit->numero->when=array('show','modify');

		$edit->observa1 = new inputField('Observaciones', 'observ1');
		$edit->observa1->size      = 40;
		$edit->observa1->maxlength = 80;

		$edit->almacen = new  dropdownField ('Almac&eacute;n', 'almacen');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub WHERE invfis="N" AND gasto<>"S" ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:200px;';
		$edit->almacen->size = 5;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo <#o#>', 'codigo_<#i#>');
		$edit->codigo->size     = 12;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rel_id   = 'itconv';
		$edit->codigo->rule     = 'required|callback_chrepetidos|callback_chcodigoa';
		$edit->codigo->append($btn);

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->db_name='descrip';
		$edit->descrip->rel_id='itconv';
		$edit->descrip->type='inputhidden';

		$edit->entrada = new inputField('Entrada <#o#>', 'entrada_<#i#>');
		$edit->entrada->db_name  = 'entrada';
		$edit->entrada->css_class= 'inputnum';
		$edit->entrada->rel_id   = 'itconv';
		$edit->entrada->maxlength= 10;
		$edit->entrada->size     = 6;
		$edit->entrada->style    = 'color:green';
		$edit->entrada->rule     = 'required|positive';
		$edit->entrada->autocomplete=false;
		$edit->entrada->onkeyup  ='validaEnt(<#i#>)';
		$edit->entrada->onfocus  ='this.select()';
		$edit->entrada->title    ='Cantidad de productos que se suman al inventario';

		$edit->salida = new inputField('Salida <#o#>', 'salida_<#i#>');
		$edit->salida->db_name  = 'salida';
		$edit->salida->css_class= 'inputnum';
		$edit->salida->rel_id   = 'itconv';
		$edit->salida->maxlength= 10;
		$edit->salida->size     = 6;
		$edit->salida->style    = 'color:red';
		$edit->salida->rule     = 'required|positive';
		$edit->salida->autocomplete=false;
		$edit->salida->onkeyup  ='validaSalida(<#i#>)';
		$edit->salida->onfocus  ='this.select()';
		$edit->salida->title    ='Cantidad de productos que se restan al inventario';

		$edit->costo = new hiddenField('', 'costo_<#i#>');
		$edit->costo->db_name   = 'costo';
		$edit->costo->rel_id    = 'itconv';

		$edit->sinvpeso = new inputField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name='sinvpeso';
		$edit->sinvpeso->rel_id='itconv';
		$edit->sinvpeso->pointer=true;
		$edit->sinvpeso->type='inputhidden';

		//**************************
		//fin de campos para detalle
		//**************************

		$edit->container = new containerField('alert','<b style="font-size:1.2em">Advertencia</b>:
		el costo de los productos de <b style="color:#219F21">entrada</b> <b>ser&aacute;n modificados</b> en base al costos de los productos
		de <b style="color:red">salida</b>, si los productos de entrada son mas de uno su costo se calculara tomando en cuenta su participaci&oacute;n en peso.');
		$edit->container->when = array('create');

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('save', 'undo', 'back','add_rel','add');
		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);

			echo json_encode($rt);
		}else{
			$conten['form']  =& $edit;
			$data['content']  = $this->load->view('view_conv', $conten, false);
		}
	}

	//se paso al pre-process, no se esta usando
	function chpeso($codigo,$id){
		$entrada=floatval($this->input->post('entrada_'.$id));
		$this->validation->set_message('chpeso', 'El art&iacute;culo '.$codigo.' no tiene peso, se necesita para el c&aacute;lculo del costo');
		if($entrada>0){
			$dbcodigo=$this->db->escape($codigo);
			$peso=floatval($this->datasis->dameval('SELECT peso FROM sinv WHERE codigo='.$dbcodigo));
			if($peso>0){
				return true;
			}
			return false;
		}
		return true;
	}

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) AS val FROM sinv WHERE activo=\'S\' AND MID(tipo,1,1)="A" AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo de producto no v&aacute;lido o inactivo');
			return false;
		}
		return true;
	}

	function chrepetidos($cod){
		if(!isset($this->chrepetidos)) $this->chrepetidos=array();
		if(array_search($cod, $this->chrepetidos)===false){
			$this->chrepetidos[]=$cod;
			return true;
		}else{
			$this->validation->set_message('chrepetidos', 'El art&iacute;culo '.$cod.' esta repetido');
			return false;
		}
	}

	function _pre_insert($do){
		$monto=$entradas=$salidas=0;
		$this->costo_salidas= 0;
		$this->peso_entradas= 0;
		$this->pesos=$errpeso= array();

		//Hasta aca en costo trae el valor del ultimo de sinv, se opera para cambiarlo a:
		//costo=costo*(entrada o salida segun se el caso)
		$cana=$do->count_rel('itconv');
		for($i=0;$i<$cana;$i++){
			$ent   =floatval($do->get_rel('itconv','entrada',$i));
			$sal   =floatval($do->get_rel('itconv','salida' ,$i));
			$costo =floatval($do->get_rel('itconv','costo' ,$i));
			$codigo=$do->get_rel('itconv','codigo' ,$i);

			if ($ent!=0 && $sal!=0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='No puede tener entradas y salidas en el rubro .'.$i+1;
				return false;
			}
			if ($ent==0 && $sal==0){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe tener entradas o salidas en el rubro .'.$i+1;
				return false;
			}
			if($sal != 0){
				$salidas+=$sal;
				$this->costo_salidas+=$sal*$costo;
				$monto=round($sal*$do->get_rel('itconv','costo',$i),2);
			}
			if($ent != 0){
				$entradas+=$ent;
				$dbcodigo=$this->db->escape($codigo);
				$peso    =floatval($this->datasis->dameval('SELECT peso FROM sinv WHERE codigo='.$dbcodigo));
				if($peso<=0){
					$errpeso[]=$codigo;
				}
				$this->pesos[$codigo] = $peso;

				$this->peso_entradas+=$sal*$peso;
				$monto=round($ent*$do->get_rel('itconv','costo',$i),2);
			}
			$do->set_rel('itconv','costo'   ,$monto  ,$i);
		}

		//Si es solo un producto de entrada no importa el peso
		if($entradas==1 && $this->peso_entradas==0){
			$this->peso_entradas=$this->pesos[$codigo]=1;
		}

		if($entradas>1 && count($errpeso)>0){
			$errprod=implode(',',$errpeso);
			if(count($errpeso)>1){
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']="Los productos de entrada: ${errprod} no tienen pesos registrado";
			}else{
				$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']="El producto de entrada ${errprod} no tienen peso registrado";
			}
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert'].=', debe registrar los pesos ya que estos seran usados para calcular los nuevos costos';
			return false;
		}

		if($entradas == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos una entrada.';
			return false;
		}

		if($salidas == 0){
			$do->error_message_ar['pre_ins'] = $do->error_message_ar['insert']='Debe ingresar al menos una salida.';
			return false;
		}

		$numero =$this->datasis->fprox_numero('nconv');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date('H:i:s');

		$obs1=$obs2=$observa='';
		if(strlen($do->get('observ1')) >80 ) $observa=substr($do->get('observ1'),0,80);
		else $observa=$do->get('observ1');
		if (strlen($observa)>40){
			$obs1=substr($observa, 0, 39 );
			$obs2=substr($observa,40);
		}else{
			$obs1=$observa;
		}

		$do->set('observ1',$obs1);
		$do->set('observ2',$obs2);
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);

		for($i=0;$i<$cana;$i++){
			//$do->set_rel('itconv','numero'  ,$estampa,$i);
			$do->set_rel('itconv','estampa' ,$estampa,$i);
			$do->set_rel('itconv','hora'    ,$hora   ,$i);
			$do->set_rel('itconv','transac' ,$transac,$i);
			$do->set_rel('itconv','usuario' ,$usuario,$i);
		}
		return true;
	}

	function _pre_update($do){
		$do->error_message_ar['pre_upd'] = 'No se puede modificar una conversion, debe eliminarla y volverla a hacer';
		return false;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$alma   = $do->get('almacen');
		$numero = $do->get('numero');
		$cana   = $do->count_rel('itconv');
		for($i=0;$i<$cana;$i++){
			$codigo = $do->get_rel('itconv','codigo' ,$i);
			$ent    = $do->get_rel('itconv','entrada',$i);
			$sal    = $do->get_rel('itconv','salida' ,$i);

			$monto   = $ent-$sal;
			$dbcodigo= $this->db->escape($codigo);
			$dbalma  = $this->db->escape($alma);

			$this->datasis->sinvcarga($codigo, $alma, $monto);

			if($monto > 0 && $this->peso_entradas>0){   // Entrada
				$peso      = $this->pesos[$codigo]*$monto;
				$participa = $peso/$this->peso_entradas;
				$ncosto    = round($this->costo_salidas*$participa/$monto,2);

				if($ncosto>0){
					$mycosto="IF(formcal='P',pond,IF(formcal='U',${ncosto},IF(formcal='S',standard,GREATEST(pond,ultimo))))";
					$mSQL='UPDATE sinv SET
							ultimo ='.$ncosto.',
							base1  =ROUND(precio1*10000/(100+iva))/100,
							base2  =ROUND(precio2*10000/(100+iva))/100,
							base3  =ROUND(precio3*10000/(100+iva))/100,
							base4  =ROUND(precio4*10000/(100+iva))/100,
							margen1=ROUND(10000-(('.$mycosto.')*10000/base1))/100,
							margen2=ROUND(10000-(('.$mycosto.')*10000/base2))/100,
							margen3=ROUND(10000-(('.$mycosto.')*10000/base3))/100,
							margen4=ROUND(10000-(('.$mycosto.')*10000/base4))/100
					WHERE codigo='.$dbcodigo;
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'conv');}
				}
			}
		}

		//trafrac ittrafrac
		logusu('conv',"Conversion ${numero} CREADA");
	}

	function _post_delete($do){
		$numero = $do->get('numero');
		$alma   = $do->get('almacen');
		$cana   = $do->count_rel('itconv');
		for($i=0;$i<$cana;$i++){
			$codigo = $do->get_rel('itconv','codigo' ,$i);
			$ent    = $do->get_rel('itconv','entrada',$i);
			$sal    = $do->get_rel('itconv','salida' ,$i);

			$monto   = $sal-$ent;

			$this->datasis->sinvcarga($codigo, $alma, $monto);
		}

		logusu('conv',"Conversion ${numero} ELIMINADA");
	}

	function _post_update($do){
		logusu('conv',"Conversion ${numero} MODIFICADA");
	}

	function tabla() {
		$id   = isset($_REQUEST['id'])  ? $_REQUEST['id']   :  0;
		$salida = '';
/*
		$cliente = $this->datasis->dameval("SELECT cod_cli FROM conv WHERE id='$id'");
		$mSQL = "SELECT cod_cli, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM smov WHERE cod_cli='$cliente' AND abonos<>monto AND tipo_doc<>'AB' ORDER BY fecha ";
		$query = $this->db->query($mSQL);
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Cuentas X Cobrar</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";

			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
				$salida .= "</tr>";
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();


/*
		// Revisa formas de pago sfpa
		$mSQL = "SELECT codbanc, numero, monto FROM bmov WHERE transac='$transac' ";
		$query = $this->db->query($mSQL);
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento en Caja o Banco</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Bco</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				$salida .= "<tr>";
				$salida .= "<td>".$row['codbanc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "</table>";
		}
*/
		echo $salida;
	}

	function instalar(){
		$campos=$this->db->list_fields('conv');
		if(!in_array('id',$campos)){
			$this->db->simple_query('ALTER TABLE conv DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE conv ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id) ');
			$this->db->simple_query('ALTER TABLE conv ADD UNIQUE INDEX numero (numero)');
		}

		$itcampos=$this->db->list_fields('itconv');
		if(!in_array('costo',$campos)){
			$this->db->simple_query('ALTER TABLE `itconv` ADD COLUMN `costo` DECIMAL(10,2) NULL DEFAULT NULL');
		}
	}
}
