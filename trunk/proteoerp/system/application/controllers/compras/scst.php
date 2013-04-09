<?php
class Scst extends Controller {
	var $mModulo = 'SCST';
	var $titp    = 'Compras de Productos';
	var $tits    = 'Compras de Productos';
	var $url     = 'compras/scst/';
	var $genesal = true;
	var $solo    = false;

	function Scst(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->load->library('jqdatagrid');
		$this->back_dataedit='compras/scst/datafilter';
		$this->datasis->modulo_nombre( 'SCST', $ventana=0 );
	}

	function index(){
		$this->instalar();
		$this->datasis->modintramenu( 900, 650, substr($this->url,0,-1) );
		// Crea los accesos en tmenus
		if ($this->datasis->dameval("SELECT COUNT(*) FROM tmenus WHERE modulo='SCSTOTR' AND proteo='actualizar'") == 0) {
			$this->db->query("INSERT INTO tmenus SET modulo='SCSTOTR',secu=1,titulo='Actualizar',proteo='actualizar' ");
		}
		if ($this->datasis->dameval("SELECT COUNT(*) FROM tmenus WHERE modulo='SCSTOTR' AND proteo='reversar'") == 0) {
			$this->db->query("INSERT INTO tmenus SET modulo='SCSTOTR',secu=2,titulo='Reversar',proteo='reversar' ");
		}

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

		$readyLayout = $grid->readyLayout2( 212, 200, $param['grids'][0]['gridname'],$param['grids'][1]['gridname']);
		$bodyscript  = $this->bodyscript( $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		$WpAdic = "
		<tr><td>\n
			<table cellpadding='0' cellspacing='0' style='width:100%;'>
				<tr>
					<td style='vertical-align:center;border:1px solid #AFAFAF;'><div class='tema1 botones'>".img(array('src' =>"assets/default/images/print.png",  'height' => 18, 'alt' => 'Imprimir',  'title' => 'Imprimir', 'border'=>'0'))."</div></td>
					<td style='vertical-align:top;text-align:center;'><div class='tema1 botones'><a style='width:70px;text-align:left;vertical-align:top;' border='0' href='#' id='imprimir'>Compra</a></div></td>
					<td style='vertical-align:top;text-align:center;'><div class='tema1 botones'><a class='tema1 botones' style='width:80px;text-align:left;vertical-align:top;' href='#' id='reteprin'>Retencion</a></div></td>
				</tr>
			</table>
		</td></tr>\n
		";

		$grid->setWpAdicional($WpAdic);


		//Botones Panel Izq
		$grid->wbotonadd(array('id'=>'cprecios','img'=>'images/precio.png'   ,'alt' => 'Ajustar Precios'    ,'label'=>'Cambiar Precios'));
		$grid->wbotonadd(array('id'=>'reversar','img'=>'images/arrow_up.png' ,'alt' => 'Actualizar/Reversar','label'=>'Actualizar Reversar'));
		$grid->wbotonadd(array('id'=>'bcmonto' ,'img'=>'images/arrow_up.png' ,'alt' => 'Modificar la CxP'   ,'label'=>'Modificar la CxP'));
		if ( $this->datasis->traevalor('MOTOS') == 'S' )
			$grid->wbotonadd(array('id'=>'vehiculo', 'img'=>'images/carro.png',  'alt' => 'Seriales Vehiculares',   'label'=>'Seriales Vehiculares'));

		$WestPanel = $grid->deploywestp();

		//Panel Central y Sur
		$centerpanel = $grid->centerpanel( $id = "radicional", $param['grids'][0]['gridname'], $param['grids'][1]['gridname'] );

		//Panel de pie de forma
		$adic = array(
			array('id'=>'fcompra' , 'title'=>'Modificar Compra'),
			array('id'=>'factuali', 'title'=>'Actualizar'),
			array('id'=>'fvehi'   , 'title'=>'Seriales Vehiculares'),
			array('id'=>'fcmonto' , 'title'=>'Cambiar los montos que van a CxP'),
		);
		$SouthPanel = $grid->SouthPanel($this->datasis->traevalor('TITULO1'), $adic);


		$param['WestPanel']    = $WestPanel;
		//$param['EastPanel']  = $EastPanel;
		$param['readyLayout']  = $readyLayout;
		$param['SouthPanel']   = $SouthPanel;
		$param['listados']     = $this->datasis->listados('SCST', 'JQ');
		$param['otros']        = $this->datasis->otros('SCST', 'JQ');

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
	function bodyscript( $grid0, $grid1 ){
		$bodyscript = '<script type="text/javascript">';

		// Imprime Compra
		$bodyscript .= '
		jQuery("#imprimir").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+id+"/id"').';
			} else { $.prompt("<h1>Por favor Seleccione un Movimiento</h1>");}
		});';

		//Imprimir retencion
		$bodyscript .= '
		jQuery("#reteprin").click( function(){
			var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				if ( ret.actuali >= ret.fecha ) {
					'.$this->datasis->jwinopen(site_url($this->url.'printrete').'/\'+id+"/id"').';
				}else{
					$.prompt("<h1>Debe actualizar la compra para imprimir la retenci&oacute;n.</h1>");
				}
			} else {
				$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
			}
		});';


		$bodyscript .= '
		jQuery("#serie").click( function(){
			var gr = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if( gr != null ){
				jQuery("#newapi'.$grid0.'").jqGrid(\'editGridRow\',gr,
				{
					closeAfterEdit:true,
					mtype: "POST",
					height:200,
					width: 350,
					closeOnEscape: true,
					top: 50,
					left:20,
					recreateForm:true,
					afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];},
					reloadAfterSubmit:false
				});
			}else{
				$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
			}
		});';

		$bodyscript .= '
		function scstadd(){
			$.post("'.site_url('compras/scst/solo/create').'",
			function(data){
				$("#factuali").html("");
				$("#fvehi").html("");
				$("#fcompra").html(data);
				$("#fcompra").dialog( "open" );
			});
		};';

		$bodyscript .= '
		jQuery("#vehiculo").click(function(){
			var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id)	{
				var rt= $.ajax({ type: "POST", url: "'.site_url($this->url.'getvehicular').'/"+id, async: false }).responseText;
				if(rt=="1"){
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url('compras/scst/dataeditvehiculo/modify').'/"+id, function(data){
						$("#factuali").html("");
						$("#fcompra").html("");
						$("#fvehi").html(data);
						$("#fvehi").dialog("open");
					});
				}else{
					$.prompt("<h1>La compra seleccionada no posee veh&iacute;culos</h1>");
				}
			}else{
				$.prompt("<h1>Por favor Seleccione una compra</h1>");
			}
		});';

		$bodyscript .= '
		jQuery("#bcmonto").click(function(){
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				if ( ret.actuali >= ret.fecha ) {
					$.prompt("<h1>Compra ya Actualizada</h1>Debe reversarla si desea hacer modificaciones");
				}else{
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					mId = id;
					$.post("'.site_url('compras/scst/montoscxp/modify').'/"+id, function(data){
						$("#factuali").html("");
						$("#fcompra").html("");
						$("#fcmonto").html(data);
						$("#fcmonto").dialog("open");
					});
				}
			}else{
				$.prompt("<h1>Por favor Seleccione una compra</h1>");
			}
		});';

		$bodyscript .= '
		function scstedit() {
			var id = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
			if (id){
				var ret    = $("#newapi'.$grid0.'").getRowData(id);
				if ( ret.actuali >= ret.fecha ) {
					$.prompt("<h1>Compra ya Actualizada</h1>Debe reversarla si desea hacer modificaciones");
				} else {
					mId = id;
					$.post("'.site_url('compras/scst/solo/modify').'/"+id, function(data){
						$("#factuali").html("");
						$("#fvehi").html("");
						$("#fcompra").html(data);
						$("#fcompra" ).dialog( "open" );
					});
				}
			} else {
				$.prompt("<h1>Por favor Seleccione una compra</h1>");
			}
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

		//Actualizar y Reversar
		$bodyscript .= '
			$("#reversar").click( function(){
				var id = jQuery("#newapi'. $grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret = jQuery("#newapi'.$grid0.'").jqGrid(\'getRowData\',id);
					if(ret.tipo_doc == "XX"){
						$.prompt( "<h1>Documento Eliminado.</h1>");
					} else {
					mid = id;
					if(ret.actuali<ret.fecha){'."\n";

		if($this->datasis->sidapuede('SCSTOTR','actualizar')){

			//Revisa si puede Actualizar
			$bodyscript .= '
						$.post("'.site_url('compras/scst/actualizar').'/"+ret.control,
						function(data){
							$("#fcompra").html("");
							$("#factuali").html(data);
							$("#factuali").dialog("open");
						})
					';
		} else {
			$bodyscript .= '
						$.prompt( "<h1>Opci&oacute;n no Autorizada, comuniquese con el supervisor.</h1>");
					';
		}

		$bodyscript .= '
					} else {
					';

		if ( $this->datasis->sidapuede('SCSTOTR', 'reversar' ) ) {

		//Revisa si puede Reversar
		$bodyscript .= '
						$.prompt( "<h1>Reversar Compra Nro. "+ret.control+" ?</h1>", {
							buttons: { Reversar: true, Cancelar: false },
							submit: function(e,v,m,f){
								if(v){
									$.get("'.site_url('compras/scst/reversar').'/"+ret.control,
									function(r){
										try{
											var json = JSON.parse(r);
											if (json.status == "A"){
												apprise("Compra reversada");
												grid.trigger("reloadGrid");
												return true;
											}else{
												apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
											}
										}catch(e){
											apprise("Error en respuesta");
										}
									});
								}
							}
						});
					';
		} else {
		$bodyscript .= '
						$.prompt( "<h1>Opci&oacute;n no Autorizada, comuniquese con el supervisor.</h1>");
					';
		}

		$bodyscript .= '
					}}
				}else{
					$.prompt("<h1>Por favor Seleccione un Movimiento</h1>");
				}
			});';

		$bodyscript .= '
			$("#factuali").dialog({
				autoOpen: false, height: 300, width: 450, modal: true,
				buttons: {
					"Actualizar": function() {
						var bValid = true;
						var murl = $("#df1").attr("action");
						allFields.removeClass( "ui-state-error" );
						if ( bValid ) {
							$.ajax({
								type: "POST", dataType: "html", async: false,
								url: murl,
								data: $("#df1").serialize(),
								success: function(r,s,x){

									try{
										var json = JSON.parse(r);
										if (json.status == "A"){
											apprise("Registro Guardado");
											$( "#factuali" ).dialog("close");
											grid.trigger("reloadGrid");
											'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+mid+"/id"').';
											return true;
										}else{
											apprise("<div style=\"font-size:16px;font-weight:bold;background:red;color:white\">Error:</div> <h1>"+res.mensaje+"</h1>");
										}
									}catch(e){
										$("#factuali").html(r);
									}
								}
							});
						}
					},
					Cancelar: function() {
						$( this ).dialog( "close" );
						$( "#factuali" ).html("");
					}
				},
				close: function() {
					allFields.val( "" ).removeClass( "ui-state-error" );
					$( "#factuali" ).html("");
				}
			});';

		//Cambiar Precios
		$bodyscript .= '
			$("#cprecios").click(function() {
				var id     = jQuery("#newapi'.$grid0.'").jqGrid(\'getGridParam\',\'selrow\');
				if (id)	{
					var ret    = $("#newapi'.$grid0.'").getRowData(id);
					if ( ret.actuali >= ret.fecha ) {
						$.prompt("<h1>Compra ya Actualizada</h1>Debe reversarla si desea hacer modificaciones");
					} else {
						mId = id;
						$.post("'.site_url('compras/scst/solo/cprecios').'/"+ret.control, function(data){
							$("#factuali").html("");
							$("#fcompra").html(data);
							$( "#fcompra" ).dialog( "open" );
						});
					}
				} else { $.prompt("<h1>Por favor Seleccione una compra no actualizada</h1>");}
			});
		';


		$bodyscript .= '
			$("#fcompra").dialog({
				autoOpen: false, height: 570, width: 860, modal: true,
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
										$( "#fcompra" ).dialog( "close" );
										grid.trigger("reloadGrid");
										'.$this->datasis->jwinopen(site_url('formatos/ver/COMPRA').'/\'+res.id+\'/id\'').';
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
					Cancelar: function() {
						$( this ).dialog( "close" );
						$( "#fcompra" ).html("");
					}
				},
				close: function() {
					allFields.val( "" ).removeClass( "ui-state-error" );
					$( "#fcompra" ).html("");
				}
			});';

		$bodyscript .= '
			$( "#fcmonto" ).dialog({
				autoOpen: false, height: 300, width: 300, modal: true,
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
									try{
										var json = JSON.parse(r);
										if ( json.status == "A" ) {
											$( "#fcmonto" ).dialog( "close" );
											jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
											$("#fcmonto").html("");
											apprise("Montos Guardado");
											return true;
										} else {
											apprise(json.mensaje);
										}
									}catch(e){
										$("#fcmonto").html(r);
									}
								}
							});
						}
					},
					Cancelar: function() {
						$( this ).dialog( "close" );
						$( "#fcmonto" ).html("");
					}
				},
				close: function() {
					allFields.val( "" ).removeClass( "ui-state-error" );
					$( "#fcmonto" ).html("");
				}
			});';

		$bodyscript .= '
			$( "#fvehi" ).dialog({
				autoOpen: false, height: 570, width: 860, modal: true,
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
									try{
										var json = JSON.parse(r);
										if ( json.status == "A" ) {
											$( "#fvehi" ).dialog( "close" );
											jQuery("#newapi'.$grid0.'").trigger("reloadGrid");
											$("#fvehi").html("");
											apprise("Seriales Guardado");
											return true;
										} else {
											apprise(json.mensaje);
										}
									}catch(e){
										$("#fvehi").html(r);
									}
								}
							});
						}
					},
					Cancelar: function() {
						$( this ).dialog( "close" );
						$( "#fvehi" ).html("");
					}
				},
				close: function() {
					allFields.val( "" ).removeClass( "ui-state-error" );
					$( "#fvehi" ).html("");

				}
			});
		});';

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


		$grid->addField('tipo_doc');
		$grid->label('Tipo');
		$grid->params(array(
			'align'         => "'center'",
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 2 }',
		));

		$grid->addField('numero');
		$grid->label('N&uacute;mero');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('serie');
		$grid->label('Serie');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:15, maxlength: 20 }',
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

		$grid->addField('recep');
		$grid->label('Recepci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
		));

		$grid->addField('actuali');
		$grid->label('Actualizada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Fecha" }'
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


		$grid->addField('orden');
		$grid->label('Orden');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('ncont');
		$grid->label('N.Cont');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('montotot');
		$grid->label('Base');
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

		$grid->addField('vence');
		$grid->label('Vence');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 80,
			'align'         => "'center'",
			'edittype'      => "'text'",
			'editrules'     => '{ required:true,date:true}',
			'formoptions'   => '{ label:"Vencimiento" }'
		));


		$grid->addField('nfiscal');
		$grid->label('N.Fiscal');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 120,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:10, maxlength: 12 }',
		));

