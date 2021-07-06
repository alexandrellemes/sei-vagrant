<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 05/09/2008 - criado por mga
* 
* 26/10/2012 - modificado por mkr
*
* Vers�o do Gerador de C�digo: 1.23.0
*
* Vers�o no CVS: $Id$
*/

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $arrNumIdOrgao = array();

  $strRestritoNegado = null;

  $arrComandos = array();

  $strLinkVisualizarSigilosoPublicado = '';

  if (isset($_POST['hdnInicio'])){
      	
    PaginaSEI::getInstance()->salvarCampo('rdoPesquisarEm', $_POST['rdoPesquisarEm']);
    PaginaSEI::getInstance()->salvarCampo('chkSinDocumentosGerados', $_POST['chkSinDocumentosGerados']);
    PaginaSEI::getInstance()->salvarCampo('chkSinDocumentosRecebidos', $_POST['chkSinDocumentosRecebidos']);
    PaginaSEI::getInstance()->salvarCampo('chkSinTramitacao', $_POST['chkSinTramitacao']);

    if(isset($_POST['selOrgao'])){
      $arrNumIdOrgao = $_POST['selOrgao'];
      if (!is_array($arrNumIdOrgao)){
        $arrNumIdOrgao = array($arrNumIdOrgao);
      }
    }

    PaginaSEI::getInstance()->salvarCampo('selOrgao', implode(',',$arrNumIdOrgao));

    PaginaSEI::getInstance()->salvarCampo('chkSinInteressado', $_POST['chkSinInteressado']);
    PaginaSEI::getInstance()->salvarCampo('chkSinRemetente', $_POST['chkSinRemetente']);
    PaginaSEI::getInstance()->salvarCampo('chkSinDestinatario', $_POST['chkSinDestinatario']);

    PaginaSEI::getInstance()->salvarCamposPost(array('q',
  	                                                 'txtContato',
  	                                                 'hdnIdContato',
  	                                                 'txtAssinante',
  	                                                 'hdnIdAssinante',
  	                                                 'txtDescricaoPesquisa', 
  	                                                 'txtObservacaoPesquisa', 	                                                 
  	                                                 'txtAssunto',
  	                                                 'hdnIdAssunto',
  	                                                 'txtUnidade',
  	                                                 'hdnIdUnidade',
  	                                                 'txtProtocoloPesquisa', 
  	                                                 'selTipoProcedimentoPesquisa', 
  	                                                 'selSeriePesquisa', 
  	                                                 'txtNumeroDocumentoPesquisa',
  	                                                 'rdoData',
  	                                                 'txtDataInicio',
  	                                                 'txtDataFim',
                                                     'txtUsuarioGerador1',
  	                                                 'hdnIdUsuarioGerador1',
                                                     'txtUsuarioGerador2',
  	                                                 'hdnIdUsuarioGerador2',
                                                     'txtUsuarioGerador3',
  	                                                 'hdnIdUsuarioGerador3'
  	                                                 ));
    
    
  }else{

    if (isset($_POST['txtPesquisaRapida'])){

      PaginaSEI::getInstance()->salvarCampo('q', $_POST['txtPesquisaRapida']);
      PaginaSEI::getInstance()->salvarCampo('txtProtocoloPesquisa', '');

      $strProtocoloFormatadoLimpo = '';
      
      if (is_numeric(InfraUtil::retirarFormatacao($_POST['txtPesquisaRapida']))){

        $strProtocoloFormatadoLimpo = InfraUtil::retirarFormatacao($_POST['txtPesquisaRapida'],false);

        $objProtocoloRN = new ProtocoloRN();
        //busca pelo numero do processo
        $objProtocoloDTOPesquisa = new ProtocoloDTO();
        $objProtocoloDTOPesquisa->setStrProtocoloFormatadoPesquisa($strProtocoloFormatadoLimpo);
        $arrObjProtocoloDTOPesquisado = $objProtocoloRN->pesquisarProtocoloFormatado($objProtocoloDTOPesquisa);

        if (count($arrObjProtocoloDTOPesquisado)==1){

          $objProtocoloDTO = $arrObjProtocoloDTOPesquisado[0];

        	$bolAcesso = true;
        	
        	if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO || $objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_RESTRITO){
        		
		        //verifica permiss�o de acesso ao documento
		        $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();

            if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_PROCEDIMENTO){
              $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
            }else if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO){
              $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_GERADOS);
            }else if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_RECEBIDO){
              $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_DOCUMENTOS_RECEBIDOS);
            }else{
              $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_TODOS);
            }

		        $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
		        $objPesquisaProtocoloDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());

		        $objProtocoloRN = new ProtocoloRN();
		        $arrObjProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
		        
		        if (count($arrObjProtocoloDTO)==0){

              if ($objProtocoloDTO->getStrStaNivelAcessoGlobal()==ProtocoloRN::$NA_SIGILOSO) {

                $bolAcesso = false;

              }else{

                $objUnidadeDTO = new UnidadeDTO();
                $objUnidadeDTO->retStrSinProtocolo();
                $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

                $objUnidadeRN = new UnidadeRN();
                $objUnidadeDTO = $objUnidadeRN->consultarRN0125($objUnidadeDTO);


                $objInfraParametro = new InfraParametro(BancoSEI::getInstance());
                $numTipoPesquisaRestrito = $objInfraParametro->getValor('SEI_EXIBIR_ARVORE_RESTRITO_SEM_ACESSO', false);

                if ($objUnidadeDTO->getStrSinProtocolo()=='N' && $numTipoPesquisaRestrito!='1') {
                  $strRestritoNegado = 'Unidade atual n�o possui acesso ao ' . ($objProtocoloDTO->getStrStaProtocolo() == ProtocoloRN::$TP_PROCEDIMENTO ? 'processo' : 'documento') . ' restrito ' . $objProtocoloDTO->getStrProtocoloFormatado() . '.';
                  $bolAcesso = false;
                }

              }
     		    }else{
     		    	
     		    	$objProtocoloDTO = $arrObjProtocoloDTO[0];
     		    	
     		    	if ($objProtocoloDTO->getStrStaProtocolo()==ProtocoloRN::$TP_DOCUMENTO_GERADO &&
     		    	    $objProtocoloDTO->getStrSinPublicado()=='S'){
     		    		$strLinkVisualizarSigilosoPublicado = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_visualizar&acao_origem='.$_GET['acao'].'&id_documento='.$objProtocoloDTO->getDblIdProtocolo());
     		    		$_POST['txtPesquisaRapida'] = '';
     		    		$bolAcesso = false;
     		    	}
     		    }
        	}
        	
        	/////////////////////////////////////////
        	//die(nl2br(InfraDebug::getStrDebug()));
        	/////////////////////////////////////////
        	
        	if ($bolAcesso){
            header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem='.$_GET['acao'].'&id_protocolo='.$objProtocoloDTO->getDblIdProtocolo()));
            die;
        	}

        }else if (count($arrObjProtocoloDTOPesquisado) > 1){
          PaginaSEI::getInstance()->salvarCampo('q', '');
          PaginaSEI::getInstance()->salvarCampo('txtProtocoloPesquisa', $_POST['txtPesquisaRapida']);
        }
      }

    }else{
      if ($_GET['sugestao']=='1'){
        PaginaSEI::getInstance()->salvarCampo('q', $_GET['q']);
      }else{
        PaginaSEI::getInstance()->salvarCampo('q', '');
      }  
      PaginaSEI::getInstance()->salvarCampo('txtProtocoloPesquisa', '');
    }

    PaginaSEI::getInstance()->salvarCampo('selOrgao', '');
    PaginaSEI::getInstance()->salvarCampo('rdoPesquisarEm', 'D');
    PaginaSEI::getInstance()->salvarCampo('chkSinDocumentosGerados', 'S');
    PaginaSEI::getInstance()->salvarCampo('chkSinDocumentosRecebidos', 'S');
    PaginaSEI::getInstance()->salvarCampo('chkSinTramitacao', 'N');
  	PaginaSEI::getInstance()->salvarCampo('txtContato', '');
  	PaginaSEI::getInstance()->salvarCampo('hdnIdContato', '');
    PaginaSEI::getInstance()->salvarCampo('chkSinInteressado', 'S');
    PaginaSEI::getInstance()->salvarCampo('chkSinRemetente', 'S');
    PaginaSEI::getInstance()->salvarCampo('chkSinDestinatario', 'S');
  	PaginaSEI::getInstance()->salvarCampo('txtAssinante', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdAssinante', '');
    PaginaSEI::getInstance()->salvarCampo('txtDescricaoPesquisa', ''); 
    PaginaSEI::getInstance()->salvarCampo('txtObservacaoPesquisa', ''); 	                                                 
    PaginaSEI::getInstance()->salvarCampo('txtAssunto', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdAssunto', '');
    PaginaSEI::getInstance()->salvarCampo('txtUnidade', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdUnidade', '');
    //PaginaSEI::getInstance()->salvarCampo('txtProtocoloPesquisa', '');
    PaginaSEI::getInstance()->salvarCampo('selTipoProcedimentoPesquisa', ''); 
    PaginaSEI::getInstance()->salvarCampo('selSeriePesquisa', ''); 
    PaginaSEI::getInstance()->salvarCampo('txtNumeroDocumentoPesquisa', '');
    PaginaSEI::getInstance()->salvarCampo('rdoData', '');
    PaginaSEI::getInstance()->salvarCampo('txtDataInicio', '');
    PaginaSEI::getInstance()->salvarCampo('txtDataFim', '');

    PaginaSEI::getInstance()->salvarCampo('txtUsuarioGerador1', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdUsuarioGerador1', '');
    PaginaSEI::getInstance()->salvarCampo('txtUsuarioGerador2', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdUsuarioGerador2', '');
    PaginaSEI::getInstance()->salvarCampo('txtUsuarioGerador3', '');
    PaginaSEI::getInstance()->salvarCampo('hdnIdUsuarioGerador3', '');
  }
	                                   

  switch($_GET['acao']){
      
    case 'protocolo_pesquisar':
    case 'protocolo_pesquisa_rapida':  
      
      if ($_GET['acao_origem']!='protocolo_pesquisar'){
        $strTitulo = 'Pesquisa';  
      }else{
        $strTitulo = 'Resultado da Pesquisa';  
      }

      $strPesquisarEm = PaginaSEI::getInstance()->recuperarCampo('rdoPesquisarEm');
      $strSinDocumentosGerados = PaginaSEI::getInstance()->recuperarCampo('chkSinDocumentosGerados');		
      $strSinDocumentosRecebidos = PaginaSEI::getInstance()->recuperarCampo('chkSinDocumentosRecebidos');
      $strSinTramitacao = PaginaSEI::getInstance()->recuperarCampo('chkSinTramitacao');

      $strPalavrasPesquisa = PaginaSEI::getInstance()->recuperarCampo('q');

      if (PaginaSEI::getInstance()->recuperarCampo('selOrgao')!='') {
        $arrNumIdOrgao = explode(',', PaginaSEI::getInstance()->recuperarCampo('selOrgao'));
      }else{
        $arrNumIdOrgao = array();
      }

    	$strIdContato = PaginaSEI::getInstance()->recuperarCampo('hdnIdContato');
    	$strNomeContato = PaginaSEI::getInstance()->recuperarCampo('txtContato');
      $strSinInteressado = PaginaSEI::getInstance()->recuperarCampo('chkSinInteressado');
      $strSinRemetente = PaginaSEI::getInstance()->recuperarCampo('chkSinRemetente');
      $strSinDestinatario = PaginaSEI::getInstance()->recuperarCampo('chkSinDestinatario');
    	$strIdAssinante = PaginaSEI::getInstance()->recuperarCampo('hdnIdAssinante');
    	$strNomeAssinante = PaginaSEI::getInstance()->recuperarCampo('txtAssinante');
    	$strDescricaoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtDescricaoPesquisa');
    	$strObservacaoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtObservacaoPesquisa');
      $strIdAssunto = PaginaSEI::getInstance()->recuperarCampo('hdnIdAssunto');
      $strDescricaoAssunto = PaginaSEI::getInstance()->recuperarCampo('txtAssunto'); 
      $numIdUnidade = PaginaSEI::getInstance()->recuperarCampo('hdnIdUnidade');
      $strDescricaoUnidade = PaginaSEI::getInstance()->recuperarCampo('txtUnidade');
    	$strProtocoloPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtProtocoloPesquisa');
      $numIdTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('selTipoProcedimentoPesquisa','null');  
      $numIdSerie = PaginaSEI::getInstance()->recuperarCampo('selSeriePesquisa','null');  
    	$strNumeroDocumentoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtNumeroDocumentoPesquisa');
      $strStaData = PaginaSEI::getInstance()->recuperarCampo('rdoData','0');
     	$strDataInicio = PaginaSEI::getInstance()->recuperarCampo('txtDataInicio');
      $strDataFim = PaginaSEI::getInstance()->recuperarCampo('txtDataFim');
      $strUsuarioGerador1 = PaginaSEI::getInstance()->recuperarCampo('txtUsuarioGerador1');
      $numIdUsuarioGerador1 = PaginaSEI::getInstance()->recuperarCampo('hdnIdUsuarioGerador1');
      $strUsuarioGerador2 = PaginaSEI::getInstance()->recuperarCampo('txtUsuarioGerador2');
      $numIdUsuarioGerador2 = PaginaSEI::getInstance()->recuperarCampo('hdnIdUsuarioGerador2');
      $strUsuarioGerador3 = PaginaSEI::getInstance()->recuperarCampo('txtUsuarioGerador3');
      $numIdUsuarioGerador3 = PaginaSEI::getInstance()->recuperarCampo('hdnIdUsuarioGerador3');

      //print_r($_POST);die;

      if ($strPesquisarEm=='P'){
        $strOcultarCampos = 'display:none;';
        $strDisabledGerados = 'disabled="disabled"';
        $strSinDocumentosGerados = 'N';
        $strDisabledRecebidos = 'disabled="disabled"';
        $strSinDocumentosRecebidos = 'N';
        $strPalavrasPesquisa = '';
        $strNomeAssinante = '';
        $strIdAssinante = '';
        $strTituloContato = 'Interessado:';
        $strTituloDescricaoPesquisa = 'Especifica��o:';
        $numIdSerie = '';
        $strNumeroDocumentoPesquisa = '';
        $strTituloData = 'Data do Processo:';
      }else{
        $strDisabledGerados = '';
        $strDisabledRecebidos = '';
        $strOcultarCampos = '';
        $strTituloContato = 'Contato:';
        $strTituloDescricaoPesquisa = 'Descri��o:';
        $strTituloData = 'Data do Documento:';
      }


      $strItensSelTipoProcedimento 	= TipoProcedimentoINT::montarSelectNome('null','&nbsp;',$numIdTipoProcedimento);
      $strItensSelSerie = SerieINT::montarSelectNomeRI0802('null','&nbsp;',$numIdSerie);

      $strLinkAjaxContatos = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_pesquisa');
      $strLinkAjaxAssinantes = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_usuario_pesquisa');
      $strLinkAjaxUsuarios = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_auto_completar_sigla');
      $strLinkAjaxAssuntoRI1223 = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=assunto_auto_completar_RI1223');
      $strLinkAjaxUnidade = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=unidade_auto_completar_todas');

      if ($strStaData!='0'){
        $strDisplayPeriodoExplicito = 'none';
      }

      $strLinkAjuda = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=pesquisa_solr_ajuda&acao_origem='.$_GET['acao']);

      $q = PaginaSEI::getInstance()->recuperarCampo('q');
            
      $strResultado = '';


      if (($strPesquisarEm=='P' || $strSinDocumentosGerados=='S' || $strSinDocumentosRecebidos=='S') &&
          (!InfraString::isBolVazia($q) ||
           count($arrNumIdOrgao) ||
    	     !InfraString::isBolVazia($strIdContato) ||
    	     !InfraString::isBolVazia($strIdAssinante) ||
    	     !InfraString::isBolVazia($strDescricaoPesquisa) ||
    	     !InfraString::isBolVazia($strObservacaoPesquisa) ||
           !InfraString::isBolVazia($strIdAssunto) ||
           !InfraString::isBolVazia($numIdUnidade) ||
    	     !InfraString::isBolVazia($strProtocoloPesquisa) ||
           !InfraString::isBolVazia($numIdTipoProcedimento) ||
           !InfraString::isBolVazia($numIdSerie) ||
    	     !InfraString::isBolVazia($strNumeroDocumentoPesquisa) ||
           (!InfraString::isBolVazia($strStaData) && $strStaData!='0') ||
         	 !InfraString::isBolVazia($strDataInicio) ||
           !InfraString::isBolVazia($strDataFim) ||
           !InfraString::isBolVazia($numIdUsuarioGerador1) ||
           !InfraString::isBolVazia($numIdUsuarioGerador2) ||
           !InfraString::isBolVazia($numIdUsuarioGerador3))){

        try {

          $objPesquisaProtocoloSolrDTO = new PesquisaProtocoloSolrDTO();
          $objPesquisaProtocoloSolrDTO->setStrPalavrasChave($strPalavrasPesquisa);
          $objPesquisaProtocoloSolrDTO->setStrSinProcessos($strPesquisarEm=='P'?'S':'N');
          $objPesquisaProtocoloSolrDTO->setStrSinDocumentosGerados($strSinDocumentosGerados);
          $objPesquisaProtocoloSolrDTO->setStrSinDocumentosRecebidos($strSinDocumentosRecebidos);
          $objPesquisaProtocoloSolrDTO->setStrSinTramitacao($strSinTramitacao);
          $objPesquisaProtocoloSolrDTO->setArrNumIdOrgao($arrNumIdOrgao);
          $objPesquisaProtocoloSolrDTO->setNumIdContato($strIdContato);
          $objPesquisaProtocoloSolrDTO->setStrSinInteressado($strSinInteressado);
          $objPesquisaProtocoloSolrDTO->setStrSinRemetente($strSinRemetente);
          $objPesquisaProtocoloSolrDTO->setStrSinDestinatario($strSinDestinatario);
          $objPesquisaProtocoloSolrDTO->setNumIdAssinante($strIdAssinante);
          $objPesquisaProtocoloSolrDTO->setStrDescricao($strDescricaoPesquisa);
          $objPesquisaProtocoloSolrDTO->setStrObservacao($strObservacaoPesquisa);
          $objPesquisaProtocoloSolrDTO->setNumIdAssunto($strIdAssunto);
          $objPesquisaProtocoloSolrDTO->setNumIdUnidadeGeradora($numIdUnidade);
          $objPesquisaProtocoloSolrDTO->setStrProtocoloPesquisa($strProtocoloPesquisa);
          $objPesquisaProtocoloSolrDTO->setNumIdTipoProcedimento($numIdTipoProcedimento);
          $objPesquisaProtocoloSolrDTO->setNumIdSerie($numIdSerie);
          $objPesquisaProtocoloSolrDTO->setStrNumero($strNumeroDocumentoPesquisa);
          $objPesquisaProtocoloSolrDTO->setStrStaTipoData($strStaData);
          $objPesquisaProtocoloSolrDTO->setDtaInicio($strDataInicio);
          $objPesquisaProtocoloSolrDTO->setDtaFim($strDataFim);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador1($numIdUsuarioGerador1);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador2($numIdUsuarioGerador2);
          $objPesquisaProtocoloSolrDTO->setNumIdUsuarioGerador3($numIdUsuarioGerador3);
          $objPesquisaProtocoloSolrDTO->setNumInicioPaginacao($_POST['hdnInicio']);
          $objPesquisaProtocoloSolrDTO->setDblIdProcedimento(null);
          $objPesquisaProtocoloSolrDTO->setBolArvore(false);

          $strResultado = SolrProtocolo::executar($objPesquisaProtocoloSolrDTO);

        } catch (Exception $e) {
          PaginaSEI::getInstance()->setStrMensagem(SolrUtil::$MSG_ERRO_PESQUISA, InfraPagina::$TIPO_MSG_AVISO);
          LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        }
      }
      
      break;

    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }

  $objOrgaoDTO = new OrgaoDTO();
  $objOrgaoDTO->retNumIdOrgao();
  $objOrgaoDTO->retStrSigla();
  $objOrgaoDTO->retStrDescricao();
  $objOrgaoDTO->setOrdStrSigla(InfraDTO::$TIPO_ORDENACAO_ASC);

  $objOrgaoRN = new OrgaoRN();
  $arrObjOrgaoDTO = $objOrgaoRN->listarRN1353($objOrgaoDTO);

  $numOrgaos = count($arrObjOrgaoDTO);

  $strOptionsOrgaos='';
  foreach($arrObjOrgaoDTO as $objOrgaoDTO){
    $strOptionsOrgaos.='<option value="'.$objOrgaoDTO->getNumIdOrgao().'"';
    if (isset($_POST['selOrgao'])){
      if (in_array($objOrgaoDTO->getNumIdOrgao(), $arrNumIdOrgao)) {
        $strOptionsOrgaos .= ' selected="selected"';
      }
    }
    $strOptionsOrgaos.='>'.PaginaPublicacoes::tratarHTML($objOrgaoDTO->getStrSigla()).'</option>'."\n";
  }

  $arrComandos[] = '<button type="submit" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton">Pesquisar</button>';

}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
} 
PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
if(0){?><style><?}
?>

#lblPesquisarEm {position:absolute;left:0%;top:30%;width:20%;}
#fldPesquisarEm {position:absolute;left:15.9%;top:0%;height:70%;width:35%;border: .1em solid #666;-moz-border-radius: 5px;border-radius: 5px;-webkit-border-radius: 5px;}
#divOptProcessos {position:absolute;left:8%;top:15%;}
#divOptDocumentos {position:absolute;left:8%;top:55%;}
#chkSinDocumentosGerados {margin-left:2em;}
#chkSinDocumentosRecebidos {margin-left:2em;}
#divSinTramitacao {position:absolute;left:54%;top:30%;}

#divPalavrasPesquisa {<?=$strOcultarCampos?>}
#lblPalavrasPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#q {position:absolute;left:16%;top:0%;width:60%;}
#ancAjuda {position:absolute;left:77.6%;top:0%;}

#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao, .multipleSelect {position:absolute;left:16%;top:0%;width:20%;}

#lblUnidade {position:absolute;left:0%;top:0%;width:20%;}
#txtUnidade {position:absolute;left:16%;top:0%;width:60%;}

#lblAssunto {position:absolute;left:0%;top:0%;width:20%;}
#txtAssunto {position:absolute;left:16%;top:0%;width:60%;}

#divAssinante {<?=$strOcultarCampos?>}
#lblAssinante {position:absolute;left:0%;top:0%;width:20%;}
#txtAssinante {position:absolute;left:16%;top:0%;width:60%;}

#divContatoOpcoes {<?=$strOcultarCampos?>}
#lblContato {position:absolute;left:0%;top:0%;width:20%;}
#txtContato {position:absolute;left:16%;top:0%;width:60%;}

#divSinInteressado {position:absolute;left:16%;top:0%;}
#divSinRemetente {position:absolute;left:36%;top:0%;}
#divSinDestinatario {position:absolute;left:56%;top:0%;}

#lblDescricaoPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#txtDescricaoPesquisa {position:absolute;left:16%;top:0%;width:60%;}
#ancAjudaDescricao {position:absolute;left:77.6%;top:0%;}

#lblObservacaoPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#txtObservacaoPesquisa {position:absolute;left:16%;top:0%;width:60%;}
#ancAjudaObservacao {position:absolute;left:77.6%;top:0%;}

#lblProtocoloPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#txtProtocoloPesquisa {position:absolute;left:16%;top:0%;width:20%;}
#lblProtocoloPesquisaComplemento {position:absolute;left:38%;top:0%;width:25%;<?=$strOcultarCampos?>}

#lblTipoProcedimentoPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#selTipoProcedimentoPesquisa {position:absolute;left:16%;top:0%;width:60.5%;}

#divSeriePesquisa {<?=$strOcultarCampos?>}
#lblSeriePesquisa {position:absolute;left:0%;top:0%;width:20%;}
#selSeriePesquisa {position:absolute;left:16%;top:0%;width:60.5%;}

#divNumeroDocumentoPesquisa {<?=$strOcultarCampos?>}
#lblNumeroDocumentoPesquisa {position:absolute;left:0%;top:0%;width:20%;}
#txtNumeroDocumentoPesquisa {position:absolute;left:16%;top:0%;width:20%;}

#lblData {position:absolute;left:0%;top:0%;width:20%;}
#divOptPeriodoExplicito {position:absolute;left:16%;top:0%;}
#divOptPeriodo30 {position:absolute;left:36%;top:0%;}
#divOptPeriodo60 {position:absolute;left:56%;top:0%;}
 
#txtDataInicio {position:absolute;left:19%;top:0%;width:8%;}
#imgDataInicio {position:absolute;left:28%;top:10%;}
#lblDataE {position:absolute;left:30.5%;top:10%;width:1%;}
#txtDataFim {position:absolute;left:34%;top:0%;width:8%;}
#imgDataFim {position:absolute;left:43%;top:10%;}

#lblUsuarioGerador {position:absolute;left:0%;top:0%;width:20%;}
#txtUsuarioGerador1 {position:absolute;left:16%;top:0%;width:18.5%;}
#txtUsuarioGerador2 {position:absolute;left:36.75%;top:0%;width:18.5%;}
#txtUsuarioGerador3 {position:absolute;left:57.5%;top:0%;width:18.5%;}

#divPeriodoExplicito {display:<?=$strDisplayPeriodoExplicito?>;}

.sugestao{
  font-size: 1.2em;
}

div#conteudo > div.barra {
	border-bottom: .1em solid #909090;
	font-size: 1.2em;
	margin: 0 0 .5em 0;
	padding: 0 0 .5em 0;
	text-align: right;
}

div#conteudo > div.paginas {
	border-top: .1em solid #909090;
	margin: 0 0 5em;
	padding: .5em 0 0 0;
	text-align: center;
	font-size: 1.2em;
}

