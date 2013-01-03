<?php
class Otin extends Controller {
	var $mModulo = 'OTIN';
	var $titp    = 'OTROS INGRESOS';
	var $tits    = 'OTROS INGRESOS';
	var $url     = 'ventas/otin/';

	function Otin(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->datasis->modulo_nombre( 'OTIN', $ventana=0 );
	}

	function index(){
		if ( !$this->datasis->iscampo('otin','id') ) {
			$this->db->simple_query('ALTER TABLE otin DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE otin ADD UNIQUE INDEX tipo_doc, numero (numero)');
			$this->db->simple_query('ALTER TABLE otin ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
		};
		$this->datasis->modintramenu( 900, 650, substr($this->url,0,-1) );
		redirect($this->url.'jqdatag');
	}

	//***************************
	//Layout en la Ventana
	//
	//***************************
	function jqdatag(){

		$grid = $this->defgrid();
		$grid->setHeight('185');
		$param['grids'][] = $grid->deploy();

		$grid1   = $this->defgridit();
		$grid1->setHeight('190');
		$param['grids'][] = $grid1->deploy();

		// Configura los Paneles
		$readyLayout = $grid->readyLayout2( 212, 220, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);

		//Funciones que ejecutan los botones
		$bodyscript = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Botones Panel Izq
		$grid->wbotonadd(array("id"=>"imprime",  "img"=>"assets/default/images/print.png","alt" => 'Reimprimir', "label"=>"Reimprimir Documento"));
		$WestPanel = $grid->deploywestp();

		//Panel Central
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$adic = array(
		array("id"=>"fedita" , "title"=>"Agregar/Editar Pedido"),
		);

		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);

		$param['WestPanel']    = $WestPanel;
		$param['script']       = script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('OTIN', 'JQ');
		$param['otros']        = $this->datasis->otros('OTIN', 'JQ');
		$param['centerpanel']  = $centerpanel;
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
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '		<script type="text/javascript">';

		$bodyscript .= '
		function otinadd() {
			$.post("'.site_url('ventas/otin/dataedit/create').'",
			function(data){
				$("#fedita").html(data);
				$("#fedita").dialog( "open" );
			})
		};';

		$bodyscript .= '
		function otinedit() {
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				mId = id;
				$.post("'.site_url('ventas/otin/dataedit/modify').'/"+id, function(data){
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
			autoOpen: false, height: 500, width: 700, modal: true,
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
							'.$this->datasis->jwinopen(site_url('formatos/ver/OTIN').'/\'+res.id+\'/id\'').';
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

		$grid->addField('tipo_doc');
		$grid->label('Tipo_doc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


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


		$grid->addField('orden');
		$grid->label('Orden');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('cod_cli');
		$grid->label('Cod_cli');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('rifci');
		$grid->label('Rifci');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 130,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:13, maxlength: 13 }',
		));


		$grid->addField('nombre');
		$grid->label('Nombre');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('direc');
		$grid->label('Direc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('dire1');
		$grid->label('Dire1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('totals');
		$grid->label('Totals');
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
			'width'         => 100,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));


		$grid->addField('totalg');
		$grid->label('Totalg');
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


		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('observa1');
		$grid->label('Observa1');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('observa2');
		$grid->label('Observa2');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 30 }',
		));


		$grid->addField('transac');
		$grid->label('Transac');
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


		$grid->addField('nfiscal');
		$grid->label('Nfiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('afecta');
		$grid->label('Afecta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:8, maxlength: 8 }',
		));


		$grid->addField('fafecta');
		$grid->label('Fafecta');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));


		$grid->addField('montasa');
		$grid->label('Montasa');
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


		$grid->addField('monredu');
		$grid->label('Monredu');
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


		$grid->addField('monadic');
		$grid->label('Monadic');
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


		$grid->addField('tasa');
		$grid->label('Tasa');
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


		$grid->addField('reducida');
		$grid->label('Reducida');
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


		$grid->addField('sobretasa');
		$grid->label('Sobretasa');
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


		$grid->addField('exento');
		$grid->label('Exento');
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


		$grid->addField('sucu');
		$grid->label('Sucu');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


		$grid->addField('depto');
		$grid->label('Depto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:3, maxlength: 3 }',
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

		$grid->setOnSelectRow('
			function(id){
				if (id){
					jQuery(gridId2).jqGrid("setGridParam",{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
					jQuery(gridId2).trigger("reloadGrid");
				}
			}'
		);
		$grid->setFormOptionsE('closeAfterEdit:true, mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setFormOptionsA('closeAfterAdd:true,  mtype: "POST", width: 520, height:300, closeOnEscape: true, top: 50, left:20, recreateForm:true, afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},afterShowForm: function(frm){$("select").selectmenu({style:"popup"});} ');
		$grid->setAfterSubmit("$('#respuesta').html('<span style=\'font-weight:bold; color:red;\'>'+a.responseText+'</span>'); return [true, a ];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('OTIN','INCLUIR%' ));
		$grid->setEdit(   $this->datasis->sidapuede('OTIN','MODIFICA%'));
		$grid->setDelete( $this->datasis->sidapuede('OTIN','BORR_REG%'));
		$grid->setSearch( $this->datasis->sidapuede('OTIN','BUSQUEDA%'));
		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: otinadd,\n\t\teditfunc: otinedit");

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
		$mWHERE = $grid->geneTopWhere('otin');

		$response   = $grid->getData('otin', array(array()), array(), false, $mWHERE, 'id', 'desc' );
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
				$check = $this->datasis->dameval("SELECT count(*) FROM otin WHERE $mcodp=".$this->db->escape($data[$mcodp]));
				if ( $check == 0 ){
					$this->db->insert('otin', $data);
					echo "Registro Agregado";

					logusu('OTIN',"Registro ????? INCLUIDO");
				} else
					echo "Ya existe un registro con ese $mcodp";
			} else
				echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$nuevo  = $data[$mcodp];
			$anterior = $this->datasis->dameval("SELECT $mcodp FROM otin WHERE id=$id");
			if ( $nuevo <> $anterior ){
				//si no son iguales borra el que existe y cambia
				$this->db->query("DELETE FROM otin WHERE $mcodp=?", array($mcodp));
				$this->db->query("UPDATE otin SET $mcodp=? WHERE $mcodp=?", array( $nuevo, $anterior ));
				$this->db->where("id", $id);
				$this->db->update("otin", $data);
				logusu('OTIN',"$mcodp Cambiado/Fusionado Nuevo:".$nuevo." Anterior: ".$anterior." MODIFICADO");
				echo "Grupo Cambiado/Fusionado en clientes";
			} else {
				unset($data[$mcodp]);
				$this->db->where("id", $id);
				$this->db->update('otin', $data);
				logusu('OTIN',"Grupo de Cliente  ".$nuevo." MODIFICADO");
				echo "$mcodp Modificado";
			}

		} elseif($oper == 'del') {
			$meco = $this->datasis->dameval("SELECT $mcodp FROM otin WHERE id=$id");
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM otin WHERE id='$id' ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			} else {
				$this->db->simple_query("DELETE FROM otin WHERE id=$id ");
				logusu('OTIN',"Registro ????? ELIMINADO");
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

		$grid->addField('tipo_doc');
		$grid->label('Tipo_doc');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:2, maxlength: 2 }',
		));


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


		$grid->addField('codigo');
		$grid->label('Codigo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 50,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:5, maxlength: 5 }',
		));


		$grid->addField('descrip');
		$grid->label('Descrip');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 200,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:40, maxlength: 40 }',
		));


		$grid->addField('precio');
		$grid->label('Precio');
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


		$grid->addField('impuesto');
		$grid->label('Impuesto');
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


		$grid->addField('transac');
		$grid->label('Transac');
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


		$grid->addField('cantidad');
		$grid->label('Cantidad');
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


		$grid->addField('larga');
		$grid->label('Larga');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 250,
			'edittype'      => "'textarea'",
			'editoptions'   => "'{rows:2, cols:60}'",
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


		$grid->setShrinkToFit('false');
		#Set url
		$grid->setUrlput(site_url($this->url.'setdatait/'));

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
	function getdatait( $id = 0 )
	{
		if ($id === 0 ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM otin");
		}
		if(empty($id)) return "";
		$numero   = $this->datasis->dameval("SELECT numero FROM otin WHERE id=$id");
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itotin WHERE numero='$numero' ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setDatait()
	{
	}

/*
	//***********************************
	// DataEdit  
	//***********************************

	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'otin');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->tipo_doc = new inputField('Tipo_doc','tipo_doc');
		$edit->tipo_doc->rule='max_length[2]';
		$edit->tipo_doc->size =4;
		$edit->tipo_doc->maxlength =2;

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->orden = new inputField('Orden','orden');
		$edit->orden->rule='max_length[8]';
		$edit->orden->size =10;
		$edit->orden->maxlength =8;

		$edit->cod_cli = new inputField('Cod_cli','cod_cli');
		$edit->cod_cli->rule='max_length[5]';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;

		$edit->rifci = new inputField('Rifci','rifci');
		$edit->rifci->rule='max_length[13]';
		$edit->rifci->size =15;
		$edit->rifci->maxlength =13;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->direc = new inputField('Direc','direc');
		$edit->direc->rule='max_length[40]';
		$edit->direc->size =42;
		$edit->direc->maxlength =40;

		$edit->dire1 = new inputField('Dire1','dire1');
		$edit->dire1->rule='max_length[40]';
		$edit->dire1->size =42;
		$edit->dire1->maxlength =40;

		$edit->totals = new inputField('Totals','totals');
		$edit->totals->rule='max_length[14]|numeric';
		$edit->totals->css_class='inputnum';
		$edit->totals->size =16;
		$edit->totals->maxlength =14;

		$edit->iva = new inputField('Iva','iva');
		$edit->iva->rule='max_length[14]|numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size =16;
		$edit->iva->maxlength =14;

		$edit->totalg = new inputField('Totalg','totalg');
		$edit->totalg->rule='max_length[14]|numeric';
		$edit->totalg->css_class='inputnum';
		$edit->totalg->size =16;
		$edit->totalg->maxlength =14;

		$edit->vence = new dateField('Vence','vence');
		$edit->vence->rule='chfecha';
		$edit->vence->size =10;
		$edit->vence->maxlength =8;

		$edit->observa1 = new inputField('Observa1','observa1');
		$edit->observa1->rule='max_length[30]';
		$edit->observa1->size =32;
		$edit->observa1->maxlength =30;

		$edit->observa2 = new inputField('Observa2','observa2');
		$edit->observa2->rule='max_length[30]';
		$edit->observa2->size =32;
		$edit->observa2->maxlength =30;

		$edit->transac = new inputField('Transac','transac');
		$edit->transac->rule='max_length[8]';
		$edit->transac->size =10;
		$edit->transac->maxlength =8;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		$edit->nfiscal = new inputField('Nfiscal','nfiscal');
		$edit->nfiscal->rule='max_length[8]';
		$edit->nfiscal->size =10;
		$edit->nfiscal->maxlength =8;

		$edit->afecta = new inputField('Afecta','afecta');
		$edit->afecta->rule='max_length[8]';
		$edit->afecta->size =10;
		$edit->afecta->maxlength =8;

		$edit->fafecta = new dateField('Fafecta','fafecta');
		$edit->fafecta->rule='chfecha';
		$edit->fafecta->size =10;
		$edit->fafecta->maxlength =8;

		$edit->montasa = new inputField('Montasa','montasa');
		$edit->montasa->rule='max_length[17]|numeric';
		$edit->montasa->css_class='inputnum';
		$edit->montasa->size =19;
		$edit->montasa->maxlength =17;

		$edit->monredu = new inputField('Monredu','monredu');
		$edit->monredu->rule='max_length[17]|numeric';
		$edit->monredu->css_class='inputnum';
		$edit->monredu->size =19;
		$edit->monredu->maxlength =17;

		$edit->monadic = new inputField('Monadic','monadic');
		$edit->monadic->rule='max_length[17]|numeric';
		$edit->monadic->css_class='inputnum';
		$edit->monadic->size =19;
		$edit->monadic->maxlength =17;

		$edit->tasa = new inputField('Tasa','tasa');
		$edit->tasa->rule='max_length[17]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =19;
		$edit->tasa->maxlength =17;

		$edit->reducida = new inputField('Reducida','reducida');
		$edit->reducida->rule='max_length[17]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =19;
		$edit->reducida->maxlength =17;

		$edit->sobretasa = new inputField('Sobretasa','sobretasa');
		$edit->sobretasa->rule='max_length[17]|numeric';
		$edit->sobretasa->css_class='inputnum';
		$edit->sobretasa->size =19;
		$edit->sobretasa->maxlength =17;

		$edit->exento = new inputField('Exento','exento');
		$edit->exento->rule='max_length[17]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =19;
		$edit->exento->maxlength =17;

		$edit->sucu = new inputField('Sucu','sucu');
		$edit->sucu->rule='max_length[2]';
		$edit->sucu->size =4;
		$edit->sucu->maxlength =2;

		$edit->depto = new inputField('Depto','depto');
		$edit->depto->rule='max_length[3]';
		$edit->depto->size =5;
		$edit->depto->maxlength =3;

		$edit->modificado = new inputField('Modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		$edit->build();

		$script= '';

		$data['content'] = $edit->output;
		$data['script'] = $script;
		$this->load->view('jqgrid/ventanajq', $data);

	}

	function _pre_insert($do){
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}
}
*/

/*
	function dataedit(){
		$this->rapyd->load('dataedit');

		$edit = new DataEdit($this->tits, 'otin');

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');
		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->tipo_doc = new inputField('Tipo_doc','tipo_doc');
		$edit->tipo_doc->rule='max_length[2]';
		$edit->tipo_doc->size =4;
		$edit->tipo_doc->maxlength =2;

		$edit->numero = new inputField('Numero','numero');
		$edit->numero->rule='max_length[8]';
		$edit->numero->size =10;
		$edit->numero->maxlength =8;

		$edit->fecha = new dateField('Fecha','fecha');
		$edit->fecha->rule='chfecha';
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->orden = new inputField('Orden','orden');
		$edit->orden->rule='max_length[8]';
		$edit->orden->size =10;
		$edit->orden->maxlength =8;

		$edit->cod_cli = new inputField('Cod_cli','cod_cli');
		$edit->cod_cli->rule='max_length[5]';
		$edit->cod_cli->size =7;
		$edit->cod_cli->maxlength =5;

		$edit->rifci = new inputField('Rifci','rifci');
		$edit->rifci->rule='max_length[13]';
		$edit->rifci->size =15;
		$edit->rifci->maxlength =13;

		$edit->nombre = new inputField('Nombre','nombre');
		$edit->nombre->rule='max_length[40]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =40;

		$edit->direc = new inputField('Direc','direc');
		$edit->direc->rule='max_length[40]';
		$edit->direc->size =42;
		$edit->direc->maxlength =40;

		$edit->dire1 = new inputField('Dire1','dire1');
		$edit->dire1->rule='max_length[40]';
		$edit->dire1->size =42;
		$edit->dire1->maxlength =40;

		$edit->totals = new inputField('Totals','totals');
		$edit->totals->rule='max_length[14]|numeric';
		$edit->totals->css_class='inputnum';
		$edit->totals->size =16;
		$edit->totals->maxlength =14;

		$edit->iva = new inputField('Iva','iva');
		$edit->iva->rule='max_length[14]|numeric';
		$edit->iva->css_class='inputnum';
		$edit->iva->size =16;
		$edit->iva->maxlength =14;

		$edit->totalg = new inputField('Totalg','totalg');
		$edit->totalg->rule='max_length[14]|numeric';
		$edit->totalg->css_class='inputnum';
		$edit->totalg->size =16;
		$edit->totalg->maxlength =14;

		$edit->vence = new dateField('Vence','vence');
		$edit->vence->rule='chfecha';
		$edit->vence->size =10;
		$edit->vence->maxlength =8;

		$edit->observa1 = new inputField('Observa1','observa1');
		$edit->observa1->rule='max_length[30]';
		$edit->observa1->size =32;
		$edit->observa1->maxlength =30;

		$edit->observa2 = new inputField('Observa2','observa2');
		$edit->observa2->rule='max_length[30]';
		$edit->observa2->size =32;
		$edit->observa2->maxlength =30;

		$edit->transac = new inputField('Transac','transac');
		$edit->transac->rule='max_length[8]';
		$edit->transac->size =10;
		$edit->transac->maxlength =8;

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));

		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));

		$edit->nfiscal = new inputField('Nfiscal','nfiscal');
		$edit->nfiscal->rule='max_length[8]';
		$edit->nfiscal->size =10;
		$edit->nfiscal->maxlength =8;

		$edit->afecta = new inputField('Afecta','afecta');
		$edit->afecta->rule='max_length[8]';
		$edit->afecta->size =10;
		$edit->afecta->maxlength =8;

		$edit->fafecta = new dateField('Fafecta','fafecta');
		$edit->fafecta->rule='chfecha';
		$edit->fafecta->size =10;
		$edit->fafecta->maxlength =8;

		$edit->montasa = new inputField('Montasa','montasa');
		$edit->montasa->rule='max_length[17]|numeric';
		$edit->montasa->css_class='inputnum';
		$edit->montasa->size =19;
		$edit->montasa->maxlength =17;

		$edit->monredu = new inputField('Monredu','monredu');
		$edit->monredu->rule='max_length[17]|numeric';
		$edit->monredu->css_class='inputnum';
		$edit->monredu->size =19;
		$edit->monredu->maxlength =17;

		$edit->monadic = new inputField('Monadic','monadic');
		$edit->monadic->rule='max_length[17]|numeric';
		$edit->monadic->css_class='inputnum';
		$edit->monadic->size =19;
		$edit->monadic->maxlength =17;

		$edit->tasa = new inputField('Tasa','tasa');
		$edit->tasa->rule='max_length[17]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =19;
		$edit->tasa->maxlength =17;

		$edit->reducida = new inputField('Reducida','reducida');
		$edit->reducida->rule='max_length[17]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =19;
		$edit->reducida->maxlength =17;

		$edit->sobretasa = new inputField('Sobretasa','sobretasa');
		$edit->sobretasa->rule='max_length[17]|numeric';
		$edit->sobretasa->css_class='inputnum';
		$edit->sobretasa->size =19;
		$edit->sobretasa->maxlength =17;

		$edit->exento = new inputField('Exento','exento');
		$edit->exento->rule='max_length[17]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =19;
		$edit->exento->maxlength =17;

		$edit->sucu = new inputField('Sucu','sucu');
		$edit->sucu->rule='max_length[2]';
		$edit->sucu->size =4;
		$edit->sucu->maxlength =2;

		$edit->depto = new inputField('Depto','depto');
		$edit->depto->rule='max_length[3]';
		$edit->depto->size =5;
		$edit->depto->maxlength =3;

		$edit->modificado = new inputField('Modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		$edit->build();

		$script= '';

		$data['content'] = $edit->output;
		$data['script'] = $script;
		$this->load->view('jqgrid/ventanajq', $data);

	}

	function _pre_insert($do){
		return true;
	}

	function _pre_update($do){
		return true;
	}

	function _pre_delete($do){
		return true;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Creo $this->tits $primary ");
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Elimino $this->tits $primary ");
	}
*/



/*
class Otin extends Controller {
	//otrosingresos
	function otin()
	{
		parent::Controller(); 
		$this->load->library("rapyd");
    $this->datasis->modulo_id(122,1);      
	}
	function index() {		
		$this->rapyd->load("datagrid","datafilter");
		
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
		
		$filter = new DataFilter("Filtro de Otros Ingresos");
		$filter->db->select('fecha,numero,cod_cli,nombre,totals,totalg,iva,tipo_doc');
		$filter->db->from('otin');
		
    	$filter->fechad = new dateonlyField("Desde", "fechad",'d/m/Y');
		$filter->fechah = new dateonlyField("Hasta", "fechah",'d/m/Y');
		$filter->fechad->clause  =$filter->fechah->clause="where";
		$filter->fechad->db_name =$filter->fechah->db_name="fecha";
		$filter->fechad->insertValue = date("Y-m-d"); 
		$filter->fechah->insertValue = date("Y-m-d"); 
		$filter->fechah->size=$filter->fechad->size=10;
		$filter->fechad->operator=">="; 
		$filter->fechah->operator="<=";
    
		$filter->numero = new inputField("N&uacute;mero", "numero");
		$filter->numero->size = 30;

    	$filter->cliente = new inputField("Cliente", "cod_cli");
    	$filter->cliente->size = 30;
		$filter->cliente->append($boton);

		$filter->buttons("reset","search");
		$filter->build();
    
		$uri = anchor('ventas/otin/dataedit/show/<#tipo_doc#>/<#numero#>','<#numero#>');
    	$uri2 = anchor_popup('formatos/verhtml/OTINND/<#tipo_doc#>/<#numero#>',"Ver HTML",$atts);
		
		$grid = new DataGrid();
		$grid->order_by("fecha","desc");
		$grid->per_page = 15;  
		
		$grid->column_orderby("N&uacute;mero",$uri,'numero');
    	$grid->column_orderby("Fecha","<dbdate_to_human><#fecha#></dbdate_to_human>",'fecha',"align='center'");
    	$grid->column_orderby("Nombre","nombre",'nombre');
    	$grid->column_orderby("Sub.Total","<number_format><#totals#>|2</number_format>",'totals',"align=right");
    	$grid->column_orderby("IVA","<number_format><#iva#>|2</number_format>",'iva',"align=right");
    	$grid->column_orderby("Total","<number_format><#totalg#>|2</number_format>",'totalg',"align=right");
    	//$grid->column("Vista",$uri2,"align='center'");
    

		//$grid->add("ventas/agregaroi");
		$grid->build();
		
		//echo $grid->db->last_query();
		
		$data['content'] =$filter->output.$grid->output;
		$data["head"]    = $this->rapyd->get_head();
		$data['title']   ='<h1> Otros Ingresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
*/

	function dataedit(){
 		$this->rapyd->load("dataedit","datadetalle","fields","datagrid");
 		
 		$formato=$this->datasis->dameval('SELECT formato FROM cemp LIMIT 0,1');
 		$qformato='%';
 		for($i=1;$i<substr_count($formato, '.')+1;$i++) $qformato.='.%';
 		$this->qformato=$qformato;
 	 	
 	 	$modbusp=array(
	  'tabla'   =>'scli',
	  'columnas'=> array(
		'cliente' =>'C&oacute;digo Cliente',
		'nombre'  =>'Nombre',
		'dire11'  =>'Direcci&oacute;n',
		'rifci'   =>'Rif/CI'),
	  'filtro'  =>array('cliente'=>'C&oacute;digo Cliente','nombre'=>'Nombre'),
	  'retornar'=>array('cliente'=>'cod_cli'),
  	'titulo'  =>'Buscar Cliente');
 
    $boton=$this->datasis->modbus($modbusp);
 		//Script necesario para totalizar los detalles
 		
 		 $modbus=array(
		'tabla'   =>'sinv',
		'columnas'=>array(
		'codigo' =>'C&oacute;digo',
    'descrip'=>'descrip'),
		'filtro'  =>array('codigo' =>'C&oacute;digo','descrip'=>'descrip'),
		//'retornar'=>array('codigo'=>'codigo<#i#>','precio1'=>'precio1<#i#>','precio2'=>'precio2<#i#>','precio3'=>'precio3<#i#>','precio4'=>'precio4<#i#>','iva'=>'iva<#i#>','pond'=>'costo<#i#>'),
		'retornar'=>array('codigo'=>'codigo<#i#>'),
		'p_uri'=>array(4=>'<#i#>'),
		'titulo'  =>'Buscar Articulo');
  		
		$fdepar = new dropdownField("ccosto", "ccosto");    
		$fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$fdepar->status='create';
		$fdepar->build();
		$dpto=$fdepar->output;
		
		$dpto=trim($dpto);
		$dpto=preg_replace('/\n/i', '', $dpto);
 		
 		$uri=site_url("/contabilidad/casi/dpto/");

 		$script='
 		function totalizar(){
 			monto=debe=haber=0;
 			amonto=$$(\'input[id^="monto"]\');
			for(var i=0; i<amonto.length; i++) {
    		valor=parseFloat(amonto[i].value);
    		if (isNaN(valor))
					valor=0.0;
				if (valor>0)
    			haber=haber+valor;
    		else{
    			valor=valor*(-1);
    			debe=debe+valor;
    		}
				$("haber").value=haber;
    		$("debe").value=debe;
				$("total").value=haber-debe;
			}
		}

		function departa(i){
			ccosto=$F(\'ccosto\'+i.toString())
			if (ccosto==\'S\'){
				//alert("come una matina");
				departamen=window.open("'.$uri.'/"+i.toString(),"buscardeparta","width=500,height=200,scrollbars=Yes,status=Yes,resizable=Yes,screenx=5,screeny=5,top="+ ((screen.height - 200) / 2) + ",left=" + ((screen.width - 500) / 2)); 
				departamen.focus();
				//new Insertion.Before(\'departa\'+i.toString(), \''.$dpto.'\')
			}
		}
		';
 		
		$edit = new DataEdit(" otrosingresos","otin");
		
		$edit->post_process("insert","_guarda_detalle");
		$edit->post_process("update","_actualiza_detalle");
		$edit->post_process("delete","_borra_detalle");
		$edit->pre_process('insert','_pre_insert');
		
		$edit->back_url = "ventas/ otin";
		
		$edit->fecha = new DateonlyField("Fecha", "fecha","d/m/Y");
		$edit->fecha->insertValue = date("Y-m-d");
		$edit->fecha->mode="autohide";
		$edit->fecha->size = 10;
		
		$edit->vence = new DateonlyField("Vence", "vence","d/m/Y");
		$edit->vence->insertValue = date("Y-m-d");
		$edit->vence->size = 10;

		$edit->numero = new inputField("N&uacute;mero", "numero");
		$edit->numero->size = 10;
		$edit->numero->rule= "required";
		$edit->numero->mode="autohide";
		$edit->numero->maxlength=8;
		
    	$edit->nombre = new inputField("Nombre", "nombre");
		$edit->nombre->size = 55;
		$edit->nombre->maxlength=40;   
		
		$edit->iva  = new inputField("IVA", "iva");
		$edit->iva->size = 20;
		$edit->iva->css_class='inputnum';
		
		$edit->subtotal  = new inputField("Sub.Total", "totals");
		$edit->subtotal->size = 20;
		$edit->subtotal->css_class='inputnum';
		
		$edit->total  = new inputField("Total", "totalg");
		$edit->total->size = 20;
		$edit->total->css_class='inputnum';

		$edit->cliente = new inputField("Cliente"  , "cod_cli");
		$edit->cliente->size = 10;        
		$edit->cliente->maxlength=5;
		$edit->cliente->append($boton); 
		
		$edit->rifci   = new inputField("RIF/CI"  , "rifci");
		$edit->rifci->size = 20;        
		
		$edit->direc = new inputField("Direcci&oacute;n","direc");
		$edit->direc->size = 55;  
		
		$edit->dire1 = new inputField(" ","dire1");
		$edit->dire1->size = 55; 
		
		$edit->observaciones = new inputField("Observaciones"  , "observa1");
		$edit->observaciones->size = 40; 
		
		$edit->observaciones1 = new inputField("Observaciones"  , "observa2");
		$edit->observaciones1->size = 40;  	 		  
		
		$edit->orden  = new inputField("Orden","orden");
		$edit->orden->size = 20;
						
		$edit->tipo = new dropdownField("Tipo", "tipo_doc");  
		$edit->tipo->option("FC","FC");  
		$edit->tipo->option("ND","ND");
		$edit->tipo->option("OT","OT");
		$edit->tipo->size = 20;  
	  $edit->tipo->style='width:70px;';
		
		$numero=$edit->_dataobject->get('numero');
		$tipo  =$edit->_dataobject->get('tipo_doc');
				
		$detalle = new DataDetalle($edit->_status);
		
		//Campos para el detalle
		
		$detalle->db->select('codigo,descrip,precio,impuesto,importe');
		$detalle->db->from('itotin');
		$detalle->db->where("numero='$numero'");
		$detalle->db->where("tipo_doc='$tipo'");
		
		$detalle->codigo = new inputField("C&oacute;digo", "codigo<#i#>");
		$detalle->codigo->size=18;
		$detalle->codigo->db_name='codigo';
		$detalle->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$detalle->codigo->readonly=TRUE;
		
		$detalle->descripcion = new inputField("Descripci&oacute;n", "descrip<#i#>");
		$detalle->descripcion->size=30;
		$detalle->descripcion->db_name='descrip';
		$detalle->descripcion->maxlength=12;
			
		$detalle->precio = new inputField("Precio", "precio<#i#>");
		$detalle->precio->css_class='inputnum';
		$detalle->precio->size=20;
		$detalle->precio->db_name='precio';
		
		$detalle->impuesto = new inputField("Impuesto", "impuesto<#i#>");
		$detalle->impuesto->css_class='inputnum';
		$detalle->impuesto->size=20;
		$detalle->impuesto->db_name='impuesto';
		
		$detalle->total = new inputField2("Total", "importe<#i#>");
		$detalle->total->db_name='importe';
		$detalle->total->size=20;
		$detalle->total->css_class='inputnum';
		
		//fin de campos para detalle
		
		$detalle->onDelete('totalizar()');
		$detalle->onAdd('totalizar()');
		$detalle->script($script);
		$detalle->style="width:110px";
		
		//Columnas del detalle
		$detalle->column("C&oacute;digo"      , "<#codigo#>");
		$detalle->column("Descripci&oacute;n" , "<#descripcion#>");
		$detalle->column("Precio"      , "<#precio#>");
		$detalle->column("Impuesto"    , "<#impuesto#>");
		$detalle->column("Total"       , "<#total#>");
	
		$detalle->build();	
		$conten["detalle"] = $detalle->output;
		
		$edit->detalle=new freeField("detalle", 'detalle',$detalle->output);

		$edit->buttons( "save", "undo","back");
		$edit->build();
		
		$smenu['link']=barra_menu('122');
		$data['smenu'] = $this->load->view('view_sub_menu', $smenu,true);
		$conten["form"]  =&  $edit;
		$data['content'] = $this->load->view('view_otrosingresos', $conten,true); 
		$data["head"]    = script("tabber.js").script("prototype.js").$this->rapyd->get_head().script("scriptaculous.js").script("effects.js");
		$data['title']   = '<h1>Otros Ingresos</h1>';
		$this->load->view('view_ventanas', $data);
	}
		function dpto() {		
		$this->rapyd->load("dataform");
		$campo='ccosto'.$this->uri->segment(4);
 		$script='
 		function pasar(){
			if($F("departa")!="-!-"){
				window.opener.document.getElementById("'.$campo.'").value = $F("departa");
				window.close();
			}else{
				alert("Debe elegir un departamento");
			}
		}';
		
		$form = new DataForm('');
		$form->script($script);
		
		$form->fdepar = new dropdownField("Departamento", "departa");
		$form->fdepar->option('-!-','Seleccion un departamento');
		$form->fdepar->options("SELECT depto,descrip FROM dpto WHERE tipo='G' ORDER BY descrip");
		$form->fdepar->onchange='pasar()';
		
		$form->build_form();
		
		$data['content'] =$form->output;
		$data["head"]    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   ='<h1>Seleccione un departamento</h1>';
		$this->load->view('view_detalle', $data);
	}
  function _actualiza_detalle($do){
		$this->_borra_detalle($do);
		$this->_guarda_detalle($do);
	 }
  function _guarda_detalle($do) {
		$cant=$this->input->post('cant_0');
		$i=$o=0;
		while($o<$cant){
			if (isset($_POST["codigo$i"])){
				if($this->input->post("codigo$i")){
						
					$sql = "INSERT INTO itspre (tipo_doc,numero,codigo,descrip,precio,impuesto,importe,transac,usuario,estampa,hora,cantidad,larga)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
					//$haber=($this->input->post("monto$i") < 0)? $this->input->post("monto$i")*(-1) : 0;
					
					$llena=array(
							0=> $do->get('tipo_doc'),        
							1=> $do->get('numero'), 
							2=> $this->input->post("codigo$i"),
							3=> $this->input->post("descrip$i"),
							4=> $this->input->post("precio$i"),
							5=> $this->input->post("impuest$i"),
							6=> $this->input->post("importe$i"),
							7=> $do->get('transac'), 
							8=> $do->get('usuario'), 
							9=> $do->get('estampa'), 
						 10=> $do->get('hora'), 
						 11=> $this->input->post("cantidad$i"),
						 12=> $this->input->post("larga$i"),
					
							);
					$this->db->query($sql,$llena);
				}
				$o++;
			}
			$i++;
		}
	  function _borra_detalle($do){
		$numero=$do->get('numero');
		$sql = "DELETE FROM itotin WHERE numero='$numero'";
		$this->db->query($sql);
	}
	  function _pre_insert($do){
		$sql    = 'INSERT INTO ntransa (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db->query($sql);
      
		$sql    = 'INSERT INTO notind (usuario,fecha) VALUES ("'.$this->session->userdata('usuario').'",NOW())';
    $query  =$this->db-> query($sql);
    $numero =str_pad($this->db->insert_id(),8, "0", STR_PAD_LEFT);
    
    $do->set('numero', $numero);
		$do->set('transac', $transac);
		$do->set('estampa', 'CURDATE()', FALSE);
		$do->set('hora'   , 'CURRENT_TIME()', FALSE);
		$do->set('usuario', $this->session->userdata('usuario'));
	}
}
}
?>

