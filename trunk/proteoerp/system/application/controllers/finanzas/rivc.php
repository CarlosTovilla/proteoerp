<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include('common.php');

class rivc extends Controller {
	var $titp='Retenciones de Clientes';
	var $tits='Retenciones de Clientes';
	var $url ='finanzas/rivc/';

	function rivc(){
		parent::Controller();
		$this->load->library('rapyd');
		$this->datasis->modulo_id('511',1);
		$this->instalar();
	}

	function index(){
		redirect($this->url.'filteredgrid');
	}

	function filteredgrid(){
		$this->rapyd->load('datafilter','datagrid');
		$this->rapyd->uri->keep_persistence();

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

		$filter = new DataFilter($this->titp, 'rivc');

		$filter->cliente = new inputField('Cliente', 'cod_cli');
		$filter->cliente->size = 8;
		$filter->cliente->append($boton);
		//$filter->cliente->group = '2';

		$filter->periodo = new inputField('Comprobante','periodo');
		$filter->periodo->rule      ='max_length[8]';
		$filter->periodo->size      =4;
		$filter->periodo->maxlength =4;

		$filter->nrocomp = new inputField('Comprobante','nrocomp');
		$filter->nrocomp->rule      ='max_length[8]';
		$filter->nrocomp->size      =10;
		$filter->nrocomp->maxlength =8;
		$filter->nrocomp->in='periodo';

		$filter->emision = new dateField('Emisi&oacute;n','emision');
		$filter->emision->rule      ='chfecha';
		$filter->emision->size      =10;
		$filter->emision->maxlength =8;
		$filter->emision->group = 'Fecha';

		$filter->fecha = new dateField('Recepci&oacute;n','fecha');
		$filter->fecha->rule      ='chfecha';
		$filter->fecha->size      =10;
		$filter->fecha->maxlength =8;
		$filter->fecha->group = 'Fecha';

		$filter->rif = new inputField('RIF','rif');
		$filter->rif->rule      ='max_length[14]';
		$filter->rif->size      =16;
		$filter->rif->maxlength =14;

		$filter->operacion = new radiogroupField('Operaci&oacute;n', 'operacion', array(''=>'Todos','R'=>'Reintegro','A'=>'Anticipo','P'=>'CxP'));

		$filter->buttons('reset', 'search');
		$filter->build();

		$uri = anchor($this->url.'dataedit/show/<#id#>','<#nrocomp#>');

		$grid = new DataGrid('');
		$grid->order_by('id','desc');
		$grid->per_page = 40;

		$grid->column_orderby('Comprobante'     ,$uri,'nrocomp','align="left"');
		$grid->column_orderby('Anulado'         ,'anulado','anulado','align="center"');
		$grid->column_orderby('Emisi&oacute;n'  ,'<dbdate_to_human><#emision#></dbdate_to_human>','emision','align="center"');
		$grid->column_orderby('Recepci&oacute;n','<dbdate_to_human><#fecha#></dbdate_to_human>'  ,'fecha','align="center"');
		$grid->column_orderby('Cliente'         ,'cod_cli','cod_cli','align="left"');
		$grid->column_orderby('Nombre'          ,'nombre' ,'nombre','align="left"');
		$grid->column_orderby('RIF'             ,'rif'    ,'rif'   ,'align="left"');
		$grid->column_orderby('Impuesto'        ,'<nformat><#impuesto#></nformat>','impuesto','align="right"');
		//$grid->column_orderby('Total'         ,'<nformat><#gtotal#></nformat>'  ,'gtotal','align="right"');
		$grid->column_orderby('Monto Ret.'    ,'<nformat><#reiva#></nformat>'   ,'reiva','align="right"');

		$grid->add($this->url.'dataedit/create');
		$grid->build();

		$data['filtro']  = $filter->output;
		$data['content'] = $grid->output;
		$data['head']    = $this->rapyd->get_head().script('jquery.js');
		$data['title']   = heading($this->titp);
		$this->load->view('view_ventanas', $data);
	}