div#conteudo > div.sem-resultado {
  font-size:1.2em;
	margin: .5em 0 0 0;
}

div#conteudo table {
	border-collapse: collapse;
	border-spacing: 0px;
}

div#conteudo > table {
	margin: 0 0 .5em;
	width: 100%;
}

table.resultado td {
	background: #f0f0f0;
	padding: .3em .5em;
}

div#conteudo > table > tbody > tr:first-child > td {
	background: #e0e0e0;
}

tr.resTituloRegistro td {
  background: #e0e0e0;
}


div#conteudo a.protocoloAberto,
div#conteudo a.protocoloNormal{
	font-size:1.1em !important;
}

div#conteudo a.protocoloAberto:hover,
div#conteudo a.protocoloNormal:hover{
  text-decoration:underline !important;
}


div#conteudo td.metatag > table {
	border-collapse: collapse;
	margin: 0px auto;
	white-space: nowrap;
}

div#conteudo td.metatag > table {
	text-align: left;
	width:75%;
}

div#conteudo td.metatag > table > tbody > tr > td {
	color: #333333;
	font-size: .9em;
	padding: 0 2em;
	width:30%;
}


div#conteudo td.metatag > table > tbody > tr > td:first-child {
	width:45%;
}


div#conteudo td.metatag > table > tbody > tr > td > b {
	color: #006600;
	font-weight: normal;
}

