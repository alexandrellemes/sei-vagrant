<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
*
* 22/10/2013 - criado por mga
*
*
* Versão do Gerador de Código:1.6.1
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

  $objProcedimentoDTO = null;
  $strLinkProcedimento = '';
  $strLinkDocumento = '';

  switch($_GET['acao']){ 
  	  	
    case 'protocolo_visualizar':

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->retStrStaProtocolo();
      $objProtocoloDTO->retDblIdProcedimentoDocumento();
      $objProtocoloDTO->setDblIdProtocolo($_GET['id_protocolo']);
      
      $objProtocoloRN = new ProtocoloRN();
      $objProtocoloDTO = $objProtocoloRN->consultarRN0186($objProtocoloDTO);

      if ($objProtocoloDTO==null){
        throw new InfraException('Protocolo não encontrado.');
      }
      
      if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
        header('Location:'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=protocolo_visualizar&id_procedimento='.$_GET['id_protocolo']));
        die;
      }else{

        if ($_GET['id_procedimento_atual']==$objProtocoloDTO->getDblIdProcedimentoDocumento()) {
          header('Location:'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=arvore_visualizar&id_documento='.$_GET['id_protocolo']));
          die;
        }else{


          $objDocumentoDTO = new DocumentoDTO();
          $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
          $objDocumentoDTO->retStrNomeSerie();
          $objDocumentoDTO->setDblIdDocumento($_GET['id_protocolo']);

          $objDocumentoRN = new DocumentoRN();
          $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

          $strTitulo = DocumentoINT::montarTitulo($objDocumentoDTO);

          $objProcedimentoDTO = new ProcedimentoDTO();
          $objProcedimentoDTO->retStrProtocoloProcedimentoFormatado();
          $objProcedimentoDTO->retStrNomeTipoProcedimento();
          $objProcedimentoDTO->setDblIdProcedimento($objProtocoloDTO->getDblIdProcedimentoDocumento());

          $objProcedimentoRN = new ProcedimentoRN();
          $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);

          $strLinkProcedimento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=protocolo_visualizar&id_procedimento='.$objProtocoloDTO->getDblIdProcedimentoDocumento().'&id_documento='.$_GET['id_protocolo']);
          $strLinkDocumento = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem=arvore_visualizar&id_documento='.$_GET['id_protocolo']);

          $strIdentificacao = '<a id="ancProcesso" href="'.$strLinkProcedimento.'" title="'.PaginaSEI::tratarHTML($objProcedimentoDTO->getStrNomeTipoProcedimento()).'">'.$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado().'</a>';
          $strAcoes = '<a href="'.$strLinkProcedimento.'" tabindex="'.PaginaSEI::getInstance()->getProxTabDados().'"><img id="imgArvore" src="imagens/sei_arvore_32.png" alt="Visualizar Árvore do Processo" title="Visualizar Árvore do Processo"></a>';

          SeiINT::montarCabecalhoConteudo($strIdentificacao, $strAcoes, $strLinkDocumento, $strCss, $strJsInicializar, $strJsCorpo, $strHtml);

        }
      }
      break;

    default:
      throw new InfraException("Ação '".$_GET['acao']."' não reconhecida.");
  }
  
}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
echo '<meta name="viewport" content="width=980">';
PaginaSEI::getInstance()->montarTitle($strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
echo $strCss;
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
function inicializar(){
<?=$strJsInicializar?>
}
<?
echo $strJsCorpo;
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
echo $strHtml;
PaginaSEI::getInstance()->fecharHtml();
?>