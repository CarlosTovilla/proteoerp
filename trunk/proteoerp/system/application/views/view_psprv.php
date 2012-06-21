<?php
ob_start('comprimir_pagina');

$container_bl=join('&nbsp;', $form->_button_container['BL']);
$container_br=join('&nbsp;', $form->_button_container['BR']);
$container_tr=join('&nbsp;', $form->_button_container['TR']);

if ($form->_status=='delete' || $form->_action=='delete' || $form->_status=='unknow_record'):
	echo $form->output;
else:

if(isset($form->error_string)) echo '<div class="alert">'.$form->error_string.'</div>';

echo $form_begin;
$dbcliente=$this->db->escape($form->cod_prv->value);
$nomcli=$this->datasis->dameval("SELECT nombre FROM scli WHERE cliente=$dbcliente");

if($form->getstatus()!='show'){

	$bmov_campos=$form->template_details('bmov');
	$bmov_scampos  ='<tr id="tr_bmov_<#i#>">';
	$bmov_scampos .='<td class="littletablerow" align="left" >'.$bmov_campos['banco']['field']. '</td>';
	$bmov_scampos .='<td class="littletablerow" align="left" >'.$bmov_campos['tipo_op']['field'].  '</td>';
	$bmov_scampos .='<td class="littletablerow" align="left" >'.$bmov_campos['bmovfecha']['field'].  '</td>';
	$bmov_scampos .='<td class="littletablerow" align="left" >'.$bmov_campos['numref']['field'].'</td>';
	$bmov_scampos .='<td class="littletablerow" align="right">'.$bmov_campos['itmonto']['field'].'</td>';
	//$bmov_scampos .='<td class="littletablerow"><a href=# onclick="del_bmov(<#i#>);return false;">'.img("images/delete.jpg").'</a></td></tr>';
	$bmov_campos=$form->js_escape($bmov_scampos);
?>
<script type="text/javascript">
var bmov_cont=<?php echo $form->max_rel_count['bmov'];?>;
$(function() {
	$(".inputnum").numeric(".");
	$('input[name^="abono_"]').keyup(function(){
		totaliza();
	});
	$('input[name^="abono_"]').focusout(function(){
		totaliza();
	});
	totaliza();
	$('form').submit(function() {
		var r=confirm("Confirma guardar las transacciones?");
		return r;
	});
});

function totaliza(){
	var stota= 0;
	var arr  = $('input[name^="abono_"]');

	jQuery.each(arr, function(){
		nom=this.name;
		pos=this.name.lastIndexOf('_');
		if(pos>0){
			ind    = this.name.substring(pos+1);
			num    = Number(this.value);
			if(!isNaN(num)){
				stota += num;
			}else{
				this.value='0';
			}
		}
	});
	$('#monto').val(roundNumber(stota,2));
	$('#monto_val').text(nformat(stota,2));

	resto=faltante();
	utmo =$('input[id^="itmonto_"]').first();
	num  =Number(utmo.val());
	if(!isNaN(num)){
		hay = num
	}else{
		hay = 0;
		utmo.val('0');
	}

	utmo.val(roundNumber(hay+resto,2));
}

function add_bmov(){
	var htm = <?php echo $bmov_campos; ?>;
	can = bmov_cont.toString();
	con = (bmov_cont+1).toString();
	htm = htm.replace(/<#i#>/g,can);
	htm = htm.replace(/<#o#>/g,con);
	$("#__ITPL__bmov").after(htm);
	falta = faltante();
	$("#itmonto_"+can).val(falta);
	bmov_cont=bmov_cont+1;
	return can;
}

function itsaldo(obj,saldo){
	if(obj.value.length==0){
		obj.value=saldo;
		totaliza();
	}
}

function itppago(obj,ind){
	var monto=0;
	var valor=Number(obj.value);
	var nval=0;

	if(valor==NaN){
		obj.value='0';
	}else if(valor<0){
		monto=Number($('#abono_'+ind).val());
		nval=monto*valor*-1/100;
		obj.value=roundNumber(nval,2);
		$('#abono_'+ind).val(roundNumber(monto-nval,2));
		totaliza();
	}
}

function del_bmov(id){
	id = id.toString();
	$('#tr_bmov_'+id).remove();
	totaliza();
	var arr = $('input[id^="itmonto_"]');
	if(arr.length<=0){
		add_bmov();
	}
}

//Totaliza el monto por pagar
function apagar(){
	var pago=0;
	jQuery.each($('input[id^="itmonto_"]'), function() {
		pago+=Number($(this).val());
	});
	if(isNaN(pago)) return 0; else return pago;
}

//Determina lo que falta por pagar
function faltante(){
	totalg=Number($("#monto").val());
	if(isNaN(totalg)){
		$("#monto").val('0');
		totalg=0;
	}
	paga  = apagar();
	resto = totalg-paga;
	return resto;
}
</script>
<?php } ?>
<table align='center' width="100%">
	<tr>
		<td colspan=3><?php echo $form->numero->value.$form->cod_prv->output ?>
		<?php if($form->getstatus()=='show'){ ?>
			<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/verhtml/CCLIAB/<?php echo raencode($form->cod_prv->value).'/'.raencode($form->tipo_doc->value).'/'.raencode($form->numero->value).'/'.raencode($form->get_from_dataobjetct('fecha')) ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
			<img src='<?php echo base_url() ?>images/html_logo.gif'></a>
			<a href="#" onclick="window.open('<?php echo base_url() ?>formatos/ver/CCLIAB/<?php echo raencode($form->cod_prv->value).'/'.raencode($form->tipo_doc->value).'/'.raencode($form->numero->value).'/'.raencode($form->get_from_dataobjetct('fecha')) ?>', '_blank', 'width=800, height=600, scrollbars=Yes, status=Yes, resizable=Yes, screenx='+((screen.availWidth/2)-400)+',screeny='+((screen.availHeight/2)-300)+'');" heigth="600" >
			<img src='<?php echo base_url() ?>images/pdf_logo.gif'></a>
		<?php } ?>
		</td>
		<td align=right><?php echo $container_tr;?></td>
	</tr>
	<tr>
		<td><?php echo $form->tipo_doc->label;  ?></td>
		<td><?php echo $form->tipo_doc->output; ?></td>
		<td><?php echo $form->fecdoc->label;    ?></td>
		<td><?php echo $form->fecdoc->output;   ?></td>
	</tr>
</table>

<table width='100%' align='center'>
	<col>
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg1">
	<col class="colbg2">
	<thead>
		<tr>
			<td class="littletableheaderdet"><b>Documento</b></td>
			<td align="center" class="littletableheaderdet"><b>Fecha</b></td>
			<td align="right"  class="littletableheaderdet"><b>Monto</b></td>
			<td align="center" class="littletableheaderdet"><b>Saldo</b></td>
			<td align="right"  class="littletableheaderdet"><b>Abonar</b></td>
			<td align="right"  class="littletableheaderdet"><b>P.Pago</b></td>
		</tr>
	</thead>
	<tbody>
	<?php
	$pmarcat='';
	for($i=0;$i<$cana;$i++) {
		$it_tipo_doc = "tipo_doc_$i";
		$it_numero   = "numero_$i";
		$it_fecha    = "fecha_$i";
		$it_monto    = "monto_$i";
		$it_abono    = "abono_$i";
		$it_saldo    = "saldo_$i";
		$it_ppago    = "ppago_$i";
	?>
	<tr id='tr_itccli_<?php echo $i; ?>' <?php echo ($i%2 == 0) ? 'class="odd"' : '';?> >
		<td><?php echo $form->$it_tipo_doc->output;?>-<?php echo $form->$it_numero->output;?></td>
		<td align="center"><?php echo $form->$it_fecha->output; ?></td>
		<td align="right"><?php echo $form->$it_monto->output; ?></td>
		<td align="right"><?php echo $form->$it_saldo->output; ?></td>
		<td align="right"><?php echo $form->$it_abono->output; ?></td>
		<td align="right"><?php echo $form->$it_ppago->output; ?></td>
	</tr>
	<?php } ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan=4 align="right"><b><?php echo $form->monto->label; ?></b></td>
		<td align="right"><?php echo $form->monto->output; ?></td>
	</tr>
	</tfoot>
</table>

<?php echo $container_br.$container_bl;?>
<table width='100%'>
	<tr id='__ITPL__bmov'>
		<td class="littletableheaderdet">Banco</td>
		<td class="littletableheaderdet">Tipo</td>
		<td class="littletableheaderdet">Fecha</td>
		<td class="littletableheaderdet">N&uacute;mero</td>
		<td class="littletableheaderdet">Monto</td>
		<?php
		//if($form->_status!='show') {
		//	echo '<td class="littletableheaderdet"></td>';
		//}
		?>
	</tr>
	<?php

	for($i=0; $i < $form->max_rel_count['bmov']; $i++) {
		$tipo      = "tipo_op_$i";
		$bmovfecha = "bmovfecha_$i";
		$numref    = "numref_$i";
		$monto     = "itmonto_$i";
		$banco     = "banco_$i";
	?>
	<tr id='tr_bmov_<?php echo $i; ?>'>
		<td class="littletablerow">       <?php echo $form->$banco->output     ?></td>
		<td class="littletablerow" nowrap><?php echo $form->$tipo->output      ?></td>
		<td class="littletablerow" nowrap><?php echo $form->$bmovfecha->output ?></td>
		<td class="littletablerow">       <?php echo $form->$numref->output    ?></td>
		<td class="littletablerow" align="right"><?php echo $form->$monto->output ?></td>
		<?php
		//if($form->_status!='show') {
		//	echo '<td class="littletablerow"><a href=# onclick="del_bmov('.$i.');return false;">'.img("images/delete.jpg").'</a></td></tr>';
		//}
		?>
	</tr>
	<?php } ?>
	<tr id='__UTPL__bmov'>
		<td colspan='9' class="littletableheaderdet">&nbsp;</td>
	</tr>
</table>
<?php echo $form_end; ?>

<?php endif; ?>
<?php
ob_end_flush();

// Función para eliminar todos los espacios en blanco
function comprimir_pagina($buffer) {
    $busca = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $reemplaza = array('>','<','\\1');
    return preg_replace($busca, $reemplaza, $buffer);
}
?>