span.pequeno {
	font-size: .9em;
}

div#mensagem {
	background: #e0e0e0;
	border-color: #c0c0c0;
	border-style: solid;
	border-width: .1em;
	margin: 4em auto 0;
	padding: 2em;
}

div#mensagem > span.pequeno {
	color: #909090;
	font-size: .9em;
}

td.resTituloEsquerda img.arvore {
	margin: 0px 5px -3px 0px;
}

td.resTituloDireita {
	text-align:right;
	width:20%;
}

div.paginas, div.paginas * {
	font-size: 12px;
}

div.paginas b {
	font-weight: bold;
}

div.paginas a {
	border-bottom: 1px solid transparent;
	color: #000080;
	text-decoration: none;
}

div.paginas a:hover {
	border-bottom: 1px solid #000000;
	color: #800000;
}

td.resSnippet b {
  font-weight:bold;
}

#divInfraAreaTabela tr.infraTrClara td {padding:.3em;}
#divInfraAreaTabela table.infraTable {border-spacing:0;}

<?
if(0){?></style><?}
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
if(0){?><script><?}
?>

var objAutoCompletarInteressadoRI1225 = null;
var objAutoCompletarUsuario = null;
var objAutoCompletarAssuntoRI1223 = null;
var objAutoCompletarUnidade = null;
var objAutoCompletarUsuarioGerador1 = null;
var objAutoCompletarUsuarioGerador2 = null;
var objAutoCompletarUsuarioGerador3 = null;

