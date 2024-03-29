<?php
$maxlin=33; //Maximo de lineas de items.

if(count($parametros) < 0) show_error('Faltan parametros');
$id=$parametros[0];

$sel=array('a.tipo_doc','a.numero','a.cod_cli','a.fecha','a.monto','a.abonos'
,'a.exento','a.montasa','a.tasa','a.monredu','a.reducida','a.monadic','a.sobretasa'
,'b.nombre','TRIM(b.nomfis) AS nomfis','CONCAT_WS(\'\',TRIM(b.dire11),b.dire12) AS direc','b.rifci','b.telefono','monredu'
,'CONCAT_WS(\' \',observa1,observa2) AS observa','b.rifci','a.transac','a.codigo','a.descrip','a.num_ref','a.tipo_ref');
$this->db->select($sel);
$this->db->from('smov AS a');
$this->db->join('scli AS b'  ,'a.cod_cli=b.cliente');
$this->db->where('a.id'   , $id);
$this->db->where('a.tipo_doc','NC');

$mSQL_1 = $this->db->get();
if($mSQL_1->num_rows()==0) show_error('Registro no encontrado');

$row = $mSQL_1->row();
$tipo_doc  = trim($row->tipo_doc);
$numero    = $row->numero;
$cliente   = $this->us_ascii2html(trim($row->cod_cli));
$tipo_doc  = trim($row->tipo_doc);
$fecha     = $row->fecha;
$hfecha    = dbdate_to_human($row->fecha);
$monto     = nformat($row->monto);
$montole   = strtoupper(numletra($row->monto));
$abonos    = $row->abonos;
$nombre    = (empty($row->nomfis))? $this->us_ascii2html($row->nombre) : $this->us_ascii2html($row->nomfis);
$rifci     = trim($row->rifci);
$direc     = $this->us_ascii2html($row->direc);
$observa   = wordwrap($this->us_ascii2html(str_replace(',',', ',$row->observa)), 100, '<br>');
$transac   = $row->transac;
$codigo    = $this->us_ascii2html(trim($row->codigo));
$descrip   = $this->us_ascii2html($row->descrip);
$telefono  = htmlspecialchars(trim($row->telefono));

$exento    = floatval($row->exento   );
$montasa   = floatval($row->montasa  );
$tasa      = floatval($row->tasa     );
$monredu   = floatval($row->monredu  );
$reducida  = floatval($row->reducida );
$monadic   = floatval($row->monadic  );
$sobretasa = floatval($row->sobretasa);
$alicuota1 = ($montasa  >0)? 100*round($tasa/$montasa,2)      : 0;
$alicuota2 = ($monredu  >0)? 100*round($reducida/$monredu,2)  : 0;
$alicuota3 = ($sobretasa>0)? 100*round($monadic/$sobretasa,2) : 0;
$gtotal    = floatval($row->monto);
$stotal    = $montasa+$monredu+$monadic+$exento;

$s_exento    = nformat($exento   );
$s_montasa   = nformat($montasa  );
$s_monredu   = nformat($monredu  );
$s_monadic   = nformat($monadic  );
$s_tasa      = nformat($tasa     );
$s_reducida  = nformat($reducida );
$s_sobretasa = nformat($sobretasa);
$s_alicuota1 = nformat($alicuota1);
$s_alicuota2 = nformat($alicuota2);
$s_alicuota3 = nformat($alicuota3);
$s_exento    = nformat($exento   );
$s_gtotal    = nformat($gtotal   );


$sel=array('b.tipo_doc','b.numero','b.fecha','b.monto','b.abono','b.reten','b.ppago','b.cambio','b.mora','b.reteiva');
$this->db->select($sel);
$this->db->from('smov AS a');
$this->db->join('itccli AS b','a.tipo_doc = b.tipoccli AND a.numero=b.numccli AND a.cod_cli=b.cod_cli');
$this->db->where('a.cod_cli' ,$row->cod_cli);
$this->db->where('a.tipo_doc',$row->tipo_doc);
$this->db->where('a.numero'  ,$row->numero);
$this->db->where('a.fecha'   ,$row->fecha);
$mSQL_2 = $this->db->get();

if($mSQL_2->num_rows()==0 && $tipo_doc=='NC'){
	$dbnumero=$this->db->escape($row->num_ref);
	$obj  = new stdClass();
	$obj->tipo_doc=$row->tipo_ref;
	$obj->numero  =$row->num_ref;
	$obj->fecha   =$this->datasis->dameval("SELECT fecha FROM sfac WHERE tipo_doc='F' AND numero=${dbnumero}");
	$obj->abono   =$row->monto;

	$detalle2= array($obj);
}else{
	$detalle2  = $mSQL_2->result();
}

$lineas=0;
?><html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?php echo $this->config->item('charset'); ?>" >
<title>Nota de cr&eacute;dito <?php echo $numero ?></title>
<link rel="stylesheet" href="<?php echo $this->_direccion ?>/assets/default/css/formatos.css" type="text/css" >
</head>
<body style="margin-left: 30px; margin-right: 30px;">

