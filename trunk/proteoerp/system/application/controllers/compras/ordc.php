<?php
require_once(BASEPATH.'application/controllers/validaciones.php');

class Ordc extends Controller {
	var $mModulo='ORDC';
	var $titp = 'Orden de Compras';
	var $tits = 'Orden de Compras';
	var $url  = 'compras/ordc/';
	var $genesal = true;
	var $solo    = false;


	function Ordc(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		//$this->datasis->modulo_nombre( $modulo, $ventana=0 );
	}

	function index(){
		/*if ( !$this->datasis->iscampo('ordc','id') ) {
			$this->db->simple_query('ALTER TABLE ordc DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE ordc ADD UNIQUE INDEX numero (numero)');
			$this->db->simple_query('ALTER TABLE ordc ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};*/
		$this->datasis->modintramenu( 950, 650, substr($this->url,0,-1) );
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

		$readyLayout = $grid->readyLayout2( 212, 232, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		#Set url
		$grid->setUrlput(site_url($this->url.'setdata/'));

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"imprime",  "img"=>"images/pdf_logo.gif", "alt" => 'Formato PDF', "label"=>"Reimprimir Documento"));
		$grid->wbotonadd(array("id"=>"agregar",  "img"=>"images/agrega4.png",  "alt" => 'Agregar',     "label"=>"Agregar Orden"));
		$grid->wbotonadd(array("id"=>"modifica", "img"=>"images/editar.png",   "alt" => 'Modificar',   "label"=>"Modificar Orden"));
		$WestPanel = $grid->deploywestp();

		//Panel Central y Sur
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
			array("id"=>"fne",  "title"=>"Agregar/Editar Orden de Compra")
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('ORDC', 'JQ');
		$param['otros']        = $this->datasis->otros('ORDC', 'JQ');
		$param['centerpanel']  = $centerpanel;
		//$param['funciones']    = $funciones;
		$param['temas']        = array('proteo','darkness','anexos1');
		$param['bodyscript']   = $bodyscript;
		$param['tabs']         = false;
		$param['encabeza']     = $this->titp;
		$this->load->view('jqgrid/crud2',$param);
	}

	//***************************
	//Funciones de los Botones
	//***************************
	function bodyscript( $grid0, $grid1 ){
		$bodyscript ='<script type="text/javascript">';

		$bodyscript .='
		jQuery("#imprime").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'. $grid0.'").jqGrid(\'getRowData\',id);
				window.open(\''.site_url('formatos/ver/ORDC').'/\'+id+"/id", \'_blank\', \'width=900,height=800,scrollbars=yes,status=yes,resizable=yes,screenx=((screen.availHeight/2)-450), screeny=((screen.availWidth/2)-400)\');
			} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
		});';

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

		$bodyscript .='
			jQuery("#modifica").click( function(){
				var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					if ( ret.status == "PE" ) {
						$.post("'.site_url('compras/ordc/solo/modify').'/"+id, function(data){
							$("#fne").html(data);
							$( "#fne" ).dialog( "open" );
						});
					} else {
						$.prompt("<h1>Orden no modificable, esta cerrada o en back order");
					}
				} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
			});
			';

		//Agregar Compra
		$bodyscript .= '
			$( "#agregar" ).click(function() {
				$.post("'.site_url('compras/ordc/solo/create').'",
				function(data){
					$("#fne").html(data);
					$( "#fne" ).dialog( "open" );
				})
			});';

		$bodyscript .= '
			$( "#fne" ).dialog({
				autoOpen: false, height: 510, width: 840, modal: true,
				buttons: {
					"Guardar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){
									var res = $.parseJSON(r);
									if ( res.status == "A"){
										apprise(res.mensaje);
										$( "#fne" ).dialog( "close" );
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/ORDC').'/\'+res.id+\'/id\'').';
										return true;
									} else if ( res.status == "C"){
										apprise("<div style=\"font-size:16px;font-weight:bold;background:green;color:white\">Mensaje:</div> <h1>"+res.mensaje);
									} else {
										apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
									}
								}
							});
						}
					},
					Cancelar: function() { $( this ).dialog( "close" ); }
				},
				close: function() { allFields.val( "" ).removeClass( "ui-state-error" );}
			});';

		$bodyscript .= '	});';
		$bodyscript .= "\n</script>\n";



		return $bodyscript;
	}

	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgrid( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

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

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

		$grid->addField('status');
		$grid->label('Status');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

/*
		$grid->addField('almacen');
		$grid->label('Almacen');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));
*/

		$grid->addField('proveed');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre Proveedor');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('montotot');
		$grid->label('Sub Total');
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


		$grid->addField('montoiva');
		$grid->label('I.V.A.');
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

		$grid->addField('montonet');
		$grid->label('Total');
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
		$grid->addField('montofac');
		$grid->label('Montofac');
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
*/

		$grid->addField('condi');
		$grid->label('Condiciones');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('codban');
		$grid->label('Banco');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('tipo_op');
		$grid->label('Tipo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));


		$grid->addField('cheque');
		$grid->label('Cheque');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('comprob');
		$grid->label('Comprobante');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 6 }',
		));

		$grid->addField('anticipo');
		$grid->label('Anticipo');
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
		$grid->addField('fechafac');
		$grid->label('Fechafac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('arribo');
		$grid->label('Arribo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('factura');
		$grid->label('Factura');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('mdolar');
		$grid->label('Mdolar');
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
*/

		$grid->addField('peso');
		$grid->label('Peso');
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
		$grid->addField('benefi');
		$grid->label('Benefi');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));