function inicializar(){

  <?if ($strRestritoNegado!=null){ ?>
    return;
  <?}?>

  infraOcultarMenuSistemaEsquema();

  $("#selOrgao").multipleSelect({
    filter: false,
    minimumCountSelected: 1,
    selectAll: false,
  });

  objAutoCompletarInteressadoRI1225 = new infraAjaxAutoCompletar('hdnIdContato','txtContato','<?=$strLinkAjaxContatos?>');
  objAutoCompletarInteressadoRI1225.limparCampo = true;
  objAutoCompletarInteressadoRI1225.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtContato').value;
  };
  objAutoCompletarInteressadoRI1225.selecionar('<?=$strIdContato;?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeContato)?>');

  
  objAutoCompletarUsuario = new infraAjaxAutoCompletar('hdnIdAssinante','txtAssinante','<?=$strLinkAjaxAssinantes?>');
  objAutoCompletarUsuario.limparCampo = true;
  objAutoCompletarUsuario.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtAssinante').value + '&sin_usuario_interno=S&sin_usuario_externo=S';
  };
  objAutoCompletarUsuario.selecionar('<?=$strIdAssinante?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeAssinante)?>');
  
  
  objAutoCompletarAssuntoRI1223 = new infraAjaxAutoCompletar('hdnIdAssunto','txtAssunto','<?=$strLinkAjaxAssuntoRI1223?>');
  objAutoCompletarAssuntoRI1223.limparCampo = true;
  objAutoCompletarAssuntoRI1223.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtAssunto').value;
  };
 	objAutoCompletarAssuntoRI1223.selecionar('<?=$strIdAssunto;?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strDescricaoAssunto)?>');
  
  objAutoCompletarUnidade = new infraAjaxAutoCompletar('hdnIdUnidade','txtUnidade','<?=$strLinkAjaxUnidade?>');
  objAutoCompletarUnidade.limparCampo = true;
  objAutoCompletarUnidade.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUnidade').value+'&id_orgao=' + obterOrgaosSelecionados();
  };
 	objAutoCompletarUnidade.selecionar('<?=$numIdUnidade;?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strDescricaoUnidade)?>');
 	
  objAutoCompletarUsuarioGerador1 = new infraAjaxAutoCompletar('hdnIdUsuarioGerador1','txtUsuarioGerador1','<?=$strLinkAjaxUsuarios?>');
  objAutoCompletarUsuarioGerador1.limparCampo = true;
  objAutoCompletarUsuarioGerador1.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUsuarioGerador1').value + '&inativos=1';
  };
  objAutoCompletarUsuarioGerador1.selecionar('<?=$numIdUsuarioGerador1?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strUsuarioGerador1)?>');
  objAutoCompletarUsuarioGerador1.processarResultado = function(id, descricao, complemento){
    if (id!=''){
      document.getElementById('hdnIdUsuarioGerador1').value = id;
      document.getElementById('txtUsuarioGerador1').value = complemento;
    }
  };

  objAutoCompletarUsuarioGerador2 = new infraAjaxAutoCompletar('hdnIdUsuarioGerador2','txtUsuarioGerador2','<?=$strLinkAjaxUsuarios?>');
  objAutoCompletarUsuarioGerador2.limparCampo = true;
  objAutoCompletarUsuarioGerador2.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUsuarioGerador2').value + '&inativos=1';
  };
  objAutoCompletarUsuarioGerador2.selecionar('<?=$numIdUsuarioGerador2?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strUsuarioGerador2)?>');
  objAutoCompletarUsuarioGerador2.processarResultado = function(id, descricao, complemento){
    if (id!=''){
      document.getElementById('hdnIdUsuarioGerador2').value = id;
      document.getElementById('txtUsuarioGerador2').value = complemento;
    }
  };

  objAutoCompletarUsuarioGerador3 = new infraAjaxAutoCompletar('hdnIdUsuarioGerador3','txtUsuarioGerador3','<?=$strLinkAjaxUsuarios?>');
  objAutoCompletarUsuarioGerador3.limparCampo = true;
  objAutoCompletarUsuarioGerador3.prepararExecucao = function(){
    return 'palavras_pesquisa='+document.getElementById('txtUsuarioGerador3').value + '&inativos=1';
  };
  objAutoCompletarUsuarioGerador3.selecionar('<?=$numIdUsuarioGerador3?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strUsuarioGerador3)?>');
  objAutoCompletarUsuarioGerador3.processarResultado = function(id, descricao, complemento){
    if (id!=''){
      document.getElementById('hdnIdUsuarioGerador3').value = id;
      document.getElementById('txtUsuarioGerador3').value = complemento;
    }
  };

  //remover a string null dos combos
  document.getElementById('selTipoProcedimentoPesquisa').options[0].value='';
  document.getElementById('selSeriePesquisa').options[0].value='';
  
  infraProcessarResize();
  
  <? if ($strLinkVisualizarSigilosoPublicado != ''){ ?>
    infraAbrirJanela('<?=$strLinkVisualizarSigilosoPublicado?>','janelaSigilosoPublicado',750,550,'location=0,status=1,resizable=1,scrollbars=1',false);
  <? } ?>
  
  document.getElementById('q').focus();
}