<?php
//************************
//     Encabezado
//************************
$encabezado = "
	<p style='height: 50px;'> </p>
	<table style='width:100%;font-size: 9pt;' class='header' cellpadding='0' cellspacing='0'>
		<tr>
			<td><h1 style='text-align:left; border-bottom:1px solid;font-size:12pt;'>NOTA DE CREDITO Nro. ${numero}</h1></td>
			<td style='width:230px;'><h1 style='text-align:left;border-bottom:1px solid;font-size:12pt;'>FECHA: ${hfecha}</h1></td>
		</tr><tr>
			<td>RIF, CI o Pasaporte: <b>${rifci}</b></td>
			<td>&nbsp;</b></td>
		</tr><tr>
			<td>Raz&oacute;n Social: <b>${nombre}</b></td>
			<td>C&oacute;digo de Cliente: <b>${cliente}</b></td>
		</tr><tr>
			<td>Domicilio Fiscal: <b>${direc}</b></td>
			<td>Tel&eacute;fono:  <b>${telefono}</b></td>
		</tr><tr>
			<td colspan='2'>Hemos acreditado en su cuenta la suma de Bs... <b>${monto}</b></td>
		</tr><tr>
			<td colspan='2'>Imputable a: <b>${codigo} ${descrip}</b></td>
		</tr><tr>
			<td colspan='2'>Concepto de: <b>${observa}</b></td>
		</tr>
	</table>
";
// Fin  Encabezado

//************************
//   Encabezado Tabla
//************************
$estilo  = "style='color: #111111;background: #EEEEEE;border: 1px solid black;font-size: 8pt;";
$encabezado_tabla="
	<table class=\"change_order_items\" style=\"padding-top:0; \">
		<thead>
			<tr>
				<th ${estilo}' >Documento   </th>
				<th ${estilo}' >Fecha  </th>
				<th ${estilo}' >Monto/Cr&eacute;dito  </th>
			</tr>
		</thead>
		<tbody>
";
//Fin Encabezado Tabla

//************************
//     Pie Pagina
//************************
$pie_final=<<<piefinal
		</tbody>
		<tfoot style='border:1px solid;background:#EEEEEE;'>
			<tr>
				<td style="text-align: right;"></td>
				<td style="text-align: right;"><b>Monto Total de la Base Imponible seg&uacute;n Alicuota ${s_alicuota1}%:</b></td>
				<td style="text-align: right;font-size:16px;font-weight:bold;" >${s_montasa}</td>
			</tr>
			<tr>
				<td style="text-align: right;"></td>
				<td style="text-align: right;"><b>Monto Total del Impuesto seg&uacute;n Alicuota:</b></td>
				<td style="text-align: right;font-size:16px;font-weight:bold;">${s_tasa}</td>
			</tr>
piefinal;


if($alicuota2>0){
		$pie_final .= "
			<tr>
				<td style='text-align: right;'></td>
				<td style='text-align: right;'><b>Monto Total de la Base Imponible seg&uacute;n Alicuota ${s_alicuota2}%:</b></td>
				<td style='text-align: right;font-size:16px;font-weight:bold;' >${s_reducida}</td>
			</tr>
			<tr>
				<td style='text-align: right;'></td>
				<td style='text-align: right;'><b>Monto Total del Impuesto seg&uacute;n Alicuota:</b></td>
				<td style='text-align: right;font-size:16px;font-weight:bold;'>${s_monredu}</td>
			</tr>";
}

if($alicuota3>0){
		$pie_final .= "
			<tr>
				<td style='text-align: right;'></td>
				<td style='text-align: right;'><b>Monto Total de la Base Imponible seg&uacute;n Alicuota ${s_alicuota3}%:</b></td>
				<td style='text-align: right;font-size:16px;font-weight:bold;' >${s_sobretasa}</td>
			</tr>
			<tr>
				<td style='text-align: right;'></td>
				<td style='text-align: right;'><b>Monto Total del Impuesto seg&uacute;n Alicuota:</b></td>
				<td style='text-align: right;font-size:16px;font-weight:bold;'>${s_monadic}</td>
			</tr>";
}


$pie_final.=<<<piefinal
			<tr style='border-top: 1px solid;background:#AAAAAA;'>
				<td style="text-align: right;"></td>
				<td style="text-align: right;"><b>MONTO TOTAL:</b></td>
				<td style="text-align: right;font-size:20px;font-weight:bold;">${s_gtotal}</td>
			</tr>
		</tfoot>

	</table>
piefinal;

$pie_continuo=<<<piecontinuo
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3" style="text-align: right;">CONTINUA...</td>
			</tr>
		</tfoot>
	</table>
<div style="page-break-before: always;"></div>
piecontinuo;
//Fin Pie Pagina

$mod     = $clinea = false;

//$this->incluir('X_CINTILLO');
echo $encabezado;
echo $encabezado_tabla;
$npagina = false;
$i       = 0;
foreach ($detalle2 AS $items2){ $i++;
	do {
		if($npagina){
			//$this->incluir('X_CINTILLO');
			echo $encabezado;
			echo $encabezado_tabla;
			$npagina=false;
		}
		$partici = $items->abono/$gtotal;
?>
			<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">

				<td style="text-align: center"><?php echo $items2->tipo_doc.$items2->numero;?></td>
				<td style="text-align: center"><?php echo dbdate_to_human($items2->fecha);   ?></td>
				<td style="text-align: right" ><?php echo nformat($stotal*$partici);           ?></td>
				<?php
				$lineas++;
				if($lineas > $maxlin){
					$lineas =0;
					$npagina=true;
					echo $pie_continuo;
					break;
				}
				?>
			</tr>
<?php

		$mod = ! $mod;
	} while ($clinea);
}

for(1;$lineas<$maxlin;$lineas++){ ?>
			<tr class="<?php if(!$mod) echo 'even_row'; else  echo 'odd_row'; ?>">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
<?php
	$mod = ! $mod;
}
echo $pie_final;
?></body>
</html>