*/

		$grid->addField('condi1');
		$grid->label('Condi1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('condi2');
		$grid->label('Condi2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('condi3');
		$grid->label('Condi3');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 40 }',
		));


		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));


		$grid->addField('hora');
		$grid->label('Hora');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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

		$grid->addField('cliente');
		$grid->label('Cliente');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
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
		$grid->setHeight('200');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('function(id){
				if (id){
					var ret = $("#titulos").getRowData(id);
					jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
					$.ajax({
						url: "'.base_url().$this->url.'tabla/"+id,
						success: function(msg){
							$("#ladicional").html(msg);
						}
					});
				}
			},
			afterInsertRow:
			function( rid, aData, rowe){
				if ( aData.status == "PE"  ){
					$(this).jqGrid( "setRowData", rid, false,{color:"#000000", background:"#DCFFB5" });
				} else if ( aData.status == "BA" ){
					$(this).jqGrid( "setRowData", rid, false,{color:"#000000", background:"#ECE2FF" });
				}
			}
		');

		$grid->setOndblClickRow("");

		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 460, height:280, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 460, height:280, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

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
		$mWHERE = $grid->geneTopWhere('ordc');

		$response   = $grid->getData('ordc', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM ordc WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('ordc', $data);
					echo "Registro Agregado";

					logusu('ORDC',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM ordc WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM ordc WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE ordc SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("ordc", $data);
				logusu('ORDC',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('ordc', $data);
				logusu('ORDC',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
		$meco = $this->datasis->dameval("SELECT $mcodp FROM ordc WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM ordc WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM ordc WHERE id=$id ");
				logusu('ORDC',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}


	//***************************
	//Definicion del Grid y la Forma
	//***************************
	function defgridit( $deployed = false ){
		$i      = 1;
		$editar = "false";

		$grid  = new $this->jqdatagrid;

/*
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

		$grid->addField('numero');
		$grid->label('Numero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('proveed');
		$grid->label('Proveed');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 5 }',
		));


		$grid->addField('depo');
		$grid->label('Depo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 4 }',
		));
*/

		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 15 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 45 }',
		));


		$grid->addField('cantidad');
		$grid->label('Cantidad');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('recibido');
		$grid->label('Recibido');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 70,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


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


		$grid->addField('importe');
		$grid->label('Importe');
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


		$grid->addField('iva');
		$grid->label('Iva');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('montoiva');
		$grid->label('Montoiva');
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
		$grid->addField('control');
		$grid->label('Control');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));
*/

		$grid->addField('garantia');
		$grid->label('Garantia');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 0 }'
		));