	function dataedit(){
		$this->rapyd->uri->keep_persistence();
		$this->rapyd->load('datadetails','dataobject');
		$usrdata=common::_traedatausr();

		$do = new DataObject('rivc');
		//$do->pointer('scli' ,'scli.cliente=rivc.cod_cli','sprv.tipo AS sprvtipo, sprv.reteiva AS sprvreteiva','left');
		$do->rel_one_to_many('itrivc' ,'itrivc' ,array('id'=>'idrivc'));

		$edit = new DataDetails($this->tits, $do);

		$edit->back_url = site_url($this->url.'filteredgrid');

		$edit->post_process('insert','_post_insert');
		$edit->post_process('update','_post_update');
		$edit->post_process('delete','_post_delete');

		$edit->pre_process('insert','_pre_insert');
		$edit->pre_process('update','_pre_update');
		$edit->pre_process('delete','_pre_delete');

		$edit->nrocomp = new inputField('Comprobante','nrocomp');
		$edit->nrocomp->rule='max_length[8]|required';
		$edit->nrocomp->size =10;
		$edit->nrocomp->maxlength = '8';
		$edit->nrocomp->autocomplete = false;

		$edit->emision = new dateField('Fecha de Emisi&oacute;n','emision');
		$edit->emision->rule='chfecha|required';
		$edit->emision->size =10;
		$edit->emision->maxlength =8;

		$edit->periodo = new inputField('Per&iacute;odo','periodo');
		$edit->periodo->rule='max_length[6]|required';
		$edit->periodo->size =6;
		$edit->periodo->insertValue=date('Ym');
		$edit->periodo->maxlength =6;

		$edit->fecha = new dateField('Fecha de Recepci&oacute;n','fecha');
		$edit->fecha->rule='chfecha|required';
		$edit->fecha->insertValue = date('Y-m-d');
		$edit->fecha->size =10;
		$edit->fecha->maxlength =8;

		$edit->cod_cli = new inputField('Cliente','cod_cli');
		$edit->cod_cli->rule='max_length[5]|required|strtoupper|existescli';
		$edit->cod_cli->size =10;
		//$edit->cod_cli->maxlength =5;

		$edit->nombre = new hiddenField('Nombre','nombre');
		$edit->nombre->rule='max_length[200]';
		$edit->nombre->size =42;
		$edit->nombre->maxlength =200;

		$edit->rif = new hiddenField('RIF','rif');
		$edit->rif->rule='max_length[14]|strtoupper';
		$edit->rif->size =16;
		$edit->rif->maxlength =14;
		$edit->rif->autocomplete = false;

		$edit->operacion = new radiogroupField('Operaci&oacute;n', 'operacion', array('R'=>'Reintegrar','A'=>'Crear anticipo','P'=>'Crear CxP'));
		$edit->operacion->insertValue='A';
		$edit->operacion->rule='required';

		$edit->exento = new inputField('Monto Exento','exento');
		$edit->exento->rule='max_length[15]|numeric';
		$edit->exento->css_class='inputnum';
		$edit->exento->size =17;
		$edit->exento->maxlength =15;

		$edit->tasa = new inputField('tasa','tasa');
		$edit->tasa->rule='max_length[5]|numeric';
		$edit->tasa->css_class='inputnum';
		$edit->tasa->size =7;
		$edit->tasa->maxlength =5;

		$edit->general = new inputField('general','general');
		$edit->general->rule='max_length[15]|numeric';
		$edit->general->css_class='inputnum';
		$edit->general->size =17;
		$edit->general->maxlength =15;

		$edit->geneimpu = new inputField('geneimpu','geneimpu');
		$edit->geneimpu->rule='max_length[15]|numeric';
		$edit->geneimpu->css_class='inputnum';
		$edit->geneimpu->size =17;
		$edit->geneimpu->maxlength =15;

		$edit->tasaadic = new inputField('tasaadic','tasaadic');
		$edit->tasaadic->rule='max_length[5]|numeric';
		$edit->tasaadic->css_class='inputnum';
		$edit->tasaadic->size =7;
		$edit->tasaadic->maxlength =5;

		$edit->adicional = new inputField('adicional','adicional');
		$edit->adicional->rule='max_length[15]|numeric';
		$edit->adicional->css_class='inputnum';
		$edit->adicional->size =17;
		$edit->adicional->maxlength =15;

		$edit->adicimpu = new inputField('adicimpu','adicimpu');
		$edit->adicimpu->rule='max_length[15]|numeric';
		$edit->adicimpu->css_class='inputnum';
		$edit->adicimpu->size =17;
		$edit->adicimpu->maxlength =15;

		$edit->tasaredu = new inputField('tasaredu','tasaredu');
		$edit->tasaredu->rule='max_length[5]|numeric';
		$edit->tasaredu->css_class='inputnum';
		$edit->tasaredu->size =7;
		$edit->tasaredu->maxlength =5;

		$edit->reducida = new inputField('reducida','reducida');
		$edit->reducida->rule='max_length[15]|numeric';
		$edit->reducida->css_class='inputnum';
		$edit->reducida->size =17;
		$edit->reducida->maxlength =15;

		$edit->reduimpu = new inputField('reduimpu','reduimpu');
		$edit->reduimpu->rule='max_length[15]|numeric';
		$edit->reduimpu->css_class='inputnum';
		$edit->reduimpu->size =17;
		$edit->reduimpu->maxlength =15;

		$edit->stotal = new hiddenField('Sub-total','stotal');
		$edit->stotal->rule='max_length[15]|numeric';
		$edit->stotal->css_class='inputnum';
		$edit->stotal->size =17;
		$edit->stotal->maxlength =15;

		$edit->impuesto = new hiddenField('Impuesto','impuesto');
		$edit->impuesto->rule='max_length[15]|numeric';
		$edit->impuesto->css_class='inputnum';
		$edit->impuesto->size =17;
		$edit->impuesto->maxlength =15;

		$edit->gtotal = new hiddenField('Total de facturas','gtotal');
		$edit->gtotal->rule='max_length[15]|numeric';
		$edit->gtotal->css_class='inputnum';
		$edit->gtotal->size =17;
		$edit->gtotal->maxlength =15;

		$edit->reiva = new hiddenField('Total Retenido','reiva');
		$edit->reiva->rule='max_length[15]|numeric';
		$edit->reiva->css_class='inputnum';
		$edit->reiva->size =17;
		$edit->reiva->maxlength =15;

		$edit->estampa = new autoUpdateField('estampa' ,date('Ymd'), date('Ymd'));
		$edit->hora    = new autoUpdateField('hora',date('H:i:s'), date('H:i:s'));
		$edit->usuario = new autoUpdateField('usuario',$this->session->userdata('usuario'),$this->session->userdata('usuario'));
		$edit->origen  = new autoUpdateField('origen' ,'R','R');

		$edit->modificado = new inputField('modificado','modificado');
		$edit->modificado->rule='max_length[8]';
		$edit->modificado->size =10;
		$edit->modificado->maxlength =8;

		//****************************
		//Inicio del Detalle
		//****************************
		$edit->it_tipo_doc = new hiddenField('tipo_doc','tipo_doc_<#i#>');
		$edit->it_tipo_doc->db_name='tipo_doc';
		$edit->it_tipo_doc->rule='max_length[2]|required';
		$edit->it_tipo_doc->size =4;
		$edit->it_tipo_doc->maxlength =1;
		$edit->it_tipo_doc->rel_id ='itrivc';

		$edit->it_fecha = new dateonlyField('fecha','fecha_<#i#>');
		$edit->it_fecha->db_name='fecha';
		$edit->it_fecha->rule='required';
		$edit->it_fecha->size =11;
		$edit->it_fecha->maxlength =10;
		$edit->it_fecha->rel_id ='itrivc';
		$edit->it_fecha->type='inputhidden';

		$edit->it_numero = new inputField('numero','numero_<#i#>');
		$edit->it_numero->db_name='numero';
		$edit->it_numero->rule='max_length[12]|required|callback_chrepetidos|callback_chfac[<#i#>]|callback_chriva[<#i#>]';
		$edit->it_numero->size =14;
		$edit->it_numero->maxlength =12;
		$edit->it_numero->rel_id ='itrivc';
		$edit->it_numero->autocomplete = false;

		$edit->it_stotal = new inputField('stotal','stotal_<#i#>');
		$edit->it_stotal->db_name='stotal';
		$edit->it_stotal->rule='max_length[15]|numeric';
		$edit->it_stotal->css_class='inputnum';
		$edit->it_stotal->size =17;
		$edit->it_stotal->maxlength =15;
		$edit->it_stotal->rel_id ='itrivc';
		$edit->it_stotal->showformat ='decimal';

		$edit->it_impuesto = new hiddenField('impuesto','impuesto_<#i#>');
		$edit->it_impuesto->db_name='impuesto';
		$edit->it_impuesto->rule='max_length[15]|numeric';
		$edit->it_impuesto->css_class='inputnum';
		$edit->it_impuesto->size =17;
		$edit->it_impuesto->maxlength =15;
		$edit->it_impuesto->showformat ='decimal';
		$edit->it_impuesto->rel_id ='itrivc';

		$edit->it_gtotal = new hiddenField('gtotal','gtotal_<#i#>');
		$edit->it_gtotal->db_name='gtotal';
		$edit->it_gtotal->rule='max_length[15]|numeric';
		$edit->it_gtotal->css_class='inputnum';
		$edit->it_gtotal->size =17;
		$edit->it_gtotal->maxlength =15;
		$edit->it_gtotal->rel_id ='itrivc';
		$edit->it_gtotal->showformat ='decimal';
		$edit->it_gtotal->autocomplete = false;

		$edit->it_reiva = new inputField('reiva','reiva_<#i#>');
		$edit->it_reiva->db_name='reiva';
		$edit->it_reiva->rule='max_length[15]|nocero|numeric';
		$edit->it_reiva->css_class='inputnum';
		$edit->it_reiva->size =17;
		$edit->it_reiva->maxlength =15;
		$edit->it_reiva->rel_id ='itrivc';
		$edit->it_reiva->onkeyup ='totalizar()';
		$edit->it_reiva->autocomplete = false;
		$edit->it_reiva->disable_paste= true;
		$edit->it_reiva->showformat ='decimal';
		//****************************
		//Fin del Detalle
		//****************************

		$edit->codbanc = new dropdownField('Caja','codbanc');
		$edit->codbanc->option('','Seleccionar');
		$edit->codbanc->options("SELECT codbanc, CONCAT_WS('-',codbanc,banco) AS label FROM banc WHERE activo='S' AND tbanco='CAJ' ORDER BY codbanc");
		$edit->codbanc->rule='max_length[5]|condi_required|callback_chcaja';
		$edit->codbanc->style='width:200px;';

		/*if($edit->_status=='show' || $edit->_status=='delete'){
			//chequea que pueda aparecer el boton
			$ide=$this->db->escape($edit->get_from_dataobjetct('id'));
			$query="SELECT SUM(monto-abonos) abonos
			FROM  rivc a
			JOIN itrivc b  ON a.id=b.idrivc
			JOIN smov c ON b.transac=c.transac
			WHERE a.id=$ide AND c.tipo_doc='AN'";
			$xabonar=$this->datasis->dameval($query);

			if($xabonar>0){
				if($edit->_status!='delete'){
					$action = "javascript:window.location='".site_url('finanzas/rivc/reintegrar/'.$edit->get_from_dataobjetct('id'))."'";
					$edit->button('btn_reintegrar', 'Reintegrar', $action, 'TR');

					$action = "javascript:window.location='".site_url('finanzas/rivc/convcxp/'.$edit->get_from_dataobjetct('id'))."'";
					$edit->button('btn_convcxp', 'Convertir a CxP', $action, 'TR');
				}

				$edit->buttons('delete');
			}
		}*/

		$edit->buttons('save', 'undo','delete', 'back','add_rel','add');
		//$edit->buttons('save', 'undo', 'back','add_rel');
		$edit->build();

		$conten['form']  =& $edit;
		$data['content'] = $this->load->view('view_rivc', $conten,true);
		$data['head']    = $this->rapyd->get_head();
		$data['head']   .= script('jquery.js');
		$data['head']   .= script('jquery-ui.js');
		$data['head']   .= script('plugins/jquery.numeric.pack.js');
		$data['head']   .= script('plugins/jquery.floatnumber.js');
		$data['head']   .= phpscript('nformat.js');
		$data['head']   .= style('redmond/jquery-ui-1.8.1.custom.css');
		$data['head']   .= script('plugins/jquery.ui.autocomplete.autoSelectOne.js');
		$data['title']   = heading($this->tits);
		$this->load->view('view_ventanas', $data);
	}