/*
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
*/

		$grid->addField('control');
		$grid->label('Control');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

/*
		$grid->addField('flete');
		$grid->label('Flete');
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


		$grid->addField('otros');
		$grid->label('Otros');
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


		$grid->addField('reten');
		$grid->label('Reten');
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


		$grid->addField('ppago');
		$grid->label('Ppago');
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


		$grid->addField('peaje');
		$grid->label('Peaje');
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


		$grid->addField('moriginal');
		$grid->label('Moriginal');
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


		$grid->addField('msubtotal');
		$grid->label('Msubtotal');
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


		$grid->addField('transac');
		$grid->label('Transaci&oacute;n');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('estampa');
		$grid->label('Estampa');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 70,
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
			'width'         => 60,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));


		$grid->addField('usuario');
		$grid->label('Usuario');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 100,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 12 }',
		));

		$grid->addField('exento');
		$grid->label('Exento');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('sobretasa');
		$grid->label('Iva A.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('reducida');
		$grid->label('Iva R.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('tasa');
		$grid->label('Iva G.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 90,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));
/*
		$grid->addField('preauto');
		$grid->label('Preauto');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 40,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 1 }',
		));
*/
		$grid->addField('reteiva');
		$grid->label('Retenci&oacute;n IVA');
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

		$grid->addField('fafecta');
		$grid->label('F. Afectada');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:false}',
			'editoptions'   => '{ label:"Factura Afectada", size:10, maxlength: 8 }',
		));

