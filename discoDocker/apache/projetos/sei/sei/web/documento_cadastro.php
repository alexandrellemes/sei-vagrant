<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 *
 * 01/08/2008 - criado por leandro_db
 *
 * Vers?o do Gerador de C?digo: 1.13.1
 *
 * Vers?o no CVS: $Id$
 */

try {
  require_once dirname(__FILE__).'/SEI.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('documento_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }

  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }

  if (isset($_GET['id_serie'])){
    $strParametros .= '&id_serie='.$_GET['id_serie'];
  }

  if (isset($_GET['flag_protocolo'])){
    $strParametros .= '&flag_protocolo='.$_GET['flag_protocolo'];
  }

  //PaginaSEI::getInstance()->salvarCamposPost(array());

  $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

  $objDocumentoDTO = new DocumentoDTO();

  $arrComandos = array();

  switch($_GET['acao']){

    case 'documento_upload_anexo':
      if (isset($_FILES['filArquivo'])){
        PaginaSEI::getInstance()->processarUpload('filArquivo', DIR_SEI_TEMP, false);
      }
      die;

    case 'documento_gerar':
    case 'documento_receber':

      if ($_GET['acao']=='documento_receber'){
        $strTitulo = 'Registrar Documento Externo';
        $strRotuloData = 'Data do Documento:';
      }elseif ($_GET['acao']=='documento_gerar'){
        $strTitulo = 'Gerar Documento';
        $strRotuloData = 'Data de Elabora??o:';
      }

      $arrComandos[] = '<button type="button" onclick="confirmarDados()" accesskey="C" name="btnSalvar" id="btnSalvar" value="Confirmar Dados" class="infraButton"><span class="infraTeclaAtalho">C</span>onfirmar Dados</button>';
      $arrComandos[] = '<button type="button" accesskey="V" name="btnCancelar" value="Voltar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($_GET['id_serie'])).'\';" class="infraButton"><span class="infraTeclaAtalho">V</span>oltar</button>';

      $objDocumentoDTO->setDblIdDocumento(null);
      $objDocumentoDTO->setDblIdProcedimento($_GET['id_procedimento']);

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setDblIdProtocolo(null);

      if (isset($_GET['id_serie']) && $_GET['id_serie']!=-1){
        $objDocumentoDTO->setNumIdSerie($_GET['id_serie']);
        $objProtocoloDTO->setNumIdSerieDocumento($_GET['id_serie']);
      }else{
        $objDocumentoDTO->setNumIdSerie($_POST['hdnIdSerie']);
        $objProtocoloDTO->setNumIdSerieDocumento($_POST['hdnIdSerie']);
      }

      $objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objDocumentoDTO->setStrNumero($_POST['txtNumero']);
      $objDocumentoDTO->setNumIdTipoConferencia($_POST['selTipoConferencia']);
      $objDocumentoDTO->setStrSinBloqueado('N');

      if ($_GET['acao']=='documento_receber' && $_GET['flag_protocolo']=='S'){
        $arrObjUnidadeDTOReabertura = array();
        $arrUnidadesReabertura = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnUnidadesReabertura']);
        for($i=0; $i< count($arrUnidadesReabertura) ;$i++){
          $objUnidadeDTO  = new UnidadeDTO();
          $objUnidadeDTO->setNumIdUnidade($arrUnidadesReabertura[$i]);
          $arrObjUnidadeDTOReabertura[] = $objUnidadeDTO;
        }
        $objDocumentoDTO->setArrObjUnidadeDTO($arrObjUnidadeDTOReabertura);
      }

      if (!isset($_POST['rdoNivelAcesso'])){
        $objProtocoloDTO->setStrStaNivelAcessoLocal(null);
        //$objProtocoloDTO->setNumIdHipoteseLegal(null);
        //$objProtocoloDTO->setStrStaGrauSigilo(null);
      }else{
        $objProtocoloDTO->setStrStaNivelAcessoLocal($_POST['rdoNivelAcesso']);
        $objProtocoloDTO->setNumIdHipoteseLegal($_POST['selHipoteseLegal']);
        $objProtocoloDTO->setStrStaGrauSigilo($_POST['selGrauSigilo']);
      }

      $objProtocoloDTO->setStrDescricao($_POST['txtDescricao']);

      if ($_GET['acao']=='documento_gerar'){
        $objProtocoloDTO->setDtaGeracao(InfraData::getStrDataAtual());
      }else{
        $objProtocoloDTO->setDtaGeracao($_POST['txtDataElaboracao']);
      }

      $arrAssuntos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnAssuntos']);
      $arrObjAssuntosDTO = array();
      for($x = 0;$x<count($arrAssuntos);$x++){
        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setNumIdAssunto($arrAssuntos[$x]);
        $objRelProtocoloAssuntoDTO->setNumSequencia($x);
        $arrObjAssuntosDTO[$x] = $objRelProtocoloAssuntoDTO;
      }
      $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntosDTO);

      $arrObjParticipantesDTO = array();

      //INTERESSADO
      $arrParticipantes = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnInteressados']);
      for($i=0; $i< count($arrParticipantes) ;$i++){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($arrParticipantes[$i]);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
        $objParticipante->setNumSequencia($i);
        $arrObjParticipantesDTO[] = $objParticipante;
      }

      //REMETENTE
      if ($_POST['hdnIdRemetente']!=''){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($_POST['hdnIdRemetente']);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
        $objParticipante->setNumSequencia(0);
        $arrObjParticipantesDTO[] = $objParticipante;
      }

      //DESTINATARIO
      $arrParticipantes = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnDestinatarios']);
      for($i=0; $i< count($arrParticipantes) ;$i++){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($arrParticipantes[$i]);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
        $objParticipante->setNumSequencia($i);
        $arrObjParticipantesDTO[] = $objParticipante;
      }
      $objProtocoloDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);

      //OBSERVACOES
      $objObservacaoDTO  = new ObservacaoDTO();
      $objObservacaoDTO->setStrDescricao($_POST['txaObservacoes']);
      $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));

      //ANEXOS
      $objProtocoloDTO->setArrObjAnexoDTO(AnexoINT::processarRI0872($_POST['hdnAnexos']));

      $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);
      $objDocumentoDTO->setNumIdTextoPadraoInterno($_POST['selTextoPadrao']);
      $objDocumentoDTO->setStrProtocoloDocumentoTextoBase($_POST['txtProtocoloDocumentoTextoBase']);

      if ($_GET['acao']=='documento_gerar' ){
        $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EDITOR_INTERNO);
      }else	if ($_GET['acao']=='documento_receber' ){
        $objDocumentoDTO->setStrStaDocumento(DocumentoRN::$TD_EXTERNO);
      }

      if ($_POST['hdnFlagDocumentoCadastro']=='2'){

        try{

          $objDocumentoRN = new DocumentoRN();
          $objDocumentoDTO = $objDocumentoRN->cadastrarRN0003($objDocumentoDTO);

          //PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem='.$_GET['acao'].'&acao_retorno='.PaginaSEI::getInstance()->getAcaoRetorno().'&id_procedimento='.$objProtocoloDTO->getDblIdProcedimento().'&id_documento='.$objDocumentoDTO->getDblIdDocumento().'&id_texto_padrao='.$_POST['selTextoPadrao'].'&atualizar_arvore=1'.$strParametros));
          die;

        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'documento_alterar':
    case 'documento_alterar_recebido':
    case 'publicacao_gerar_relacionada':

      if ($_GET['acao']=='documento_alterar_recebido'){
        $strTitulo = 'Alterar Registro de Documento Externo';
        $strRotuloData = 'Data do Documento:';
      }else if ($_GET['acao']=='documento_alterar'){
        $strTitulo = 'Alterar Documento';
        $strRotuloData = 'Data de Elabora??o:';
      }else if ($_GET['acao']=='publicacao_gerar_relacionada'){
        $strTitulo = 'Gerar Publica??o Relacionada';
        $strRotuloData = 'Data de Elabora??o:';
      }

      $arrComandos[] = '<button type="button" onclick="confirmarDados()" accesskey="C" name="btnSalvar" id="btnSalvar" value="Confirmar Dados" class="infraButton"><span class="infraTeclaAtalho">C</span>onfirmar Dados</button>';

      $objProtocoloDTO = new ProtocoloDTO();

      $strObservacao = '';

      if (!isset($_POST['hdnIdDocumento'])){

        $objDocumentoDTO = new DocumentoDTO();

        $objDocumentoDTO->retStrDescricaoProtocolo();
        $objDocumentoDTO->retDblIdProcedimento();
        $objDocumentoDTO->retDblIdDocumento();
        $objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
        $objDocumentoDTO->retNumIdHipoteseLegalProtocolo();
        $objDocumentoDTO->retStrStaGrauSigiloProtocolo();
        $objDocumentoDTO->retDtaGeracaoProtocolo();
        $objDocumentoDTO->retNumIdSerie();
        $objDocumentoDTO->retStrStaDocumento();
        $objDocumentoDTO->retNumIdTipoConferencia();
        $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
        $objDocumentoDTO->retStrSinBloqueado();
        $objDocumentoDTO->retStrNumero();


        $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
        if ($objDocumentoDTO==null){
          throw new InfraException("Registro n?o encontrado.", null, null, false);
        }

        $objProtocoloDTO->setStrDescricao($objDocumentoDTO->getStrDescricaoProtocolo());
        $objProtocoloDTO->setStrStaNivelAcessoLocal($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo());
        $objProtocoloDTO->setNumIdHipoteseLegal($objDocumentoDTO->getNumIdHipoteseLegalProtocolo());
        $objProtocoloDTO->setStrStaGrauSigilo($objDocumentoDTO->getStrStaGrauSigiloProtocolo());
        $objProtocoloDTO->setDtaGeracao($objDocumentoDTO->getDtaGeracaoProtocolo());

        //observa??o buscar
        $objObservacaoDTO  = new ObservacaoDTO();
        $objObservacaoDTO->retStrDescricao();
        $objObservacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
        $objObservacaoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());

        $objObservacaoRN = new ObservacaoRN();
        $objObservacaoDTO = $objObservacaoRN->consultarRN0221($objObservacaoDTO);

        if ($objObservacaoDTO!=null){
          $strObservacao = $objObservacaoDTO->getStrDescricao();
        }

      }else{

        $objDocumentoDTO->setDblIdDocumento($_POST['hdnIdDocumento']);
        $objDocumentoDTO->setDblIdProcedimento($_POST['hdnIdProcedimento']);
        $objDocumentoDTO->setStrStaDocumento($_POST['hdnStaDocumento']);
        $objDocumentoDTO->setStrSinBloqueado($_POST['hdnSinBloqueado']);
        $objDocumentoDTO->setNumIdUnidadeGeradoraProtocolo($_POST['hdnIdUnidadeGeradoraProtocolo']);
        $objProtocoloDTO->setStrDescricao($_POST['txtDescricao']);

        if (!isset($_POST['rdoNivelAcesso'])){
          $objProtocoloDTO->setStrStaNivelAcessoLocal($_POST['hdnStaNivelAcessoLocal']);
          $objProtocoloDTO->setNumIdHipoteseLegal($_POST['hdnIdHipoteseLegal']);
          $objProtocoloDTO->setStrStaGrauSigilo($_POST['hdnStaGrauSigilo']);
        }else{
          $objProtocoloDTO->setStrStaNivelAcessoLocal($_POST['rdoNivelAcesso']);
          $objProtocoloDTO->setNumIdHipoteseLegal($_POST['selHipoteseLegal']);
          $objProtocoloDTO->setStrStaGrauSigilo($_POST['selGrauSigilo']);
        }

        //$objDocumentoDTO->setNumIdUnidadeResponsavel(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

        if ($_GET['acao']=='documento_alterar' || $_GET['acao']=='publicacao_gerar_relacionada'){
          $objDocumentoDTO->setNumIdSerie($_POST['hdnIdSerie']);
          $objProtocoloDTO->setNumIdSerieDocumento($_POST['hdnIdSerie']);
        }else{
          $objDocumentoDTO->setNumIdSerie($_POST['selSerie']);
          $objProtocoloDTO->setNumIdSerieDocumento($_POST['selSerie']);
        }

        $objDocumentoDTO->setStrNumero($_POST['txtNumero']);

        if (isset($_POST['selTipoConferencia'])) {
          $objDocumentoDTO->setNumIdTipoConferencia($_POST['selTipoConferencia']);
        }else{
          $objDocumentoDTO->setNumIdTipoConferencia($_POST['hdnIdTipoConferencia']);
        }

        $objProtocoloDTO->setDtaGeracao($_POST['txtDataElaboracao']);

        //observa??o buscar
        $strObservacao = $_POST['txaObservacoes'];
      }

      $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());

      $arrAssuntos = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnAssuntos']);
      $arrObjAssuntosDTO = array();
      for($x = 0;$x<count($arrAssuntos);$x++){
        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setNumIdAssunto($arrAssuntos[$x]);
        $objRelProtocoloAssuntoDTO->setNumSequencia($x);
        $arrObjAssuntosDTO[$x] = $objRelProtocoloAssuntoDTO;
      }
      $objProtocoloDTO->setArrObjRelProtocoloAssuntoDTO($arrObjAssuntosDTO);


      $arrObjParticipantesDTO = array();

      //REMETENTE
      if ($_POST['hdnIdRemetente']){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($_POST['hdnIdRemetente']);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
        $objParticipante->setNumSequencia(0);
        $arrObjParticipantesDTO[] = $objParticipante;
      }

      //INTERESSADO
      $arrParticipantes = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnInteressados']);
      for($i=0; $i< count($arrParticipantes) ;$i++){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($arrParticipantes[$i]);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_INTERESSADO);
        $objParticipante->setNumSequencia($i);
        $arrObjParticipantesDTO[] = $objParticipante;
      }

      //DESTINATARIO
      $arrParticipantes = PaginaSEI::getInstance()->getArrValuesSelect($_POST['hdnDestinatarios']);
      for($i=0; $i< count($arrParticipantes) ;$i++){
        $objParticipante  = new ParticipanteDTO();
        $objParticipante->setNumIdContato($arrParticipantes[$i]);
        $objParticipante->setStrStaParticipacao(ParticipanteRN::$TP_DESTINATARIO);
        $objParticipante->setNumSequencia($i);
        $arrObjParticipantesDTO[] = $objParticipante;
      }
      $objProtocoloDTO->setArrObjParticipanteDTO($arrObjParticipantesDTO);

      //OBSERVACOES
      $objObservacaoDTO  = new ObservacaoDTO();
      $objObservacaoDTO->setStrDescricao($strObservacao);
      $objProtocoloDTO->setArrObjObservacaoDTO(array($objObservacaoDTO));

      //ANEXOS
      $objProtocoloDTO->setArrObjAnexoDTO(AnexoINT::processarRI0872($_POST['hdnAnexos']));

      $objDocumentoDTO->setObjProtocoloDTO($objProtocoloDTO);

      //$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::getInstance()->montarAncora($objDocumentoDTO->getDblIdDocumento()))).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if ($_POST['hdnFlagDocumentoCadastro']=='2'){
        try{

          $objDocumentoRN = new DocumentoRN();

          if ($_GET['acao']=='documento_alterar' || $_GET['acao']=='documento_alterar_recebido'){
            $objDocumentoRN->alterarRN0004($objDocumentoDTO);
          }else if ($_GET['acao']=='publicacao_gerar_relacionada'){
            $objDocumentoDTO = $objDocumentoRN->gerarPublicacaoRelacionadaRN1207($objDocumentoDTO);
          }

          //PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem='.$_GET['acao'].'&id_documento='.$objDocumentoDTO->getDblIdDocumento().$strParametros.'&atualizar_arvore=1'));
          die;

        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;


    case 'documento_consultar':
    case 'documento_consultar_recebido':

      if ($_GET['acao']=='documento_consultar_recebido'){
        $strTitulo = 'Consultar Registro de Documento Externo';
        $strRotuloData = 'Data do Documento:';
      }else{
        $strTitulo = "Consultar Documento";
        $strRotuloData = 'Data de Elabora??o:';
      }

      $strAncora = '';
      $strParametros = '&id_procedimento='.$_GET['id_procedimento'].'&id_documento='.$_GET['id_documento'];

      $objDocumentoDTO->retStrDescricaoProtocolo();
      $objDocumentoDTO->retDblIdProcedimento();
      $objDocumentoDTO->retDblIdDocumento();
      $objDocumentoDTO->retStrStaNivelAcessoLocalProtocolo();
      $objDocumentoDTO->retNumIdHipoteseLegalProtocolo();
      $objDocumentoDTO->retStrStaGrauSigiloProtocolo();
      $objDocumentoDTO->retDtaGeracaoProtocolo();
      $objDocumentoDTO->retNumIdSerie();
      $objDocumentoDTO->retStrStaDocumento();
      $objDocumentoDTO->retNumIdTipoConferencia();
      $objDocumentoDTO->retNumIdUnidadeGeradoraProtocolo();
      $objDocumentoDTO->retStrSinBloqueado();
      $objDocumentoDTO->retStrNumero();

      $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);

      $objDocumentoRN = new DocumentoRN();
      $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
      if ($objDocumentoDTO==null){
        throw new InfraException("Registro n?o encontrado.");
      }

      $objProtocoloDTO = new ProtocoloDTO();
      $objProtocoloDTO->setStrDescricao($objDocumentoDTO->getStrDescricaoProtocolo());
      $objProtocoloDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
      $objProtocoloDTO->setStrStaNivelAcessoLocal($objDocumentoDTO->getStrStaNivelAcessoLocalProtocolo());
      $objProtocoloDTO->setNumIdHipoteseLegal($objDocumentoDTO->getNumIdHipoteseLegalProtocolo());
      $objProtocoloDTO->setStrStaGrauSigilo($objDocumentoDTO->getStrStaGrauSigiloProtocolo());
      $objProtocoloDTO->setDtaGeracao($objDocumentoDTO->getDtaGeracaoProtocolo());

      //observa??o buscar
      $objObservacaoDTO  = new ObservacaoDTO();
      $objObservacaoDTO->retStrDescricao();
      $objObservacaoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
      $objObservacaoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());

      $objObservacaoRN = new ObservacaoRN();
      $objObservacaoDTO = $objObservacaoRN->consultarRN0221($objObservacaoDTO);

      if ($objObservacaoDTO==null){
        $objObservacaoDTO  = new ObservacaoDTO();
        $objObservacaoDTO->setStrDescricao('');
      }

      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  //ASSUNTOS
  $strAssuntosNegados = 'var arrAssuntosNegados = Array();'."\n";
  $numAssuntos = 0;
  if (!isset($_POST['hdnFlagDocumentoCadastro'])){
    if ($_GET['acao']=='documento_gerar' || $_GET['acao']=='documento_receber'){
      $strItensSelRelProtocoloAssunto = SerieINT::montarSelectSugestaoAssuntos($objDocumentoDTO->getNumIdSerie());
    }else{

      $strItensSelRelProtocoloAssunto = RelProtocoloAssuntoINT::conjuntoPorCodigoDescricaoRI0510($objDocumentoDTO->getDblIdDocumento());

      if ($_GET['acao']=='documento_alterar' || $_GET['acao']=='documento_alterar_recebido'){
        $objRelProtocoloAssuntoDTO = new RelProtocoloAssuntoDTO();
        $objRelProtocoloAssuntoDTO->setDistinct(true);
        $objRelProtocoloAssuntoDTO->retNumIdAssunto();
        $objRelProtocoloAssuntoDTO->retStrSiglaUnidade();
        $objRelProtocoloAssuntoDTO->setDblIdProtocolo($objDocumentoDTO->getDblIdDocumento());
        $objRelProtocoloAssuntoDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual(), InfraDTO::$OPER_DIFERENTE);
        $objRelProtocoloAssuntoRN = new RelProtocoloAssuntoRN();
        $arrObjRelProtocoloAssuntoDTO = $objRelProtocoloAssuntoRN->listarRN0188($objRelProtocoloAssuntoDTO);

        foreach($arrObjRelProtocoloAssuntoDTO as $objRelProtocoloAssuntoDTO){
          $strAssuntosNegados .= 'arrAssuntosNegados['.$numAssuntos++.'] = {id_assunto:\''.$objRelProtocoloAssuntoDTO->getNumIdAssunto().'\',sigla_unidade:\''.$objRelProtocoloAssuntoDTO->getStrSiglaUnidade().'\'};'."\n";;
        }

      }
    }
  }else if ($_POST['hdnFlagDocumentoCadastro']=='1'){
    $_POST['hdnAssuntos'] = '';
    $strItensSelRelProtocoloAssunto = AssuntoINT::montarSelectTrocaSerie($objDocumentoDTO->getNumIdSerie(), $arrAssuntos);
  }

  $strDisplayTextoInicial = 'display:none';
  $strDesabilitarDocumentoTextoBase = '';
  if ($_GET['acao']=='documento_gerar'){
    $strDisplayTextoInicial = 'display:block';
    $strItensSelTextoPadrao = TextoPadraoInternoINT::montarSelectSigla('null','&nbsp;',$_POST['selTextoPadrao']);
  }

  if (($_GET['acao']=='documento_gerar' || $_GET['acao']=='documento_receber') && isset($_GET['id_procedimento']) && !isset($_POST['hdnIdDocumento'])){
    $dblProtocoloInicializacao = $_GET['id_procedimento'];
  }else{
    $dblProtocoloInicializacao = $objProtocoloDTO->getDblIdProtocolo();
  }

  $strDisplayAssuntos = 'display:block';
  $strDisplayInteressados = 'display:block';
  $strDisplayDestinatarios = 'display:block';
  $strDisplayUnidadesReabertura = 'display:none';
  $strDisplaySerieData = 'display:none;';
  $strDisplayNumero = 'display:none;';
  $strDisplayDivFormato = 'display:none;';
  $strDisplayTipoConferencia = 'display:none;';
  $strDisplayDescricao = 'display:none;';
  $strDisplaySerieTitulo = 'display:block;';
  $strDisplayAnexos = 'display:none;';
  $strTituloLabelNumero = '';
  $strClassLabelNumero = 'class="infraLabelOpcional"';
  $strNomeSerie = '';
  $strItensSelSerie = '';
  $strItensSelTipoConferencia = '';
  $strFormatoNatoChecked = '';
  $strFormatoDigitalizadoChecked = '';
  $strFormatoNatoDisabled = '';
  $strFormatoDigitalizadoDisabled = '';
  $selTipoConferenciaDisabled = '';

  if ($_GET['acao']=='documento_gerar' ||
      $_GET['acao']=='documento_alterar' ||
      $_GET['acao']=='documento_consultar' ||
      $_GET['acao']=='publicacao_gerar_relacionada'){

    $strTituloLabelNumero = 'N?mero:';

    //BUSCA DADOS DA SERIE
    $objSerieDTO = new SerieDTO();
    $objSerieDTO->setBolExclusaoLogica(false);
    $objSerieDTO->retNumIdSerie();
    $objSerieDTO->retStrStaNumeracao();
    $objSerieDTO->retStrStaAplicabilidade();
    $objSerieDTO->retStrNome();
    $objSerieDTO->retStrSinInteressado();
    $objSerieDTO->retStrSinDestinatario();
    $objSerieDTO->retNumIdModelo();
    $objSerieDTO->setNumIdSerie($objDocumentoDTO->getNumIdSerie());

    $objSerieRN = new SerieRN();
    $objSerieDTO = $objSerieRN->consultarRN0644($objSerieDTO);

    if ($objSerieDTO==null){
      throw new InfraException("Registro de Tipo de Documento n?o encontrado.");
    }

    $strNomeSerie = $objSerieDTO->getStrNome();

    if ($objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_FORMULARIO_AUTOMATICO || $objDocumentoDTO->getStrStaDocumento() == DocumentoRN::$TD_FORMULARIO_GERADO){
      $strDisplayAssuntos = 'display:none';
    }

    if ($objSerieDTO->getStrSinInteressado()=='N'){
      $strDisplayInteressados = 'display:none';
    }

    if ($objSerieDTO->getStrSinDestinatario()=='N'){
      $strDisplayDestinatarios = 'display:none';
    }

    $strStaNumeracao = $objSerieDTO->getStrStaNumeracao();

    if ($strStaNumeracao==SerieRN::$TN_INFORMADA){
      $strClassLabelNumero = 'class="infraLabelObrigatorio"';
      $strDisplayNumero = 'display:block;';
    }

    $strDisplayDescricao = 'display:block;';

    if (($objSerieDTO->getStrStaAplicabilidade()==SerieRN::$TA_INTERNO || $objSerieDTO->getStrStaAplicabilidade()==SerieRN::$TA_INTERNO_EXTERNO) &&  $objSerieDTO->getNumIdModelo()==null){
      throw new InfraException('Tipo de documento "'.$objSerieDTO->getStrNome().'" n?o possui modelo associado.');
    }

  }else{

    if ($_GET['acao']=='documento_receber' && $_GET['flag_protocolo']=='S'){
      $strDisplayUnidadesReabertura = 'display:block;';
    }

    $strDisplaySerieData = 'display:block;';
    $strTituloLabelNumero = 'N?mero / Nome na ?rvore:';
    $strDisplayDivFormato = 'display:block;';
    $strDisplayNumero = 'display:block;';
    $strDisplayTipoConferencia = 'display:block;';
    $strDisplaySerieTitulo = 'display:none;';
    $strDisplayAnexos = 'display:block';
    $strItensSelSerie = SerieINT::montarSelectNomeExternos('null','&nbsp;',$objDocumentoDTO->getNumIdSerie());
    $strItensSelTipoConferencia = TipoConferenciaINT::montarSelectDescricao('null','&nbsp;',$objDocumentoDTO->getNumIdTipoConferencia());

    if (isset($_POST['rdoFormato'])){
      if ($_POST['rdoFormato']=='N'){
        $strFormatoNatoChecked = ' checked="checked" ';
        $strDisplayTipoConferencia = 'display:none;';
      }else if ($_POST['rdoFormato']=='D'){
        $strFormatoDigitalizadoChecked = ' checked="checked" ';
        $strDisplayTipoConferencia = 'display:block;';
      }
    }else if ($_GET['acao']=='documento_alterar_recebido' || $_GET['acao']=='documento_consultar_recebido'){
      if ($objDocumentoDTO->getNumIdTipoConferencia() == null){
        $strFormatoNatoChecked = ' checked="checked" ';
        $strDisplayTipoConferencia = 'display:none;';
      }else if ($objDocumentoDTO->getNumIdTipoConferencia() != null){
        $strFormatoDigitalizadoChecked = ' checked="checked" ';
        $strDisplayTipoConferencia = 'display:block;';
      }

      if ($objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual()){
        $strFormatoNatoDisabled = 'disabled="disabled"';
        $strFormatoDigitalizadoDisabled = 'disabled="disabled"';
        $selTipoConferenciaDisabled = 'disabled="disabled"';
      }
    }
  }

  //busca somente ao entrar na tela ou vindo da escolha do clone
  if (!isset($_POST['hdnIdDocumento'])){

    //REMETENTE
    $objRemetente = new ParticipanteDTO();
    $objRemetente->retNumIdContato();
    $objRemetente->retStrNomeContato();
    $objRemetente->retStrSiglaContato();
    $objRemetente->setDblIdProtocolo($dblProtocoloInicializacao);
    $objRemetente->setStrStaParticipacao(ParticipanteRN::$TP_REMETENTE);
    $objParticipanteRN = new ParticipanteRN();
    $objRemetente = $objParticipanteRN->consultarRN1008($objRemetente);

    if ($objRemetente!=null){
      $strIdRemetente = $objRemetente->getNumIdContato();
      $strNomeRemetente = ContatoINT::formatarNomeSiglaRI1224($objRemetente->getStrNomeContato(),$objRemetente->getStrSiglaContato());
    }
  }else{
    $strIdRemetente = $_POST['hdnIdRemetente'];
    $strNomeRemetente = $_POST['txtRemetente'];
  }


  //REMETENTE
  //$strItensSelRemetente = ParticipanteINT::conjuntoPorParticipacaoRI0513($dblProtocoloInicializacao,array(ParticipanteRN::$TP_REMETENTE));

  $strInteressadosNegados = 'var arrInteressadosNegados = Array();'."\n";
  $strDestinatariosNegados = 'var arrDestinatariosNegados = Array();'."\n";
  $numInteressados = 0;
  $numDestinatarios = 0;
  if ($_GET['acao']=='documento_alterar' || $_GET['acao']=='documento_alterar_recebido' || $_GET['acao']=='publicacao_gerar_relacionada'){
    $objParticipanteDTO = new ParticipanteDTO();
    $objParticipanteDTO->retNumIdContato();
    $objParticipanteDTO->retStrStaParticipacao();
    $objParticipanteDTO->retStrSiglaUnidade();
    $objParticipanteDTO->setDblIdProtocolo($dblProtocoloInicializacao);
    $objParticipanteDTO->setStrStaParticipacao(array(ParticipanteRN::$TP_INTERESSADO,ParticipanteRN::$TP_DESTINATARIO),InfraDTO::$OPER_IN);
    $objParticipanteDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual(),InfraDTO::$OPER_DIFERENTE);
    $objParticipanteRN = new ParticipanteRN();
    $arrObjParticipanteDTO = $objParticipanteRN->listarRN0189($objParticipanteDTO);

    foreach($arrObjParticipanteDTO as $objParticipanteDTO){
      if ($objParticipanteDTO->getStrStaParticipacao()==ParticipanteRN::$TP_INTERESSADO){
        $strInteressadosNegados .= 'arrInteressadosNegados['.$numInteressados++.'] = {id_contato: \''.$objParticipanteDTO->getNumIdContato().'\', sigla_unidade: \''.$objParticipanteDTO->getStrSiglaUnidade().'\'};'."\n";
      }else if ($objParticipanteDTO->getStrStaParticipacao()==ParticipanteRN::$TP_DESTINATARIO){
        $strDestinatariosNegados .= 'arrDestinatariosNegados['.$numDestinatarios++.'] = {id_contato: \''.$objParticipanteDTO->getNumIdContato().'\', sigla_unidade: \''.$objParticipanteDTO->getStrSiglaUnidade().'\'};'."\n";
      }
    }
  }

  //INTERESSADOS
  $strItensSelInteressado = ParticipanteINT::conjuntoPorParticipacaoRI0513($dblProtocoloInicializacao,array(ParticipanteRN::$TP_INTERESSADO));

  //DESTINATARIO
  $strItensSelDestinatario = ParticipanteINT::conjuntoPorParticipacaoRI0513($dblProtocoloInicializacao,array(ParticipanteRN::$TP_DESTINATARIO));

  //OBSERVACOES
  $strTabObservacoes = ObservacaoINT::tabelaObservacoesOutrasUnidades($dblProtocoloInicializacao);

  $objProcedimentoDTO = new ProcedimentoDTO();
  $objProcedimentoDTO->retNumIdTipoProcedimento();
  $objProcedimentoDTO->retStrStaEstadoProtocolo();
  $objProcedimentoDTO->setDblIdProcedimento($objDocumentoDTO->getDblIdProcedimento());

  $objProcedimentoRN = new ProcedimentoRN();
  $objProcedimentoDTO = $objProcedimentoRN->consultarRN0201($objProcedimentoDTO);
  $numIdTipoProcedimento = $objProcedimentoDTO->getNumIdTipoProcedimento();

  $bolPermitirAlteracaoNivelAcesso = $objInfraParametro->getValor('SEI_ALTERACAO_NIVEL_ACESSO_DOCUMENTO',false);

  ProtocoloINT::montarNivelAcesso(array($numIdTipoProcedimento),
      $objProtocoloDTO,
      (($_GET['acao']=='documento_consultar' || $_GET['acao']=='documento_consultar_recebido') || ($objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo()!=SessaoSEI::getInstance()->getNumIdUnidadeAtual() && $bolPermitirAlteracaoNivelAcesso!='1')),
      $strCssNivelAcesso,
      $strHtmlNivelAcesso,
      $strJsGlobalNivelAcesso,
      $strJsInicializarNivelAcesso,
      $strJsValidacoesNivelAcesso);

  //ANEXOS
  $bolAlteracaoAnexoPermitida = $objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo()==SessaoSEI::getInstance()->getNumIdUnidadeAtual() &&
                                $objDocumentoDTO->getStrSinBloqueado()=='N' &&
                                $_GET['acao']!='documento_consultar' &&
                                $_GET['acao']!='documento_consultar_recebido' &&
                                $objProcedimentoDTO->getStrStaEstadoProtocolo()!=ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO;

  $strDisplayAnexarArquivo = '';
  if (!$bolAlteracaoAnexoPermitida){
    $strDisplayAnexarArquivo = 'display:none;';
  }

  $bolAcaoUpload = SessaoSEI::getInstance()->verificarPermissao('documento_upload_anexo');
  $bolAcaoDownload = SessaoSEI::getInstance()->verificarPermissao('documento_download_anexo');
  $bolAcaoRemoverAnexo = (SessaoSEI::getInstance()->verificarPermissao('documento_remover_anexo') && $bolAlteracaoAnexoPermitida);

  // tamanho m?ximo do arquivo para upload
  $jsArrayExtensoesArq = '';
  $numTamMbDocExterno = $objInfraParametro->getValor('SEI_TAM_MB_DOC_EXTERNO');
  if (InfraString::isBolVazia($numTamMbDocExterno) || !is_numeric($numTamMbDocExterno)){
    throw new InfraException('Valor do par?metro SEI_TAM_MB_DOC_EXTERNO inv?lido.');
  }

  $bolValidarExtensaoArq = $objInfraParametro->getValor('SEI_HABILITAR_VALIDACAO_EXTENSAO_ARQUIVOS'); //string "1" ou "0" (default se n?o hover param no bd)
  // Se adicionado o par?metro SEI_HABILITAR_LISTAGEM_EXTENSAO_ARQUIVOS a apresenta??o fica configur?vel ao desejo do gestor: se 1 exibe as extens?es permitidas na tela. Se 0 n?o exibe na tela. Contudo, se isso for usado mais vezes, penso que deveria ser elaborado como componente/objeto para aumentar o reuso de c?digo e evitar repeti??es.
  if ( $bolValidarExtensaoArq == "1" ) {
    $objArquivoExtensaoDTO = new ArquivoExtensaoDTO();
    $objArquivoExtensaoDTO->retNumTamanhoMaximo();
    $objArquivoExtensaoDTO->retStrExtensao();
    $objArquivoExtensaoDTO->retStrDescricao();
    $objArquivoExtensaoDTO->setOrdStrExtensao(InfraDTO::$TIPO_ORDENACAO_ASC);
    $objArquivoExtensaoRN = new ArquivoExtensaoRN();
    $arrObjArquivoExtensaoDTO = $objArquivoExtensaoRN->listar($objArquivoExtensaoDTO);

    $numExt = count($arrObjArquivoExtensaoDTO);
    for($i = 0; $i < $numExt; $i++){
      $jsArrayExtensoesArq .= '  arrExt['.$i.'] = {nome : "'.InfraString::transformarCaixaBaixa($arrObjArquivoExtensaoDTO[$i]->getStrExtensao()).'", tamanho : '.(($arrObjArquivoExtensaoDTO[$i]->getNumTamanhoMaximo()!=null)?$arrObjArquivoExtensaoDTO[$i]->getNumTamanhoMaximo():$numTamMbDocExterno).'};'."\n";
    }
  }

  $arrIdAnexos = null;
  if ($objProtocoloDTO->getDblIdProtocolo()!=null) {
    //Itens da tabela de anexos
    $objAnexoDTO = new AnexoDTO();
    $objAnexoDTO->retNumIdAnexo();
    $objAnexoDTO->setDblIdProtocolo($objProtocoloDTO->getDblIdProtocolo());

    $objAnexoRN = new AnexoRN();
    $arrIdAnexos = InfraArray::converterArrInfraDTO($objAnexoRN->listarRN0218($objAnexoDTO),'IdAnexo');
  }

  $_POST['hdnAnexos'] = AnexoINT::montarAnexos($arrIdAnexos,
                                               $bolAcaoDownload,
                               'documento_download_anexo',
                                               $arrAcoesDownload,
                                               $bolAcaoRemoverAnexo,
                                               $arrAcoesRemover);

  //Links para uso com AJAX
  $strLinkAjaxTextoPadrao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=texto_padrao_editor_listar');
  $strLinkDocumentoTextoBaseSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_modelo_selecionar&tipo_selecao=1&id_object=objLupaDocumentoTextoBase');
  $strLinkAjaxAssuntoRI1223 = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=assunto_auto_completar_RI1223');
  $strLinkAssuntosSelecao = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assunto_selecionar&tipo_selecao=2&id_object=objLupaAssuntos');
  $strLinkAjaxContatos = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_auto_completar_contexto_RI1225');
  $strLinkAjaxCadastroAutomatico = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contato_cadastro_contexto_temporario');
  $strLinkAjaxDocumentoRecebidoDuplicado = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=documento_recebido_duplicado');
  $strLinkInteressados = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_selecionar&tipo_selecao=2&id_object=objLupaInteressados');
  $strLinkDestinatarios = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_selecionar&tipo_selecao=2&id_object=objLupaDestinatarios');
  $strLinkRemetente = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_selecionar&tipo_selecao=1&id_object=objLupaRemetente');
  $strLinkUnidadesReabertura = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_selecionar_reabertura_processo&tipo_selecao=2&id_object=objLupaUnidadesReabertura&id_procedimento='.$_GET['id_procedimento']);
  $strLinkAlterarContato = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=contato_alterar&acao_origem='.$_GET['acao']);
  $strLinkConsultarAssunto = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assunto_consultar&acao_origem='.$_GET['acao']);

  $strLinkAnexos = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=documento_upload_anexo');

  $strCheckedTextoPadrao = '';
  $strCheckedProtocoloDocumentoTextoBase = '';
  $strCheckedNenhum = '';

  if ($_POST['rdoTextoInicial']=='D'){
    $strCheckedProtocoloDocumentoTextoBase = 'checked="checked"';
  }else if ($_POST['rdoTextoInicial']=='T'){
    $strCheckedTextoPadrao = 'checked="checked"';
  }else{
    $strCheckedNenhum = 'checked="checked"';
  }


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
?>

  #divSerieTitulo {<?=$strDisplaySerieTitulo?>}

  #lblDescricao {position:absolute;left:0;top:0;width:50%;}
  #txtDescricao {position:absolute;left:0;top:40%;width:89.5%;}

  #divUnidadesReabertura {<?=$strDisplayUnidadesReabertura?>}
  #lblUnidadesReabertura {position:absolute;left:0;top:0;}
  #selUnidadesReabertura {position:absolute;left:0;top:25%;width:90%;}
  #divOpcoesUnidadesReabertura {position:absolute;left:91%;top:25%;}

  #divSerieDataElaboracao {<?=$strDisplaySerieData?>}
  #lblSerie {position:absolute;left:0;top:0;}
  #selSerie {position:absolute;left:0;top:40%;width:50%;}

  #lblDataElaboracao {position:absolute;left:53%;top:0;}
  #txtDataElaboracao {position:absolute;left:53%;top:40%;width:13%;}
  #imgDataElaboracao {position:absolute;left:67%;top:45%;}

  #divTextoInicial {<?=$strDisplayTextoInicial?>}
  #fldTextoInicial {position:absolute;left:0;top:0;height:85%;width:88%;}

  #divOptProtocoloDocumentoTextoBase {position:absolute;left:13%;top:22%;}
  #txtProtocoloDocumentoTextoBase {position:absolute;left:40%;top:22%;width:15%}
  #lblOuModeloFavorito {position:absolute;left:57%;top:23%;}
  #btnEscolherDocumentoTextoBase {position:absolute;left:60.5%;top:23%;}

  #divOptTextoPadrao {position:absolute;left:13%;top:47%;}
  #selTextoPadrao {position:absolute;left:40%;top:47%;width:55%}

  #divOptNenhum   {position:absolute;left:13%;top:72%;}

  #divNumero {<?=$strDisplayNumero?>}
  #lblNumero {position:absolute;left:0;top:0;}
  #txtNumero {position:absolute;left:0;top:40%;width:50%;}

  #divFormato {<?=$strDisplayDivFormato?>;}
  #fldFormato {position:absolute;left:0%;top:0%;height:80%;width:48.5%;}
  #ancAjudaFormato > img {height:16px;width:16px;padding:.1em 0;vertical-align:middle;}
  #divOptNato {position:absolute;left:15%;top:<?=(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'15%':'35%');?>}
  #divOptDigitalizado {position:absolute;left:15%;top:<?=(PaginaSEI::getInstance()->isBolNavegadorFirefox()?'50%':'65%');?>}

  #lblTipoConferencia {position:absolute;left:53%;top:19%;width:37%;<?=$strDisplayTipoConferencia?>}
  #selTipoConferencia {position:absolute;left:53%;top:40%;width:37%;<?=$strDisplayTipoConferencia?>}

  #divDescricao {<?=$strDisplayDescricao?>}

  #lblRemetente {position:absolute;left:0;top:0;}
  #txtRemetente {position:absolute;left:0;top:40%;width:89.5%;}
  #divOpcoesRemetente {position:absolute;left:91%;top:40%;}

  #divInteressados {<?=$strDisplayInteressados?>}
  #lblInteressados {position:absolute;left:0;top:0;}
  #txtInteressado {position:absolute;left:0;top:18%;width:50%;}
  #selInteressados {position:absolute;left:0;top:38%;width:90%;height:56%;}
  #divOpcoesInteressados {position:absolute;left:91%;top:38%;}

  #divDestinatarios {<?=$strDisplayDestinatarios?>}
  #lblDestinatarios {position:absolute;left:0;top:0;}
  #txtDestinatario {position:absolute;left:0;top:18%;width:50%;}
  #selDestinatarios {position:absolute;left:0;top:38%;width:90%;height:56%;}
  #divOpcoesDestinatarios {position:absolute;left:91%;top:38%;}

  #divAssuntos {<?=$strDisplayAssuntos?>}
  #lblAssuntos {position:absolute;left:0;top:0;}
  #txtAssunto {position:absolute;left:0;top:18%;width:50%;}
  #selAssuntos {position:absolute;left:0;top:38%;width:90%;height:56%;}
  #divOpcoesAssuntos {position:absolute;left:91%;top:38%;}

  #lblObservacoes {position:absolute;left:0;top:0;width:50%;}
  #txaObservacoes {position:absolute;left:0;top:25%;width:89.5%;}

  /* #divObservacoesOutras {display:none;} */