	//*****************************
	// Metodos de chequeo
	//*****************************
	function chrepetidos($numero){
		if(!isset($this->ch_repetido)) $this->ch_repetido=array();

		if(array_search($numero,$this->ch_repetido)===false){
			$this->ch_repetido[]=$numero;
			return true;
		}else{
			$this->validation->set_message('chrepetidos', 'La factura '.$numero.' esta repetida');
			return false;
		}
	}

	function chcajero($cajero){
		$op=$this->input->post('operacion');
		if($op=='R' && empty($cajero)){
			$this->validation->set_message('chcajero', 'El campo %s es obligatorio cuando la operaci&oacute;n es reintegro');
			return false;
		}
		return true;
	}

	//Chequea que no se repita la retencion cuando se hace por DataSIS
	function chriva($numero,$ind){
		$cod_cli = $this->input->post('cod_cli');
		$tipo_doc= $this->input->post('tipo_doc_'.$ind);
		$fecha   = $this->input->post('fecha');
		$tipo_doc=($tipo_doc=='F')? 'FC':'NC';

		$this->db->where('numero'  , $numero  );
		$this->db->where('tipo_doc', $tipo_doc);
		$this->db->where('nroriva >', 0);
		$this->db->from('itccli');
		$cana=$this->db->count_all_results();

		if($cana>0){
			$this->validation->set_message('chriva', 'El documento '.$numero.' ya le fue aplicada una retenci&oacute;n de iva');
			return false;
		}
		return true;
	}

	function chfac($numero,$ind){
		$cod_cli = $this->input->post('cod_cli');
		$tipo_doc= $this->input->post('tipo_doc_'.$ind);
		$fecha   = $this->input->post('fecha');
		$ww=' WHERE numero='.$this->db->escape($numero).' AND cod_cli='.$this->db->escape($cod_cli).' AND tipo_doc='.$this->db->escape($tipo_doc);
		$mSQL='SELECT COUNT(*) FROM sfac '.$ww;
		$cana=$this->datasis->dameval($mSQL);

		if($cana!=1){
			$this->validation->set_message('chfac', 'El documento '.$numero.' no pertenece al cliente '.$cod_cli);
			return false;
		}

		$mSQL='SELECT COUNT(*) AS cana FROM rivc AS a JOIN itrivc AS b ON a.id=b.idrivc WHERE a.anulado=\'N\' AND b.numero='.$this->db->escape($numero).' AND b.tipo_doc='.$this->db->escape($tipo_doc);
		$cana=$this->datasis->dameval($mSQL);
		if($cana>0){
			$this->validation->set_message('chfac', 'El documento '.$numero.' ya se le aplico una retenci&oacute;n');
			return false;
		}

		/*if($tipo_doc=='D'){
			$mSQL  = 'SELECT fecha FROM sfac '.$ww;
			$ffech = $this->datasis->dameval($mSQL);
			$ar_dfech = explode('-',$ffech);
			$ar_rfech = explode('/',$fecha);

			$d_dfech=(ceil($ar_dfech[2]/15)>2)? 2 : 1;
			$d_rfech=(ceil($ar_rfech[0]/15)>2)? 2 : 1;

			if($ar_dfech[0]!=$ar_rfech[2] || $ar_dfech[1]!=$ar_rfech[1] || $d_dfech!=$d_rfech){
				$this->validation->set_message('chfac', 'El documento '.$numero.' esta fuera de per&iacute;odo');
				return false;
			}
		}*/
		return true;
	}