/*
		$grid->addField('cexento');
		$grid->label('Cexento');
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

		$grid->addField('cgenera');
		$grid->label('Cgenera');
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

		$grid->addField('civagen');
		$grid->label('Civagen');
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

		$grid->addField('creduci');
		$grid->label('Creduci');
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

		$grid->addField('civared');
		$grid->label('Civared');
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

		$grid->addField('cadicio');
		$grid->label('Cadicio');
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

		$grid->addField('civaadi');
		$grid->label('Civaadi');
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

		$grid->addField('cstotal');
		$grid->label('Cstotal');
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

		$grid->addField('ctotal');
		$grid->label('Ctotal');
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

		$grid->addField('cimpuesto');
		$grid->label('Cimpuesto');
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


		$grid->addField('notae');
		$grid->label('Nota E.');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
		));

/*
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
*/

		$grid->addField('consigna');
		$grid->label('Consigna');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'width'         => 80,
			'edittype'      => "'text'",
			'editrules'     => '{ required:true}',
			'editoptions'   => '{ size:30, maxlength: 8 }',
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
		$grid->setHeight('210');
		$grid->setTitle($this->titp);
		$grid->setfilterToolbar(true);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){
			if (id){
				var ret = $(gridId1).getRowData(id);
				jQuery(gridId2).jqGrid(\'setGridParam\',{url:"'.site_url($this->url.'getdatait/').'/"+id+"/", page:1});
				jQuery(gridId2).trigger("reloadGrid");
				$.ajax({
					url: "'.base_url().$this->url.'tabla/"+id,
					success: function(msg){
						$("#ladicional").html(msg);
					}
				});
			}},
			afterInsertRow:
			function( rid, aData, rowe){
				if ( aData.fecha >  aData.actuali ){
					$(this).jqGrid( "setCell", rid, "tipo_doc","", {color:"#FFFFFF", background:"#166D05" });
				}
				if ( aData.tipo_doc == "XX" ){
					$(this).jqGrid( "setCell", rid, "tipo_doc","", {color:"#FFFFFF", background:"#C90623" });
				}
			}
		');

//				} else {
//					$(this).jqGrid( "setCell", rid, "tipo_doc", "", {color:"#FFFFFF", background:"#06276B" });



//		$grid->setOndblClickRow("");

		$grid->setFormOptionsE('
				       closeAfterEdit:true,
				       mtype: "POST",
				       width: 350,
				       height:200,
				       closeOnEscape: true,
				       top: 50,
				       left:20,
				       recreateForm:true,
				       afterSubmit: function(a,b){if (a.responseText.length > 0) $.prompt(a.responseText); return [true, a ];}
		');

		$grid->setFormOptionsA('-');
		$grid->setAfterSubmit("$.prompt('Respuesta:'+a.responseText); return [true, a];");

		#show/hide navigations buttons
		$grid->setAdd(    $this->datasis->sidapuede('SCST','1' ));
		$grid->setEdit(   $this->datasis->sidapuede('SCST','2'));
		$grid->setDelete( $this->datasis->sidapuede('SCST','5'));
		$grid->setSearch( $this->datasis->sidapuede('SCST','6'));

		$grid->setRowNum(30);
		$grid->setShrinkToFit('false');

		$grid->setBarOptions("\t\taddfunc: scstadd,\n\t\teditfunc: scstedit");

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
		$grid     = $this->jqdatagrid;
		// CREA EL WHERE PARA LA BUSQUEDA EN EL ENCABEZADO
		$mWHERE   = $grid->geneTopWhere('scst');
		$response = $grid->getData('scst', array(array()), array(), false, $mWHERE, '(tipo_doc="XX"),  (fecha>actuali) desc, fecha', 'desc');
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Guarda la Informacion
	*/
	function setData(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			//if(false == empty($data)){
			//	$this->db->insert('scst', $data);
			//	echo "Registro Agregado";
			//	logusu('SCST',"Registro ????? INCLUIDO");
			//} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('scst', $data);
			logusu('SCST',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//Si no esta actualizado permite borrar
			$check   = $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE id='$id' AND actuali>=fecha ");
			if ($check > 0){
				echo " El registro no puede ser eliminado; debe reversarlo ";
			} else {
				$control =  $this->datasis->dameval("SELECT control FROM scst WHERE id='$id' ");
				$this->db->query("UPDATE scst   SET tipo_doc='XX' WHERE id=$id ");

				//$this->db->query("UPDATE itscst SET WHERE control=".$this->db->escape($control) );
				//$this->db->query("DELETE FROM scst WHERE id=$id ");
				//$this->db->query("DELETE FROM itscst WHERE control=".$this->db->escape($control) );

				logusu('SCST',"Registro ".$control." marcado como ELIMINADO");
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
		$grid->label('Descripci&oacute;n');
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
			'width'         => 60,
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
			'width'         => 80,
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
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('devcant');
		$grid->label('Faltante');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => 'true',
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 60,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('iva');
		$grid->label('IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 40,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

		$grid->addField('montoiva');
		$grid->label('Monto IVA');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
		));

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

		$grid->addField('ultimo');
		$grid->label('Ultimo');
		$grid->params(array(
			'search'        => 'true',
			'editable'      => $editar,
			'align'         => "'right'",
			'edittype'      => "'text'",
			'width'         => 80,
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
			'width'         => 80,
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
			'width'         => 80,
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
			'width'         => 80,
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
			'width'         => 80,
			'editrules'     => '{ required:true }',
			'editoptions'   => '{ size:10, maxlength: 10, dataInit: function (elem) { $(elem).numeric(); }  }',
			'formatter'     => "'number'",
			'formatoptions' => '{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2 }'
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
		$grid->setHeight('170');
		//$grid->setTitle($this->titp);
		$grid->setfilterToolbar(false);
		$grid->setToolbar('false', '"top"');

		$grid->setOnSelectRow('
			function(id){if (id){var ret = $("#titulos").getRowData(id);}},
				cellEdit: true,
				cellsubmit: "remote",
				cellurl: "'.site_url($this->url.'setdatait/').'"
		');
		$grid->setOndblClickRow("");

		$grid->setFormOptionsE('');
		$grid->setFormOptionsA('');
		$grid->setAfterSubmit('');

		#show/hide navigations buttons
		$grid->setAdd(true);
		$grid->setEdit(true);
		$grid->setDelete(true);
		$grid->setSearch(true);
		$grid->setRowNum(300);
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
	function getdatait(){
		$id = $this->uri->segment(4);
		if ($id == false ){
			$id = $this->datasis->dameval("SELECT MAX(id) FROM scst");
		}
		if(empty($id)) return '';
		$control = $this->datasis->dameval("SELECT control FROM scst WHERE id=$id");
		$grid    = $this->jqdatagrid;
		$mSQL    = "SELECT * FROM itscst WHERE control='$control' ";
		$response   = $grid->getDataSimple($mSQL);
		$rs = $grid->jsonresult( $response);
		echo $rs;
	}

	/**
	* Busca la data en el Servidor por json
	*/
	function setdatait(){
		$this->load->library('jqdatagrid');
		$oper   = $this->input->post('oper');
		$id     = $this->input->post('id');
		$data   = $_POST;
		$check  = 0;

		unset($data['oper']);
		unset($data['id']);
		if($oper == 'add'){
			//if(false == empty($data)){
			//	$this->db->insert('scst', $data);
			//	echo "Registro Agregado";
			//	logusu('SCST',"Registro ????? INCLUIDO");
			//} else
			echo "Fallo Agregado!!!";

		} elseif($oper == 'edit') {
			$this->db->where('id', $id);
			$this->db->update('itscst', $data);
			logusu('SCST',"Registro $id MODIFICADO");
			echo "Registro Modificado";

		} elseif($oper == 'del') {
			//$check =  $this->datasis->dameval("SELECT COUNT(*) FROM scst WHERE id='$id' ");
			//if ($check > 0){
				echo " El registro no puede ser eliminado; tiene movimiento ";
			//} else {
			//	$this->db->simple_query("DELETE FROM scst WHERE id=$id ");
			//	logusu('SCST',"Registro ????? ELIMINADO");
			//	echo "Registro Eliminado";
			//}
		};

	}

	//*************************************************
	//
	//  Informacion Adicional
	//
	//*************************************************
	function tabla() {
		$id = $this->uri->segment($this->uri->total_segments());
		$proveed = $this->datasis->dameval("SELECT proveed FROM scst WHERE id='$id'");
		$transac = $this->datasis->dameval("SELECT transac FROM scst WHERE id='$id'");
		$salida = '';

		// Cuentas por Cobrar
		$mSQL = "SELECT cod_prv, MID(nombre,1,25) nombre, tipo_doc, numero, monto, abonos FROM sprm WHERE cod_prv = '$proveed' AND abonos <> monto AND tipo_doc <> 'AB' ORDER BY fecha DESC LIMIT 5";
		$query = $this->db->query($mSQL);
		$saldo = 0;
		if ( $query->num_rows() > 0 ){
			$salida .= "<br><table width='100%' border=1>";
			$salida .= "<tr bgcolor='#e7e3e7'><td colspan=3>Movimiento Pendientes en CxC</td></tr>";
			$salida .= "<tr bgcolor='#e7e3e7'><td>Tp</td><td align='center'>Numero</td><td align='center'>Monto</td></tr>";
			$i = 1;
			foreach ($query->result_array() as $row)
			{
				if ( $i < 6 ) {
					$salida .= "<tr>";
					$salida .= "<td>".$row['tipo_doc']."</td>";
					$salida .= "<td>".$row['numero'].  "</td>";
					$salida .= "<td align='right'>".nformat($row['monto']-$row['abonos']).   "</td>";
					$salida .= "</tr>";
				}
				if ( $i == 6 ) {
					$salida .= "<tr>";
					$salida .= "<td colspan=3>Mas......</td>";
					$salida .= "</tr>";
				}
				if ( $row['tipo_doc'] == 'FC' or $row['tipo_doc'] == 'ND' or $row['tipo_doc'] == 'GI' )
					$saldo += $row['monto']-$row['abonos'];
				else
					$saldo -= $row['monto']-$row['abonos'];
				$i ++;
			}
			$salida .= "<tr bgcolor='#d7c3c7'><td colspan='4' align='center'>Saldo : ".nformat($saldo). "</td></tr>";
			$salida .= "</table>";
		}
		$query->free_result();
		echo $salida;
	}

	//***********************************
	//
	// DataEdit Principal
	//
	function dataedit(){
		$this->rapyd->load('dataobject','datadetails');
		$this->rapyd->uri->keep_persistence();

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

		$do = new DataObject('scst');
		$do->rel_one_to_many('itscst', 'itscst', 'control');
		$do->pointer('sprv' ,'sprv.proveed=scst.proveed','sprv.nombre AS sprvnombre,sprv.reteiva AS sprvreteiva','left');
		$do->rel_pointer('itscst','sinv','itscst.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Compras',$do);
		$edit->set_rel_title('itscst','Producto <#o#>');

		$edit->pre_process('insert' ,'_pre_insert');
		$edit->pre_process('update' ,'_pre_update');
		$edit->pre_process('delete' ,'_pre_delete');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->back_url = $this->back_dataedit;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule ='required';
		$edit->fecha->calendar=false;
		$transac=$edit->get_from_dataobjetct('transac');

		$edit->vence = new DateonlyField('Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->size = 10;
		$edit->vence->rule ='required';
		$edit->vence->calendar=false;

		$edit->actuali = new DateonlyField('Actualizado', 'actuali','d/m/Y');
		//$edit->actuali->insertValue = date('Y-m-d');
		$edit->actuali->when=array('show');
		$edit->actuali->size = 10;
		$edit->actuali->mode ='autohide';
		$edit->actuali->calendar=false;


		$edit->recep = new DateonlyField('recibido', 'v','d/m/Y');
		//$edit->recep->insertValue = date('Y-m-d');
		$edit->recep->size = 10;
		$edit->recep->mode = 'autohide';
		$edit->recep->when=array('show');
		$edit->recep->calendar=false;


		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 15;
		$edit->serie->autocomplete=false;
		$edit->serie->rule = 'required';
		$edit->serie->mode = 'autohide';
		$edit->serie->maxlength=12;

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 7;
		$edit->proveed->maxlength= 5;
		$edit->proveed->autocomplete=false;
		$edit->proveed->rule     = 'required';
		$edit->proveed->append($this->datasis->modbus($sprvbus));

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->sprvreteiva = new hiddenField('', 'sprvreteiva');
		$edit->sprvreteiva->pointer=true;

		$edit->cfis = new inputField('Numero fiscal', 'nfiscal');
		$edit->cfis->size = 15;
		$edit->cfis->autocomplete=false;
		$edit->cfis->rule = 'required';
		$edit->cfis->maxlength=12;

		$edit->almacen = new  dropdownField ('Almacen', 'depo');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->style='width:145px;';

		$edit->tipo = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo->option('FC','Factura a Cr&eacute;dito');
		$edit->tipo->option('NC','Nota de Cr&eacute;dito'); //Falta implementar los metodos post para este caso
		//$edit->tipo->option('NE','Nota de Entrega');        //Falta implementar los metodos post para este caso
		$edit->tipo->rule = 'required';
		$edit->tipo->style='width:140px;';

		$edit->peso  = new hiddenField('Peso', 'peso');
		$edit->peso->size = 20;
		$edit->peso->css_class='inputnum';

		$edit->orden  = new inputField('Orden', 'orden');
		$edit->orden->when=array('show');
		$edit->orden->size = 15;

		$edit->credito  = new inputField('Cr&eacute;dito', 'credito');
		$edit->credito->size = 12;
		$edit->credito->css_class='inputnum';
		$edit->credito->when=array('show');

		$edit->montotot  = new inputField('Subtotal', 'montotot');
		$edit->montotot->onkeyup='cmontotot()';
		$edit->montotot->size = 12;
		$edit->montotot->autocomplete=false;
		$edit->montotot->css_class='inputnum';

		$edit->montoiva  = new inputField('IVA', 'montoiva');
		$edit->montoiva->onkeyup='cmontoiva()';
		$edit->montoiva->size = 12;
		$edit->montoiva->autocomplete=false;
		$edit->montoiva->css_class='inputnum';

		$edit->montonet  = new hiddenField('Total', 'montonet');
		//$edit->montonet->size = 20;
		//$edit->montonet->css_class='inputnum';

		$edit->anticipo  = new inputField('Anticipo', 'anticipo');
		$edit->anticipo->size = 12;
		$edit->anticipo->css_class='inputnum';
		$edit->anticipo->when=array('show');

		$edit->inicial  = new inputField('Contado', 'inicial');
		$edit->inicial->size = 12;
		$edit->inicial->css_class='inputnum';
		$edit->inicial->when=array('show');

		$edit->rislr  = new inputField('Retenci&oacute;n ISLR', 'reten');
		$edit->rislr->size = 12;
		$edit->rislr->css_class='inputnum';
		$edit->rislr->when=array('show');

		$edit->riva  = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 11;
		$edit->riva->css_class='inputnum';
		//$edit->riva->when=array('show');

		$edit->mdolar  = new inputField('Monto US $', 'mdolar');
		$edit->mdolar->size = 12;
		$edit->mdolar->css_class='inputnum';
		$edit->mdolar->when=array('show');

		$edit->observa1 = new textareaField('Observaci&oacute;n', 'observa1');
		$edit->observa1->cols=90;
		$edit->observa1->rows=3;

		$edit->observa2 = new textareaField('Observaci&oacute;n', 'observa2');
		$edit->observa2->when=array('show');
		$edit->observa2->rows=3;

		$edit->observa3 = new textareaField('Observaci&oacute;n', 'observa3');
		$edit->observa3->when=array('show');
		$edit->observa3->rows=3;

		//****************************
		//Campos para el detalle
		//****************************
		$edit->codigo = new inputField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo';
		$edit->codigo->append($this->datasis->p_modbus($modbus,'<#i#>'));
		$edit->codigo->autocomplete=false;
		$edit->codigo->db_name  = 'codigo';
		$edit->codigo->rule     = 'required|callback_chcodigoa';
		$edit->codigo->rel_id   = 'itscst';

		$edit->descrip = new hiddenField('Descripci&oacute;n', 'descrip_<#i#>');
		$edit->descrip->size     =30;
		$edit->descrip->db_name  ='descrip';
		$edit->descrip->maxlength=12;
		$edit->descrip->rel_id  ='itscst';

		$edit->cantidad = new inputField('Cantidad', 'cantidad_<#i#>');
		$edit->cantidad->db_name      = 'cantidad';
		$edit->cantidad->css_class    = 'inputnum';
		$edit->cantidad->rel_id       = 'itscst';
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
		$edit->costo->rel_id          = 'itscst';
		$edit->costo->showformat      = 'decimal';

		$edit->importe = new inputField('Importe', 'importe_<#i#>');
		$edit->importe->db_name       = 'importe';
		$edit->importe->size          = 12;
		$edit->importe->rel_id        = 'itscst';
		$edit->importe->autocomplete  = false;
		$edit->importe->onkeyup       = 'costo(<#i#>)';
		$edit->importe->css_class     = 'inputnum';
		$edit->importe->showformat    = 'decimal';

		$edit->sinvpeso = new hiddenField('', 'sinvpeso_<#i#>');
		$edit->sinvpeso->db_name      = 'sinvpeso';
		$edit->sinvpeso->rel_id       = 'itscst';
		$edit->sinvpeso->pointer      = true;
		$edit->sinvpeso->showformat   = 'decimal';

		$edit->iva = new hiddenField('Impuesto', 'iva_<#i#>');
		$edit->iva->db_name           = 'iva';
		$edit->iva->rel_id            = 'itscst';
		$edit->iva->showformat        = 'decimal';

		//fin de campos para detalle

		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));

		$recep  =strtotime($edit->get_from_dataobjetct('recep'));
		$fecha  =strtotime($edit->get_from_dataobjetct('fecha'));
		$actuali=strtotime($edit->get_from_dataobjetct('actuali'));

		if($actuali < $fecha){
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/actualizar/'.$control)."'";
			$accio2="javascript:window.location='".site_url('compras/scst/cprecios/'.$control)."'";
			$accio3="javascript:window.location='".site_url('compras/scst/montoscxp/modify/'.$control)."'";

			$edit->button_status('btn_actuali','Actualizar'     ,$accion,'TR','show');
			$edit->button_status('btn_precio' ,'Asignar precios',$accio2,'TR','show');
			$edit->button_status('btn_cxp'    ,'Ajuste CxP'     ,$accio3,'TR','show');
			$edit->buttons('save', 'delete','modify', 'exit','add_rel','add');
		} else {
			$control=$this->rapyd->uri->get_edited_id();
			$accion="javascript:window.location='".site_url('compras/scst/reversar/'.$control)."'";
			$edit->button_status('btn_reversar','Reversar'     ,$accion,'TR','show');
			$edit->buttons('save', 'exit','add_rel');
		}

		if($this->genesal){
			$edit->build();
			$smenu['link']  =  barra_menu('201');
			$data['smenu']  =  $this->load->view('view_sub_menu', $smenu,true);
			$conten['form'] =& $edit;
			$conten['solo'] = $this->solo;
			$ffecha=$edit->get_from_dataobjetct('fecha');
			$conten['alicuota']=$this->datasis->ivaplica(($ffecha==false)? null : $ffecha);
			if (  $this->solo ){
				$this->load->view('view_compras', $conten);
			} else {
				$data['script']  = script('jquery.js');
				$data['script'] .= script('jquery-ui.js');
				$data['script'] .= script('plugins/jquery.numeric.pack.js');
				$data['script'] .= script('plugins/jquery.floatnumber.js');
				$data['script'] .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
				$data['script'] .= phpscript('nformat.js');

				$data['head']    = $this->rapyd->get_head();
				$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');

				$data['content'] = $this->load->view('view_compras', $conten, true);
				$data['title']   = heading('Compras');
				$this->load->view('view_ventanas', $data);
			}
		}else{
			$edit->on_save_redirect=false;
			$edit->build();
			if($edit->on_success()){
				$this->claves=$edit->_dataobject->pk;
				$this->claves['control']=$edit->_dataobject->get('control');
				$rt= 'Compra Guardada';
			}elseif($edit->on_error()){
				$rt= html_entity_decode(preg_replace('/<[^>]*>/', '', $edit->error_string));
			}
			return $rt;
		}

	}

	function solo() {
		$this->solo = true;
		$id = $this->uri->segment($this->uri->total_segments());

		//Creando Compra
		if ( $id == 'create'){
			$this->dataedit();
		}elseif( $id == 'insert'){
			$this->genesal = false;
			$rt = $this->dataedit();
			$id = (isset($this->claves['id']))? $this->claves['id'] :0;

			if(strlen($rt) > 0 ){
				$rtjson=array('id'=> $id,'mensaje'=> utf8_encode(str_replace("\n",'<br />',$rt)));
				if($rt=='Compra Guardada'){
					$rtjson['status']='A';
				}else{
					$rtjson['status']='C';
				}
				echo json_encode($rtjson);
			}else{
				$rtjson=array('id'=> 0,'mensaje'=> utf8_encode('Error desconocido'), 'status'=>'C');
				echo json_encode($rtjson);
			}
		}elseif( $id == 'process'){
			$control = $this->uri->segment($this->uri->total_segments()-1);
			$rt = $this->actualizar($control);
			if ( strlen($rt[1]) > 0 )
				if ( $rt[0] === false ) $p = 'E'; else $p='A';
				echo '{"status":"'.$p.'","id":"'.$control.'" ,"mensaje":"<h1>'.$rt[1].'</h1>"}';
		} else {
			$modo = $this->uri->segment($this->uri->total_segments()-1);

			if ( $modo == 'actualizar' ){
				$this->actualizar($id);
			} elseif ( $modo == 'reversar' ){
				$rt = $this->reversar($id);
				echo $rt;
			} elseif ( $modo == 'cprecios' ){
				$rt = $this->cprecios($id);
				echo $rt;
			} else {
				if ( $modo == 'update' ) $this->genesal = false;
				$rt = $this->dataedit();
				if ( strlen($rt) > 0 )
					echo '{"status":"A","id":"'.$id.'" ,"mensaje":"'.$rt.'"}';
			}
		}
	}

	//***************************************
	// Precios
	//
	function cprecios($control){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datagrid','fields');

		$error='';
		$msj='';

		//if($this->input->post('pros') !== false){
		if($this->input->post('scstp_1') !== false){
			$precio1=$this->input->post('scstp_1');
			$precio2=$this->input->post('scstp_2');
			$precio3=$this->input->post('scstp_3');
			$precio4=$this->input->post('scstp_4');

			foreach(array_keys($precio1) as $ind){
				$pt1 = $precio1[$ind]>=$precio2[$ind] && $precio2[$ind]>=$precio3[$ind] && $precio3[$ind]>=$precio4[$ind];
				$pt2 = $precio1[$ind]>0 && $precio2[$ind]>0 && $precio3[$ind]>0 && $precio4[$ind]>0;
				if($pt1 && $pt2){
					$data=array(
						'precio1'=>$precio1[$ind],
						'precio2'=>$precio2[$ind],
						'precio3'=>$precio3[$ind],
						'precio4'=>$precio4[$ind]
					);

					$where = 'id = '.$this->db->escape($ind);
					$mSQL = $this->db->update_string('itscst',$data,$where);
					$ban=$this->db->simple_query($mSQL);
				}else{
					$error='Los precios deben cumplir esta regla (precio 1 >= precio 2 >= precio 3 >= precio 4) y mayores a cero';
				}
			}
			if(strlen($error)==0){
				$msj='Nuevos Precios guardados';
			}
			if ( $this->solo )
				return '{"status":"C","id":"'.$control.'" ,"mensaje":"'.$msj.$error.'"}';
		}

		if ( $this->solo )
			$ggrid =form_open('/compras/scst/solo/cprecios/'.$control, array("id" => "df1"));
		else
			$ggrid =form_open('/compras/scst/cprecios/'.$control);


		function costo($formcal,$pond,$ultimo,$standard,$existen,$itcana){
			$CI =& get_instance();
			$costo_pond=$CI->_pond($existen,$itcana,$pond,$ultimo);
			return $CI->_costos($formcal,$costo_pond,$ultimo,$standard);
		}

		function margen($formcal,$pond,$ultimo,$standard,$existen,$itcana,$precio,$iva){
			$costo=costo($formcal,$pond,$ultimo,$standard,$existen,$itcana);
			if($precio==0) return 0;
			return round(100-(($costo*100)/($precio/(1+($iva/100)))),2);
		}

		function tcosto($id,$iva,$formcal,$pond,$ultimo,$standard,$existen,$itcana){
			$costo=costo($formcal,$pond,$ultimo,$standard,$existen,$itcana);
			$rt = nformat($costo);

			$rt.= '<input type="hidden" id="costo['.$id.']" name="costo['.$id.']" value="'.$costo.'" />';
			$rt.= '<input type="hidden" id="iva['.$id.']" name="iva['.$id.']" value="'.$iva.'" />';
			return $rt;
		}

		$grid = new DataGrid('Precios de art&iacute;culos');
		$grid->use_function('costo','margen','tcosto');
		$grid->order_by('descrip');
		$select=array('b.codigo','b.descrip','b.formcal','a.costo','b.ultimo','b.pond','b.standard','a.id',
					  'a.precio1 AS scstp_1','a.precio2 AS scstp_2','a.precio3 AS scstp_3','a.precio4 AS scstp_4',
					  'b.precio1 AS sinvp1' ,'b.precio2 AS sinvp2' ,'b.precio3 AS sinvp3' ,'b.precio4 AS sinvp4',
					  'b.formcal','a.cantidad','b.existen','b.iva'
					);
		$grid->db->select($select);
		$grid->db->from('itscst AS a');
		$grid->db->join('sinv AS b','a.codigo=b.codigo');
		$grid->db->where('control' , $control);

		//$grid->column('C&oacute;digo'     , '' );
		$grid->column_orderby('Descripci&oacute;n', '<b class=\'mininegro\'><#codigo#></b><br><#descrip#>', 'descrip');

		$ittt=array('sinvp1','sinvp2','sinvp3','sinvp4');
		$itt=array('scstp_1','scstp_2','scstp_3','scstp_4');
		foreach ($itt as $id=>$val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->status   ='modify';
			$campo->size     =8;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';
			$campo->append('<#'.$ittt[$id].'#>');
			$campo->disable_paste=true;

			$grid->column('Precio '.($id+1) , $campo,'align=\'center\'');
		}

		$itt=array('margen_1','margen_2','margen_3','margen_4');
		foreach ($itt as $id=>$val){
			$ind = $val;

			$campo = new inputField('Campo', $ind);
			$campo->grid_name=$ind.'[<#id#>]';
			$campo->pattern  ='<margen><#formcal#>|<#pond#>|<#costo#>|<#standard#>|<#existen#>|<#cantidad#>|<#scstp_'.($id+1).'#>|<#iva#></margen>';
			$campo->status   ='modify';
			$campo->size     =3;
			$campo->autocomplete=false;
			$campo->css_class='inputnum';
			$campo->disable_paste=true;

			$grid->column('Marg.'.($id+1) , $campo,'align=\'center\'');
		}
		$grid->column('Costo' , '<tcosto><#id#>|<#iva#>|<#formcal#>|<#pond#>|<#costo#>|<#standard#>|<#existen#>|<#cantidad#></tcosto>','align=\'right\'');
		if ( !$this->solo ){
			$action = "javascript:window.location='".site_url('compras/scst/dataedit/show/'.$control)."'";
			$grid->button('btn_regresa', 'Regresar', $action, 'TR');
			$grid->submit('pros', 'Guardar','BR');
		}

		$grid->build();

		$ggrid.=$grid->output;
		$ggrid.=form_close();

		$script='<script language="javascript" type="text/javascript">
		$(function(){
			$(".inputnum").numeric(".");
			$(".inputonlynum").numeric();
			$(\'input[name^="margen_"]\').keyup(function() {
				nom=this.name;
				pos0=this.name.lastIndexOf("_");
				pos1=this.name.lastIndexOf("[");
				pos2=this.name.lastIndexOf("]");
				if(pos0>0 && pos1>0 && pos2>0){
					idp = this.name.substring(pos0+1,pos1);
					ind = this.name.substring(pos1+1,pos2);

					costo  = Number($("#costo\\\["+ind+"\\\]").val());
					iva    = Number($("#iva\\\["+ind+"\\\]").val());
					margen = Number($(this).val());

					precio = roundNumber((costo*100/(100-margen))*(1+(iva/100)),2);
					$("#scstp_"+idp+"\\\["+ind+"\\\]").val(precio);
				}
			});

			$(\'input[name^="scstp_"]\').keyup(function() {
				nom=this.name;
				pos0=this.name.lastIndexOf("_");
				pos1=this.name.lastIndexOf("[");
				pos2=this.name.lastIndexOf("]");
				if(pos0>0 && pos1>0 && pos2>0){
					idp = this.name.substring(pos0+1,pos1);
					ind = this.name.substring(pos1+1,pos2);

					precio = Number($(this).val());
					costo  = Number($("#costo\\\["+ind+"\\\]").val());
					iva    = Number($("#iva\\\["+ind+"\\\]").val());

					margen=roundNumber(100-((costo*100)/(precio/(1+(iva/100)))),2);
					$("#margen_"+idp+"\\\["+ind+"\\\]").val(margen);
				}
			});
		});
		</script>';
		$data['content']  = '<div class="alert">'.$error.'</div>';
		$data['content'] .= '<div>'.$msj.'</div>';
		$data['content'] .= $ggrid;

		if ( $this->solo ){
			$mensaje = "<table><tr><td>Mensaje: ".$msj."</td><td>Error: ".$error."</td></tr></table>\n";
			return $script."\n".$data['content'];
		} else {
			$data['title']    = heading('Cambio de precios');
			$data['script']   = $script;
			$data['script']  .= phpscript('nformat.js');
			$data['head']     = $this->rapyd->get_head();
			$data['head']     = script('jquery.pack.js');
			$data['head']     = script('plugins/jquery.numeric.pack.js');
			$data['head']     = script('plugins/jquery.floatnumber.js');
			$data['head']    .= style('estilos.css');
			$this->load->view('view_ventanas', $data);
		}
	}

	function montoscxp(){
		$this->rapyd->load('dataedit');
		//$this->rapyd->uri->keep_persistence();
		$control=$this->rapyd->uri->get_edited_id();

		//$ffecha=$edit->get_from_dataobjetct('fecha');
		$ffecha=false;
		$alicuota=$this->datasis->ivaplica(($ffecha==false)? null : $ffecha);

		$edit = new DataEdit('Compras','scst');
		$edit->on_save_redirect=false;
		//$edit->post_process('update' ,'_post_cxp_update');

		//Para CXP
		$edit->cexento = new inputField('Excento', 'cexento');
		$edit->cexento->size = 15;
		$edit->cexento->autocomplete=false;
		$edit->cexento->showformat= 'decimal';
		$edit->cexento->onkeyup='ctotales()';
		$edit->cexento->rule='numeric';
		$edit->cexento->css_class='inputnum';

		$edit->cgenera = new inputField('Base imponible tasa General', 'cgenera');
		$edit->cgenera->size = 15;
		$edit->cgenera->onkeyup='cal_iva('.$alicuota['tasa'].',\'civagen\',this.value)';
		$edit->cgenera->css_class='inputnum';
		$edit->cgenera->showformat= 'decimal';
		$edit->cgenera->rule='numeric';
		$edit->cgenera->autocomplete=false;

		$edit->civagen = new inputField('Monto alicuota tasa General', 'civagen');
		$edit->civagen->size = 10;
		$edit->civagen->autocomplete=false;
		$edit->civagen->showformat= 'decimal';
		$edit->civagen->onkeyup='cal_base('.$alicuota['tasa'].',\'cgenera\',this.value)';
		$edit->civagen->rule='numeric';
		$edit->civagen->css_class='inputnum';

		$edit->creduci = new inputField('Base imponible tasa Reducida', 'creduci');
		$edit->creduci->size = 15;
		$edit->creduci->autocomplete=false;
		$edit->creduci->showformat= 'decimal';
		$edit->creduci->onkeyup='cal_iva('.$alicuota['redutasa'].',\'civared\',this.value)';
		$edit->creduci->rule='numeric';
		$edit->creduci->css_class='inputnum';

		$edit->civared = new inputField('Monto alicuota tasa Reducida', 'civared');
		$edit->civared->size = 10;
		$edit->civared->autocomplete=false;
		$edit->civared->showformat= 'decimal';
		$edit->civared->onkeyup='cal_base('.$alicuota['redutasa'].',\'creduci\',this.value)';
		$edit->civared->css_class='inputnum';

		$edit->cadicio = new inputField('Base imponible tasa Adicional', 'cadicio');
		$edit->cadicio->size = 15;
		$edit->cadicio->autocomplete=false;
		$edit->cadicio->showformat= 'decimal';
		$edit->cadicio->onkeyup='cal_iva('.$alicuota['sobretasa'].',\'civaadi\',this.value)';
		$edit->cadicio->css_class='inputnum';

		$edit->civaadi = new inputField('Monto alicuota tasa Adicional', 'civaadi');
		$edit->civaadi->size = 10;
		$edit->civaadi->autocomplete=false;
		$edit->civaadi->showformat= 'decimal';
		$edit->civaadi->rule='numeric';
		$edit->civaadi->onkeyup='cal_base('.$alicuota['sobretasa'].',\'cadicio\',this.value)';
		$edit->civaadi->css_class='inputnum';

		$edit->cstotal = new hiddenField('Sub-total', 'cstotal');
		$edit->cstotal->size = 20;
		$edit->cstotal->rule='numeric';
		$edit->cstotal->css_class='inputnum';

		$edit->riva = new inputField('Retenci&oacute;n IVA', 'reteiva');
		$edit->riva->size = 10;
		$edit->riva->showformat= 'decimal';
		$edit->riva->rule='numeric';
		$edit->riva->autocomplete=false;
		$edit->riva->css_class='inputnum';
		$contribu= $this->datasis->traevalor('CONTRIBUYENTE');
		$rif     = $this->datasis->traevalor('RIF');
		if(!($contribu=='ESPECIAL' && strtoupper($rif[0])!='V')){
			$edit->riva->when=array('show');
		}

		$edit->cimpuesto = new hiddenField('Total Impuesto', 'cimpuesto');
		$edit->cimpuesto->size = 10;
		$edit->cimpuesto->rule='numeric';
		$edit->cimpuesto->autocomplete=false;
		$edit->cimpuesto->css_class='inputnum';

		$edit->ctotal  = new hiddenField('Total', 'ctotal');
		$edit->ctotal->size = 20;
		$edit->ctotal->rule='numeric';
		$edit->ctotal->css_class='inputnum';
		//Fin de CxP

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);

			echo json_encode($rt);
		}else{
			$conten['form'] =& $edit;
			$conten['alicuota'] = $alicuota;
			$proveed=$edit->get_from_dataobjetct('proveed');
			$conten['priva']   = $this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
			$conten['priva']   = $conten['priva']/100;
			$data['content']   = $this->load->view('view_compras_cmontos', $conten);
		}

	}

	//Chequea que los registros no esten repatidos en los datos vehiculares
	function chrepetido($valor,$campo){
		$this->db->where($campo,$valor);
		$cana=$this->db->count_all_results('sinvehiculo');
		if($cana>0){
			$this->validation->set_message('chrepetido', "Ya existe un veh&iacute;culo con el mismo $campo registrado.");
			return false;
		}
		return true;
	}

	function chcodigoa($codigo){
		$cana=$this->datasis->dameval('SELECT COUNT(*) FROM sinv WHERE activo=\'S\' AND codigo='.$this->db->escape($codigo));
		if(empty($cana) || $cana==0){
			$this->validation->set_message('chcodigoa', 'El campo %s contiene un codigo no v&aacute;lido o inactivo');
			return false;
		}
		return true;
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
		$data['head']    =script('prototype.js').$this->rapyd->get_head();
		$data['title']   =heading('Seleccione un departamento');
		$this->load->view('view_detalle', $data);
	}

	function scstserie(){
		$serie      = $this->uri->segment($this->uri->total_segments());
		$control    = $this->uri->segment($this->uri->total_segments()-1);
		$dbserie    = $this->db->escape($serie);
		$dbconstrol = $this->db->escape($control);
		if (!empty($serie)) {
			$this->db->simple_query("UPDATE scst SET serie=$dbserie WHERE control=$dbcontrol");
			echo " con exito ";
		} else {
			echo " NO se guardo ";
		}
		logusu('SCST',"Cambia Nro. Serie $control ->  $serie ");
	}

	function autocomplete($campo,$cod=FALSE){
		if($cod!==false){
			$cod=$this->db->escape_like_str($cod);
			$qformato=$this->datasis->formato_cpla();
			$data['control']="SELECT control AS c1,fecha AS c2,numero AS c3,nombre AS c4 FROM scst WHERE control LIKE '$cod%' ORDER BY control DESC LIMIT 10";
			if(isset($data[$campo])){
				$query=$this->db->query($data[$campo]);
				if($query->num_rows() > 0){
					foreach($query->result_array() AS $row){
						echo $row['c1'].'|'.dbdate_to_human($row['c2']).'|'.$row['c3'].'|'.$row['c4']."\n";
					}
				}
			}
		}
	}

	function actualizar($control){
		$this->rapyd->load('dataform');
		$dbcontrol = $this->db->escape($control);

		$script = '$(function(){ $("#fecha").datepicker({ dateFormat: "dd/mm/yy" }); })';

		$form = new DataForm("compras/scst/actualizar/$control/process");
		$form->script($script);

		$scstrow = $this->datasis->damerow("SELECT proveed,nombre,fecha,montotot, montoiva,montonet,serie,vence FROM scst WHERE control=$dbcontrol");


		$htmltabla="<table width='100%' style='background-color:#FBEC88;text-align:center;font-size:12px'>
			<tr>
				<td>Proveedor:</td>
				<td><b>(".htmlspecialchars($scstrow['proveed']).")</b></td>
				<td colspan='4'><b>".htmlspecialchars($scstrow['nombre'])."</b></td>
			</tr><tr>
				<td>Compra:</td>
				<td>".htmlspecialchars($scstrow['serie'])."</b></td>
				<td>Fecha: </td>
				<td><b>".dbdate_to_human($scstrow['fecha'])."</b></td>
				<td>Vence:</td>
				<td><b>".dbdate_to_human($scstrow['vence'])."</b></td>
			</tr><tr>
				<td>Sub Total:</td>
				<td><b>".nformat($scstrow['montotot'])."</b></td>
				<td> I.V.A.: </td>
				<td><b>".nformat($scstrow['montoiva'])."</b></td>
				<td>Monto: </td>
				<td><b>".nformat($scstrow['montonet'])."</b></td>
			</tr>
		</table>";

		$form->tablafo = new containerField('tablafo',$htmltabla);

		$form->cprecio = new  dropdownField ('Cambiar precios', 'cprecio');
		$form->cprecio->option('D','Dejar el precio mayor');
		$form->cprecio->option('N','No');
		$form->cprecio->option('S','Si');
		$form->cprecio->rule  = 'enum[D,N,S]';
		$form->cprecio->style = 'width:150px;';
		$form->cprecio->rule  = 'required';

		//$form->ffecha = new dateonlyField('Fecha de la compra', 'fecha','d/m/Y');
		//$form->ffecha->insertValue = date('Y-m-d');
		//$form->ffecha->rule='required|callback_chddate';
		//$form->ffecha->calendar = false;
		//$form->ffecha->size=10;

		$form->fecha = new dateonlyField('Fecha de recepci&oacute;n de la compra', 'fecha','d/m/Y');
		$form->fecha->insertValue = date('Y-m-d');
		$form->fecha->rule='required|callback_chddate';
		$form->fecha->calendar = false;
		$form->fecha->size=10;

		$form->build_form();

		if($form->on_success()){
			$cprecio   = $form->cprecio->newValue;
			$actualiza = $form->fecha->newValue;
			$cambio    = $cprecio;
			$dbcontrol = $this->db->escape($control);

			$id = $this->datasis->dameval("SELECT id FROM scst WHERE control=$dbcontrol");
			$rt = $this->_actualizar($id,$cambio,$actualiza);
			if($rt === false){
				$rt=array(
					'status' =>'B',
					'mensaje'=>utf8_encode($this->error_string),
					'pk'     =>array('id'=>$id)
				);
				echo json_encode($rt);
			}else{
				$data['content']  = 'Compra actualizada'.br();
				$rt=array(
					'status' =>'A',
					'mensaje'=>'Registro guardado',
					'pk'     =>array('id'=>$id)
				);
				echo json_encode($rt);
			}
		}else{
			echo $form->output;
		}
	}

	//Proporciona por ajax el dato vehicular
	function getvehicular($id=null){
		//$id  = $this->input->post('id');
		if(!empty($id)){
			$dbid= $this->db->escape($id);
			$mSQL="SELECT COUNT(*) AS cana
				FROM scst   AS a
				JOIN itscst AS b ON a.control=b.control
				JOIN sinv   AS c ON b.codigo=c.codigo
				WHERE c.serial='V' AND a.id=$dbid";
			$cana = $this->datasis->dameval($mSQL);
		}else{
			$cana=0;
		}

		if ($cana > 0){
			echo 1;
		}else{
			echo 0;
		}
	}

	//Permite colocar los seriale a los vehiculos
	function dataeditvehiculo($sta,$id){
		$this->rapyd->load('dataobject','datadetails');

		//Cheque que tenga vehiculos
		$dbid=$this->db->escape($id);
		$cana=$this->datasis->dameval("SELECT COUNT(*) AS cana FROM sinvehiculo WHERE id_scst=$dbid");
		if(empty($cana)){
			$mSQL="SELECT c.codigo,c.descrip,c.peso,b.cantidad AS cana
				FROM scst   AS a
				JOIN itscst AS b ON a.control=b.control
				JOIN sinv   AS c ON b.codigo=c.codigo
				WHERE c.serial='V' AND a.id=$dbid";
			$query = $this->db->query($mSQL);

			if ($query->num_rows() > 0){
				foreach ($query->result() as $row){
					for($i=0;$i<$row->cana;$i++){
						$data=array(
							'codigo_sinv'=>$row->codigo,
							'modelo'     =>$row->descrip,
							'peso'       =>$row->peso,
							'anio'       =>date('Y'),
							'color'      =>'',
							'motor'      =>'',
							'carroceria' =>'',
							'id_scst'    =>$id
						);
						$sql = $this->db->insert_string('sinvehiculo', $data);
						$this->db->simple_query($sql);
					}
				}
			}else{
				echo 'Compra no tiene Veh&iacute;culos.';
				exit();
			}
		}

		$do = new DataObject('scst');
		$do->rel_one_to_many('sinvehiculo', 'sinvehiculo', array('id'=>'id_scst'));
		$do->pointer('sprv' ,'sprv.proveed=scst.proveed','sprv.nombre AS sprvnombre','left');
		$do->order_rel_one_to_many('sinvehiculo','codigo_sinv');
		//$do->rel_pointer('itscst','sinv','itscst.codigo=sinv.codigo','sinv.descrip AS sinvdescrip, sinv.base1 AS sinvprecio1, sinv.base2 AS sinvprecio2, sinv.base3 AS sinvprecio3, sinv.base4 AS sinvprecio4, sinv.iva AS sinviva, sinv.peso AS sinvpeso,sinv.tipo AS sinvtipo');

		$edit = new DataDetails('Compras',$do);
		$edit->set_rel_title('itscst','Producto <#o#>');
		$edit->on_save_redirect=false;

		$edit->pre_process('insert' ,'_pre_vehi_insert');
		$edit->pre_process('update' ,'_pre_vehi_update');
		$edit->pre_process('delete' ,'_pre_vehi_delete');
		$edit->post_process('update','_post_vehi_update');

		$edit->proveed = new inputField('Proveedor', 'proveed');
		$edit->proveed->size     = 7;
		$edit->proveed->maxlength= 5;
		$edit->proveed->autocomplete=false;
		$edit->proveed->mode = 'autohide';
		$edit->proveed->rule = 'required';

		$edit->nombre = new hiddenField('Nombre', 'nombre');
		$edit->nombre->mode = 'autohide';
		$edit->nombre->size = 50;
		$edit->nombre->maxlength=40;

		$edit->fecha = new DateonlyField('Fecha', 'fecha','d/m/Y');
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size = 10;
		$edit->fecha->rule ='required';
		$edit->fecha->calendar=false;
		$edit->fecha->mode = 'autohide';
		$transac=$edit->get_from_dataobjetct('transac');

		$edit->vence = new DateonlyField('Vence', 'vence','d/m/Y');
		$edit->vence->insertValue = date('Y-m-d');
		$edit->vence->size = 10;
		$edit->vence->rule ='required';
		$edit->vence->mode = 'autohide';
		$edit->vence->calendar=false;

		$edit->serie = new inputField('N&uacute;mero', 'serie');
		$edit->serie->size = 15;
		$edit->serie->autocomplete=false;
		$edit->serie->rule = 'required';
		$edit->serie->mode = 'autohide';
		$edit->serie->maxlength=12;

		$edit->cfis = new inputField('Numero fiscal', 'nfiscal');
		$edit->cfis->size = 15;
		$edit->cfis->autocomplete=false;
		$edit->cfis->mode = 'autohide';
		$edit->cfis->rule = 'required';
		$edit->cfis->maxlength=12;

		$edit->almacen = new  dropdownField ('Almacen', 'depo');
		$edit->almacen->options('SELECT ubica, CONCAT(ubica,\' \',ubides) nombre FROM caub ORDER BY ubica');
		$edit->almacen->rule = 'required';
		$edit->almacen->mode = 'autohide';
		$edit->almacen->style='width:145px;';

		$edit->tipo = new dropdownField('Tipo', 'tipo_doc');
		$edit->tipo->option('FC','Factura a Cr&eacute;dito');
		$edit->tipo->option('NC','Nota de Cr&eacute;dito'); //Falta implementar los metodos post para este caso
		//$edit->tipo->option('NE','Nota de Entrega');        //Falta implementar los metodos post para este caso
		$edit->tipo->rule = 'required';
		$edit->tipo->style='width:140px;';

		$edit->observa1 = new textareaField('Observaci&oacute;n', 'observa1');
		$edit->observa1->cols=90;
		$edit->observa1->rows=3;

		$edit->montotot  = new inputField('Subtotal', 'montotot');
		$edit->montotot->onkeyup='cmontotot()';
		$edit->montotot->size = 12;
		$edit->montotot->autocomplete=false;
		$edit->montotot->css_class='inputnum';

		$edit->montoiva  = new inputField('IVA', 'montoiva');
		$edit->montoiva->onkeyup='cmontoiva()';
		$edit->montoiva->size = 12;
		$edit->montoiva->autocomplete=false;
		$edit->montoiva->css_class='inputnum';

		$edit->montonet  = new hiddenField('Total', 'montonet');
		//$edit->montonet->size = 20;
		//$edit->montonet->css_class='inputnum';

		$edit->idrel = new hiddenField('', 'id_<#i#>');
		$edit->idrel->rel_id   = 'sinvehiculo';
		$edit->idrel->size=10;
		$edit->idrel->db_name='id';

		$edit->codigo = new hiddenField('C&oacute;digo', 'codigo_<#i#>');
		$edit->codigo->rel_id   = 'sinvehiculo';
		$edit->codigo->size=10;
		$edit->codigo->db_name='codigo_sinv';
		$edit->codigo->autocomplete=false;
		$edit->codigo->rule     = 'required|callback_chcodigoa';

		$edit->modelo = new hiddenField('Modelo', 'modelo_<#i#>');
		$edit->modelo->rel_id   = 'sinvehiculo';
		$edit->modelo->size     = 30;
		$edit->modelo->db_name  = 'modelo';
		$edit->modelo->group    = 'Datos del veh&iacute;culo';

		$edit->anio = new inputField('A&ntildeo','anio_<#i#>');
		$edit->anio->rel_id   = 'sinvehiculo';
		$edit->anio->rule='exact_length[4]|numeric|required';
		$edit->anio->db_name  = 'anio';
		$edit->anio->size =5;
		$edit->anio->maxlength =4;
		$edit->anio->insertValue=date('Y');
		$edit->anio->autocomplete=false;

		$edit->color = new inputField('Color','color_<#i#>');
		$edit->color->rel_id   = 'sinvehiculo';
		$edit->color->db_name = 'color';
		$edit->color->rule='max_length[50]|strtoupper|required';
		$edit->color->size =10;
		$edit->color->maxlength =50;
		$edit->color->autocomplete=false;

		$edit->motor = new inputField('Serial de Motor','motor_<#i#>');
		$edit->motor->rel_id   = 'sinvehiculo';
		$edit->motor->rule='max_length[50]|strtoupper|callback_chrepetidos[motor]|required';
		$edit->motor->db_name  = 'motor';
		$edit->motor->size =25;
		$edit->motor->maxlength =50;
		$edit->motor->autocomplete=false;

		$edit->carroceria = new inputField('Serial de Carrocer&iacute;a','carroceria_<#i#>');
		$edit->carroceria->rel_id   = 'sinvehiculo';
		$edit->carroceria->rule='max_length[50]|strtoupper|callback_chrepetidos[carroceria]|required';
		$edit->carroceria->db_name  = 'carroceria';
		$edit->carroceria->size =25;
		$edit->carroceria->maxlength =50;
		$edit->carroceria->autocomplete=false;

		$edit->uso = new  dropdownField('Tipo de uso','uso_<#i#>');
		$edit->uso->rel_id   = 'sinvehiculo';
		$edit->uso->db_name  = 'uso';
		$edit->uso->option('P','Particular');
		$edit->uso->option('T','Trabajo');
		$edit->uso->option('C','Carga');
		$edit->uso->style='width:150px;';
		$edit->uso->size = 6;
		$edit->uso->rule='required';

		$edit->tipo = new  dropdownField('Tipo','tipo_<#i#>');
		$edit->tipo->rel_id   = 'sinvehiculo';
		$edit->tipo->db_name  = 'tipo';
		$edit->tipo->option('UTILITARIO'        ,'Utilitario');
		$edit->tipo->option('ENDURO'            ,'Enduro');
		$edit->tipo->option('SCOOTER'           ,'Scooter');
		$edit->tipo->option('MOTOCICLETA'       ,'Motocicleta');
		$edit->tipo->option('RACING'            ,'Racing');
		$edit->tipo->option('CHASIS'            ,'Chasis');
		$edit->tipo->option('CAVA REFRIGERADA'  ,'Cava Refrigerada');
		$edit->tipo->option('CAVA TERMINA'      ,'Cava Termina');
		$edit->tipo->option('CAVA SECA'         ,'Cava Seca');
		$edit->tipo->option('PLATAFORMA'        ,'Plataforma');
		$edit->tipo->option('PLATAFORMA GRUA'   ,'Plataforma Grua');
		$edit->tipo->option('PLATAFORMA BARANDA','Plataforma Barandas');
		$edit->tipo->option('AUTOBUS'           ,'Autobus');
		$edit->tipo->option('VOLTEO'            ,'Volteo');
		$edit->tipo->option('CUADRILLERO'       ,'Cuadrillero');
		$edit->tipo->option('CHUTO'             ,'Chuto');
		$edit->tipo->option('TANQUE'            ,'Tanque');
		$edit->tipo->option('JAULA GANADERA'    ,'Jaula Ganadera');
		$edit->tipo->option('FERRETERO'         ,'Ferretero');
		$edit->tipo->option('AMBULACIA'         ,'Ambulacia');
		$edit->tipo->style='width:150px;';
		$edit->tipo->size = 6;
		$edit->tipo->rule='required';

		$edit->clase = new  dropdownField('Clase','clase_<#i#>');
		$edit->clase->rel_id   = 'sinvehiculo';
		$edit->clase->db_name  = 'clase';
		$edit->clase->option('','Seleccionar');
		$edit->clase->option('AUTOMOVIL','Automovil');
		$edit->clase->option('MOTO'     ,'Moto');
		$edit->clase->option('CAMIONETA','Camioneta');
		$edit->clase->option('CAMION'   ,'Camion');
		$edit->clase->style='width:120px;';
		$edit->clase->size = 6;
		$edit->clase->rule='required';

		$edit->transmision = new  dropdownField('Transmisi&oacute;n','transmision_<#i#>');
		$edit->transmision->rel_id   = 'sinvehiculo';
		$edit->transmision->db_name='transmision';
		$edit->transmision->option('','Seleccionar');
		$edit->transmision->option('AUTOMATICO','Automatico');
		$edit->transmision->option('MANUAL'    ,'Manual');
		$edit->transmision->style='width:120px;';
		$edit->transmision->size = 6;
		$edit->transmision->rule='required';

		$edit->peso = new inputField('Peso Kg.','peso_<#i#>');
		$edit->peso->rel_id   = 'sinvehiculo';
		$edit->peso->db_name  = 'peso';
		$edit->peso->rule='max_length[10]|numeric|required';
		$edit->peso->css_class='inputnum';
		$edit->peso->size =12;
		$edit->peso->maxlength =12;

		$edit->placa = new inputField('Placa','placa_<#i#>');
		$edit->placa->rel_id   = 'sinvehiculo';
		$edit->placa->db_name  = 'placa';
		$edit->placa->rule='max_length[50]|strtoupper|callback_chrepetidos[placa]';
		$edit->placa->size =12;
		$edit->placa->maxlength =50;
		$edit->placa->autocomplete=false;

		$mSQL="SELECT a.costo FROM itscst AS a JOIN scst   AS b ON a.control=b.control WHERE codigo='PLACA' AND b.id=${dbid} GROUP BY a.costo";
		$qquery = $this->db->query($mSQL);
		$edit->precioplaca = new dropdownField('Precio placa.','precioplaca_<#i#>');
		$edit->precioplaca->rel_id   = 'sinvehiculo';
		$edit->precioplaca->db_name  = 'precioplaca';
		$edit->precioplaca->rule='max_length[10]|numeric|required';
		$edit->precioplaca->style='width:100px;';
		if ($qquery->num_rows() > 1){
			$edit->precioplaca->option('','Seleccionar');
		}
		foreach ($qquery->result() as $rrow){
			$edit->precioplaca->option($rrow->costo,nformat($rrow->costo));
		}

		$edit->id_sfac = new hiddenField('','id_sfac_<#i#>');
		$edit->id_sfac->rel_id   = 'sinvehiculo';
		$edit->id_sfac->db_name  = 'id_sfac';

		$recep  =strtotime($edit->get_from_dataobjetct('recep'));
		$fecha  =strtotime($edit->get_from_dataobjetct('fecha'));
		$actuali=strtotime($edit->get_from_dataobjetct('actuali'));

		if($actuali >= $fecha){
			$edit->carroceria->type='inputhidden';
			$edit->motor->type='inputhidden';
		}

		$edit->build();

		if($edit->on_success()){
			$rt=array(
				'status' =>'A',
				'mensaje'=>'Registro guardado',
				'pk'     =>$edit->_dataobject->pk
			);

			echo json_encode($rt);
		}else{
			$conten['form'] =& $edit;
			$this->load->view('view_seriales_vehi', $conten);
		}
	}

	function _actualizar($id, $cprecio, $actuali=null){
		$error = 0;
		$pasa  = $this->datasis->dameval('SELECT COUNT(*) FROM scst WHERE actuali>=fecha AND id= '.$id);

		if( $pasa==0 ){
			$control=$this->datasis->dameval('SELECT control FROM scst WHERE  id='.$id);

			//Chequea si tiene vehiculos y estan registrados los seriales
			if($this->db->table_exists('sinvehiculo')){
				$SQL="SELECT COUNT(*) AS cana
					FROM itscst AS b
					JOIN sinv   AS c ON b.codigo=c.codigo
					WHERE c.serial='V' AND b.control=".$this->db->escape($control);
				$cana = $this->datasis->dameval($SQL);
				if($cana>0){
					$SQL="SELECT COUNT(*) AS cana FROM sinvehiculo WHERE id_scst=$id AND (motor IS NULL OR motor='' OR carroceria IS NULL OR carroceria='')";
					$cana = $this->datasis->dameval($SQL);
					$SQL="SELECT COUNT(*) AS cana FROM sinvehiculo WHERE id_scst=$id";
					$cana2 = $this->datasis->dameval($SQL);
					if($cana > 0 || $cana2==0){
						$this->error_string='Debe cargar los seriales de los veh&iacute;culos para poder recibir la compra ';
						return false;
					}

				}
			}
			//Fin de la validacion vehicular



			$SQL='SELECT tipo_doc,transac,depo,proveed,fecha,vence, nombre,tipo_doc,nfiscal,fafecta,reteiva,
			cexento,cgenera,civagen,creduci,civared,cadicio,civaadi,cstotal,ctotal,cimpuesto,numero
			FROM scst WHERE control=?';
			$query=$this->db->query($SQL,array($control));

			if($query->num_rows()==1){
				$estampa = date('Y-m-d');
				$hora    = date('H:i:s');
				$usuario = $this->session->userdata('usuario');
				$row     = $query->row_array();

				if($row['tipo_doc']=='FC'){
					$transac = $row['transac'];
					$depo    = $row['depo'];
					$proveed = $row['proveed'];
					$fecha   = str_replace('-','',$row['fecha']);
					$vence   = $row['vence'];
					if(empty($row['reteiva'])){
						$reteiva = 0;
					}else{
						$reteiva = $row['reteiva'];
					}
					if(empty($actuali)) $actuali=date('Ymd');

					$itdata=array();
					$sql='SELECT a.codigo,a.cantidad,a.importe,a.importe/a.cantidad AS costo,
						a.precio1,a.precio2,a.precio3,a.precio4,b.formcal,b.ultimo,b.standard,b.pond,b.existen
						FROM itscst AS a JOIN sinv AS b ON a.codigo=b.codigo WHERE a.control=?';
					$qquery=$this->db->query($sql,array($control));
					if($qquery->num_rows()>0){
						foreach ($qquery->result() as $itrow){
							$pond     = $this->_pond($itrow->existen,$itrow->cantidad,$itrow->pond,$itrow->costo);

							$costo    = $this->_costos($itrow->formcal,$pond,$itrow->costo,$itrow->standard);
							$dbcodigo = $this->db->escape($itrow->codigo);
							//Actualiza el inventario
							$mSQL='UPDATE sinv SET
								pond='.$pond.',
								ultimo='.$itrow->costo.',
								prov3=prov2, prepro3=prepro2, pfecha3=pfecha2, prov2=prov1, prepro2=prepro1, pfecha2=pfecha1,
								prov1='.$this->db->escape($proveed).',
								prepro1='.$itrow->costo.',
								pfecha1='.$this->db->escape($fecha).'
								WHERE codigo='.$dbcodigo;
							$ban=$this->db->simple_query($mSQL);
							if(!$ban){ memowrite($mSQL,'scst'); $error++; }

							$this->datasis->sinvcarga($itrow->codigo,$depo, $itrow->cantidad );

							if(!$ban){ memowrite($mSQL,'scst'); $error++; }

							if($itrow->precio1>0 && $itrow->precio2>0 && $itrow->precio3>0 && $itrow->precio4>0){
								//Cambio de precios
								if($cprecio=='S'){
									$mSQL='UPDATE sinv SET
									precio1='.round(floatval($itrow->precio1),2).',
									precio2='.round(floatval($itrow->precio2),2).',
									precio3='.round(floatval($itrow->precio3),2).',
									precio4='.round(floatval($itrow->precio4),2).'
									WHERE codigo='.$dbcodigo;
									$ban=$this->db->simple_query($mSQL);
									if(!$ban){ memowrite($mSQL,'scst'); $error++; }
								}elseif($cprecio=='D'){
									$pps=array('precio1','precio2','precio3','precio4');
									foreach($pps as $obj){
										$pp  =round(floatval($itrow->$obj),2);
										$mSQL="UPDATE sinv SET ${obj}=${pp} WHERE ${pp}>${obj} AND codigo=${dbcodigo}";
										$ban =$this->db->simple_query($mSQL);
										if(!$ban){ memowrite($mSQL,'scst'); $error++; }
									}
								}
								//Fin del cambio de precios
							}

							//Actualiza los margenes y bases
							$mSQL='UPDATE sinv SET
								base1=ROUND(precio1*10000/(100+iva))/100,
								base2=ROUND(precio2*10000/(100+iva))/100,
								base3=ROUND(precio3*10000/(100+iva))/100,
								base4=ROUND(precio4*10000/(100+iva))/100,
								margen1=ROUND(10000-('.$costo.'*10000/base1))/100,
								margen2=ROUND(10000-('.$costo.'*10000/base2))/100,
								margen3=ROUND(10000-('.$costo.'*10000/base3))/100,
								margen4=ROUND(10000-('.$costo.'*10000/base4))/100,
								activo="S"
							WHERE codigo='.$dbcodigo;
							$ban=$this->db->simple_query($mSQL);
							if(!$ban){ memowrite($mSQL,'scst'); $error++; }
							//Fin de la actualizacion de inventario
						}
					}

					//Limpia primero la data
					$mSQL='DELETE FROM sprm WHERE transac='.$this->db->escape($transac);
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }

					//Inicio de la retencion
					if($reteiva>0){
						//Crea la nota de credito
						$mnumnc = $this->datasis->fprox_numero('num_nc');
						$sprm=array();
						$sprm['cod_prv']    = $proveed;
						$sprm['nombre']     = $row['nombre'];
						$sprm['tipo_doc']   = 'NC';
						$sprm['numero']     = $mnumnc;
						$sprm['fecha']      = $actuali;
						$sprm['monto']      = $reteiva;
						$sprm['impuesto']   = 0;
						$sprm['abonos']     = $reteiva;
						$sprm['vence']      = $actuali;
						$sprm['tipo_ref']   = 'FC';
						$sprm['num_ref']    = $row['numero'];
						$sprm['observa1']   = 'RET/IVA CAUSADA A FC'.$row['numero'];
						$sprm['estampa']    = $estampa;
						$sprm['hora']       = $hora;
						$sprm['transac']    = $transac;
						$sprm['usuario']    = $usuario;
						$sprm['codigo']     = 'NOCON';
						$sprm['descrip']    = 'NOTA DE CONTABILIDAD';
						$mSQL = $this->db->insert_string('sprm', $sprm);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }

						//Aplica la NC a la FC
						$itppro=array();
						$itppro['numppro']    = $mnumnc;
						$itppro['tipoppro']   = 'NC';
						$itppro['cod_prv']    = $proveed;
						$itppro['tipo_doc']   = 'FC';
						$itppro['numero']     = $row['numero'];
						$itppro['fecha']      = $actuali;
						$itppro['monto']      = $reteiva;
						$itppro['abono']      = $reteiva;
						$itppro['ppago']      = 0;
						$itppro['reten']      = 0;
						$itppro['cambio']     = 0;
						$itppro['mora']       = 0;
						$itppro['transac']    = $transac;
						$itppro['estampa']    = $estampa;
						$itppro['hora']       = $hora;
						$itppro['usuario']    = $usuario;
						$itppro['preten']     = 0;
						$itppro['creten']     = 0;
						$itppro['breten']     = 0;
						$itppro['reteiva']    = 0;
						$mSQL = $this->db->insert_string('itppro', $itppro);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++;}

						//Crea la nota de debito
						$mnumnd = $this->datasis->fprox_numero('num_nd');
						$sprm=array();
						$sprm['cod_prv']   = 'REIVA';
						$sprm['nombre']    = 'RETENCION DE I.V.A. POR COMPENSAR';
						$sprm['tipo_doc']  = 'ND';
						$sprm['numero']    = $mnumnd;
						$sprm['fecha']     = $actuali;
						$sprm['monto']     = $reteiva;
						$sprm['impuesto']  = 0;
						$sprm['abonos']    = 0;
						$sprm['vence']     = $actuali;
						$sprm['tipo_ref']  = 'FC';
						$sprm['num_ref']   = $row['numero'];
						$sprm['observa1']  = 'RET/IVA DE '.$proveed.' A DOC. FC'.$row['numero'];
						$sprm['estampa']   = $estampa;
						$sprm['hora']      = $hora;
						$sprm['transac']   = $transac;
						$sprm['usuario']   = $usuario;
						$sprm['codigo']    = 'NOCON';
						$sprm['descrip']   = 'NOTA DE CONTABILIDAD';
						$mSQL = $this->db->insert_string('sprm', $sprm);
						$ban=$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++;}

						//Crea la retencion
						$niva    = $this->datasis->fprox_numero('niva');
						$ivaplica= $this->datasis->ivaplica($fecha);

						$riva['nrocomp']    = $niva;
						$riva['emision']    = ($fecha > $actuali) ? $fecha : $actuali;
						$riva['periodo']    = substr($riva['emision'],0,6) ;
						$riva['tipo_doc']   = $row['tipo_doc'];
						$riva['fecha']      = $fecha;
						$riva['numero']     = $row['numero'];
						$riva['nfiscal']    = $row['nfiscal'];
						$riva['afecta']     = $row['fafecta'];
						$riva['clipro']     = $proveed;
						$riva['nombre']     = $row['nombre'];
						$riva['rif']        = $this->datasis->dameval('SELECT rif FROM sprv WHERE proveed='.$this->db->escape($proveed));
						$riva['exento']     = $row['cexento'];
						$riva['tasa']       = $ivaplica['tasa'];
						$riva['tasaadic']   = $ivaplica['sobretasa'];
						$riva['tasaredu']   = $ivaplica['redutasa'];
						$riva['general']    = $row['cgenera'];
						$riva['geneimpu']   = $row['civagen'];
						$riva['adicional']  = $row['cadicio'];
						$riva['adicimpu']   = $row['civaadi'];
						$riva['reducida']   = $row['creduci'];
						$riva['reduimpu']   = $row['civared'];
						$riva['stotal']     = $row['cstotal'];
						$riva['impuesto']   = $row['cimpuesto'];
						$riva['gtotal']     = $row['ctotal'];
						$riva['reiva']      = $reteiva;
						$riva['transac']    = $transac;
						$riva['estampa']    = $estampa;
						$riva['hora']       = $hora;
						$riva['usuario']    = $usuario;
						$mSQL=$this->db->insert_string('riva', $riva);
						$ban =$this->db->simple_query($mSQL);
						if(!$ban){ memowrite($mSQL,'scst'); $error++; }
					}//Fin de la retencion

					//Carga la CxP
					$sprm=array();
					$causado = $this->datasis->fprox_numero('ncausado');
					$sprm['cod_prv']  = $proveed;
					$sprm['nombre']   = $row['nombre'];
					$sprm['tipo_doc'] = $row['tipo_doc'];
					$sprm['numero']   = $row['numero'];
					$sprm['fecha']    = $actuali;
					$sprm['vence']    = $vence;
					$sprm['monto']    = $row['ctotal'];
					$sprm['impuesto'] = $row['cimpuesto'];
					$sprm['abonos']   = $reteiva;
					$sprm['observa1'] = 'FACTURA DE COMPRA';
					$sprm['reteiva']  = $reteiva;
					$sprm['causado']  = $causado;
					$sprm['estampa']  = $estampa;
					$sprm['usuario']  = $usuario;
					$sprm['hora']     = $hora;
					$sprm['transac']  = $transac;
					//$sprm['montasa']  = $row['cimpuesto'];
					//$sprm['impuesto'] = $row['cimpuesto'];

					$mSQL=$this->db->insert_string('sprm', $sprm);
					$ban =$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }
					//Fin de la carga de la CxP

					$mSQL='UPDATE scst SET `actuali`='.$actuali.', `recep`='.$actuali.' WHERE `control`='.$this->db->escape($control);
					$ban=$this->db->simple_query($mSQL);
					if(!$ban){ memowrite($mSQL,'scst'); $error++; }

				}elseif($row['tipo_doc']=='NC'){
					//Falta implementar
				}elseif($row['tipo_doc']=='NE'){
					//Falta implementar
				}
			}else{
				$this->error_string='Compra no existe '.$id;
				return false;
			}
		}else{
			$this->error_string='No se puede actualizar una compra que ya fue actualizada';
			return false;
		}
	}

	function reversar($control=null){
		if(empty($control)){
			$rt=array(
				'status' =>'B',
				'mensaje'=>'Parametros invalidos',
				'pk'     =>null
			);
			echo json_encode($rt);
		}else{
			$rt = $this->_reversar($control);
			if($rt){
				$rt=array(
					'status' =>'A',
					'mensaje'=>'Compra reversada',
					'pk'     =>null
				);
				echo json_encode($rt);
			}else{
				$rt=array(
					'status' =>'B',
					'mensaje'=>utf8_encode($this->error_string),
					'pk'     =>null
				);
				echo json_encode($rt);
			}
		}
	}

	function _reversar($control){
		// Condiciones para reversar
		// Si no tiene transaccion vino por migracion desde otro sistema
		$dbcontrol = $this->db->escape($control);

		$mSQL = "SELECT * FROM scst WHERE control=$dbcontrol";
		$query=$this->db->query($mSQL);

		if($query->num_rows()==0){
			$this->error_string = 'Compra inexistente';
			return false;
		}

		$scst     = $query->row_array();
		$mTRANSAC = $scst['transac'];
		// Si esta actualizada
		$mACTUALI = $scst['actuali'];
		$fecha    = $scst['fecha'];
		$tipo_doc = $scst['tipo_doc'];
		$numero   = $scst['numero'];
		$montonet = $scst['montonet'];
		$reteiva  = $scst['reteiva'];
		$fafecta  = $scst['fafecta'];
		$anticipo = $scst['anticipo'];
		$proveed  = $scst['proveed'];
		$mALMA    = $scst['depo'];

		//********************************
		//
		//    Busca si tiene abonos
		//
		//********************************
		$abonado = 0;
		if ($tipo_doc == 'FC'){
			$mSQL  = "SELECT a.abonos -( b.inicial + b.anticipo + b.reten + b.reteiva) ";
			$mSQL .= "FROM sprm a JOIN scst b ON a.transac=b.transac ";
			$mSQL .= "WHERE a.tipo_doc='$tipo_doc' AND a.numero='$numero' AND a.cod_prv=b.proveed AND a.numero=b.numero ";
			$mSQL .= "AND a.transac='$mTRANSAC' ";
			$abonado = $this->datasis->dameval($mSQL);
		};

		// CONDICIONES QUE DEBEN CUMPLIR PARA PODER REVERSAR
		//Si esta abonada
		if ($abonado > 0.1 ) {
			$this->error_string = 'Compra abonada, elimine el pago primero!';
			return false;
		}
		//Si no tiene transaccion
		if (empty($mTRANSAC)){
			$this->error_string= 'Compra sin nro de transaccion, llame a soporte';
			return false;
		}
		// Si no esta cargada
		if ( $mACTUALI < $fecha ){
			$this->error_string= 'Factura no ha sido cargada';
			return false;
		}

		//Chequea si tiene vehiculos y estan registrados los seriales
		$id_scst=$this->datasis->dameval("SELECT id FROM scst WHERE control=$dbcontrol");
		$SQL="SELECT COUNT(*) AS cana
			FROM itscst AS b
			JOIN sinv   AS c ON b.codigo=c.codigo
			WHERE b.control=$dbcontrol";
		$cana = $this->datasis->dameval($SQL);
		if($cana>0){
			$SQL="SELECT COUNT(*) AS cana
			FROM sinvehiculo AS a
			JOIN sfac AS b ON a.id_sfac=b.id
			WHERE a.id_scst=$id_scst AND b.tipo_doc<>'X'";
			$cana = $this->datasis->dameval($SQL);
			if($cana > 0){
				echo 'Compra con venta vehicular, no se puede reversar';
				return false;
			}else{
				$mSQL = "DELETE FROM sinvehicular WHERE id_scst=$id_scst";
				$this->db->simple_query($mSQL);
			}
		}
		//Fin de la validacion vehicular

		// ******* Borra de a CxC *******\\
		$mSQL = "DELETE FROM sprm WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		if ( $tipo_doc == 'NC' ){
			$mSQL = "UPDATE sprm SET abonos=abonos-$montonet-$reteiva WHERE numero='$fafecta' AND tipo_doc='FC' AND cod_prv='$proveed' ";
			$this->db->simple_query($mSQL);
		}

		$mSQL = "DELETE FROM itppro WHERE transac='$mTRANSAC'";
		$this->db->simple_query($mSQL);

		// ANULA LA RETENCION SI TIENE
		if ( $this->datasis->dameval("SELECT COUNT(*) FROM riva WHERE transac='$mTRANSAC'") > 0 ){
			$mTRANULA = '_'.substr($this->datasis->prox_sql('rivanula'),1,7);
			$this->db->simple_query("UPDATE riva SET transac='$mTRANULA' WHERE transac='$mTRANSAC' ");
		}

		// Busca las Ordenes
		$mORDENES = array();
		$query = $this->db->query("SELECT orden FROM scstordc WHERE compra='$control'");
		if ($query->num_rows() > 0 ){
			foreach( $query->result() as $row ) {
				$mORDENES[] = $row->orden;
			}
		}
		//$query->destroy();

		// DESACTUALIZA INVENTARIO
		$query = $this->db->query("SELECT codigo, cantidad FROM itscst WHERE control='$control'");
		foreach ( $query->result() as $row ) {
			$mTIPO = $this->datasis->dameval("SELECT MID(tipo,1,1) FROM sinv WHERE codigo='".$row->codigo."'");

			if ( $tipo_doc == 'FC' || $tipo_doc =='NE' ) {
				$this->datasis->sinvcarga($row->codigo,  $mALMA, -$row->cantidad);

				// DEBE ARREGLAR EL PROMEDIO BUSCANDO EN KARDEX
				$mSQL = "SELECT promedio FROM costos WHERE codigo='".$row->codigo."' ORDER BY fecha DESC LIMIT 1";
				$mPROM = $this->datasis->dameval($mSQL);
				if ( !empty($mPROM) ) {
					$mSQL = "UPDATE sinv SET pond=$mPROM WHERE codigo='".$row->codigo."'";
					$this->db->simple_query($mSQL);
				}

				if (count($mORDENES) > 0 ){
					$mSALDO = $row->cantidad;
					foreach( $mORDENES as $orden){
						if ($mSALDO > 0 ) {
							$mSQL   = "SELECT recibido  FROM itordc WHERE numero='".$mORDENE."' AND codigo='".$row->codigo."'";
							$mTEMPO = $this->datasis->dameval($mSQL);
							if ( $mTEMPO > 0 ){
								if ($mTEMPO >= $mSALDO ) {
									$mSQL  = "UPDATE itordc SET recibido=recibido-$mSALDO WHERE numero='$orden' AND codigo='".$row->codigo."'";
									$this->db->simple_query($mSQL);
									$mSQL = "UPDATE sinv SET exord=exord+$mSALDO WHERE codigo='".$row->codigo."' ";
									$this->db->simple_query($mSQL);
									$mSALDO = 0;
								} elseif ($mTEMPO < $mSALDO) {
									$mSQL   = "UPDATE itordc SET recibido=recibido-$mTEMPO WHERE numero='$orden' AND codigo='"+$row->codigo+"'";
									$this->db->simple_query($mSQL);
									$mSQL = "UPDATE sinv SET exord=exord+$mTEMPO WHERE codigo='".$row->codigo."' ";
									$mSALDO -= $mTEMPO;
								}
							}
						}
					}
				}
			} else {
				$this->datasis->sinvcarga($row->codigo, $mALMA, $row->cantidad);
			}
		}

		$mSQL = "UPDATE scst SET actuali=0 WHERE control='$control'";
		$this->db->simple_query($mSQL);

		// Carga Ordenes
		if (count($mORDENES) > 0 ) {
			// SUMA A VER SI ESTA COMPLETA
			foreach ( $mORDENES as $orden ) {
				$mSQL = "UPDATE itordc SET recibido=0 WHERE numero='$orden' AND recibido<0 ";
				$this->db->simple_query($mSQL);
				$mSQL = "SELECT COUNT(*) FROM itordc WHERE numero='$orden' AND recibido>0";
				if($this->datasis->dameval($mSQL) == 0){
					$mSQL = "UPDATE ordc SET status='PE' WHERE numero='$orden' ";
				}else{
					$mSQL = "UPDATE ordc SET status='BA' WHERE numero='$orden' ";
				}
				$this->db->simple_query($mSQL);
			}
		}
		return true;
	}

	function creadseri($cod_prov,$factura){
		$cod_prove=$this->db->escape($cod_prov);
		$facturae =$this->db->escape($factura);
		$control=$this->datasis->fprox_numero('ntemp');
		$control=substr($control,1,7).'_';
		$controle=$this->db->escape($control);

		$query="
		INSERT INTO itscst (`numero`,`proveed`,`codigo`,`descrip`,`cantidad`,`control`,`iva`,`costo`,`importe`)
		SELECT refe2,clipro,b.codigo,b.descrip,SUM(b.cant) cant,$controle,c.iva,c.ultimo,SUM(b.cant)*c.ultimo
		FROM recep a
		JOIN seri b ON a.recep=b.recep
		JOIN sinv c ON b.codigo=c.codigo
		WHERE origen='scst' AND a.refe2=$facturae AND clipro=$cod_prove
		GROUP BY b.codigo";

		$this->db->query($query);

		$query="
		INSERT INTO scst (`numero`,`proveed`,`control`,`serie`)
		VALUES ($facturae,$cod_prove,$controle,$facturae)";
		$this->db->query($query);
		redirect("compras/scst/dataedit/modify/$control");
	}

	function _pre_delete($do){
		$recep  =strtotime($do->get('recep'));
		$fecha  =strtotime($do->get('fecha'));
		$actuali=strtotime($do->get('actuali'));

		if ($actuali >= $fecha){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='No se puede eliminar una factura cargada, debe reversarla primero';
			return false;
		}
		return true;
	}

	function _pre_insert($do){

		$control=$do->get('control');
		$transac=$do->get('transac');
		$tolera =0.07; //Tolerancia entre los items y el encabezado

		if(substr($control,7,1)=='_') $control = $this->datasis->fprox_numero('nscst');
		if(empty($control)) $control = $this->datasis->fprox_numero('nscst');
		if(empty($transac)) $transac = $this->datasis->fprox_numero('ntransa');

		$fecha   = $do->get('fecha');
		$numero  = substr($do->get('serie'),-8);
		$usuario = $do->get('usuario');
		$proveed = $do->get('proveed');
		$depo    = $do->get('depo');
		$estampa = date('Ymd');
		$hora    = date('H:i:s');
		$alicuota=$this->datasis->ivaplica($fecha);

		$iva=$stotal=0;
		$cgenera=$civagen=$creduci=$civared=$cadicio=$civaadi=$cexento=0;
		$cana=$do->count_rel('itscst');
		for($i=0;$i<$cana;$i++){
			$itcodigo  = $do->get_rel('itscst','codigo'  ,$i);
			$itcana    = $do->get_rel('itscst','cantidad',$i);
			$itprecio  = $do->get_rel('itscst','costo'   ,$i);
			$itiva     = $do->get_rel('itscst','iva'     ,$i);

			//$itimporte = $itprecio*$itcana;
			$itimporte = $do->get_rel('itscst','importe',$i);
			$iiva      = $itimporte*($itiva/100);

			$mSQL='SELECT ultimo,existen,pond,standard,formcal,margen1,margen2,margen3,margen4,precio1,precio2,precio3,precio4,iva FROM sinv WHERE codigo='.$this->db->escape($itcodigo);
			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$costo_pond=(($row->pond*$row->existen)+($itcana*$itprecio))/($itcana+$row->existen);
				$costo_ulti=$itprecio;

				$costo=$this->_costos($row->formcal,$costo_pond,$costo_ulti,$row->standard);
			}

			for($o=1;$o<5;$o++){
				$obj='margen'.$o;
				$pob='precio'.$o;

				$cmargen=$row->$obj;
				if($cmargen>=100){

					//Si el margen esta malo intenta calcularlo respetando el precio
					if($row->$pob>0){
						$cmargen=100-($itprecio*(100+$row->iva)/$row->$pob);

						if($cmargen<100){
							//El producto tiene arreglo
							$mSQL="UPDATE sinv SET
								$obj=ROUND(100-((IF(formcal='U',ultimo,IF(formcal='P',pond,GREATEST(pond,ultimo)))*(100+iva))/$pob),2)
								WHERE codigo=".$this->db->escape($itcodigo);
							$this->db->simple_query($mSQL);
						}
					}

					//Si no puede hacer nada manda error.
					if($cmargen>=100){
						$do->error_message_ar['pre_ins']="El producto $itcodigo presenta problema con los márgenes, por favor cambielos por el módulo de maestro de inventario.";
						return false;
					}
				}else{
					$pp=(($costo*100)/(100-$cmargen))*(1+($itiva/100));
				}
				$do->set_rel('itscst','precio'.$o ,$pp,$i);
			}

			$itimporte = $do->get_rel('itscst','importe' ,$i);
			//$do->set_rel('itscst','importe' ,$itimporte,$i);
			$do->set_rel('itscst','montoiva',$iiva     ,$i);
			$do->set_rel('itscst','ultimo'  ,$row->ultimo,$i);
			$do->set_rel('itscst','fecha'   ,$fecha    ,$i);
			$do->set_rel('itscst','numero'  ,$numero   ,$i);
			$do->set_rel('itscst','proveed' ,$proveed  ,$i);
			$do->set_rel('itscst','depo'    ,$depo     ,$i);
			$do->set_rel('itscst','control' ,$control  ,$i);
			$do->set_rel('itscst','transac' ,$transac  ,$i);
			$do->set_rel('itscst','usuario' ,$usuario  ,$i);
			$do->set_rel('itscst','hora'    ,$hora     ,$i);
			$do->set_rel('itscst','estampa' ,$estampa  ,$i);

			if($itiva-$alicuota['tasa']==0){
				$cgenera += $itimporte;
				$civagen += $iiva;
			}elseif($itiva-$alicuota['redutasa']==0){
				$creduci += $itimporte;
				$civared += $iiva;
			}elseif($itiva-$alicuota['sobretasa']==0){
				$cadicio += $itimporte;
				$civaadi += $iiva;
			}else{
				$cexento += $itimporte;
			}

			$iva    += $iiva;
			$stotal += $itimporte;
		}
		$gtotal=$stotal+$iva;
		$do->set('numero'   , $numero);
		$do->set('control'  , $control);
		$do->set('estampa'  , $estampa);
		$do->set('hora'     , $hora);
		$do->set('transac'  , $transac);

		//$montonet = $do->get('montonet');
		$montotot = $do->get('montotot');
		$montoiva = $do->get('montoiva');
		$cm=false;
		if(abs($montotot-$stotal)<=$tolera){
			$cm     = true;
			$stotal = $montotot;
		}
		if(abs($montoiva-$iva)<=$tolera){
			$cm  = true;
			$iva = $montoiva;
		}
		if($cm){
			$gtotal=$stotal+$iva;
		}

		$do->set('montotot' , round($stotal,2));
		$do->set('montonet' , round($gtotal,2));
		$do->set('montoiva' , round($iva   ,2));
		$do->set('cgenera'  , round($cgenera,2));
		$do->set('civagen'  , round($civagen,2));
		$do->set('creduci'  , round($creduci,2));
		$do->set('civared'  , round($civared,2));
		$do->set('cadicio'  , round($cadicio,2));
		$do->set('civaadi'  , round($civaadi,2));
		$do->set('cexento'  , round($cexento,2));
		$do->set('ctotal'   , round($gtotal ,2));
		$do->set('cstotal'  , round($stotal ,2));
		$do->set('cimpuesto', round($iva    ,2));

		//Para la retencion de iva si aplica
		$contribu= trim($this->datasis->traevalor('CONTRIBUYENTE'));
		$rif     = trim($this->datasis->traevalor('RIF'));
		if($contribu=='ESPECIAL' && strtoupper($rif[0])!='V'){
			$por_rete=$this->datasis->dameval('SELECT reteiva FROM sprv WHERE proveed='.$this->db->escape($proveed));
			if($por_rete!=100){
				$por_rete=0.75;
			}else{
				$por_rete=$por_rete/100;
			}
			$do->set('reteiva', round($iva*$por_rete,2));
		}
		//fin de la retencion

		//$do->set('estampa', 'CURDATE()', FALSE);
		//$do->set('hora'   , 'CURRENT_TIME()', FALSE);

		//Para picar la observacion en varios campos
		$obs=$do->get('observa1');
		$ff = strlen($obs);
		for($i=0; $i<$ff; $i=$i+60){
			$ind=($i % 60)+1;
			$do->set('observa'.$ind,substr($obs,$i,60));
			if($i>180) break;
		}
		return true;
	}

	//Chequea que el dia no sea superior a hoy

	function _post_update($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('scst',"Compra $codigo control $control MODIFICADA");
	}

	function chddate($fecha){
		$control   = $this->uri->segment($this->uri->total_segments()-1);
		$dbcontrol = $this->db->escape($control);
		$f=$this->datasis->dameval('SELECT fecha FROM scst WHERE control='.$dbcontrol);

		$d1 = DateTime::createFromFormat(RAPYD_DATE_FORMAT, $fecha);              //Fecha de recepcion
		$d2 = new DateTime();                                                     //Fecha de hoy
		$d3 = DateTime::createFromFormat(RAPYD_DATE_FORMAT, dbdate_to_human($f)); //Fecha de la factura

		if($d2>=$d1 && $d1>=$d3){
			return true;
		}elseif($d1<$d3){
			$this->validation->set_message('chddate', 'No se puede recepcionar una compra con fecha superior al la fecha de la factura '.$d3->format(RAPYD_DATE_FORMAT).'.');
		}else{
			$this->validation->set_message('chddate', 'No se puede recepcionar una compra con fecha superior al d&iacute;a de hoy.');
		}
		return false;
	}

	function printrete($id_scst){
		$sel=array('b.id');
		$this->db->select($sel);
		$this->db->from('scst AS a');
		$this->db->join('riva AS b','a.transac=b.transac');
		$this->db->where('a.id' , $id_scst);
		$mSQL_1 = $this->db->get();

		if ($mSQL_1->num_rows() == 0){ show_error('Retención no encontrada');}

		$row = $mSQL_1->row();
		$id  = $row->id;
		redirect("formatos/ver/RIVA/$id");
	}

	function _pond($existen,$itcana,$pond,$ultimo){
		if($itcana+$existen==0) return $ultimo;
		return (($pond*$existen)+($itcana*$ultimo))/($itcana+$existen);
	}

	function _costos($formcal,$costo_pond,$costo_ulti,$costo_stan){
		switch($formcal){
			case 'P':
				$costo=$costo_pond;
				break;
			case 'U':
				$costo=$costo_ulti;
				break;
			case 'S':
				$costo=$costo_stan;
				break;
			default:
				$costo=($costo_pond>$costo_ulti) ? $costo_pond : $costo_ulti;
		}
		return $costo;
	}

	function _post_insert($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('scst',"Compra $codigo control $control CREADA");
	}

	function _post_cxp_update($do){
		return false;
	}


	function _pre_update($do){
		$actuali= new DateTime($do->get('actuali'));
		$fecha  = new DateTime($do->get('fecha'));

		if($actuali >= $fecha){
			$do->error_message_ar['pre_upd'] = $do->error_message_ar['update']='No se puede modificar una compra actualizada, debe reversarla primero.';
			return false;
		}
		$this->_pre_insert($do);
	}

	function _post_delete($do){
		$codigo =$do->get('numero');
		$control=$do->get('control');
		logusu('scst',"Compra ${codigo} Control ${control} ELIMINADA");
	}

	function _pre_vehi_insert($do){ return false; }
	function _pre_vehi_delete($do){ return false;}

	function _pre_vehi_update($do){
		$rel='sinvehiculo';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$id_sfac  = $do->get_rel($rel,'id_sfac', $i);
			if(empty($id_sfac)){
				$do->rel_rm_field($rel,'id_sfac',$i);
			}
		}

		return true;
	}

	function _post_vehi_update($do){
		$codigo  = $do->get('numero');
		$control = $do->get('control');
		logusu('scst',"Compra $codigo control $control SERIALES CAMBIADOS");
	}

	function instalar(){
		if (!$this->db->table_exists('sinvehiculo')) {
			$mSQL="CREATE TABLE `sinvehiculo` (
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`id_sfac` INT(10) NULL DEFAULT NULL,
				`id_scst` INT(10) NULL DEFAULT NULL,
				`codigo_sinv` VARCHAR(15) NULL DEFAULT NULL,
				`modelo` VARCHAR(50) NULL DEFAULT NULL,
				`color` VARCHAR(50) NULL DEFAULT NULL,
				`motor` VARCHAR(50) NULL DEFAULT NULL,
				`carroceria` VARCHAR(50) NULL DEFAULT NULL,
				`uso` VARCHAR(50) NULL DEFAULT NULL,
				`tipo` VARCHAR(50) NULL DEFAULT NULL,
				`clase` VARCHAR(50) NULL DEFAULT NULL,
				`anio` VARCHAR(50) NULL DEFAULT NULL,
				`peso` DECIMAL(10,2) NULL DEFAULT '0.00',
				`transmision` VARCHAR(50) NULL DEFAULT NULL,
				`placa` VARCHAR(10) NULL DEFAULT NULL,
				`precioplaca` DECIMAL(10,2) NULL DEFAULT NULL,
				`tasa` DECIMAL(10,2) NULL DEFAULT NULL,
				`nombre` VARCHAR(200) NULL DEFAULT NULL,
				`casa` VARCHAR(100) NULL DEFAULT NULL,
				`calle` VARCHAR(100) NULL DEFAULT NULL,
				`urb` VARCHAR(100) NULL DEFAULT NULL,
				`ciudad` VARCHAR(100) NULL DEFAULT NULL,
				`municipio` VARCHAR(100) NULL DEFAULT NULL,
				`estado` VARCHAR(100) NULL DEFAULT NULL,
				`cpostal` VARCHAR(10) NULL DEFAULT NULL,
				`ctelefono1` VARCHAR(4) NULL DEFAULT NULL,
				`telefono1` VARCHAR(8) NULL DEFAULT NULL,
				`ctelefono2` VARCHAR(4) NULL DEFAULT NULL,
				`telefono2` VARCHAR(8) NULL DEFAULT NULL,
				`distrito` VARCHAR(100) NULL DEFAULT NULL,
				`aseguradora` VARCHAR(200) NULL DEFAULT NULL,
				`vence` DATE NULL DEFAULT NULL,
				`nomban` VARCHAR(200) NULL DEFAULT NULL,
				`banrif` VARCHAR(20) NULL DEFAULT NULL,
				`representante` VARCHAR(100) NULL DEFAULT NULL,
				`concesionario` VARCHAR(100) NULL DEFAULT NULL,
				`concesionariorif` VARCHAR(20) NULL DEFAULT NULL,
				`poliza` VARCHAR(50) NULL DEFAULT NULL,
				`neumaticos` INT(11) NULL DEFAULT NULL,
				`tipo_neumatico` VARCHAR(50) NULL DEFAULT NULL,
				`distanciaeje` FLOAT NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			)
			COMMENT='Vehiculos a la venta'
			COLLATE='latin1_swedish_ci'
			ENGINE=MyISAM";
			$this->db->simple_query($mSQL);
		}

		if ( !$this->datasis->iscampo('scst','id') ) {
			$this->db->simple_query('ALTER TABLE scst DROP PRIMARY KEY');
			$this->db->simple_query('ALTER TABLE scst ADD UNIQUE INDEX control (control)');
			$this->db->simple_query('ALTER TABLE scst ADD COLUMN id INT(11) NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');

			$this->db->simple_query("update tmenus set secu=1 where titulo='Incluye'");
			$this->db->simple_query("update tmenus set secu=2 where titulo='Modifica'");
			$this->db->simple_query("update tmenus set secu=3 where titulo='Prox'");
			$this->db->simple_query("update tmenus set secu=4 where titulo='Ante'");
			$this->db->simple_query("update tmenus set secu=5 where titulo='Elimina'");
			$this->db->simple_query("update tmenus set secu=6 where titulo='Busca'");
			$this->db->simple_query("update tmenus set secu=7 where titulo='Tabla'");
			$this->db->simple_query("update tmenus set secu=8 where titulo='Lista'");
			$this->db->simple_query("update tmenus set secu=9 where titulo='Otros'");
		};

	}
}