/*
		$grid->addField('ultimo');
		$grid->label('Ultimo');
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


		$grid->addField('precio1');
		$grid->label('Precio1');
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


		$grid->addField('precio2');
		$grid->label('Precio2');
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


		$grid->addField('precio3');
		$grid->label('Precio3');
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


		$grid->addField('precio4');
		$grid->label('Precio4');
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


		$grid->addField('transac');
		$grid->label('Transac');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
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
		$grid->setHeight('170');
		//$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){if (id){var ret = $("#titulos").getRowData(id);}},
			cellEdit: true,
			cellsubmit: "remote",
			cellurl: "'.site_url($this->url.'setdatait/').'"
		');
		$grid->setOndblClickRow("");


		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(false);
		$grid->setEdit(false);
		$grid->setDelete(false);
		$grid->setSearch(true);
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

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
	function getdatait()
	{
		$id = $this->uri->segment(4);
		if ($id == false ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM ordc");
		}
		$numero = $this->datasis->dameval("SELECT numero FROM ordc WHERE id=$id");
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itordc WHERE numero='$numero' ORDER BY codigo ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;

	}

	/**
	* Guarda la Informacion
	*/
/*
	function setDatait()
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
				$check = $this->datasis->dameval("SELECT count(*) FROM itordc WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('itordc', $data);
					echo "Registro Agregado";

					logusu('ITORDC',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM itordc WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM itordc WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE itordc SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("itordc", $data);
				logusu('ITORDC',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('itordc', $data);
				logusu('ITORDC',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
		$meco = $this->datasis->dameval("SELECT $mcodp FROM itordc WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM itordc WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM itordc WHERE id=$id ");
				logusu('ITORDC',"Registro ????? ELIMINADO");
				echo "Registro Eliminado";
			}
		};
	}
*/


	function solo() {
		$this->solo = true;
		$id = $this->uri->segment($this->uri->total_segments());

		//Creando Orden
		if ( $id == 'create'){
			$this->dataedit();
		} elseif ( $id == 'insert'){
			$this->genesal = false;
			$rt = $this->dataedit();
			//$id = $this->claves["id"];
			if ( strlen($rt) > 0 )
				echo '{"status":"A","id":"'.$id.'" ,"mensaje":"'.$rt.'"}';
		} elseif ( $id == 'process'){
			$control = $this->uri->segment($this->uri->total_segments()-1);
			$rt = $this->actualizar($control);
			if ( strlen($rt[1]) > 0 )
				if ( $rt[0] === false ) $p = 'E'; else $p='A';
				echo '{"status":"'.$p.'","id":"'.$control.'" ,"mensaje":"<h1>'.$rt[1].'</h1>"}';
		} else {
			$modo = $this->uri->segment($this->uri->total_segments()-1);
			if ( $modo == 'update' ) $this->genesal = false;
			$rt = $this->dataedit();
			if ( strlen($rt) > 0 )
				echo '{"status":"A","id":"'.$id.'" ,"mensaje":"'.$rt.'"}';
		}
	}

	function filteredgrid(){
		echo "Opcion Eliminada";
	}

	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
