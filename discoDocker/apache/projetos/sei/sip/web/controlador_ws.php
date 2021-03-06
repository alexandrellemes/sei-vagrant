<?php
  require_once dirname(__FILE__).'/Sip.php';
  
	session_start();
	
	$strServico = $_GET['servico'];
	
  switch ($strServico) {

    case 'wsdl':
      $strArq = 'ws/sip.wsdl';
      break;
  
    default:

      foreach($SIP_MODULOS as $objModulo){
        if (($strArq = $objModulo->processarControladorWebServices($_GET['servico']))!=null){
          break;
        }
      }

      if ($strArq == null){
        throw new InfraException('Servi?o ['.$_GET['servico'].'] inv?lido.');
      }

  }

  InfraPagina::montarHeaderDownload($strArq);

  $handle = fopen($strArq, "r");
  $strWsdl = fread($handle, filesize($strArq));
  fclose($handle);

  $strServidor = ConfiguracaoSip::getInstance()->getValor('Sip','URL');
  
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'){
    $strServidor = str_replace('http://','https://',$strServidor);
  }else{
    $strServidor = str_replace('https://','http://',$strServidor);
  }
  
  echo str_replace('[servidor]', $strServidor, $strWsdl);
?>