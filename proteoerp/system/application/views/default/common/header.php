<div id="header">
<table align="center" border=0 width="99%" cellpadding=0 cellspacing=0>
<tr>
<td width="170px">&nbsp</td>
<td align='center'><h2><?=$this->datasis->traevalor("TITULO1")  ?></h2></td>
<td align="right"><?=$idus ?> <div id="form_usr">Usuario: </div> </td>
</tr>
<tr>
<td>&nbsp</td>
<td align='center'><p class="miniblanco1"><?=$this->datasis->traevalor("TITULO2")."<br>".$this->datasis->traevalor("TITULO3")."<br>RIF 
".$this->datasis->traevalor("RIF")?><p></td>
<td align="right" ><img src="<?php echo base_url() ?>images/logo.jpg" height="38px" ></td>
</tr>


</table>


</div>
<?php echo $menu ?>