/*
		$modbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  =>array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=>array('proveed'=>'proveed','nombre'=>'nombre'),
			'titulo'  =>'Buscar Proveedor');

		$boton=$this->datasis->modbus($modbus);

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
				'base1'  =>'precio1_<#i#>',
				'base2'  =>'precio2_<#i#>',
				'base3'  =>'precio3_<#i#>',
				'base4'  =>'precio4_<#i#>',
				'iva'    =>'itiva_<#i#>',
				'peso'   =>'sinvpeso_<#i#>',
				'pond'   =>'pond_<#i#>',
				'ultimo' =>'ultimo_<#i#>',
				'precio1'=>'costo_<#i#>'
				),
			'p_uri'   => array(4=>'<#i#>'),
			'titulo'  => 'Buscar Art&iacute;culo',
			'where'   => '`activo` = "S"',
			'script'  => array('post_modbus_sinv(<#i#>)')
				);
*/

		$modbus=array(
			'tabla'   =>'sinv',
			'columnas'=>array(
				'codigo' =>'C&oacute;digo',
				'descrip'=>'Descripci&oacute;n'),
			'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'Descripci&oacute;n'),
			'retornar'=>array('codigo'=>'codigo_<#i#>','descrip'=>'descrip_<#i#>','pond'=>'costo_<#i#>','iva'=>'iva_<#i#>','peso'=>'sinvpeso_<#i#>'),
			'p_uri'=>array(4=>'<#i#>'),
			'script'  => array('post_modbus_sinv(<#i#>)'),
			'titulo'  =>'Buscar Art&iacute;culo',
			'where'   =>'activo = "S"');

		$sprvbus=array(
			'tabla'   =>'sprv',
			'columnas'=>array(
				'proveed' =>'C&oacute;digo Proveedor',
				'nombre'=>'Nombre',
				'rif'=>'RIF'),
			'filtro'  => array('proveed'=>'C&oacute;digo Proveedor','nombre'=>'Nombre'),
			'retornar'=> array('proveed'=>'proveed', 'nombre'=>'nombre'),
			'script'  => array('post_modbus_sprv()'),
			'titulo'  =>'Buscar Proveedor');


		$btn=$this->datasis->p_modbus($modbus,'<#i#>');


		$do = new DataObject('ordc');
		$do->rel_one_to_many('itordc', 'itordc', 'numero');
		$do->pointer('sprv' ,'sprv.proveed=ordc.proveed','sprv.nombre AS sprvnombre','left');
		$do->rel_pointer('itordc','sinv','itordc.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Orden De Comnpra', $do);
		$edit->back_url = site_url('compras/ordc/filteredgrid');
		$edit->set_rel_title('itordc','Producto <#o#>');

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

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 7;
		$edit->proveed->maxlength= 5;
		$edit->proveed->autocomplete=false;
		$edit->proveed->rule     = 'required';
		$edit->proveed->append($this->datasis->modbus($sprvbus));

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->status = new  dropdownField ('Estado', 'status');
		$edit->status->option('','');
		$edit->status->option('PE','Pendiente');
		$edit->status->option('CE','Cerrado');
		$edit->status->option('BA','BackOrde');
		$edit->status->style='width:100px;';

		$edit->arribo = new DateonlyField('Arribo', 'arribo','d/m/Y');
		$edit->arribo->insertValue = date('Y-m-d');
		$edit->arribo->rule = 'required';
		$edit->arribo->mode = 'autohide';
		$edit->arribo->size = 10;
		
		$edit->fechafac = new DateonlyField('Fecha Factura', 'fechafac','d/m/Y');
		$edit->fechafac->insertValue = date('Y-m-d');
		$edit->fechafac->rule = 'required';
		$edit->fechafac->mode = 'autohide';
		$edit->fechafac->size = 10;

		//**************************
		//  Campos para el detalle
		//**************************
		$edit->codigo = new inputField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->autocomplete=false;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'required|callback_chcodigoa';
		$edit->codigo->rel_id   = 'itordc';

		$edit->descrip = new inputField('Descripci&oacute;n <#o#>', 'descrip_<#i#>');
		$edit->descrip->size=36;
		$edit->descrip->db_name='descrip';
		$edit->descrip->maxlength=50;
		$edit->descrip->readonly  = true;
		$edit->descrip->rel_id='itordc';

		//$edit->cantidad = new inputField('Cantidad <#o#>', 'cantidad_<#i#>');
		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->db_name      = 'cantidad';
		$edit->cantidad->css_class    = 'inputnum';
		$edit->cantidad->rel_id       = 'itordc';
		$edit->cantidad->maxlength    = 10;
		$edit->cantidad->size         =  8;
		$edit->cantidad->autocomplete = false;
		$edit->cantidad->onkeyup      = 'importe(<#i#>)';
		$edit->cantidad->rule         = 'required|positive';
		$edit->cantidad->showformat   = 'decimal';

		$edit->costo = new inputField('Costo', 'costo_<#i#>');
		$edit->costo->css_class       = 'inputnum';
		$edit->costo->rule            = 'required|positive';
		$edit->costo->onkeyup         = 'importe(<#i#>)';
		$edit->costo->size            = 10;
		$edit->costo->autocomplete    = false;
		$edit->costo->db_name         = 'costo';
		$edit->costo->rel_id          = 'itordc';
		$edit->costo->showformat      = 'decimal';

		$edit->importe = new inputField('Importe', 'importe_<#i#>');
		$edit->importe->db_name       = 'importe';
		$edit->importe->size          = 12;
		$edit->importe->rel_id        = 'itordc';
		$edit->importe->autocomplete  = false;
		$edit->importe->onkeyup       = 'costo(<#i#>)';
		$edit->importe->css_class     = 'inputnum';
		$edit->importe->showformat    = 'decimal';
		$edit->importe->readonly      = true;

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name      = 'sinvpeso';
		$edit->sinvpeso->rel_id       = 'itordc';
		$edit->sinvpeso->pointer      = true;
		$edit->sinvpeso->showformat   = 'decimal';

		$edit->iva = new hiddenField('Impuesto', 'iva_<#i#>');
		$edit->iva->db_name           = 'iva';
		$edit->iva->rel_id            = 'itordc';
		$edit->iva->showformat        = 'decimal';

		for($i=1;$i<=4;$i++){
			$obj='precio'.$i;
			$edit->$obj = new hiddenField('Precio <#o#>', $obj.'_<#i#>');
			$edit->$obj->db_name   = 'sinv'.$obj;
			$edit->$obj->rel_id    = 'itordc';
			$edit->$obj->pointer   = true;
		}

		$edit->ultimo = new hiddenField('', 'ultimo_<#i#>');
		$edit->ultimo->db_name   = 'ultimo';
		$edit->ultimo->rel_id    = 'itordc';
		$edit->ultimo->pointer   = true;

		$edit->pond = new hiddenField('', "pond_<#i#>");
		$edit->pond->db_name='pond';
		$edit->pond->rel_id   ='itordc';
		$edit->pond->pointer   = true;

		//**************************
		//fin de campos para detalle
		//**************************
		$edit->condi1 = new inputField('Condiciones', 'condi1');
		$edit->condi1->size      = 40;
		$edit->condi1->maxlength = 40;

		$edit->condi2 = new inputField('Condiciones', 'condi2');
		$edit->condi2->size      = 40;
		$edit->condi2->maxlength = 40;

		$edit->condi3 = new inputField('Condiciones', 'condi3');
		$edit->condi3->size      = 40;
		$edit->condi3->maxlength = 40;

		$edit->montoiva = new inputField('Impuesto', 'montoiva');
		$edit->montoiva->css_class ='inputnum';
		$edit->montoiva->readonly  =true;
		$edit->montoiva->size      = 10;

		$edit->montotot = new inputField('Sub-Total', 'montotot');
		$edit->montotot->css_class ='inputnum';
		$edit->montotot->readonly  =true;
		$edit->montotot->size      = 10;

		$edit->montonet = new inputField('Monto Total', 'montonet');
		$edit->montonet->css_class ='inputnum';
		$edit->montonet->readonly  =true;
		$edit->montonet->size      = 10;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->buttons('modify', 'save', 'undo', 'delete', 'exit','add_rel');

		if($this->genesal){
			$edit->build();
			$conten['form']  =&  $edit;
			$conten['solo']  = $this->solo;
			if (  $this->solo ){
				$data['content'] = $this->load->view('view_ordc', $conten);
			} else {
				$data['content'] = $this->load->view('view_ordc', $conten,true);
				$data['title']   = heading('Orden de Compra');
				$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
				$this->load->view('view_ventanas', $data);
			}
		} else {
			$edit->on_save_redirect=false;
			$edit->build();
			if($edit->on_success()){
				//$this->claves=$edit->_dataobject->pk;
				//$this->claves['control']=$edit->_dataobject->get('control');
				$rt= 'Orden Guardada';
			}elseif($edit->on_error()){
				$rt= html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
			return $rt;
		}
		
	}

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM sinv WHERE activo=\'S\' AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo no v&aacute;lido o inactivo');
			return false;
		}
		return true;
	}

	function bussug(){
		$this->rapyd->load('datagrid','datafilter');
		$uri   = anchor('compras/ordc/dataedit/show/<#codigo#>','<#codigo#>');

		$grid = new DataGrid();

		$grid->db->select(array('codigo','descrip','exmax','exmin','existen','ultimo','exmax - existen AS sug'));
		$grid->db->from('sinv');
		$grid->db->where('existen <= exmin');
		$grid->db->where('activo','S');

		$grid->order_by('codigo','desc');
		$grid->per_page = 40;

		$grid->column_orderby('C&oacute;digo',$uri ,'codigo');
		$grid->column_orderby('Descripci&oacute;n' ,'descrip' ,'descrip');
		$grid->column_orderby('M&aacute;ximo'      ,'<nformat><#exmax#></nformat> -- <nformat><#exmin#></nformat>'   ,'exmax'   , "align='right'");
		$grid->column_orderby('Existencia'         ,'<nformat><#existen#></nformat>' ,'existen' , "align='right'");
		$grid->column_orderby('Sugerido'           ,'<nformat><#sug#></nformat>'     ,'exmin'   , "align='right'");
		$grid->column_orderby('&Uacute;ltimo costo','<nformat><#ultimo#></nformat>'  ,'ultimo'  , "align='right'");
		$grid->build();

		$data['content'] = $grid->output;
		$data['title']   = heading('Productos sugueridos');
		$data['head']    = script('jquery.js').script('jquery-ui.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.meiomask.js').style('vino/jquery-ui.css').$this->rapyd->get_head().phpscript('nformat.js').script('plugins/jquery.numeric.pack.js').script('plugins/jquery.floatnumber.js').phpscript('nformat.js');
		$this->load->view('view_ventanas', $data);
	}

	function _pre_insert($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;
		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		$numero =$this->datasis->fprox_numero('nordc');
		$transac=$this->datasis->fprox_numero('ntransa');
		$usuario=$do->get('usuario');
		$estampa=date('Ymd');
		$hora   =date("H:i:s");
			
		$do->set('estampa',$estampa);
		$do->set('hora'   ,$hora);
		$do->set('numero' ,$numero);
		$do->set('transac',$transac);
		
		for($i=0;$i<$cana;$i++){
			$do->set_rel('itordc','estampa' ,$estampa,$i);
			$do->set_rel('itordc','hora'    ,$hora   ,$i);
			$do->set_rel('itordc','transac' ,$transac,$i);
			$do->set_rel('itordc','usuario' ,$usuario,$i);;
		}
		return true;
	}

	function _pre_update($do){
		$iva=$totals=0;
		$cana=$do->count_rel('itordc');
		for($i=0;$i<$cana;$i++){
			$itcana    = $do->get_rel('itordc','cantidad',$i);
			$itpreca   = $do->get_rel('itordc','costo',$i);
			$itiva     = $do->get_rel('itordc','iva',$i);
			$itimporte = $itpreca*$itcana;
			$do->set_rel('itordc','importe' ,$itimporte,$i);

			$iva    +=$itimporte*($itiva/100);
			$totals +=$itimporte;
			//$do->set_rel('itspre','mostrado',$iva+$totals,$i);
		}
		$totalg = $totals+$iva;

		
		$do->set('montonet' ,round($totals ,2));
		$do->set('montotot' ,round($totalg ,2));
		$do->set('montoiva'    ,round($iva    ,2));

		return true;
	}

	function _post_insert($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo CREADO");
	}

	function _post_update($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo MODIFICADO");
	}

	function _post_delete($do){
		$codigo=$do->get('numero');
		logusu('ordc',"O.Compra $codigo ELIMINADO");
	}
	
	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		$numero  = $this->datasis->dameval("SELECT numero  FROM ordc WHERE id='$id'");
		$transac = $this->datasis->dameval("SELECT transac FROM ordc WHERE id='$id'");
		
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE transac='$transac' ORDER BY cod_prv ";
		$query = $this->db->query($mSQL);
		$codprv = 'XXXXXXXXXXXXXXXX';
		$salida = '';
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida = "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			foreach ($query->result_array() as $row)
			{
				if ( $codprv != $row['cod_prv']){
					$codprv = $row['cod_prv'];
					$salida .= "<tr bgcolor='#c7d3c7'>";
					$salida .= "<td colspan=4>".trim($row['nombre']). "</td>";
					$salida .= "</tr>";	
				}
				if ( $row['tipo_doc'] == 'FC' ) {
					$saldo = $row['monto']-$row['abonos'];
				}
				$salida .= "<tr>";
				$salida .= "<td>".$row['tipo_doc']."</td>";
				$salida .= "<td>".$row['numero'].  "</td>";
				$salida .= "<td align='right'>".nformat($row['monto']).   "</td>";
				$salida .= "</tr>";
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		echo $salida;
	}

}