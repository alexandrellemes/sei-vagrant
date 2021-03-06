<?
/*
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 15/09/2008 - criado por marcio_db
*
*
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(false);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();
  
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
  }
  
  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);
  PaginaSEI::getInstance()->setBolAutoRedimensionar(false);
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  switch($_GET['acao']){
    
    case 'documento_visualizar':
    case 'documento_visualizar_conteudo_assinatura':  
    	
      //vindo de qualquer outro ponto que n?o seja a ?rvore ou arquivamento valida novamente o acesso
      if ($_GET['acao_origem']!='procedimento_visualizar'){
      	
        //verifica permiss?o de acesso ao documento
        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
        $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS);
        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
        $objPesquisaProtocoloDTO->setDblIdProtocolo($_GET['id_documento']);
        
        $objProtocoloRN = new ProtocoloRN();
        $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
        
        if (count($arrObjProtocoloDTO)==0){
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&id_documento='.$_GET['id_documento']));
          die;
        }
      }
      
      $objDocumentoDTO = new DocumentoDTO();
      $objDocumentoDTO->retDblIdDocumento();
      $objDocumentoDTO->retStrNomeSerie();
      $objDocumentoDTO->retStrNumero();
      $objDocumentoDTO->retStrSiglaUnidadeGeradoraProtocolo();
      $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
      $objDocumentoDTO->retStrStaProtocoloProtocolo();
      $objDocumentoDTO->retStrStaDocumento();
      $objDocumentoDTO->retDblIdDocumentoEdoc();
      $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
      
      $objDocumentoRN = new DocumentoRN();
      $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
      
      if ($objDocumentoDTO==null){
        die('Documento n?o encontrado.');
      }else{

	      $strProtocoloDocumentoFormatado = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();
	      $strNomeSerie = $objDocumentoDTO->getStrNomeSerie();
	
	      if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){
  	      
	        if ($objDocumentoDTO->getDblIdDocumentoEdoc()==null){
            die('Documento sem conte?do.');
	        }

          $objEDocRN = new EDocRN();
          $strResultado = $objEDocRN->consultarHTMLDocumentoRN1204($objDocumentoDTO);

          die($strResultado);

	      }else if ($objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_INTERNO){
	        
	        if ($_GET['acao']=='documento_visualizar'){
          	
	          $objEditorDTO = new EditorDTO();
          	$objEditorDTO->setDblIdDocumento($objDocumentoDTO->getDblIdDocumento());
          	$objEditorDTO->setNumIdBaseConhecimento(null);
          	$objEditorDTO->setStrSinCabecalho('S');
          	$objEditorDTO->setStrSinRodape('S');
            $objEditorDTO->setStrSinCarimboPublicacao('S');
          	$objEditorDTO->setStrSinIdentificacaoVersao('S');
          	$objEditorDTO->setStrSinProcessarLinks('S');
  	        
          	if (isset($_GET['versao'])){
          	  $objEditorDTO->setNumVersao($_GET['versao']);
          	}
          	
  	        $objEditorRN = new EditorRN();
  	        $strResultado = $objEditorRN->consultarHtmlVersao($objEditorDTO);

            $objAuditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
            $objAuditoriaProtocoloDTO->setStrRecurso($_GET['acao']);
            $objAuditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objAuditoriaProtocoloDTO->setDblIdProtocolo($_GET['id_documento']);
            $objAuditoriaProtocoloDTO->setNumIdAnexo(null);
            $objAuditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
            $objAuditoriaProtocoloDTO->setNumVersao($objEditorDTO->getNumVersao());
            
            $objAuditoriaProtocoloRN = new AuditoriaProtocoloRN();
            $objAuditoriaProtocoloRN->auditarVisualizacao($objAuditoriaProtocoloDTO);
  	        
	        }else{
	          
            $objDocumentoDTOAssinatura = new DocumentoDTO();
            $objDocumentoDTOAssinatura->retStrConteudoAssinatura();
            $objDocumentoDTOAssinatura->setDblIdDocumento($_GET['id_documento']);
            
            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTOAssinatura = $objDocumentoRN->consultarRN0005($objDocumentoDTOAssinatura);
            $strResultado = $objDocumentoDTOAssinatura->getStrConteudoAssinatura();
            
            AuditoriaSEI::getInstance()->auditar($_GET['acao']);
            
	        }

          if ($_GET['acao'] == 'documento_visualizar_conteudo_assinatura') {

            $strNomeDownload = $objDocumentoDTO->getStrProtocoloDocumentoFormatado().'_'.$objDocumentoDTO->getStrNomeSerie();
            if (!InfraString::isBolVazia($objDocumentoDTO->getStrNumero())) {
              $strNomeDownload .= '_' .$objDocumentoDTO->getStrNumero();
            }

            PaginaSEI::montarHeaderDownload($strNomeDownload.'.html', 'attachment', null, true);

          }else{
            PaginaSEI::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1', true);
          }

	        die($strResultado);
	        
	      }else if ($objDocumentoDTO->getStrStaProtocoloProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
	
	        $objAnexoDTO = new AnexoDTO();
	        $objAnexoDTO->retNumIdAnexo();
	        $objAnexoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
	        
	        $objAnexoRN = new AnexoRN();
	        $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
	        
	        if (count($arrObjAnexoDTO)){

            $strParamDownload = '';
            if ($_GET['acao'] == 'documento_visualizar_conteudo_assinatura') {
              $strParamDownload = '&download=1';
            }

	        	//redireciona para o download repassando a acao_origem
	          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_download_anexo&acao_origem='.$_GET['acao_origem'].'&id_anexo='.$arrObjAnexoDTO[0]->getNumIdAnexo().$strParamDownload));
	          die;
	        }else{
	          die('Documento n?o cont?m anexo.');
	        }

	      }else{

          if ($_GET['acao']=='documento_visualizar') {

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
            $objDocumentoDTO->setObjInfraSessao(SessaoSEI::getInstance());
            $objDocumentoDTO->setStrLinkDownload('controlador.php?acao=documento_download_anexo');

            $strResultado = $objDocumentoRN->consultarHtmlFormulario($objDocumentoDTO);

            $objAuditoriaProtocoloDTO = new AuditoriaProtocoloDTO();
            $objAuditoriaProtocoloDTO->setStrRecurso($_GET['acao']);
            $objAuditoriaProtocoloDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $objAuditoriaProtocoloDTO->setDblIdProtocolo($_GET['id_documento']);
            $objAuditoriaProtocoloDTO->setNumIdAnexo(null);
            $objAuditoriaProtocoloDTO->setDtaAuditoria(InfraData::getStrDataAtual());
            $objAuditoriaProtocoloDTO->setNumVersao(null);

            $objAuditoriaProtocoloRN = new AuditoriaProtocoloRN();
            $objAuditoriaProtocoloRN->auditarVisualizacao($objAuditoriaProtocoloDTO);

            InfraPagina::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1', true);
            die($strResultado);

          }else{

            $objDocumentoDTOAssinatura = new DocumentoDTO();
            $objDocumentoDTOAssinatura->retStrConteudoAssinatura();
            $objDocumentoDTOAssinatura->setDblIdDocumento($_GET['id_documento']);

            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTOAssinatura = $objDocumentoRN->consultarRN0005($objDocumentoDTOAssinatura);
            $strResultado = $objDocumentoDTOAssinatura->getStrConteudoAssinatura();

            AuditoriaSEI::getInstance()->auditar($_GET['acao']);

            $strNomeDownload = $objDocumentoDTO->getStrProtocoloDocumentoFormatado().'_'.$objDocumentoDTO->getStrNomeSerie();
            if (!InfraString::isBolVazia($objDocumentoDTO->getStrNumero())) {
              $strNomeDownload .= '_' .$objDocumentoDTO->getStrNumero();
            }

            InfraPagina::montarHeaderDownload($strNomeDownload.'.html', 'attachment', null, true);
            die($strResultado);
          }
	      }
      }      
      break;

    case 'base_conhecimento_visualizar':
    	
      $objBaseConhecimentoDTO = new BaseConhecimentoDTO();
      $objBaseConhecimentoDTO->retNumIdBaseConhecimento();
      $objBaseConhecimentoDTO->retStrDescricao();
      $objBaseConhecimentoDTO->retStrStaDocumento();
      $objBaseConhecimentoDTO->retStrSiglaUnidade();
      $objBaseConhecimentoDTO->retStrConteudo();
      $objBaseConhecimentoDTO->setNumIdBaseConhecimento($_GET['id_base_conhecimento']);
      
      $objBaseConhecimentoRN = new BaseConhecimentoRN();
      $objBaseConhecimentoDTO = $objBaseConhecimentoRN->consultar($objBaseConhecimentoDTO);
            
      if ($objBaseConhecimentoDTO==null){
        die('Base de conhecimento n?o encontrada.');
      }else{

        if ($objBaseConhecimentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EDITOR_EDOC){

          $strResultado = $objBaseConhecimentoDTO->getStrConteudo();

        }else {

          $objEditorDTO = new EditorDTO();
          $objEditorDTO->setDblIdDocumento(null);
          $objEditorDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTO->getNumIdBaseConhecimento());
          $objEditorDTO->setStrSinCabecalho('S');
          $objEditorDTO->setStrSinRodape('S');
          $objEditorDTO->setStrSinCarimboPublicacao('N');
          $objEditorDTO->setStrSinIdentificacaoVersao('S');

          $objEditorRN = new EditorRN();
          $strResultado = $objEditorRN->consultarHtmlVersao($objEditorDTO);
        }

        
        $objAnexoRN 	= new AnexoRN();
        $objAnexoDTO 	= new AnexoDTO();
        
        $objAnexoDTO->setNumIdBaseConhecimento($objBaseConhecimentoDTO->getNumIdBaseConhecimento());
        $objAnexoDTO->retNumIdAnexo();
        $objAnexoDTO->retStrNome();
        
        $arrObjAnexoDTO = $objAnexoRN->listarRN0218($objAnexoDTO);
  
        if (count($arrObjAnexoDTO)){
        	// Traz os anexos relacionados com o Edoc
        	$strResultado .= "<br /><br />";
        	$strResultado .= "<b>Anexos:</b>";
        	$strResultado .= "<br />";
        	foreach ($arrObjAnexoDTO as $objAnexoDTO) {
  					$strResultado .= "<a href=".SessaoSEI::getInstance()->assinarLink('controlador.php?acao=base_conhecimento_download_anexo&acao_origem='.$_GET['acao'].'&id_anexo='.$objAnexoDTO->getNumIdAnexo())." target='_blank' style='color:black;' >".$objAnexoDTO->getStrNome()."<a/>";
  					$strResultado .= "<br />";
        	}
        }

        AuditoriaSEI::getInstance()->auditar($_GET['acao']);

        InfraPagina::montarHeaderDownload(null, null, 'Content-Type: text/html; charset=iso-8859-1');
        die($strResultado);
      }      
      break;
      
    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

}catch(Exception $e){

  if ($_GET['acao_origem'] == 'procedimento_visualizar' || $_GET['acao_origem'] == 'protocolo_pesquisar'){
    throw new InfraException('Erro na visualiza??o do documento.', $e);
  }else{
    try{ LogSEI::getInstance()->gravar(InfraException::inspecionar($e)); }catch(Exception $e2){}
    die('Erro na visualiza??o do documento.');
  }
}
?>