function tratarPeriodo(valor){
  if (valor=='0'){
    document.getElementById('divPeriodoExplicito').style.display='block';
  }else{
    document.getElementById('divPeriodoExplicito').style.display='none';
  }
}

function sugerirUsuarioGerador(){
  objAutoCompletarUsuarioGerador1.selecionar('<?=SessaoSEI::getInstance()->getNumIdUsuario()?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript(SessaoSEI::getInstance()->getStrNomeUsuario())?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript(SessaoSEI::getInstance()->getStrSiglaUsuario())?>');
}

function onSubmitForm(){

  if (!document.getElementById('optProcessos').checked && !document.getElementById('optDocumentos').checked){
    alert('Selecione uma op��o para pesquisa: Processos ou Documentos.');
    return false;
  }

  if (document.getElementById('optDocumentos').checked && !document.getElementById('chkSinDocumentosGerados').checked && !document.getElementById('chkSinDocumentosRecebidos').checked){
    alert('Selecione pelo menos uma das op��es para pesquisa em documentos: Gerados e/ou Externos.');
    return false;
  }

  if (infraTrim(document.getElementById('txtContato').value)!='' && !document.getElementById('chkSinInteressado').checked && !document.getElementById('chkSinRemetente').checked && !document.getElementById('chkSinDestinatario').checked){
    alert('Selecione pelo menos umas das op��es para pesquisa do contato "'+ document.getElementById('txtContato').value + '" (Interessado, Remetente ou Destinat�rio).');
    return false;
  }

  if (document.getElementById('optPeriodoExplicito').checked){

    if ((infraTrim(document.getElementById('txtDataInicio').value)=='') ^ (infraTrim(document.getElementById('txtDataFim').value)=='')){
      alert('Per�odo incompleto.');
      document.getElementById('txtDataInicio').focus()
      return false;
    }

    if (infraTrim(document.getElementById('txtDataInicio').value)!='' && infraTrim(document.getElementById('txtDataFim').value)!='') {
      if (!infraValidarData(document.getElementById('txtDataInicio'))) {
        return false;
      }

      if (!infraValidarData(document.getElementById('txtDataFim'))) {
        return false;
      }

      if (infraCompararDatas(document.getElementById('txtDataInicio').value, document.getElementById('txtDataFim').value)<0) {
        alert('Per�odo de datas inv�lido.');
        document.getElementById('txtDataInicio').focus();
        return false;
      }
    }
  }

  return true;
}

