<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 14/02/2013 - criado por mga
*
*
* Vers�o do Gerador de C�digo:1.6.1
*/
try {
  require_once dirname(__FILE__).'/SEI.php';
  
  session_start(); 
  
  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(false);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////
      
  SessaoSEI::getInstance()->validarLink(); 
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strNomeArquivo = '';
  
  switch($_GET['acao']){ 
  	  	
    case 'pesquisa_solr_ajuda':
      $strConteudo = file_get_contents('ajuda/ajuda_solr.html');
      break;

    case 'assinatura_digital_ajuda':
      $strConteudo = file_get_contents('ajuda/assinatura_digital_ajuda.html');
      $strConteudo = str_replace('[servidor]', ConfiguracaoSEI::getInstance()->getValor('SEI','URL'), $strConteudo);
      $strConteudo = str_replace('[assinador_versao]', ASSINADOR_VERSAO, $strConteudo);
      break;
      
    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }

  InfraPagina::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1');
  echo $strConteudo;
  
}catch(Exception $e){
  die('Erro realizando download do anexo:'.$e->__toString());
}
?>