	function chcaja($caja){
		$op=$this->input->post('operacion');
		if($op=='R' && empty($caja)){
			$this->validation->set_message('chcaja', 'El campo %s es obligatorio cuando la operaci&oacute;n es reintegro');
			return false;
		}
		return true;
	}

	function chobligaban($val){
		$ban=$this->input->post('cargo');
		$tipo=common::_traetipo($ban);
		if($tipo!='CAJ'){
			if(empty($val)){
				$this->validation->set_message('chobligaban', 'El campo %s es obligatorio cuando el cargo es a un banco');
				return false;
			}
		}
		return true;
	}

	function chclave($clave){
		$cajero  = $this->input->post('cajero');
		$dbclave = $this->db->escape($clave);
		$dbcajero= $this->db->escape($cajero);
		$op=$this->input->post('operacion');

		if(empty($cajero) || $op!='R'){
			return true;
		}
		$ch    = $this->datasis->dameval("SELECT COUNT(*) FROM scaj WHERE cajero=$dbcajero AND clave=$dbclave");
		if($ch>0){
			return true;
		}
		$this->validation->set_message('chclave', 'Clave o cajeo inv&aacute;lido');
		return false;
	}

	function chdupli($numero){
		$scli=$this->input->post('cod_cli');
		$mSQL='SELECT COUNT(*) FROM rivc WHERE nrocomp='.$this->db->escape($numero).' AND cod_cli='.$this->db->escape($scli);
		$cana=$this->datasis->dameval($mSQL);
		if($cana >0 ){
			$this->validation->set_message('chdupli', 'Ya existe un registro guardado con el mismo numero de comprobante y al mismo cliente.');
			return false;
		}
		return true;
	}

