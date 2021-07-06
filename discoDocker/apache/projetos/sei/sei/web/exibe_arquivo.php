<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 17/07/2012 - criado por mkr
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
  
  SessaoSEI::getInstance()->validarAuditarPermissao($_GET['acao']);
  
  switch($_GET['acao']){ 
  	  	
    case 'exibir_arquivo':     

      $strNomeDownload = $_GET['nome_download'];
      if (InfraString::isBolVazia($strNomeDownload)){
        $strNomeDownload = $_GET['nome_arquivo'];
      }

      SeiINT::download(null, DIR_SEI_TEMP.'/'.$_GET['nome_arquivo'], $strNomeDownload, 'attachment', true);

      break;
     
    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }
  
}catch(Exception $e){
  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
  PaginaSEI::getInstance()->processarExcecao($e);
}
?>