<?=$strCssNivelAcesso?>

  #frmAnexos {margin: .5em 0 0 0;border:0;padding:0;<?=$strDisplayAnexos?>}
  #divArquivo {height:5em;<?=$strDisplayAnexarArquivo?>}
  #lblArquivo {position:absolute;left:0;top:0;width:70%;}
  #filArquivo {position:absolute;left:0;top:40%;width:70%;}
  #imgAdicionarArquivo {position:absolute;left:50%;top:40%;}

<?if (PaginaSEI::getInstance()->isBolNavegadorFirefox()) {?>

  #divOptProtocoloDocumentoTextoBase {top:15%;}
  #txtProtocoloDocumentoTextoBase {top:15%;}
  #lblOuModeloFavorito {top:16%;}
  #btnEscolherDocumentoTextoBase {top:16%;}

  #divOptTextoPadrao {top:40%;}
  #selTextoPadrao {top:40%;}

  #divOptNenhum {top:65%;}

<?
}
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

  var objLupaUnidadesReabertura = null;
  var objTabelaAnexos = null;
  var objAjaxTextoPadrao = null;
  var objLupaDocumentoTextoBase = null;
  var objAutoCompletarAssuntoRI1223 = null;
  var objLupaAssuntos = null;
  var objLupaInteressados = null;
  var objAutoCompletarInteressadoRI1225 = null;
  var objLupaRemetente = null;
  var objAutoCompletarRemetenteRI1226 = null;
  var objLupaDestinatarios = null;
  var objAutoCompletarDestinatarioRI1226 = null;
  var objContatoCadastroAutomatico = null;
  var objUpload = null;


  <?=$strJsGlobalNivelAcesso?>

  <?=$strAssuntosNegados?>

  <?=$strInteressadosNegados?>

  <?=$strDestinatariosNegados?>

  function inicializar(){

    objLupaUnidadesReabertura = new infraLupaSelect('selUnidadesReabertura','hdnUnidadesReabertura','<?=$strLinkUnidadesReabertura?>');

    if ('<?=$_GET['acao']?>'=='documento_gerar'){
      objAjaxTextoPadrao = new infraAjaxMontarSelect('selTextoPadrao','<?=$strLinkAjaxTextoPadrao?>');
      configurarTextoInicial();
      objLupaDocumentoTextoBase = new infraLupaText('txtProtocoloDocumentoTextoBase','hdnIdDocumentoTextoBase','<?=$strLinkDocumentoTextoBaseSelecao?>');
    }

    if ('<?=$_GET['acao']?>'=='documento_gerar' || '<?=$_GET['acao']?>'=='documento_alterar' || '<?=$_GET['acao']?>'=='publicacao_gerar_relacionada' ||	'<?=$_GET['acao']?>'=='documento_consultar'){
      document.getElementById('divRemetente').style.display='none';
      document.getElementById('divDestinatarios').style.display='';
    }else{
      document.getElementById('divRemetente').style.display='';
      document.getElementById('divDestinatarios').style.display='none';
    }

    //Monta tabela de anexos
    objTabelaAnexos = new infraTabelaDinamica('tblAnexos','hdnAnexos',false,false);
    objTabelaAnexos.gerarEfeitoTabela=true;

    //Monta a??es de download
    <? if (count($arrAcoesDownload)>0){
      foreach(array_keys($arrAcoesDownload) as $id) { ?>
    objTabelaAnexos.adicionarAcoes('<?=$id?>','<?=$arrAcoesDownload[$id]?>');
    <?   }
  } ?>

    //Monta a??es para remover anexos
    <? if (count($arrAcoesRemover)>0){
      foreach(array_keys($arrAcoesRemover) as $id) { ?>
    objTabelaAnexos.adicionarAcoes('<?=$id?>','',false,true);
    <?   }
  } ?>

    //Se consultando desabilita campos e n?o monta a??es para remover anexos
    if ('<?=$_GET['acao']?>'=='documento_consultar' || '<?=$_GET['acao']?>'=='documento_consultar_recebido'){
      infraDesabilitarCamposDiv(document.getElementById('divSerieDataElaboracao'));
      infraDesabilitarCamposDiv(document.getElementById('divDescricao'));
      infraDesabilitarCamposDiv(document.getElementById('divNumero'));
      infraDesabilitarCamposDiv(document.getElementById('divFormato'));
      document.getElementById('ancAjudaFormato').style.display = 'none';
      infraDesabilitarCamposDiv(document.getElementById('divAssuntos'));
      infraDesabilitarCamposDiv(document.getElementById('divRemetente'));
      infraDesabilitarCamposDiv(document.getElementById('divInteressados'));
      infraDesabilitarCamposDiv(document.getElementById('divDestinatarios'));
      infraDesabilitarCamposDiv(document.getElementById('divObservacoes'));
      infraDesabilitarCamposDiv(document.getElementById('divNivelAcesso'));
      document.getElementById('divArquivo').style.display = 'none';
      return;
    }

    objAutoCompletarAssuntoRI1223 = new infraAjaxAutoCompletar('hdnIdAssunto','txtAssunto','<?=$strLinkAjaxAssuntoRI1223?>');
    //objAutoCompletarAssuntoRI1223.maiusculas = true;
    //objAutoCompletarAssuntoRI1223.mostrarAviso = true;
    //objAutoCompletarAssuntoRI1223.tempoAviso = 1000;
    //objAutoCompletarAssuntoRI1223.tamanhoMinimo = 3;
    objAutoCompletarAssuntoRI1223.limparCampo = true;
    //objAutoCompletarAssuntoRI1223.bolExecucaoAutomatica = false;

    objAutoCompletarAssuntoRI1223.prepararExecucao = function(){
      return 'palavras_pesquisa='+document.getElementById('txtAssunto').value;
    };


    objAutoCompletarAssuntoRI1223.processarResultado = function(id,descricao,complemento){
      if (id!=''){
        objLupaAssuntos.adicionar(id,descricao,document.getElementById('txtAssunto'));
      }
    };


    //Inicializa campos hidden com valores das listas
    objLupaAssuntos = new infraLupaSelect('selAssuntos','hdnAssuntos','<?=$strLinkAssuntosSelecao?>');

    <? if ($_GET['acao']=='documento_alterar' || $_GET['acao']=='documento_alterar_recebido'){?>
      objLupaAssuntos.processarRemocao = function(itens){
        for(var i=0;i < itens.length;i++){
          for(var j=0;j < arrAssuntosNegados.length; j++){
            if (itens[i].value == arrAssuntosNegados[j].id_assunto){
              alert('Assunto \"' + itens[i].text + '\" n?o pode ser removido porque foi adicionado pela unidade ' + arrAssuntosNegados[j].sigla_unidade + '.');
              return false;
            }
          }
        }
        return true;
      }
    <?}?>

    objLupaAssuntos.processarAlteracao = function (pos, texto, valor){
      seiConsultarAssunto(valor, 'selAssuntos','frmDocumentoCadastro','<?=$strLinkConsultarAssunto?>');
    }


    document.getElementById('selAssuntos').ondblclick = function(e){
      objLupaAssuntos.alterar();
    };

    objAutoCompletarInteressadoRI1225 = new infraAjaxAutoCompletar('hdnIdInteressado','txtInteressado','<?=$strLinkAjaxContatos?>');
    //objAutoCompletarInteressadoRI1225.maiusculas = true;
    //objAutoCompletarInteressadoRI1225.mostrarAviso = true;
    //objAutoCompletarInteressadoRI1225.tempoAviso = 1000;
    //objAutoCompletarInteressadoRI1225.tamanhoMinimo = 3;
    objAutoCompletarInteressadoRI1225.limparCampo = false;
    //objAutoCompletarInteressadoRI1225.bolExecucaoAutomatica = false;

    objAutoCompletarInteressadoRI1225.prepararExecucao = function(){
      return 'palavras_pesquisa='+encodeURIComponent(document.getElementById('txtInteressado').value);
    };

    objAutoCompletarInteressadoRI1225.processarResultado = function(id,descricao,complemento){
      if (id!=''){
        objLupaInteressados.adicionar(id,descricao,document.getElementById('txtInteressado'));
      }
    };

    infraAdicionarEvento(document.getElementById('txtInteressado'),'keyup',tratarEnterInteressado);

    objLupaInteressados = new infraLupaSelect('selInteressados','hdnInteressados','<?=$strLinkInteressados?>');

    objLupaInteressados.processarAlteracao = function (pos, texto, valor){
      seiAlterarContato(valor, 'selInteressados','frmDocumentoCadastro','<?=$strLinkAlterarContato?>');
    }

    objLupaInteressados.processarRemocao = function(itens){
      for(var i=0;i < itens.length;i++){
        for(var j=0;j < arrInteressadosNegados.length; j++){
          if (itens[i].value == arrInteressadosNegados[j].id_contato) {
            alert('Interessado \"' + itens[i].text + '\" n?o pode ser removido porque foi adicionado pela unidade ' + arrInteressadosNegados[j].sigla_unidade + '.');
            return false;
          }
        }
      }
      return true;
    }

    document.getElementById('selInteressados').ondblclick = function(e){
      objLupaInteressados.alterar();
    };

    objLupaRemetente = new infraLupaText('txtRemetente','hdnIdRemetente','<?=$strLinkRemetente?>');

    objLupaRemetente.finalizarSelecao = function(){
      objAutoCompletarRemetenteRI1226.selecionar(document.getElementById('hdnIdRemetente').value,document.getElementById('txtRemetente').value);
    }

    objLupaRemetente.processarAlteracao = function (id, texto){
      seiAlterarContato(id, 'txtRemetente', 'frmDocumentoCadastro','<?=$strLinkAlterarContato?>');
    }

    objAutoCompletarRemetenteRI1226 = new infraAjaxAutoCompletar('hdnIdRemetente','txtRemetente','<?=$strLinkAjaxContatos?>');
    //objAutoCompletarRemetenteRI1226.maiusculas = true;
    //objAutoCompletarRemetenteRI1226.mostrarAviso = true;
    //objAutoCompletarRemetenteRI1226.tempoAviso = 1000;
    //objAutoCompletarRemetenteRI1226.tamanhoMinimo = 3;
    objAutoCompletarRemetenteRI1226.limparCampo = false;
    //objAutoCompletarRemetenteRI1226.bolExecucaoAutomatica = false;

    objAutoCompletarRemetenteRI1226.prepararExecucao = function(){
      return 'palavras_pesquisa='+encodeURIComponent(document.getElementById('txtRemetente').value);
    };

    objAutoCompletarRemetenteRI1226.processarResultado = function(id,descricao,complemento){
      if (id!=''){
        document.getElementById('hdnIdRemetente').value = id;
        document.getElementById('txtRemetente').value = descricao;
      }
    }
    objAutoCompletarRemetenteRI1226.selecionar('<?=$strIdRemetente?>','<?=PaginaSEI::getInstance()->formatarParametrosJavaScript($strNomeRemetente,false);?>');

    infraAdicionarEvento(document.getElementById('txtRemetente'),'keyup',tratarEnterRemetente);

    objAutoCompletarDestinatarioRI1226 = new infraAjaxAutoCompletar('hdnIdDestinatario','txtDestinatario','<?=$strLinkAjaxContatos?>');
    //objAutoCompletarDestinatarioRI1226.maiusculas = true;
    //objAutoCompletarDestinatarioRI1226.mostrarAviso = true;
    //objAutoCompletarDestinatarioRI1226.tempoAviso = 1000;
    //objAutoCompletarDestinatarioRI1226.tamanhoMinimo = 3;
    objAutoCompletarDestinatarioRI1226.limparCampo = false;
    //objAutoCompletarDestinatarioRI1226.permitirSelecaoGrupo = true;
    //objAutoCompletarDestinatarioRI1226.bolExecucaoAutomatica = false;

    objAutoCompletarDestinatarioRI1226.prepararExecucao = function(){
      return 'palavras_pesquisa='+encodeURIComponent(document.getElementById('txtDestinatario').value);
    };


    objAutoCompletarDestinatarioRI1226.processarResultado = function(id,descricao,complemento){
      if (id!=''){
        objLupaDestinatarios.adicionar(id,descricao,document.getElementById('txtDestinatario'));
      }
    };

    infraAdicionarEvento(document.getElementById('txtDestinatario'),'keyup',tratarEnterDestinatario);

    objLupaDestinatarios = new infraLupaSelect('selDestinatarios','hdnDestinatarios','<?=$strLinkDestinatarios?>');

    objLupaDestinatarios.processarAlteracao = function (pos, texto, valor){
      seiAlterarContato(valor, 'selDestinatarios', 'frmDocumentoCadastro','<?=$strLinkAlterarContato?>');
    }

    objLupaDestinatarios.processarRemocao = function(itens){
      for(var i=0;i < itens.length;i++){
        for(var j=0;j < arrDestinatariosNegados.length; j++){
          if (itens[i].value == arrDestinatariosNegados[j].id_contato) {
            alert('Destinat?rio \"' + itens[i].text + '\" n?o pode ser removido porque foi adicionado pela unidade ' + arrDestinatariosNegados[j].sigla_unidade + '.');
            return false;
          }
        }
      }
      return true;
    }

    document.getElementById('selDestinatarios').ondblclick = function(e){
      objLupaDestinatarios.alterar();
    };

    //Anexos
    objUpload = new infraUpload('frmAnexos','<?=$strLinkAnexos?>');
    objUpload.validar = function(){
      var i = 0;
      var arrExt = [];
      var oFile = document.getElementById("filArquivo");

      if (oFile.length==0) {
        return false;
      }
      var nomeArquivo,bolFileApi=false;
      if(oFile.files==undefined){
        //ie<10
        nomeArquivo=oFile.value.replace("C:\\fakepath\\", "");
      } else {
        bolFileApi=true;
        nomeArquivo = oFile.files[0].name;
      }

      if (nomeArquivo.indexOf('&#')!= -1) {
        alert('Nome do anexo possui caracteres especiais.');
        return false;
      }

      if (bolFileApi && oFile.files[0].size > (<?=$numTamMbDocExterno?> * 1024 * 1024)) {
        alert('Arquivo excede o tamanho m?ximo geral permitido para documentos externos de ' + '<?=$numTamMbDocExterno?>' + 'Mb.');
        return false;
      }

      if ('<?=$bolValidarExtensaoArq?>'=='1'){

        <?=$jsArrayExtensoesArq?>

        if (arrExt.length==0) {
          alert('Nenhuma extens?o de arquivo permitida foi cadastrada.');
          return false;
        }

        nomeArquivo = nomeArquivo.replace(/^.*\./, '').toLowerCase();

        for(i=0; i < arrExt.length; i++){
          if (nomeArquivo == arrExt[i].nome) {
            if (bolFileApi && oFile.files[0].size > (arrExt[i].tamanho * 1024 * 1024)) {
              alert('O tamanho m?ximo permitido para arquivos com extens?o ' + arrExt[i].nome.toUpperCase() + ' ? ' + arrExt[i].tamanho + 'Mb.');
              return false;
            }
            break;
          }
        }

        if (i == arrExt.length){

          var msg = "O arquivo selecionado n?o ? permitido.\n\nSomente s?o permitidos arquivos com as extens?es: ";
          for(i=0; i < arrExt.length; i++) {
            if (i){
              msg += ', ';
            }
            msg += arrExt[i].nome;
          }
          msg += '.';

          alert(msg);

          return false;
        }
      }

      desabilitarBotaoSalvar(true);
      return true;
    };

    objUpload.finalizou = function(arr){
      objTabelaAnexos.limpar();
      objTabelaAnexos.adicionar([arr['nome_upload'],arr['nome'],arr['data_hora'],arr['tamanho'],infraFormatarTamanhoBytes(arr['tamanho']),'<?=PaginaSEI::getInstance()->formatarParametrosJavaScript(SessaoSEI::getInstance()->getStrSiglaUsuario())?>' ,'<?=PaginaSEI::getInstance()->formatarParametrosJavaScript(SessaoSEI::getInstance()->getStrSiglaUnidadeAtual())?>']);
      objTabelaAnexos.adicionarAcoes(arr['nome_upload'],'',false,true);
      desabilitarBotaoSalvar(false);
    };

    objContatoCadastroAutomatico = new infraAjaxComplementar(null,'<?=$strLinkAjaxCadastroAutomatico?>');
    objContatoCadastroAutomatico.tipo = null;
    //objContatoCadastroAutomatico.mostrarAviso = false;
    //objContatoCadastroAutomatico.tempoAviso = 3000;
    //objContatoCadastroAutomatico.limparCampo = false;

    objContatoCadastroAutomatico.prepararExecucao = function(){
      if (this.tipo=='I'){
        return 'nome='+encodeURIComponent(document.getElementById('txtInteressado').value);
      }else if (this.tipo=='R'){
        return 'nome='+encodeURIComponent(document.getElementById('txtRemetente').value);
      }else if (this.tipo=='D'){
        return 'nome='+encodeURIComponent(document.getElementById('txtDestinatario').value);
      }
    };

    objContatoCadastroAutomatico.processarResultado = function(arr){
      if (arr!=null){
        if (this.tipo=='I'){
          objAutoCompletarInteressadoRI1225.processarResultado(arr['IdContato'], document.getElementById('txtInteressado').value, null);
        }else if (this.tipo=='R'){
          objAutoCompletarRemetenteRI1226.selecionar(arr['IdContato'],document.getElementById('txtRemetente').value);
        }else if (this.tipo=='D'){
          objAutoCompletarDestinatarioRI1226.processarResultado(arr['IdContato'], document.getElementById('txtDestinatario').value, null);
        }
      }
    };

    <?=$strJsInicializarNivelAcesso?>

    selecionarFormatoDigitalizado();

    infraEfeitoTabelas();
  }

  function confirmarDados(){
    if (OnSubmitForm()){
      submeter();
    }
  }

  function submeter(){
    desabilitarBotaoSalvar(true);
    document.getElementById('hdnFlagDocumentoCadastro').value = '2';
    document.getElementById('frmDocumentoCadastro').submit();
  }

  function OnSubmitForm() {
    return validarCadastroRI0881();
  }

  function validarCadastroRI0881() {

    if ('<?=$_GET['id_serie']?>'=='-1'){
      if (document.getElementById('hdnIdSerie').value==''){
        alert('Escolha um Tipo de Documento.');
        document.getElementById('selSerie').focus();
        return false;
      }
    }

    if ('<?=$_GET['acao']?>'=='documento_receber' || '<?=$_GET['acao']?>'=='documento_alterar_recebido'){

      if (document.getElementById('hdnIdRemetente').value=='' && infraTrim(document.getElementById('txtRemetente').value!='')) {
        alert('Remetente n?o cadastrado.');
        document.getElementById('txtRemetente').focus();
        return false;
      }

      if (document.getElementById('txtDataElaboracao').value=='') {
        alert('Informe a Data do Documento.');
        document.getElementById('txtDataElaboracao').focus();
        return false;
      }

      if (!infraValidarData(document.getElementById('txtDataElaboracao'))){
        return false;
      }

      if (document.getElementById("optDigitalizado").checked == false && document.getElementById("optNato").checked == false) {
        alert('Informe o Formato do documento externo.');
        return false;
      }

      if (document.getElementById("optDigitalizado").checked == true && !infraSelectSelecionado(document.getElementById("selTipoConferencia"))) {
        alert('Informe o Tipo de Confer?ncia');
        document.getElementById('selTipoConferencia').focus();
        return false;
      }
    }


    if (document.getElementById('lblNumero').className == 'infraLabelObrigatorio' && infraTrim(document.getElementById('txtNumero').value)==''){
      alert('Informe o N?mero.');
      document.getElementById('txtNumero').focus();
      return false;
    }

    <?=$strJsValidacoesNivelAcesso?>


    <?if ($_GET['acao'] == 'documento_receber') {?>

    /*
     if (document.getElementById('filArquivo').value==''){
     alert('Anexo n?o informado.');
     document.getElementById('filArquivo').focus();
     return false;
     }
     */

    var objDocumentoRecebidoDuplicado = new infraAjaxComplementar(null,'<?=$strLinkAjaxDocumentoRecebidoDuplicado?>');
    //objDocumentoRecebidoDuplicado.mostrarAviso = false;
    //objDocumentoRecebidoDuplicado.tempoAviso = 3000;
    //objDocumentoRecebidoDuplicado.limparCampo = false;

    objDocumentoRecebidoDuplicado.prepararExecucao = function(){
      return 'dta_elaboracao='+document.getElementById('txtDataElaboracao').value + '&id_serie=' + document.getElementById('selSerie').value + '&numero=' + document.getElementById('txtNumero').value;
    };

    objDocumentoRecebidoDuplicado.processarResultado = function(arr){
      if (arr!=null){
        if (!confirm('J? existe um documento (' + arr['ProtocoloDocumentoFormatado'] + ') cadastrado com estas caracter?sticas.\n\nDeseja continuar?')){
          return;
        }
      }
      submeter();
    };

    objDocumentoRecebidoDuplicado.executar();

    return false;

    <?}else{?>

    return true;

    <?}?>
  }

  function tratarEnterInteressado(ev){
    var key = infraGetCodigoTecla(ev);

    if (key == 13 && document.getElementById('hdnIdInteressado').value=='' && infraTrim(document.getElementById('txtInteressado').value)!=''){
      if (confirm('Nome inexistente. Deseja incluir?')){
        objContatoCadastroAutomatico.tipo = 'I';
        objContatoCadastroAutomatico.executar();
      }
    }
  }

  function tratarEnterRemetente(ev){
    var key = infraGetCodigoTecla(ev);

    if (key == 13 && document.getElementById('hdnIdRemetente').value=='' && infraTrim(document.getElementById('txtRemetente').value)!=''){
      if (confirm('Nome inexistente. Deseja incluir?')){
        objContatoCadastroAutomatico.tipo = 'R';
        objContatoCadastroAutomatico.executar();
      }
    }
  }

  function tratarEnterDestinatario(ev){
    var key = infraGetCodigoTecla(ev);

    if (key == 13 && document.getElementById('hdnIdDestinatario').value=='' && infraTrim(document.getElementById('txtDestinatario').value)!=''){
      if (confirm('Nome inexistente. Deseja incluir?')){
        objContatoCadastroAutomatico.tipo = 'D';
        objContatoCadastroAutomatico.executar();
      }
    }
  }

  function configurarTextoInicial(){
    if (document.getElementById('optTextoPadrao').checked){
      document.getElementById('selTextoPadrao').style.visibility = 'visible';
      document.getElementById('selTextoPadrao').focus();
      document.getElementById('txtProtocoloDocumentoTextoBase').style.visibility = 'hidden';
      document.getElementById('lblOuModeloFavorito').style.visibility = 'hidden';
      document.getElementById('btnEscolherDocumentoTextoBase').style.visibility = 'hidden';
      document.getElementById('txtProtocoloDocumentoTextoBase').value = '';
    }else if (document.getElementById('optProtocoloDocumentoTextoBase').checked){
      document.getElementById('selTextoPadrao').style.visibility = 'hidden';
      document.getElementById('selTextoPadrao').options.selectedIndex = 0;
      document.getElementById('txtProtocoloDocumentoTextoBase').style.visibility = 'visible';
      document.getElementById('lblOuModeloFavorito').style.visibility = 'visible';
      document.getElementById('btnEscolherDocumentoTextoBase').style.visibility = 'visible';
      document.getElementById('txtProtocoloDocumentoTextoBase').focus();
    }else{
      document.getElementById('selTextoPadrao').options.selectedIndex = 0;
      document.getElementById('txtProtocoloDocumentoTextoBase').value = '';
      document.getElementById('selTextoPadrao').style.visibility = 'hidden';
      document.getElementById('txtProtocoloDocumentoTextoBase').style.visibility = 'hidden';
      document.getElementById('lblOuModeloFavorito').style.visibility = 'hidden';
      document.getElementById('btnEscolherDocumentoTextoBase').style.visibility = 'hidden';
    }
  }

  function trocarSerie(){
    document.getElementById('hdnIdSerie').value = document.getElementById('selSerie').value;
    document.getElementById('frmDocumentoCadastro').submit();
  }

  function desabilitarBotaoSalvar(estado){
    var arrBotoesSalvar = document.getElementsByName('btnSalvar');
    for(var i=0; i < arrBotoesSalvar.length; i++){
      arrBotoesSalvar[i].disabled = estado;
    }
  }

  function selecionarFormatoDigitalizado() {

    if (document.getElementById('optDigitalizado').checked){
      document.getElementById("lblTipoConferencia").style.display = "block";
      document.getElementById("selTipoConferencia").style.display = "block";
    } else {
      document.getElementById("lblTipoConferencia").style.display = "none";
      document.getElementById("selTipoConferencia").style.display = "none";
      document.getElementById("selTipoConferencia").selectedIndex = 0;
    }
  }