function navegar(inicio) {
  document.getElementById('hdnInicio').value = inicio;
  if (typeof(window.onSubmitForm)=='function' && !window.onSubmitForm()) {
    return;
  }
  document.getElementById('frmPesquisaProtocolo').submit();
}

function tratarSelecaoOrgao(){
  objAutoCompletarUnidade.limpar();
}

function obterOrgaosSelecionados(){
  return $("#selOrgao").multipleSelect("getSelects");
}

function trocarFiltroUsuario(){
  objAutoCompletarInteressadoRI1225.limpar();
}

function formatarExibicao(){
  if (document.getElementById('optProcessos').checked){

    document.getElementById('chkSinDocumentosGerados').checked = false;
    document.getElementById('chkSinDocumentosGerados').disabled = true;
    document.getElementById('chkSinDocumentosRecebidos').checked = false;
    document.getElementById('chkSinDocumentosRecebidos').disabled = true;

    document.getElementById('divPalavrasPesquisa').style.display = 'none';
    document.getElementById('q').value = '';
    document.getElementById('divAssinante').style.display = 'none';
    objAutoCompletarUsuario.limpar();
    document.getElementById('divContatoOpcoes').style.display = 'none';
    document.getElementById('lblContato').innerHTML = 'Interessado:';
    document.getElementById('chkSinInteressado').checked = true;
    document.getElementById('chkSinRemetente').checked = false;
    document.getElementById('chkSinDestinatario').checked = false;
    document.getElementById('lblDescricaoPesquisa').innerHTML = 'Especifica��o:';
    document.getElementById('lblProtocoloPesquisaComplemento').style.display = 'none';
    document.getElementById('divSeriePesquisa').style.display = 'none';
    document.getElementById('selSeriePesquisa').selectedIndex = -1;
    document.getElementById('divNumeroDocumentoPesquisa').style.display = 'none';
    document.getElementById('txtNumeroDocumentoPesquisa').value = '';
    document.getElementById('lblData').innerHTML = 'Data do Processo:';

  }else{

    document.getElementById('chkSinDocumentosGerados').checked = true;
    document.getElementById('chkSinDocumentosGerados').disabled = false;
    document.getElementById('chkSinDocumentosRecebidos').checked = true;
    document.getElementById('chkSinDocumentosRecebidos').disabled = false;

    document.getElementById('divPalavrasPesquisa').style.display = 'block';
    document.getElementById('divAssinante').style.display = 'block';
    document.getElementById('divContatoOpcoes').style.display = 'block';
    document.getElementById('lblContato').innerHTML = 'Contato:';
    document.getElementById('chkSinInteressado').checked = true;
    document.getElementById('chkSinRemetente').checked = true;
    document.getElementById('chkSinDestinatario').checked = true;
    document.getElementById('lblDescricaoPesquisa').innerHTML = 'Descri��o:';
    document.getElementById('lblProtocoloPesquisaComplemento').style.display = 'block';
    document.getElementById('divSeriePesquisa').style.display = 'block';
    document.getElementById('divNumeroDocumentoPesquisa').style.display = 'block';
    document.getElementById('lblData').innerHTML = 'Data do Documento:';
  }
}