	//*****************************
	//Metodos para autocomplete
	//*****************************
	function buscasfac(){
		$mid   = $this->input->post('q');
		$scli  = $this->input->post('scli');
		$qdb   = $this->db->escape('%'.$mid.'%');
		$sclidb= $this->db->escape($scli);

		$rete=0.75;
		$data = '{[ ]}';
		if(empty($scli)){
			$retArray[0]['label']   = 'Debe seleccionar un cliente primero';
			$retArray[0]['value']   = '';
			$retArray[0]['gtotal']  = 0;
			$retArray[0]['reiva']   = 0;
			$retArray[0]['impuesto']= 0;
			$retArray[0]['fecha']   = '';
			$retArray[0]['tipo_doc']= '';
			$data = json_encode($retArray);
			echo $data;
			return;
		}
		if($mid !== false){
			$retArray = $retorno = array();

			/*$mSQL="SELECT a.tipo_doc, a.numero, a.totalg, a.fecha,a.iva, a.iva*$rete AS reiva
				FROM sfac AS a
				LEFT JOIN itrivc AS b ON a.tipo_doc=b.tipo_doc AND a.numero=b.numero
				WHERE a.cod_cli=$sclidb AND CONCAT(a.tipo_doc,'-',a.numero) LIKE $qdb AND b.numero IS NULL AND a.tipo_doc <> 'X' AND a.iva>0
				ORDER BY numero DESC LIMIT 10";*/

			$mSQL="SELECT a.tipo_doc, a.numero, a.totalg, a.fecha,a.iva, a.iva*$rete AS reiva
				FROM  rivc AS c
				JOIN itrivc AS b ON c.id=b.idrivc AND c.anulado='N'
				RIGHT JOIN sfac AS a ON a.tipo_doc=b.tipo_doc AND a.numero=b.numero
				WHERE a.cod_cli=$sclidb AND CONCAT(a.tipo_doc,'-',a.numero) LIKE $qdb AND b.numero IS NULL AND a.tipo_doc <> 'X' AND a.iva>0
				ORDER BY numero DESC LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['label']   = $row['tipo_doc'].'-'.$row['numero'].' '.$row['totalg'].' Bs.';
					$retArray['value']   = $row['numero'];
					$retArray['gtotal']  = $row['totalg'];
					$retArray['reiva']   = (($row['tipo_doc']=='D')? -1: 1)*round($row['reiva'],2);
					$retArray['impuesto']= $row['iva'];
					$retArray['fecha']   = dbdate_to_human($row['fecha']);
					$retArray['tipo_doc']= $row['tipo_doc'];

					array_push($retorno, $retArray);
				}
				$data = json_encode($retorno);
			}else{
				$retArray[0]['label']   = 'No se consiguieron facturas para aplicar';
				$retArray[0]['value']   = '';
				$retArray[0]['cod_cli'] = '';
				$retArray[0]['nombre']  = '';
				$retArray[0]['gtotal']  = 0;
				$retArray[0]['reiva']   = 0;
				$retArray[0]['impuesto']= 0;
				$retArray[0]['fecha']   = '';
				$retArray[0]['tipo_doc']= '';

				$data = json_encode($retArray);
			}
		}
		echo $data;
	}

	function buscascli(){
		$mid  = $this->input->post('q');
		$qmid = $this->db->escape($mid);
		$qdb  = $this->db->escape('%'.$mid.'%');

		$data = '{[ ]}';
		if($mid !== false){
			$retArray = $retorno = array();

			//Cheque si existe el codigo
			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE cliente=${qmid} LIMIT 1";
			$query = $this->db->query($mSQL);
			if ($query->num_rows() == 1){
				$row = $query->row_array();

				$retArray['value']   = $row['cliente'];
				$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
				$retArray['rifci']   = $row['rifci'];
				$retArray['nombre']  = utf8_encode($row['nombre']);
				$retArray['cod_cli'] = $row['cliente'];
				$retArray['tipo']    = $row['tipo'];

				array_push($retorno, $retArray);
				$ww=" AND cliente<>${qmid}";
			}else{
				$ww='';
			}

			$mSQL="SELECT TRIM(nombre) AS nombre, TRIM(rifci) AS rifci, cliente, tipo
				FROM scli WHERE (cliente LIKE ${qdb} OR rifci LIKE ${qdb} OR nombre LIKE ${qdb}) $ww
				ORDER BY rifci LIMIT 10";

			$query = $this->db->query($mSQL);
			if ($query->num_rows() > 0){
				foreach( $query->result_array() as  $row ) {
					$retArray['value']   = $row['cliente'];
					$retArray['label']   = '('.$row['rifci'].') '.utf8_encode($row['nombre']);
					$retArray['rifci']   = $row['rifci'];
					$retArray['nombre']  = utf8_encode($row['nombre']);
					$retArray['cod_cli'] = $row['cliente'];
					$retArray['tipo']    = $row['tipo'];

					array_push($retorno, $retArray);
				}
			}
			if(count($data)>0)
				$data = json_encode($retorno);
		}
		echo $data;
		return true;
	}

	//*****************************
	//    Pre y Pos procesos
	//*****************************
	function _pre_insert($do){
		$transac = $this->datasis->fprox_numero('ntransa');
		$do->set('transac', $transac);
		$estampa = $do->get('estampa');
		$hora    = $do->get('hora');
		$usuario = $do->get('usuario');
		$cod_cli = $do->get('cod_cli');
		$op      = $do->get('operacion');
		//Actualiza los datos del cliente
		$rrow=$this->datasis->damerow('SELECT nombre,rifci FROM scli WHERE cliente='.$this->db->escape($cod_cli));
		if($rrow!=false){
			$do->set('nombre',$rrow['nombre']);
			$do->set('rif'   ,$rrow['rifci']);
		}

		$exento=$general=$geneimpu=$adicional=$adicimpu=$reducida=$reduimpu=$stotal=$impuesto=$gtotal=$reiva=0;

		//Borra la clave ya que solo se usa para comprobar
		$do->rm_get('clave');

		//Si no es un reintegro borra el cajero y caja, no se necesitan
		if($op!='R'){
			$do->rm_get('codbanc');
			$do->rm_get('numche');
			$do->rm_get('cajero');
		}else{
			$do->set('tipo_op','ND');
		}

		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc   = $do->get_rel($rel, 'tipo_doc', $i);
			$itreiva      = abs($do->get_rel($rel, 'reiva', $i));
			$dbitnumero   = $this->db->escape($do->get_rel($rel, 'numero'  , $i));
			$dbittipo_doc = $this->db->escape($ittipo_doc);

			$sql="SELECT exento,tasa,reducida,sobretasa,montasa,monredu,monadic,nfiscal,totals,totalg,iva FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$do->set_rel($rel, 'exento'   , $row->exento , $i);

				$do->set_rel($rel, 'tasa'     , ($row->montasa>0)? round($row->tasa *100/$row->montasa,2) : 0, $i);
				$do->set_rel($rel, 'general'  , $row->montasa, $i);
				$do->set_rel($rel, 'geneimpu' , $row->tasa   , $i);

				$do->set_rel($rel, 'tasaadic' , ($row->monadic>0)? round($row->sobretasa*100/$row->monadic,2) : 0, $i);
				$do->set_rel($rel, 'adicional', $row->monadic  , $i);
				$do->set_rel($rel, 'adicimpu' , $row->sobretasa, $i);

				$do->set_rel($rel, 'tasaredu' , ($row->monredu>0)? round($row->reducida*100/ $row->monredu,2) : 0, $i);
				$do->set_rel($rel, 'reducida' , $row->monredu , $i);
				$do->set_rel($rel, 'reduimpu' , $row->reducida, $i);

				$do->set_rel($rel, 'nfiscal' , $row->nfiscal, $i);
				$do->set_rel($rel, 'reiva'    , $itreiva     , $i);

				$exento   =$exento+$row->exento;

				$general  =$general+$row->montasa;
				$geneimpu =$geneimpu+$row->tasa;

				$adicional=$adicional+$row->monadic;
				$adicimpu =$adicimpu+$row->sobretasa;

				$reducida =$reducida+$row->monredu;
				$reduimpu =$reduimpu+$row->reducida;

				//Totales del encabezado
				$fac=($ittipo_doc=='D')? -1:1; //Para restar las devoluciones
				$stotal   =$stotal+($fac*$row->totals);
				$impuesto =$impuesto+($fac*$row->iva);
				$gtotal   =$gtotal+($fac*$row->totalg);
				$reiva    =$reiva+($fac*$itreiva);
			}

			$do->set_rel($rel, 'estampa', $estampa, $i);
			$do->set_rel($rel, 'hora'   , $hora   , $i);
			$do->set_rel($rel, 'usuario', $usuario, $i);
			$do->set_rel($rel, 'transac', $transac, $i);
		}

		$do->set('exento'   ,$exento);
		$do->set('general'  ,$general);
		$do->set('geneimpu' ,$geneimpu);
		$do->set('adicional',$adicional);
		$do->set('adicimpu' ,$adicimpu);
		$do->set('reducida' ,$reducida);
		$do->set('reduimpu' ,$reduimpu);
		$do->set('stotal'   ,$stotal);
		$do->set('impuesto' ,$impuesto);
		$do->set('gtotal'   ,$gtotal);
		$do->set('reiva'    ,$reiva);

		return true;
	}

	function _pre_update($do){
		return false;
	}

	function _pre_delete($do){
		$id        = $do->get('id');
		$transac   = $do->get('transac');
		$sprmreinte= $do->get('sprmreinte');
		$error=0;

		$dbtransac= $this->db->escape($transac);
		$mSQL="SELECT a.cod_cli,a.nombre,a.tipo_doc,a.numero,a.fecha,a.monto,a.impuesto,a.abonos,a.vence,a.tipo_ref,a.num_ref,a.fecdoc,a.nroriva,a.ningreso FROM smov AS a WHERE transac=$dbtransac";
		$query = $this->db->query($mSQL);

		$rel='itrivc';
		$restodat=array();
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc = $do->get_rel($rel, 'tipo_doc', $i);
			$itnumero   = $do->get_rel($rel, 'numero', $i);
			$itfecha    = $do->get_rel($rel, 'fecha', $i);
			$restodat[$ittipo_doc.$itnumero]=$itfecha;
		}

		$sqls=array();
		//Reversa los cruces, anticipos y notas de debito
		foreach ($query->result() as $row){
			$dbcod_cli = $this->db->escape($row->cod_cli);
			$dbtipo_doc= $this->db->escape($row->tipo_doc);
			$dbnumero  = $this->db->escape($row->numero);
			$dbfecha   = $this->db->escape($row->fecha);
			$ww = "cod_cli=$dbcod_cli AND tipo_doc=$dbtipo_doc AND numero=$dbnumero AND fecha=$dbfecha AND transac=$dbtransac";

			if($row->tipo_doc=='NC'){
				$mmSQL="SELECT numccli,tipoccli,monto FROM itccli WHERE numero=$dbnumero AND  fecha=$dbfecha AND tipo_doc='NC' AND cod_cli=$dbcod_cli AND tipoccli='FC'";
				$qquery = $this->db->query($mmSQL);

				if ($qquery->num_rows() > 0){
					foreach ($qquery->result() as $rrow){
						$numero       = $rrow->numccli;
						$tipo_doc     = $rrow->tipoccli;
						$dbitnumero   = $this->db->escape($numero);
						$dbittipo_doc = $this->db->escape($tipo_doc);
						$itmonto      = $rrow->monto;
						$tiposfac     = 'FC';
						$iind         = substr($tipo_doc,0,1).$numero;
						if(isset($restodat[$iind])){
							$fecha        = $restodat[$iind];
							$dbfecha = $this->db->escape($fecha);

							$sqls[] = "UPDATE sfac SET reiva=0, creiva=NULL, freiva=NULL, ereiva=NULL WHERE numero=${dbitnumero} AND tipo_doc=${dbittipo_doc}";
							$sqls[] = "UPDATE smov SET abonos=abonos-$itmonto WHERE numero=${dbitnumero}  AND cod_cli=${dbcod_cli} AND tipo_doc='${tiposfac}' AND fecha=${dbfecha}";
						}else{
							$error++;
						}
					}
				}
			}elseif($row->tipo_doc=='AN'){
				//Chequea que los anticipos no se hallan aplicado
				if($row->abonos>0){
					$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Algunos de los movimientos asociados han sido aplicados, debe reversarlos antes de proceder';
					return false;
				}
			}elseif($row->tipo_doc=='ND'){
				//Chequea que las notas de debito no esten aplicadas
				if($row->abonos>0){
					$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Algunos de los movimientos asociados han sido aplicados, debe reversarlos antes de proceder';
					return false;
				}
			}
			$sqls[]="DELETE FROM smov WHERE $ww";
		}

		//Reversa la cuenta por pagar (Si la hubo)
		if(!empty($sprmreinte)){
			$dbsprmreinte=$this->db->escape($sprmreinte);
			//Chequea que no este abonado
			$abonos=$this->datasis->dameval("SELECT abonos FROM sprm WHERE numero=${dbsprmreinte} AND transac=$dbtransac");
			if($abonos>0){
				$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Algunos de los movimientos asociados han sido aplicados, debe reversarlos antes de proceder';
				return false;
			}
			$sqls[]="DELETE FROM sprm WHERE numero=${dbsprmreinte} AND transac=$dbtransac";
		}

		//Reversa el reintegro (Si lo hubo)
		$mmSQL="SELECT codbanc,monto FROM bmov WHERE transac=$dbtransac AND clipro='C'";
		$qquery = $this->db->query($mmSQL);

		if ($qquery->num_rows() > 0){
			foreach ($qquery->result() as $rrow){
				$codbanc    = $rrow->codbanc;
				$sp_fecha   = date('Ymd');
				$monto      = $rrow->monto;

				$dbcodbanc  = $this->db->escape($codbanc);
				$sqls[]="DELETE FROM bmov WHERE transac=$dbtransac AND clipro='C'";
				$sqls[]="CALL sp_actusal($dbcodbanc,'$sp_fecha',$monto)";
			}
		}
		$sqls[]="UPDATE rivc SET anulado='S' WHERE id=".$this->db->escape($id);

		if($error==0){
			foreach($sqls as $sql){
				//echo "$sql \n".br();
				$ban=$this->db->simple_query($sql);
				if($ban==false){
					$error++;
					memowrite($sql,'rivc');
				}
			}
		}

		if($error>0){
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Hubo problemas en la trasaccion, se generar&acute;n centinelas';
		}else{
			$periodo = $do->get('periodo');
			$nrocomp = $do->get('nrocomp');

			$primary =implode(',',$do->pk);
			logusu($do->table,"Anulo Retencion de cliente id: $primary  ${periodo }${nrocomp}");
			$do->error_message_ar['pre_del'] = $do->error_message_ar['delete']='Retencion anulada';
		}
		return false;
	}

	function _post_insert($do){
		$primary =implode(',',$do->pk);
		$error   = 0;
		$montan  = 0; //Monto para anticipar
		$sobrante= 0; //Monsto sobrante para anticipar, reitegrar o pagar

		$transac   = $do->get('transac');
		$estampa   = $do->get('estampa');
		$hora      = $do->get('hora');
		$cod_cli   = $do->get('cod_cli');
		$nombre    = $do->get('nombre');
		$estampa   = $do->get('estampa');
		$periodo   = $do->get('periodo');
		$usuario   = $do->get('usuario');
		$hora      = $do->get('hora');
		$operacion = $do->get('operacion');
		$periodo   = $do->get('periodo');
		$id        = $do->get('id');
		$numero    = $do->get('nrocomp');
		$comprob   = $periodo.$numero;

		//$reinte  = $this->uri->segment($this->uri->total_segments());
		$efecha  = $do->get('emision');
		$fecha   = $do->get('fecha');
		$ex_fecha=explode('-',$fecha);
		$numero  = $do->get('nrocomp');
		$vence   = $ex_fecha[0].$ex_fecha[1].days_in_month($ex_fecha[1],$ex_fecha[0]);

		$mSQL = "DELETE FROM smov WHERE transac='$transac'";
		$ban=$this->db->simple_query($mSQL);
		if($ban==false){ memowrite($mSQL,'RIVC'); }

		$rel='itrivc';
		$cana = $do->count_rel($rel);
		for($i = 0;$i < $cana;$i++){
			$ittipo_doc  = $do->get_rel($rel, 'tipo_doc', $i);
			$itnumero    = $do->get_rel($rel, 'numero'  , $i);
			$itmonto     = $do->get_rel($rel, 'reiva'  , $i);
			$itfecha     = $do->get_rel($rel, 'fecha'  , $i);

			$dbitnumero   = $this->db->escape($itnumero);
			$dbittipo_doc = $this->db->escape($ittipo_doc);

			$sql="SELECT referen,reiva,factura,cod_cli,nombre FROM sfac WHERE numero=$dbitnumero AND tipo_doc=$dbittipo_doc";
			$query = $this->db->query($sql);
			if ($query->num_rows() > 0){
				$row = $query->row();

				$anterior    = $row->reiva;
				$itreferen   = $row->referen;
				$itfactura   = $row->factura;
			}

			if($anterior == 0) {
				$mSQL = "UPDATE sfac SET reiva=${itmonto}, creiva='${periodo}${numero}', freiva='${fecha}', ereiva='${efecha}' WHERE numero=${dbitnumero} AND tipo_doc=${dbittipo_doc}";
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'rivc'); }
			}

			//Chequea si es credito y si tiene saldo
			if($itreferen=='C'){
				$saldo =  $this->datasis->dameval("SELECT monto-abonos FROM smov WHERE tipo_doc='FC' AND numero='$itnumero'");
			}else{

				if($ittipo_doc=='F'){
					$sel=array('b.monto - b.abonos AS saldo','b.numero');
					$this->db->select($sel);
					$this->db->from('sfac AS a');
					$this->db->join('smov AS b','a.cod_cli=b.cod_cli AND a.transac=b.transac AND a.fecha=b.fecha');
					$this->db->where('a.tipo_doc','F');
					$this->db->where('b.tipo_doc','ND');
					$this->db->where('a.numero'  ,$itnumero);
					$query = $this->db->get();

					if ($query->num_rows() > 0){
						$row = $query->row();

						$ittipo_doc = 'ND';
						$itnumero   = $row->numero;

						$saldo=$row->saldo;
					}else{
						$saldo = 0;
					}
				}else{
					$saldo = 0;
				}
			}

			//Si es una factura o una nota de debito
			if($ittipo_doc == 'F' || $ittipo_doc=='ND'){
				//Si el saldo es 0  o menor que el monto retenido
				if($saldo==0 || $itmonto>$saldo){
					$sobrante+=$itmonto;
				}else{
					//Como tiene saldo suficiente crea una NC y la aplica a la FC
					$mnumnc = $this->datasis->fprox_numero('nccli');
					$data=array();
					$data['cod_cli']    = $cod_cli;
					$data['nombre']     = $nombre;
					$data['tipo_doc']   = 'NC';
					$data['numero']     = $mnumnc;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['impuesto']   = 0;
					$data['abonos']     = $itmonto;
					$data['vence']      = $fecha;
					$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'ND';
					$data['num_ref']    = $itnumero;
					$data['observa1']   = 'APLICACION DE RET/IVA A '.$ittipo_doc.$itnumero;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['transac']    = $transac;
					$data['usuario']    = $usuario;
					$data['codigo']     = 'NOCON';
					$data['descrip']    = 'NOTA DE CONTABILIDAD';
					$data['fecdoc']     = $itfecha;
					$data['nroriva']    = $comprob;
					$data['emiriva']    = $efecha;

					$mSQL = $this->db->insert_string('smov', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'rivc'); }

					//Aplica la NC a la FC o ND segun sea el caso
					$data=array();
					$data['numccli']    = $itnumero;
					$data['tipoccli']   = ($ittipo_doc=='F')? 'FC' : 'ND';
					$data['cod_cli']    = $cod_cli;
					$data['tipo_doc']   = 'NC';
					$data['numero']     = $mnumnc;
					$data['fecha']      = $fecha;
					$data['monto']      = $itmonto;
					$data['abono']      = $itmonto;
					$data['ppago']      = 0;
					$data['reten']      = 0;
					$data['cambio']     = 0;
					$data['mora']       = 0;
					$data['transac']    = $transac;
					$data['estampa']    = $estampa;
					$data['hora']       = $hora;
					$data['usuario']    = $usuario;
					$data['reteiva']    = 0;
					$data['nroriva']    = '';
					$data['emiriva']    = '';
					$data['recriva']    = '';

					$mSQL = $this->db->insert_string('itccli', $data);
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'rivc');}

					// Abona la factura
					$tiposfac = ($ittipo_doc=='F')? 'FC':'ND';
					$mSQL = "UPDATE smov SET abonos=abonos+$itmonto WHERE numero='$itnumero' AND cod_cli='$cod_cli' AND tipo_doc='$tiposfac'";
					$ban=$this->db->simple_query($mSQL);
					if($ban==false){ memowrite($mSQL,'rivc'); }
				}

				$mnumnd = $this->datasis->fprox_numero('ndcli');
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $vence;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'ND';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $comprob;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}else{
			//Si es una devolucion
				// Devoluciones genera un ND al cliente
				$mnumnd = $this->datasis->fprox_numero('ndcli');
				$data=array();
				$data['cod_cli']    = $cod_cli;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnumnd;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC. '.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['nroriva']    = $comprob;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'rivc'); }

				//Devoluciones debe crear un NC si esta en el periodo
				$mnumnc = $this->datasis->fprox_numero('nccli');
				$data=array();
				$data['cod_cli']    = 'REIVA';
				$data['nombre']     = 'RETENCION DE I.V.A. POR COMPENSAR';
				$data['tipo_doc']   = 'NC';
				$data['numero']     = $mnumnc;
				$data['fecha']      = $fecha;
				$data['monto']      = $itmonto;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = ($ittipo_doc=='F')? 'FC' : 'DV';
				$data['num_ref']    = $itnumero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A DOC.'.$ittipo_doc.$itnumero;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';
				$data['nroriva']    = $comprob;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }
			}
		}

		//Chequea si es un reintegro para crear un solo egreso de caja
		//$totneto  = $do->get('reiva');
		if($sobrante>0){
			if($operacion=='A' && $sobrante>0){
				$mnumant = $this->datasis->fprox_numero('nancli');

				$data=array();
				$data['cod_cli']    = $cod_cli;
				$data['nombre']     = $nombre;
				$data['tipo_doc']   = 'AN';
				$data['numero']     = $mnumant;
				$data['fecha']      = $fecha;
				$data['monto']      = $sobrante;
				$data['impuesto']   = 0;
				$data['vence']      = $fecha;
				$data['tipo_ref']   = 'CR';
				$data['num_ref']    = $numero;
				$data['observa1']   = 'RET/IVA DE '.$cod_cli.' A CR'.$comprob;
				$data['usuario']    = $usuario;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['transac']    = $transac;
				$data['fecdoc']     = $fecha;
				$data['nroriva']    = $comprob;
				$data['emiriva']    = $efecha;

				$mSQL = $this->db->insert_string('smov', $data);
				$ban=$this->db->simple_query($mSQL);
				if($ban==false){ memowrite($mSQL,'RIVC'); }

			}elseif($operacion=='R' && $sobrante>0){
				$codbanc  = $do->get('codbanc');
				$numeroch = $this->datasis->fprox_numero('ncaja'.$codbanc);
				$datacar  = common::_traebandata($codbanc);
				$sp_fecha = date('Ymd');
				$ttipo    = $datacar['tbanco'];
				$moneda   = $datacar['moneda'];

				$tipo1  = ($ttipo=='CAJ') ? 'D': 'C';
				$negreso= $this->datasis->fprox_numero('negreso');
				$credito= 0;
				$causado= '';
				$tipo_op= ($ttipo=='CAJ') ? 'ND': 'CH';

				$data=array();
				$data['codbanc']    = $codbanc;
				$data['moneda']     = $moneda;
				$data['numcuent']   = $datacar['numcuent'];
				$data['banco']      = $datacar['banco'];
				$data['saldo']      = $datacar['saldo'];
				$data['tipo_op']    = $tipo_op;
				$data['numero']     = $numeroch;
				$data['fecha']      = date('Y-m-d');
				$data['clipro']     = 'C';
				$data['codcp']      = $cod_cli;
				$data['nombre']     = $nombre;
				$data['monto']      = $sobrante;
				$data['concepto']   = 'REINTEGRO RET/IVA DE '.$cod_cli;
				$data['concep2']    = ' CR'.$comprob;
				$data['benefi']     = '';
				$data['posdata']    = '';
				$data['abanco']     = '';
				$data['liable']     = ($ttipo=='CAJ') ? 'S': 'N';;
				$data['transac']    = $transac;
				$data['usuario']    = $usuario;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['anulado']    = 'N';
				$data['susti']      = '';
				$data['negreso']    = $negreso;

				$sql=$this->db->insert_string('bmov', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'rivc'); $error++;}

				$sql='CALL sp_actusal('.$this->db->escape($codbanc).",'$sp_fecha',-$sobrante)";
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'rivc'); $error++; }

			}elseif($operacion=='P' && $sobrante>0){ //Lo manda a cuenta por pagar

				$causado = $this->datasis->fprox_numero('ncausado');

				$mnsprm = $this->datasis->fprox_numero('num_nd');
				$data=array();
				$data['cod_prv']    = 'REINT';
				$data['nombre']     = 'REINTEGROS CLIENTES';
				$data['tipo_doc']   = 'ND';
				$data['numero']     = $mnsprm;
				$data['fecha']      = $fecha;
				$data['monto']      = $sobrante;
				$data['impuesto']   = 0;
				$data['abonos']     = 0;
				$data['vence']      = $fecha;
				$data['observa1']   = 'CARGO A CXC RET/IVA CR '.$comprob;
				$data['observa2']   = 'DEL CLIENTE '.$cod_cli;
				$data['tipo_ref']   = 'CR';
				$data['num_ref']    = $numero;
				$data['transac']    = $transac;
				$data['estampa']    = $estampa;
				$data['hora']       = $hora;
				$data['usuario']    = $usuario;
				$data['reteiva']    = 0;
				$data['montasa']    = 0;
				$data['monredu']    = 0;
				$data['monadic']    = 0;
				$data['tasa']       = 0;
				$data['reducida']   = 0;
				$data['sobretasa']  = 0;
				$data['exento']     = 0;
				$data['causado']    = $causado;
				$data['codigo']     = 'NOCON';
				$data['descrip']    = 'NOTA DE CONTABILIDAD';

				$sql=$this->db->insert_string('sprm', $data);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'rivc'); $error++;}

				$sql='UPDATE rivc SET sprmreinte='.$this->db->escape($mnsprm).' WHERE id='.$this->db->escape($id);
				$ban=$this->db->simple_query($sql);
				if($ban==false){ memowrite($sql,'rivc'); $error++;}
			}
		}
		$periodo = $do->get('periodo');
		$nrocomp = $do->get('nrocomp');

		$primary =implode(',',$do->pk);

		logusu($do->table,"Creo $this->tits ID $primary ${periodo }${nrocomp}");
		return true;
	}

	function _post_update($do){
		$primary =implode(',',$do->pk);
		logusu($do->table,"Modifico $this->tits $primary ");
	}

	function _post_delete($do){
		$periodo = $do->get('periodo');
		$nrocomp = $do->get('nrocomp');

		$primary =implode(',',$do->pk);
		logusu($do->table,"Anulo $this->tits $primary  ${periodo }${nrocomp}");
	}

	function instalar(){
		if (!$this->db->table_exists('rivc')) {
			$mSQL="CREATE TABLE `rivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`nrocomp` varchar(8) NOT NULL DEFAULT '',
			`emision` date DEFAULT NULL,
			`periodo` char(8) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`cod_cli` varchar(5) DEFAULT NULL,
			`nombre` varchar(200) DEFAULT NULL,
			`rif` varchar(14) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` varchar(12) DEFAULT NULL,
			`modificado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`transac` varchar(8) DEFAULT NULL,
			`origen` char(1) DEFAULT NULL,
			`codbanc` char(2) DEFAULT NULL,
			`tipo_op` char(2) DEFAULT NULL,
			`numche` varchar(12) DEFAULT NULL,
			`sprmreinte` varchar(8) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `modificado` (`modificado`),
			KEY `nrocomp_cod_cli` (`nrocomp`,`cod_cli`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('operacion', 'rivc')){
			$mSQL="ALTER TABLE rivc ADD COLUMN operacion CHAR(1) NOT NULL AFTER sprmreinte";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('anulado', 'rivc')){
			$mSQL="ALTER TABLE `rivc`  ADD COLUMN `anulado` CHAR(1) NULL DEFAULT 'N' AFTER `operacion`";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->field_exists('cajero', 'rivc')){
			$mSQL="ALTER TABLE rivc ADD COLUMN cajero VARCHAR(5) NULL DEFAULT NULL AFTER sprmreinte";
			$this->db->simple_query($mSQL);
		}

		if (!$this->db->table_exists('itrivc')) {
			$mSQL="CREATE TABLE `itrivc` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`idrivc` int(6) DEFAULT NULL,
			`tipo_doc` char(2) DEFAULT NULL,
			`fecha` date DEFAULT NULL,
			`numero` varchar(8) DEFAULT NULL,
			`nfiscal` char(12) DEFAULT NULL,
			`exento` decimal(15,2) DEFAULT NULL,
			`tasa` decimal(5,2) DEFAULT NULL,
			`general` decimal(15,2) DEFAULT NULL,
			`geneimpu` decimal(15,2) DEFAULT NULL,
			`tasaadic` decimal(5,2) DEFAULT NULL,
			`adicional` decimal(15,2) DEFAULT NULL,
			`adicimpu` decimal(15,2) DEFAULT NULL,
			`tasaredu` decimal(5,2) DEFAULT NULL,
			`reducida` decimal(15,2) DEFAULT NULL,
			`reduimpu` decimal(15,2) DEFAULT NULL,
			`stotal` decimal(15,2) DEFAULT NULL,
			`impuesto` decimal(15,2) DEFAULT NULL,
			`gtotal` decimal(15,2) DEFAULT NULL,
			`reiva` decimal(15,2) DEFAULT NULL,
			`transac` char(8) DEFAULT NULL,
			`estampa` date DEFAULT NULL,
			`hora` char(8) DEFAULT NULL,
			`usuario` char(12) DEFAULT NULL,
			`ffactura` date DEFAULT '0000-00-00',
			`modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `tipo_doc_numero` (`tipo_doc`,`numero`),
			KEY `Numero` (`numero`),
			KEY `modificado` (`modificado`),
			KEY `rivatra` (`transac`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED";
			$this->db->simple_query($mSQL);
		}
	}
}