<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
  <form id="frmDocumentoCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>" style="display:inline;">
    <?
    //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
    PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
    //PaginaSEI::getInstance()->montarAreaValidacao();
    ?>
    <div id="divSerieTitulo" class="tituloProcessoDocumento">
      <label id="lblSerieTitulo"><?=PaginaSEI::tratarHTML($strNomeSerie)?></label>
    </div>

    <div id="divUnidadesReabertura" class="infraAreaDados" style="height:7em;">
      <label id="lblUnidadesReabertura" for="selUnidadesReabertura" class="infraLabelOpcional">Reabrir processo nas unidades:</label>
      <select id="selUnidadesReabertura" name="selUnidadesReabertura" size="3" multiple="multiple" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
      </select>
      <div id="divOpcoesUnidadesReabertura">
        <img id="imgLupaUnidadesReabertura" onclick="objLupaUnidadesReabertura.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Selecionar Unidade" title="Selecionar Unidade" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgExcluirUnidadesReabertura" onclick="objLupaUnidadesReabertura.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Unidades Selecionadas" title="Remover Unidades Selecionadas" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>

    <div id="divSerieDataElaboracao" class="infraAreaDados" style="height:4.6em;">
      <label id="lblSerie" for="selSerie" accesskey="" class="infraLabelObrigatorio">Tipo do Documento:</label>
      <select id="selSerie" name="selSerie" onchange="trocarSerie();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
        <?=$strItensSelSerie?>
      </select>

      <label id="lblDataElaboracao" for="txtDataElaboracao" class="infraLabelObrigatorio"><?=$strRotuloData;?></label>
      <input type="text" id="txtDataElaboracao" name="txtDataElaboracao" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($objProtocoloDTO->getDtaGeracao())?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
      <img id="imgDataElaboracao" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/calendario.gif" alt="Selecionar Data" onclick="infraCalendario('txtDataElaboracao',this);" title="Selecionar Data" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>

    <div id="divTextoInicial" class="infraAreaDados" style="height:10em;">
      <fieldset id="fldTextoInicial" class="infraFieldset">
        <legend class="infraLegend">&nbsp;Texto Inicial&nbsp;</legend>

        <div id="divOptProtocoloDocumentoTextoBase" class="infraDivRadio">
          <input type="radio" <?=$strCheckedProtocoloDocumentoTextoBase?> <?=$strDesabilitarDocumentoTextoBase?> onclick="configurarTextoInicial();" name="rdoTextoInicial" id="optProtocoloDocumentoTextoBase" value="D" class="infraRadio"/>
          <span id="spnProtocoloDocumentoTextoBase"><label id="lblProtocoloDocumentoTextoBase" for="optProtocoloDocumentoTextoBase" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Documento Modelo</label></span>
        </div>

        <input type="text" id="txtProtocoloDocumentoTextoBase" name="txtProtocoloDocumentoTextoBase" onkeypress="return infraMascaraNumero(this, event)" maxlength="<?=DIGITOS_DOCUMENTO?>" class="infraText" value="<?=PaginaSEI::tratarHTML($_POST['txtProtocoloDocumentoTextoBase'])?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <label id="lblOuModeloFavorito">ou</label>
        <button type="button" id="btnEscolherDocumentoTextoBase" name="btnEscolherDocumentoTextoBase" onclick="objLupaDocumentoTextoBase.selecionar(700,500);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" class="infraButton">Selecionar nos Favoritos</button>

        <div id="divOptTextoPadrao" class="infraDivRadio">
          <input type="radio" <?=$strCheckedTextoPadrao?> onclick="configurarTextoInicial();" name="rdoTextoInicial" id="optTextoPadrao" value="T" class="infraRadio"/>
          <span id="spnTextoPadrao"><label id="lblTextoPadrao" for="optTextoPadrao" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Texto Padr?o</label></span>
        </div>

        <select id="selTextoPadrao" name="selTextoPadrao" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"  >
          <?=$strItensSelTextoPadrao?>
        </select>

        <div id="divOptNenhum" class="infraDivRadio">
          <input type="radio" <?=$strCheckedNenhum?> onclick="configurarTextoInicial();" name="rdoTextoInicial" id="optNenhum" value="N" class="infraRadio"/>
          <span id="spnNenhum"><label id="lblNenhum" for="optNenhum" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Nenhum</label></span>
        </div>

        <input type="hidden" id="hdnIdDocumentoTextoBase" name="hdnIdDocumentoTextoBase" value="<?=$_POST['hdnIdDocumentoTextoBase']?>" />

      </fieldset>
    </div>

    <div id="divNumero" class="infraAreaDados" style="height:4.6em;">
      <label id="lblNumero" for="txtNumero" <?=$strClassLabelNumero?>><?=$strTituloLabelNumero?></label>
      <input type="text" id="txtNumero" onkeypress="return infraLimitarTexto(this,event,50);" name="txtNumero" class="infraText" value="<?=PaginaSEI::tratarHTML($objDocumentoDTO->getStrNumero())?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    </div>

    <div id="divFormato" class="infraAreaDados" style="height:9em;">

      <fieldset id="fldFormato" class="infraFieldset">
        <legend class="infraLegend">&nbsp;Formato&nbsp;<a href="javascript:void(0);" id="ancAjudaFormato" <?=PaginaSEI::montarTitleTooltip('Selecione a op??o "Nato-digital" se o arquivo a ser registrado foi criado ou recebido por meio eletr?nico.'."\n\n\n".'Selecione a op??o "Digitalizado nesta Unidade" somente se o arquivo a ser registrado foi produzido a partir da digitaliza??o de um documento em papel.')?>><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a> </legend>

        <div id="divOptNato" class="infraDivRadio">
          <input type="radio" name="rdoFormato" id="optNato" value="N" class="infraRadio" <?=$strFormatoNatoChecked?> <?=$strFormatoNatoDisabled?> onclick="selecionarFormatoDigitalizado();" />
          <span id="spnNato"><label id="lblNato" for="optNato" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Nato-digital</label></span>
        </div>

        <div id="divOptDigitalizado" class="infraDivRadio">
          <input type="radio" name="rdoFormato" id="optDigitalizado" value="D" class="infraRadio" <?=$strFormatoDigitalizadoChecked?> <?=$strFormatoDigitalizadoDisabled?> onclick="selecionarFormatoDigitalizado();" />
          <span id="spnDigitalizado"><label id="lblDigitalizado" for="optDigitalizado" class="infraLabelRadio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">Digitalizado nesta Unidade</label></span>
        </div>

      </fieldset>

      <label id="lblTipoConferencia" name="lblTipoConferencia" for="selTipoConferencia" accesskey="" class="infraLabelObrigatorio">Tipo de Confer?ncia: </label>
      <select id="selTipoConferencia" name="selTipoConferencia" <?=$selTipoConferenciaDisabled?> tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <?=$strItensSelTipoConferencia?>
      </select>

    </div>

    <div id="divDescricao" class="infraAreaDados" style="height:4.6em;">
      <label id="lblDescricao" for="txtDescricao" accesskey="" class="infraLabelOpcional">Descri??o:</label>
      <input type="text" id="txtDescricao" name="txtDescricao" onkeypress="return infraMascaraTexto(this,event,250);" maxlength="250" class="infraText" value="<?=PaginaSEI::tratarHTML($objProtocoloDTO->getStrDescricao())?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
    </div>

    <div id="divRemetente" class="infraAreaDados" style="height:4.6em;">
      <label id="lblRemetente" for="txtRemetente" accesskey="R" class="infraLabelOpcional"><span class="infraTeclaAtalho">R</span>emetente:</label>
      <input type="text" id="txtRemetente" name="txtRemetente" class="infraText" value="<?=PaginaSEI::tratarHTML($strNomeRemetente)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdRemetente" name="hdnIdRemetente" value="<?=$strIdRemetente?>" />
      <div id="divOpcoesRemetente">
        <img id="imgPesquisarRemetente" onclick="objLupaRemetente.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Selecionar Remetente" title="Selecionar Remetente" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgAlterarRemetente" onclick="objLupaRemetente.alterar();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/alterar.gif" alt="Consultar/Alterar Dados do Remetente Selecionado" title="Consultar/Alterar Dados do Remetente Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
      </div>
    </div>

    <div id="divInteressados" class="infraAreaDados" style="height:11em;">
      <label id="lblInteressados" for="txtInteressado" accesskey="I" class="infraLabelOpcional"><span class="infraTeclaAtalho">I</span>nteressados:</label>
      <input type="text" id="txtInteressado" name="txtInteressado" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdInteressado" name="hdnIdInteressado" class="infraText" value="" />
      <select id="selInteressados" name="selInteressados" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"  >
        <?=$strItensSelInteressado?>
      </select>
      <div id="divOpcoesInteressados">
        <img id="imgSelecionarGrupo" onclick="objLupaInteressados.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" title="Selecionar Contatos para Interessados" alt="Selecionar Contatos para Interessados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgAlterarInteressado" onclick="objLupaInteressados.alterar();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/alterar.gif" alt="Consultar/Alterar Dados do Interessado Selecionado" title="Consultar/Alterar Dados do Interessado Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
        <img id="imgRemoverInteressados" onclick="objLupaInteressados.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Interessados Selecionados" title="Remover Interessados Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
        <br />
        <img id="imgInteressadosAcima" onclick="objLupaInteressados.moverAcima();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_acima_select.gif" alt="Mover Acima Interessado Selecionado" title="Mover Acima Interessado Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgInteressadosAbaixo" onclick="objLupaInteressados.moverAbaixo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_abaixo_select.gif" alt="Mover Abaixo Interessado Selecionado" title="Mover Abaixo Interessado Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>

    <div id="divDestinatarios" class="infraAreaDados" style="height:11em;">
      <label id="lblDestinatarios" for="txtDestinatario" accesskey="e" class="infraLabelOpcional">D<span class="infraTeclaAtalho">e</span>stinat?rios:</label>
      <input type="text" id="txtDestinatario" name="txtDestinatario" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdDestinatario" name="hdnIdDestinatario" class="infraText" value="" />
      <select id="selDestinatarios" name="selDestinatarios" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"  >
        <?=$strItensSelDestinatario?>
      </select>

      <div id="divOpcoesDestinatarios">
        <img id="imgSelecionarGrupo" onclick="objLupaDestinatarios.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" title="Selecionar Contatos para Destinat?rios" alt="Selecionar Contatos para Destinat?rios" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgAlterarDestinatario" onclick="objLupaDestinatarios.alterar();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/alterar.gif" alt="Consultar/Alterar Dados do Destinat?rio Selecionado" title="Consultar/Alterar Dados do Destinat?rio Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
        <img id="imgRemoverDestinatarios" onclick="objLupaDestinatarios.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Destinat?rios Selecionados" title="Remover Destinat?rios Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
        <br />
        <img id="imgDestinatariosAcima" onclick="objLupaDestinatarios.moverAcima();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_acima_select.gif" alt="Mover Acima Destinat?rio Selecionado" title="Mover Acima Destinat?rio Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgDestinatariosAbaixo" onclick="objLupaDestinatarios.moverAbaixo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_abaixo_select.gif" alt="Mover Abaixo Destinat?rio Selecionado" title="Mover Abaixo Destinat?rio Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>

    <div id="divAssuntos" class="infraAreaDados" style="height:11em;">
      <label id="lblAssuntos" for="txtAssunto" accesskey="u" class="infraLabelOpcional">Classifica??o por Ass<span class="infraTeclaAtalho">u</span>ntos:</label>
      <input type="text" id="txtAssunto" name="txtAssunto" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      <input type="hidden" id="hdnIdAssunto" name="hdnIdAssunto" class="infraText" value="" />
      <select id="selAssuntos" name="selAssuntos" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
        <?=$strItensSelRelProtocoloAssunto?>
      </select>
      <div id="divOpcoesAssuntos">
        <img id="imgPesquisarAssuntos" onclick="objLupaAssuntos.selecionar(700,500);" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/lupa.gif" alt="Pesquisa de Assuntos" title="Pesquisa de Assuntos" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgRemoverAssuntos" onclick="objLupaAssuntos.remover();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/remover.gif" alt="Remover Assuntos Selecionados" title="Remover Assuntos Selecionados" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <br />
        <img id="imgAssuntosAcima" onclick="objLupaAssuntos.moverAcima();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_acima_select.gif" alt="Mover Acima Assunto Selecionado" title="Mover Acima Assunto Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <img id="imgAssuntosAbaixo" onclick="objLupaAssuntos.moverAbaixo();" src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/seta_abaixo_select.gif" alt="Mover Abaixo Assunto Selecionado" title="Mover Abaixo Assunto Selecionado" class="infraImg" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
      </div>
    </div>

    <div id="divObservacoes" class="infraAreaDados" style="height:8em;">
      <label id="lblObservacoes" for="txaObservacoes" accesskey="O" class="infraLabelOpcional"><span class="infraTeclaAtalho">O</span>bserva??es desta unidade:</label>
      <textarea id="txaObservacoes" name="txaObservacoes" class="infraTextArea" rows="<?=PaginaSEI::getInstance()->isBolNavegadorFirefox()?'2':'3'?>" onkeypress="return infraLimitarTexto(this,event,1000);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" ><?=PaginaSEI::tratarHTML($objObservacaoDTO->getStrDescricao())?></textarea>
    </div>

    <div id="divObservacoesOutras" class="infraAreaTabela" style="padding-bottom: 1.5em;">
      <?=$strTabObservacoes?>
    </div>

    <?=$strHtmlNivelAcesso?>

    <input type="hidden" id="hdnFlagDocumentoCadastro" name="hdnFlagDocumentoCadastro" value="1"/>
    <input type="hidden" id="hdnAssuntos" name="hdnAssuntos" value="<?=$_POST['hdnAssuntos']?>" />
    <input type="hidden" id="hdnInteressados" name="hdnInteressados" value="<?=PaginaSEI::tratarHTML($_POST['hdnInteressados'])?>" />
    <input type="hidden" id="hdnDestinatarios" name="hdnDestinatarios" value="<?=PaginaSEI::tratarHTML($_POST['hdnDestinatarios'])?>" />
    <input type="hidden" id="hdnIdSerie" name="hdnIdSerie" value="<?=$objDocumentoDTO->getNumIdSerie()?>" />
    <input type="hidden" id="hdnIdUnidadeGeradoraProtocolo" name="hdnIdUnidadeGeradoraProtocolo" value="<?=$objDocumentoDTO->getNumIdUnidadeGeradoraProtocolo()?>" />
    <input type="hidden" id="hdnStaDocumento" name="hdnStaDocumento" value="<?=$objDocumentoDTO->getStrStaDocumento()?>" />
    <input type="hidden" id="hdnIdTipoConferencia" name="hdnIdTipoConferencia" value="<?=$objDocumentoDTO->getNumIdTipoConferencia()?>" />
    <input type="hidden" id="hdnStaNivelAcessoLocal" name="hdnStaNivelAcessoLocal" value="<?=$objProtocoloDTO->getStrStaNivelAcessoLocal()?>" />
    <input type="hidden" id="hdnIdHipoteseLegal" name="hdnIdHipoteseLegal" value="<?=$objProtocoloDTO->getNumIdHipoteseLegal()?>" />
    <input type="hidden" id="hdnStaGrauSigilo" name="hdnStaGrauSigilo" value="<?=$objProtocoloDTO->getStrStaGrauSigilo()?>" />
    <input type="hidden" id="hdnIdDocumento" name="hdnIdDocumento" value="<?=$objDocumentoDTO->getDblIdDocumento()?>" />
    <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?=$objDocumentoDTO->getDblIdProcedimento()?>" />
    <input type="hidden" id="hdnAnexos" name="hdnAnexos" value="<?=$_POST['hdnAnexos']?>"/>
    <input type="hidden" id="hdnIdHipoteseLegalSugestao" name="hdnIdHipoteseLegalSugestao" value="" />
    <input type="hidden" id="hdnIdTipoProcedimento" name="hdnIdTipoProcedimento" value="<?=$numIdTipoProcedimento?>" />
    <input type="hidden" id="hdnUnidadesReabertura" name="hdnUnidadesReabertura" value="<?=$_POST['hdnUnidadesReabertura']?>" />
    <input type="hidden" id="hdnSinBloqueado" name="hdnSinBloqueado" value="<?=$objDocumentoDTO->getStrSinBloqueado()?>" />

    <input type="hidden" id="hdnContatoObject" name="hdnContatoObject" value="" />
    <input type="hidden" id="hdnContatoIdentificador" name="hdnContatoIdentificador" value="" />

    <input type="hidden" id="hdnAssuntoIdentificador" name="hdnAssuntoIdentificador" value="" />

  </form>

  <form id="frmAnexos">
    <div id="divArquivo" class="infraAreaDados">
      <label id="lblArquivo" for="filArquivo" accesskey="" class="infraLabelOpcional">Anexar Arquivo:</label>
      <input type="file" id="filArquivo" name="filArquivo" size="50" onchange="objUpload.executar();" tabindex="1000"/><br />
    </div>

    <div id="divAnexos" class="infraAreaDadosDinamica" style="width:90%;margin-left:0px;" >
      <table id="tblAnexos" name="tblAnexos" class="infraTable" style="width:100%">
        <caption class="infraCaption"><?=PaginaSEI::getInstance()->gerarCaptionTabela("Anexos",0)?></caption>

        <tr>
          <th width="1%" style="display:none;">ID</th>
          <th class="infraTh">Nome</th>
          <th width="22%" class="infraTh" align="center">Data</th>
          <th width="1%" style="display:none;">Bytes</th>
          <th width="13%" class="infraTh" align="center">Tamanho</th>
          <th width="10%" class="infraTh" align="center">Usu?rio</th>
          <th width="10%" class="infraTh" align="center">Unidade</th>
          <th width="10%" class="infraTh">A??es</th>
        </tr>
      </table>
      <!-- campo hidden correspondente (hdnAnexos) deve ficar no outro form -->
    </div>
  </form>
<?
PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>