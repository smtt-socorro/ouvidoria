<?php
global $hesk_settings, $hesklang;
/**
 * @var array $tickets
 * @var boolean $showStaffOnlyFields
 */

// This guard is used to ensure that users can't hit this outside of actual HESK code
if (!defined('IN_SCRIPT')) {
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $hesk_settings['hesk_title']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $hesklang['ENCODING']; ?>">
    <style>
table, th, td, tr {
  border: 1px solid black;
  border-collapse: collapse;
  align:center;
  padding: 6px;
}

.center {
  margin-left: auto;
  margin-right: auto;

}

.line {
	color: #FFF;
    margin-left: auto;
    margin-right: auto;
}

</style>
</head>
<body onload="window.print()">
<?php foreach ($tickets as $ticket): ?>

    <p align="center"><img src="/theme/hesk3/cabecalho_smtt.png" width="750" height="106" />
</p>
<p>&nbsp;</p>
<p align="center"><strong>FORMULÁRIO DE ATENDIMENTO AO CIDADÃO</strong></p>
<p>&nbsp;</p>
<table class="center" width="750">
  <tr>
    <td width="163"><div align="center"><strong>ID</strong></div></td>
    <td width="160"><div align="center"><strong>STATUS</strong></div></td>
    <td width="203"><div align="center"><strong>DATA</strong></div></td>
    <td width="265"><div align="center"><strong>CATEGORIA</strong></div></td>
  </tr>
  <tr>
    <td><div align="center"><?php echo $ticket['trackid']; ?></div></td>
    <td><div align="center"><?php echo $ticket['status']; ?></div></td>
    <td><div align="center"><?php echo $ticket['dt']; ?></div></td>
    <td><div align="center"><?php echo $ticket['categoryName']; ?></div></td>
  </tr>
</table>
</br>
<table class="center" width="750">
  <tr>
    <td width="86">Assunto:</td>
    <td width="502"><div align="center"><?php echo $ticket['subject']; ?></div></td>
  </tr>
</table>
</br>
<table width="750" border="0" bgcolor="#222222" class='center'>
  <tr>
    <td><div align="center" class="line"><strong>DADOS PESSOAIS</strong></div></td>
  </tr>
</table>
</br>
<table class="center" width="750">

<tr>
    <td width="86">Nome:</td>
    <td width="502"><?php echo $ticket['name']; ?></td>
  </tr>

<tr>
    <td width="86">Email:</td>
    <td width="502"><?php echo $ticket['email']; ?></td>
</tr>
        
        <?php foreach ($ticket['custom_fields'] as $customField): ?>
            <tr>
                <td width="86"><?php echo $customField['name']; ?></td>
                <td width="86"><?php echo $customField['value']; ?></td>
            </tr>
        <?php endforeach; ?>
        
    </table>
    </br>
<table width="750" border="0" bgcolor="#222222" class='center'>
  <tr>
    <td><div align="center" class="line"><strong>RELATO</strong></div></td>
  </tr>
</table>
</br>

    <?php if (count($ticket['notes'])): ?>
        <?php foreach ($ticket['notes'] as $note): ?>
            <p><?php echo $hesklang['noteby']; ?> <b><?php echo ($note['name'] ? $note['name'] : $hesklang['e_udel']); ?></b></i> - <?php echo hesk_date($note['dt'], true); ?><br>
            <?php echo strlen($note['message']) ? $note['message'] : '<i>no message</i>'; ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($ticket['message_html'] != ''): ?>
        <table class="center" width="750">
        <tr>
        <td width="750"><?php echo $ticket['message_html']; ?></td>
        </tr>
        
        </table>            
    <?php endif; ?>
    </br>
    </br>
    </br>
 
    <table width="750" border="0" bgcolor="#222222" class='center'>
      <tr>
        <td>
        <div align="center"></BR>
          <pre align="center" class="line">LEI Nº 13.709 - LEI DE PROTEÇÃO AOS DADOS
           
AV. NOSSA SENHORA DO SOCORRO, 30,JOÃO ALVES - NOSSA SENHORA DO SOCORRO, SERGIPE. 
CEP: 49.155-372. TELEFONE: 79 3256.5474 / CNPJ: 03.598.106/0001-27
EMAIL: OUVIDORIA.SMTT@SOCORRO.SE.GOV.BR
           
PARA ACOMPANHAR ACESSE: OUVIDORIA.SMTTSOCORRO.COM.BR</pre>
        </div>
        </BR>
      </tr>
    </table>
<?php endforeach; ?>


</body>
</html>