<?
if(0){?></script><?}
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
  <?if ($strRestritoNegado!=null){?>

    <div id="divMensagem" class="infraAreaDados">
      <br />
      <label style="font-size:1.4em"><?=$strRestritoNegado?></label>
    </div>

  <?}else{?>

      <form id="frmPesquisaProtocolo" name="frmPesquisaProtocolo" method="post" onsubmit="return onSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  ?>
    <div id="divPesquisarEm" class="infraAreaDados" style="height:6.5em";>
      <label id="lblPesquisarEm" for="" accesskey="" class="infraLabelObrigatorio">Pesquisar em:</label>

      <fieldset id="fldPesquisarEm" class="infraFieldset">

      <div id="divOptProcessos" class="infraDivRadio">
        <input type="radio" id="optProcessos" name="rdoPesquisarEm" value="P" onclick="formatarExibicao()" class="infraRadio" <?=($strPesquisarEm=='P'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblProcessos" for="optProcessos" accesskey="" class="infraLabelRadio" >Processos</label>
      </div>

      <div id="divOptDocumentos" class="infraDivRadio">
        <input type="radio" id="optDocumentos" name="rdoPesquisarEm" value="D" onclick="formatarExibicao()" class="infraRadio" <?=($strPesquisarEm=='D'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblDocumentos" for="optDocumentos" accesskey="" class="infraLabelRadio" >Documentos</label>

        <input type="checkbox" id="chkSinDocumentosGerados" name="chkSinDocumentosGerados" value="S" class="infraCheckbox" <?=$strDisabledGerados?> <?=($strSinDocumentosGerados=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinDocumentosGerados" for="chkSinDocumentosGerados" accesskey="" class="infraLabelCheckbox" >Gerados</label>

        <input type="checkbox" id="chkSinDocumentosRecebidos" name="chkSinDocumentosRecebidos" value="S" class="infraCheckbox" <?=$strDisabledRecebidos?> <?=($strSinDocumentosRecebidos=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinDocumentosRecebidos" for="chkSinDocumentosRecebidos" accesskey="" class="infraLabelCheckbox" >Externos</label>

      </div>
      </fieldset>

      <div id="divSinTramitacao" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinTramitacao" name="chkSinTramitacao" value="S" class="infraCheckbox" <?=($strSinTramitacao=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinTramitacao" for="chkSinTramitacao" accesskey="" class="infraLabelCheckbox" >Com Tramita��o na Unidade</label>
      </div>
    </div>

    <div id="divPalavrasPesquisa" class="infraAreaDados" style="height: 2.8em";>
      <label id="lblPalavrasPesquisa" for="q" accesskey=""  class="infraLabelOpcional">Texto para Pesquisa:</label>
      <input type="text" id="q" name="q" class="infraText" value="<?=PaginaSEI::tratarHTML($strPalavrasPesquisa)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <a id="ancAjuda" href="<?=$strLinkAjuda?>" target="janAjuda" title="Ajuda para Pesquisa" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>
    </div>

    <div id="divOrgao" class="infraAreaDados" style="height: 2.5em; overflow: visible";>
      <label id="lblOrgao" for="selOrgao" accesskey="" class="infraLabelOpcional">�rg�o Gerador:</label>
      <select multiple id="selOrgao" name="selOrgao[]" onchange="tratarSelecaoOrgao()" class="infraSelect multipleSelect" tabindex="<?=PaginaPublicacoes::getInstance()->getProxTabDados()?>">
        <?=$strOptionsOrgaos;?>
      </select>
    </div>

    <div id="divUnidadeGeradora" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblUnidade" for="txtUnidade" class="infraLabelOpcional">Unidade Geradora:</label>
      <input type="text" id="txtUnidade" name="txtUnidade" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($strDescricaoUnidade)?>" />
      <input type="hidden" id="hdnIdUnidade" name="hdnIdUnidade" class="infraText" value="<?=$numIdUnidade?>" />
    </div>

    <div id="divAssunto" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblAssunto" for="txtAssunto" class="infraLabelOpcional">Assunto:</label>
      <input type="text" id="txtAssunto" name="txtAssunto" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($strDescricaoAssunto)?>" />
      <input type="hidden" id="hdnIdAssunto" name="hdnIdAssunto" class="infraText" value="<?=$strIdAssunto?>" />
    </div>

    <div id="divAssinante" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblAssinante" for="txtAssinante" accesskey=""  class="infraLabelOpcional">Assinatura / Autentica��o:</label>
      <input type="text" id="txtAssinante" name="txtAssinante" class="infraText" value="<?=PaginaSEI::tratarHTML($strNomeAssinante);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdAssinante" name="hdnIdAssinante" class="infraText" value="<?=$strIdAssinante?>" />
    </div>

    <div id="divContatoSelecao" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblContato" for="txtContato" accesskey=""  class="infraLabelOpcional"><?=$strTituloContato?></label>
      <input type="text" id="txtContato" name="txtContato" class="infraText" value="<?=PaginaSEI::tratarHTML($strNomeContato);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdContato" name="hdnIdContato" class="infraText" value="<?=$strIdContato?>" />
    </div>

    <div id="divContatoOpcoes" class="infraAreaDados" style="height: 2.5em";>
      <div id="divSinInteressado" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinInteressado" name="chkSinInteressado" value="S" class="infraCheckbox" <?=($strSinInteressado=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinInteressado" for="chkSinInteressado" accesskey="" class="infraLabelCheckbox" >Interessado</label>
      </div>

      <div id="divSinRemetente" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinRemetente" name="chkSinRemetente" value="S" class="infraCheckbox" <?=($strSinRemetente=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinRemetente" for="chkSinRemetente" accesskey="" class="infraLabelCheckbox" >Remetente</label>
      </div>

      <div id="divSinDestinatario" class="infraDivCheckbox">
        <input type="checkbox" id="chkSinDestinatario" name="chkSinDestinatario" value="S" class="infraCheckbox" <?=($strSinDestinatario=='S'?'checked="checked"':'')?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblSinDestinatario" for="chkSinDestinatario" accesskey="" class="infraLabelCheckbox" >Destinat�rio</label>
      </div>
    </div>

    <div id="divDescricaoPesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblDescricaoPesquisa" for="txtDescricaoPesquisa" accesskey="" class="infraLabelOpcional"><?=$strTituloDescricaoPesquisa?></label>
      <input type="text" id="txtDescricaoPesquisa" name="txtDescricaoPesquisa" class="infraText" onkeypress="return infraLimitarTexto(this,event,250);" value="<?=PaginaSEI::tratarHTML($strDescricaoPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <a id="ancAjudaDescricao" href="<?=$strLinkAjuda?>" target="janAjuda" title="Ajuda para Pesquisa" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>
    </div>

    <div id="divObservacaoPesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblObservacaoPesquisa" for="txtObservacaoPesquisa" accesskey="" class="infraLabelOpcional">Obs. desta Unidade:</label>
      <input type="text" id="txtObservacaoPesquisa" name="txtObservacaoPesquisa" class="infraText" onkeypress="return infraLimitarTexto(this,event,250);" value="<?=PaginaSEI::tratarHTML($strObservacaoPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <a id="ancAjudaObservacao" href="<?=$strLinkAjuda?>" target="janAjuda"  title="Ajuda para Pesquisa" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>
    </div>

    <div id="divProtocoloPesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblProtocoloPesquisa" for="txtProtocoloPesquisa" accesskey="" class="infraLabelOpcional">N� SEI:</label>
      <input type="text" id="txtProtocoloPesquisa" name="txtProtocoloPesquisa" class="infraText" value="<?=PaginaSEI::tratarHTML($strProtocoloPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <label id="lblProtocoloPesquisaComplemento" class="infraLabelOpcional">(Processo / Documento)</label>
    </div>

    <div id="divTipoProcedimentoPesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblTipoProcedimentoPesquisa" for="selTipoProcedimentoPesquisa" accesskey="" class="infraLabelOpcional">Tipo do Processo:</label>
      <select id="selTipoProcedimentoPesquisa" name="selTipoProcedimentoPesquisa" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
      <?=$strItensSelTipoProcedimento?>
      </select>
    </div>

    <div id="divSeriePesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblSeriePesquisa" for="selSeriePesquisa" accesskey="" class="infraLabelOpcional">Tipo do Documento:</label>
      <select id="selSeriePesquisa" name="selSeriePesquisa" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
      <?=$strItensSelSerie?>
      </select>
    </div>

    <div id="divNumeroDocumentoPesquisa" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblNumeroDocumentoPesquisa" for="txtNumeroDocumentoPesquisa" accesskey="" class="infraLabelOpcional">N�mero / Nome na �rvore:</label>
      <input type="text" id="txtNumeroDocumentoPesquisa" name="txtNumeroDocumentoPesquisa" class="infraText" value="<?=PaginaSEI::tratarHTML($strNumeroDocumentoPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>

    <div id="divData" class="infraAreaDados" style="height: 2.5em";>
      <label id="lblData" class="infraLabelOpcional"><?=$strTituloData?></label>

      <div id="divOptPeriodoExplicito" class="infraDivRadio">
      <input type="radio" name="rdoData" id="optPeriodoExplicito" value="0" onclick="tratarPeriodo(this.value);" <?=($strStaData=='0'?'checked="checked"':'')?> class="infraRadio"/>
      <label id="lblPeriodoExplicito" accesskey="" for="optPeriodoExplicito" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Per�odo expl�cito</label>
      </div>

      <div id="divOptPeriodo30" class="infraDivRadio">
      <input type="radio" name="rdoData" id="optPeriodo30" value="30" onclick="tratarPeriodo(this.value);" <?=($strStaData=='30'?'checked="checked"':'')?> class="infraRadio"/>
      <label id="lblPeriodo30" accesskey="" for="optPeriodo30" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">30 dias</label>
      </div>

      <div id="divOptPeriodo60" class="infraDivRadio">
      <input type="radio" name="rdoData" id="optPeriodo60" value="60" onclick="tratarPeriodo(this.value);" <?=($strStaData=='60'?'checked="checked"':'')?> class="infraRadio"/>
      <label id="lblPeriodo60" accesskey="" for="optPeriodo60" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">60 dias</label>
      </div>
    </div>

    <div id="divPeriodoExplicito" class="infraAreaDados" style="height:2.5em;">
      <input type="text" id="txtDataInicio" name="txtDataInicio" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($strDataInicio);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgDataInicio" src="/infra_css/imagens/calendario.gif" onclick="infraCalendario('txtDataInicio',this);" alt="Selecionar Data Inicial" title="Selecionar Data Inicial" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <label id="lblDataE" for="txtDataE" accesskey="" class="infraLabelOpcional">&nbsp;at�&nbsp;</label>
      <input type="text" id="txtDataFim" name="txtDataFim" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($strDataFim);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <img id="imgDataFim" src="/infra_css/imagens/calendario.gif" onclick="infraCalendario('txtDataFim',this);" alt="Selecionar Data Final" title="Selecionar Data Final" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>


    <div id="divUsuarioGerador" class="infraAreaDados" style="height:2.5em;">
      <label id="lblUsuarioGerador" accesskey="" class="infraLabelOpcional">Usu�rio Gerador:</label>

      <input type="text" id="txtUsuarioGerador1" name="txtUsuarioGerador1" class="infraText" onfocus="sugerirUsuarioGerador();" value="<?=PaginaSEI::tratarHTML($strUsuarioGerador1);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdUsuarioGerador1" name="hdnIdUsuarioGerador1" class="infraText" value="<?=$numIdUsuarioGerador1?>" />

      <input type="text" id="txtUsuarioGerador2" name="txtUsuarioGerador2" class="infraText" value="<?=PaginaSEI::tratarHTML($strUsuarioGerador2);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdUsuarioGerador2" name="hdnIdUsuarioGerador2" class="infraText" value="<?=$numIdUsuarioGerador2?>" />

      <input type="text" id="txtUsuarioGerador3" name="txtUsuarioGerador3" class="infraText" value="<?=PaginaSEI::tratarHTML($strUsuarioGerador3);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdUsuarioGerador3" name="hdnIdUsuarioGerador3" class="infraText" value="<?=$numIdUsuarioGerador3?>" />
    </div>

    <?
      echo '<div id="conteudo" style="width:99%;" class="infraAreaTabela">';
      echo $strResultado;
      echo '</div>';
      PaginaSEI::getInstance()->montarAreaDebug();
    ?>
      <input type="hidden" id="hdnInicio" name="hdnInicio" value="0" />
    </form>

  <?}?>
<?  
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>