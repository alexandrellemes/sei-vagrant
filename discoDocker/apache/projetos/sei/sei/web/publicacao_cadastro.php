<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 25/11/2008 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.25.0
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

  //PaginaSEI::getInstance()->verificarSelecao('publicacao_selecionar');
  
  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objPublicacaoDTO = new PublicacaoDTO();
  
  $strDesabilitar = '';
  $strOcultar = '';
    
  $arrComandos = array();   
  
  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }

  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }
  
  if (isset($_GET['id_documento'])){
    $strParametros .= '&id_documento='.$_GET['id_documento'];
  }

  // A??o do formul?rio para tratamento das requisi??es. Pass?vel de personaliza??o pelos m?dulos
  $strAcaoFormulario = SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros);

  $strAncora = '';
  if (PaginaSEI::getInstance()->getAcaoRetorno()=='documento_listar'){
    $strAncora = PaginaSEI::getInstance()->montarAncora($_GET['id_documento']);
  }else if (PaginaSEI::getInstance()->getAcaoRetorno()=='publicacao_listar'){
    $strAncora = PaginaSEI::getInstance()->montarAncora($_GET['id_publicacao']);
  }
  
  $objPublicacaoRN = new PublicacaoRN();

  $objVeiculoPublicacaoDTO = new VeiculoPublicacaoDTO();
  $objVeiculoPublicacaoDTO->retNumIdVeiculoPublicacao();
  $objVeiculoPublicacaoDTO->retStrStaTipo();
  //$objVeiculoPublicacaoDTO->setStrStaTipo(VeiculoPublicacaoRN::$TV_MODULO);

  $objVeiculoPublicacaoRN = new VeiculoPublicacaoRN();
  $arrObjVeiculoPublicacaoDTO = $objVeiculoPublicacaoRN->listar($objVeiculoPublicacaoDTO);

  $arrObjVeiculoPublicacaoAPI = array();
  foreach($arrObjVeiculoPublicacaoDTO as $objVeiculoPublicacaoDTO){
    $objVeiculoPublicacaoAPI = new VeiculoPublicacaoAPI();
    $objVeiculoPublicacaoAPI->setIdVeiculoPublicacao($objVeiculoPublicacaoDTO->getNumIdVeiculoPublicacao());
    $objVeiculoPublicacaoAPI->setStaTipo($objVeiculoPublicacaoDTO->getStrStaTipo());
    $arrObjVeiculoPublicacaoAPI[] = $objVeiculoPublicacaoAPI;
  }

  $arrBloquearBotaoSalvar = array();
  foreach ($SEI_MODULOS as $seiModulo) {
    if (($arr = $seiModulo->executar('ocultarBotaoSalvarPublicacao', $arrObjVeiculoPublicacaoAPI)) != null){
      $arrBloquearBotaoSalvar = array_unique(array_merge($arrBloquearBotaoSalvar, $arr));
    }
  }

  $arrBotoesVeiculos = array();
  foreach ($SEI_MODULOS as $seiModulo) {
    if (($arrBotoesModulo = $seiModulo->executar('montarBotaoVeiculoPublicacao', $arrObjVeiculoPublicacaoAPI)) != null) {
      foreach ($arrBotoesModulo as $numIdVeiculoPublicacao => $arrBotoesVeiculoModulo) {
        foreach($arrBotoesVeiculoModulo as $strIdBotaoVeiculoModulo => $strBotaoVeiculoModulo) {
          $arrBotoesVeiculos[$numIdVeiculoPublicacao][] = $strIdBotaoVeiculoModulo;
          $arrComandos[] = $strBotaoVeiculoModulo;
        }
      }
    }
  }

  $strArrJsVeiculos = 'var arrVeiculos = Array('.implode(',',InfraArray::converterArrInfraDTO($arrObjVeiculoPublicacaoDTO,'IdVeiculoPublicacao')).');'."\n";
  $strArrJsBotoesVeiculo = 'var arrBotoesVeiculo = Array();'."\n";
  foreach($arrBotoesVeiculos as $numIdVeiculoPublicacao => $arrIdsBotoes){
    $strArrJsBotoesVeiculo .= 'arrBotoesVeiculo['.$numIdVeiculoPublicacao.'] = '.json_encode($arrIdsBotoes).';'."\n\n";
  }


  $strArrJsBloquearSalvar = 'var arrBloquearSalvar = Array();'."\n";
  $numPosArrJs = 0;
  foreach($arrBloquearBotaoSalvar as $numIdVeiculoPublicacao){
    $strArrJsBloquearSalvar .= 'arrBloquearSalvar['.$numPosArrJs++.'] = '.$numIdVeiculoPublicacao.';'."\n\n";
  }

  $arrBloquearImprensaNacional = array();
  foreach ($SEI_MODULOS as $seiModulo) {
    if (($arr = $seiModulo->executar('ocultarDadosImprensaNacionalPublicacao', $arrObjVeiculoPublicacaoAPI)) != null){
      $arrBloquearImprensaNacional = array_unique(array_merge($arrBloquearImprensaNacional, $arr));
    }
  }

  $strArrJsBloquearImprensaNacional = 'var arrBloquearImprensaNacional = Array();'."\n";
  $numPosArrJs = 0;
  foreach($arrBloquearImprensaNacional as $numIdVeiculoPublicacao){
    $strArrJsBloquearImprensaNacional .= 'arrBloquearImprensaNacional['.$numPosArrJs++.'] = '.$numIdVeiculoPublicacao.';'."\n\n";
  }

  switch($_GET['acao']){
    case 'publicacao_agendar':
    	
      $strTitulo = 'Agendar Publica??o';
      $arrComandos[] = '<button type="submit" id="btnSalvar" accesskey="S" name="sbmCadastrarPublicacao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      //$arrComandos[] = '<button type="submit" id="btnContinuar" accesskey="C" name="sbmContinuarPublicacao" style="display: none;" value="Continuar" class="infraButton"><span class="infraTeclaAtalho">C</span>ontinuar</button>';

      $objPublicacaoDTO->setNumIdPublicacao(null);
      $objPublicacaoDTO->setDblIdDocumento($_GET['id_documento']);
      $objPublicacaoDTO->setStrStaMotivo($_POST['selStaMotivo']);

      if (!isset($_POST['selVeiculoPublicacao']) && !isset($_POST['txtDisponibilizacao'])){
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);

        $objPublicacaoDTO->setNumIdVeiculoPublicacao(null);
        $objPublicacaoDTO->setDtaDisponibilizacao(null);
        
        try{
     	    
          $objPublicacaoDTO_Sugestao = $objPublicacaoRN->obterSugestaoPublicacaoRN1053($objDocumentoDTO);
     	    
     	    if ($objPublicacaoDTO_Sugestao != null){
     	      $objPublicacaoDTO->setNumIdVeiculoPublicacao($objPublicacaoDTO_Sugestao->getNumIdVeiculoPublicacao());
     	      $objPublicacaoDTO->setDtaDisponibilizacao($objPublicacaoDTO_Sugestao->getDtaDisponibilizacao());
     	    }
     	    
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
        
      }else{
        if (isset($_POST['selVeiculoPublicacao'])){
          $objPublicacaoDTO->setNumIdVeiculoPublicacao($_POST['selVeiculoPublicacao']);
        }
        
        if (isset($_POST['txtDisponibilizacao'])){
          $objPublicacaoDTO->setDtaDisponibilizacao($_POST['txtDisponibilizacao']);
        }  
      }     

      $objPublicacaoDTO->setNumIdVeiculoIO($_POST['selVeiculoIO']);
      $objPublicacaoDTO->setNumIdSecaoIO($_POST['selSecaoIO']);
      $objPublicacaoDTO->setDtaPublicacaoIO($_POST['txtDataIO']);
      $objPublicacaoDTO->setStrPaginaIO($_POST['txtPaginaIO']);

      $objPublicacaoDTO->setStrStaEstado(null);

      $objDocumentoDTO = new DocumentoDTO();
      $objDocumentoDTO->retStrDescricaoProtocolo();
      $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);

      $objDocumentoRN = new DocumentoRN();
      $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
      
        
      if (!isset($_POST['txaResumo'])){  
        $objPublicacaoDTO->setStrResumo($objDocumentoDTO->getStrDescricaoProtocolo());
      }else{
        $objPublicacaoDTO->setStrResumo($_POST['txaResumo']);  
      }
      
      if (isset($_POST['sbmCadastrarPublicacao'])) {
        try {
          
          $objPublicacaoDTO = $objPublicacaoRN->agendarRN1041($objPublicacaoDTO);
          
          if ($_POST['selVeiculoPublicacao']==VeiculoPublicacaoRN::$TV_INTERNO && $_POST['txtDisponibilizacao']==InfraData::getStrDataAtual()){
            PaginaSEI::getInstance()->setStrMensagem('Publica??o em "'.$objPublicacaoDTO->getDtaDisponibilizacao().'" realizada com sucesso.');            
          }else{            
            PaginaSEI::getInstance()->setStrMensagem('Agendamento em "'.$objPublicacaoDTO->getDtaDisponibilizacao().'" realizado com sucesso.');
          }
          
          if (PaginaSEI::getInstance()->getAcaoRetorno()=='publicacao_listar'){
            $strAncora = PaginaSEI::getInstance()->montarAncora($objPublicacaoDTO->getNumIdPublicacao());
          }

          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].$strParametros.'&atualizar_arvore=1'.$strAncora));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      
      $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.$strAncora).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
      
      break;

    case 'publicacao_alterar_agendamento':
    	
      $strTitulo = 'Alterar Agendamento de Publica??o';
      $arrComandos[] = '<button type="submit" id="btnSalvar" accesskey="S" name="sbmAlterarPublicacao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';

      if (isset($_GET['id_publicacao'])){

        $objPublicacaoDTO->setNumIdPublicacao($_GET['id_publicacao']);
        $objPublicacaoDTO->retTodos();

        $objPublicacaoDTO = $objPublicacaoRN->consultarRN1044($objPublicacaoDTO);
        if ($objPublicacaoDTO==null){
          throw new InfraException("Registro n?o encontrado.");
        }

      } else {

        $objPublicacaoDTO->setNumIdPublicacao($_POST['hdnIdPublicacao']);
        $objPublicacaoDTO->setStrStaMotivo($_POST['selStaMotivo']);
        $objPublicacaoDTO->setNumIdVeiculoPublicacao($_POST['selVeiculoPublicacao']);
        $objPublicacaoDTO->setDtaDisponibilizacao($_POST['txtDisponibilizacao']);
				$objPublicacaoDTO->setNumIdVeiculoIO($_POST['selVeiculoIO']);
				$objPublicacaoDTO->setNumIdSecaoIO($_POST['selSecaoIO']);
        $objPublicacaoDTO->setDtaPublicacaoIO($_POST['txtDataIO']);
        $objPublicacaoDTO->setStrPaginaIO($_POST['txtPaginaIO']);	  			
        $objPublicacaoDTO->setStrResumo($_POST['txaResumo']);
        $objPublicacaoDTO->setStrStaEstado($_POST['hdnStaEstado']);
      }

      if ($objPublicacaoDTO->getStrStaEstado()==PublicacaoRN::$TE_PUBLICADO){
        $strTitulo = 'Alterar Dados de Publica??o';
        $strDesabilitar = 'disabled="disabled"';
        $strOcultar = 'style="visibility:hidden"';
      }

      if (isset($_POST['sbmAlterarPublicacao'])) {

        try{

          $objPublicacaoDTOAlteracao = clone($objPublicacaoDTO);

          if ($objPublicacaoDTOAlteracao->getStrStaEstado()==PublicacaoRN::$TE_PUBLICADO){
            $objPublicacaoDTOAlteracao->unSetNumIdVeiculoPublicacao();
            $objPublicacaoDTOAlteracao->unSetDtaDisponibilizacao();
            $objPublicacaoDTOAlteracao->unSetStrStaMotivo();
          }

          $objPublicacaoRN->alterarAgendamentoRN1042($objPublicacaoDTOAlteracao);
          PaginaSEI::getInstance()->setStrMensagem('Altera??o do agendamento em "'.$objPublicacaoDTOAlteracao->getDtaDisponibilizacao().'" realizado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.$strAncora));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }

			$arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.$strAncora).'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';
			
      break;

    case 'publicacao_consultar_agendamento':
      $strTitulo = 'Consultar Publica??o';
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].$strParametros.$strAncora).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
      $objPublicacaoDTO->setNumIdPublicacao($_GET['id_publicacao']);
      $objPublicacaoDTO->setBolExclusaoLogica(false);
      $objPublicacaoDTO->retTodos();
      $objPublicacaoDTO = $objPublicacaoRN->consultarRN1044($objPublicacaoDTO);
      if ($objPublicacaoDTO===null){
        throw new InfraException("Registro n?o encontrado.");
      }
      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }
  

  //$strItensSelStaTipo = PublicacaoINT::montarSelectStaTipoRI1050('null','&nbsp;',$objPublicacaoDTO->getStrStaTipo());
  	
  //DOCUMENTO  
  $objDocumentoDTO = new DocumentoDTO();
  $objDocumentoDTO->retDblIdDocumento();
  $objDocumentoDTO->retNumIdOrgaoUnidadeResponsavel();
  $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
  $objDocumentoDTO->retStrNomeSerie();
  $objDocumentoDTO->retStrNumero();
  $objDocumentoDTO->retNumIdSerie();
  $objDocumentoDTO->setDblIdDocumento($_GET['id_documento']);
  
  $objDocumentoRN = new DocumentoRN();
  $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);
  
  $strProtocoloDocumentoFormatado = $objDocumentoDTO->getStrProtocoloDocumentoFormatado();

  //ASSINANTE
  $strItensSelAssinante = AssinaturaINT::montarSelectNome(null,null,null,$objDocumentoDTO->getDblIdDocumento());

  //MOTIVO                               
  $strItensSelStaMotivo = PublicacaoINT::montarSelectStaMotivoRI1061('null','&nbsp;',$objPublicacaoDTO->getStrStaMotivo(),$objDocumentoDTO->getDblIdDocumento());
    
  //VEICULO
  $strItensSelVeiculoPublicacao = VeiculoPublicacaoINT::montarSelectNome('null','&nbsp;',$objPublicacaoDTO->getNumIdVeiculoPublicacao());
  $strItensSelVeiculoIO = VeiculoImprensaNacionalINT::montarSelectSigla('null', '&nbsp;', $objPublicacaoDTO->getNumIdVeiculoIO());
  $strItensSelSecaoIO = SecaoImprensaNacionalINT::montarSelectNome('null', '&nbsp;', $objPublicacaoDTO->getNumIdSecaoIO(), $objPublicacaoDTO->getNumIdVeiculoIO());
  
  $strLinkAjaxDataDisponibilizacaoRI1054 = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=data_disponibilizacao_RI1054');
  
  $strLinkAjaxSecaoIO = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=secao_imprensa_nacional_montar_select_nome');
  
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

#lblDocumento {position:absolute;left:0%;top:0%;width:25%;}
#txtDocumento {position:absolute;left:0%;top:10%;width:25%;}

#lblIdentificacaoDocumento {position:absolute;left:27%;top:0%;}
#txtIdentificacaoDocumento {position:absolute;left:27%;top:10%;width:68%;}

#lblAssinantes {position:absolute;left:0%;top:24%;width:25%;}
#selAssinantes {position:absolute;left:0%;top:34%;width:95.5%;}

#lblStaMotivo {position:absolute;left:0%;top:72%;width:25%;}
#selStaMotivo {position:absolute;left:0%;top:82%;width:25%;}

#lblVeiculoPublicacao {position:absolute;left:27%;top:72%;width:40%;}
#selVeiculoPublicacao {position:absolute;left:27%;top:82%;width:40%;}

#lblDisponibilizacao {position:absolute;left:70.2%;top:72%;width:19%;}
#txtDisponibilizacao {position:absolute;left:70.2%;top:82%;width:19%;}
#imgCalDisponibilizacao {position:absolute;left:90.5%;top:84%;}

#fldImprensaOficial {position:absolute;left:0%;top:0%;width:92%; height:80%}
	#lblVeiculoIO {position:absolute;left:3%;top:10%;width:25%;}
	#selVeiculoIO {position:absolute;left:3%;top:32%;width:25%;}
	  
	#lblSecaoIO {position:absolute;left:31%;top:10%;width:21%;}
	#selSecaoIO {position:absolute;left:31%;top:32%;width:21%;}
	  
	#lblPaginaIO {position:absolute;left:55%;top:10%;width:17%;}
	#txtPaginaIO {position:absolute;left:55%;top:32%;width:17%;}

	#lblDataIO {position:absolute;left:75%;top:10%;width:13%;}
	#txtDataIO {position:absolute;left:75%;top:32%;width:13%;}
	#imgCalPublicacaoIO {position:absolute;left:89%;top:33%;}
	
#lblResumo {position:absolute;left:0%;top:5%;width:25%;}
#txaResumo {position:absolute;left:0%;top:22%;width:94.5%;}

<?
if (PaginaSEI::getInstance()->isBolAjustarCSS()){
  PaginaSEI::getInstance()->fecharStyle();
  PaginaSEI::getInstance()->abrirStyleIE();
}
?>	
#fldImprensaOficial {width:95%; height:85%}
	#lblVeiculoIO {top:35%;}
	#selVeiculoIO {top:55%;}

	#lblDataIO {top:35%;}
	#txtDataIO {top:55%;}
	#imgCalPublicacaoIO {top:55%;}
	  
	#lblSecaoIO {top:35%;}
	#selSecaoIO {top:55%;}
	  
	#lblPaginaIO {top:35%;}
	#txtPaginaIO {top:55%;}

<?
if (PaginaSEI::getInstance()->isBolAjustarCSS()){
  PaginaSEI::getInstance()->fecharStyleIE();
}else{
	PaginaSEI::getInstance()->fecharStyle();
}
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

<?=$strArrJsVeiculos?>
<?=$strArrJsBloquearSalvar?>
<?=$strArrJsBloquearImprensaNacional?>
<?=$strArrJsBotoesVeiculo?>

var objAjaxSugestaoDataDisponibilizacaoRI1054 = null;
var objAjaxIdSecaoIO = null;

function inicializar(){
  if ('<?=$_GET['acao']?>'=='publicacao_cadastrar'){
    document.getElementById('selProtocolo').focus();
  } else if ('<?=$_GET['acao']?>'=='publicacao_consultar_agendamento'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
  
  //Acrescentar sugest?o de Data Disponibilizacao
  objAjaxSugestaoDataDisponibilizacaoRI1054 = new infraAjaxComplementar(null,'<?=$strLinkAjaxDataDisponibilizacaoRI1054?>');
  //objAjaxSugestaoDataDisponibilizacaoRI1054.mostrarAviso = true;
  //objAjaxSugestaoDataDisponibilizacaoRI1054.tempoAviso = 1000;
  objAjaxSugestaoDataDisponibilizacaoRI1054.limparCampo = false;
  objAjaxSugestaoDataDisponibilizacaoRI1054.mostrarImagemVerificado = false;
  objAjaxSugestaoDataDisponibilizacaoRI1054.prepararExecucao = function(){     
    return 'idOrgao=<?=$objDocumentoDTO->getNumIdOrgaoUnidadeResponsavel()?>&idVeiculoPublicacao='+document.getElementById('selVeiculoPublicacao').value;
  }
  
  objAjaxSugestaoDataDisponibilizacaoRI1054.processarResultado = function(arr){  
    if (arr!=null){     
	    if (arr['Disponibilizacao']!=undefined){	    
	    	document.getElementById('txtDisponibilizacao').value = arr['Disponibilizacao'];
	    }
	  }  
  }    
  
  objAjaxIdSecaoIO = new infraAjaxMontarSelectDependente('selVeiculoIO','selSecaoIO','<?=$strLinkAjaxSecaoIO?>');
  objAjaxIdSecaoIO.prepararExecucao = function(){
    return infraAjaxMontarPostPadraoSelect('null','','null') + '&idVeiculoImprensaNacional='+document.getElementById('selVeiculoIO').value;
  }
  
  infraEfeitoTabelas();
  trocarVeiculoPublicacao();
}

function trocarDataDisponibilizacao(){
  if ('<?=$_GET['acao']?>' != 'publicacao_alterar_agendamento'){
    if (infraSelectSelecionado('selVeiculoPublicacao')){    
    	objAjaxSugestaoDataDisponibilizacaoRI1054.executar();
    }
  }
}

function trocarVeiculoPublicacao(){

  if (infraSelectSelecionado('selVeiculoPublicacao')){

    var element = null;
    var idVeiculoPublicacao = parseInt(document.getElementById('selVeiculoPublicacao').value);

    if (infraInArray(idVeiculoPublicacao,arrBloquearSalvar)){
      document.getElementById('btnSalvar').style.display = 'none';
      document.getElementById('frmPublicacaoCadastro').action = null;
    }else{
      document.getElementById('btnSalvar').style.display = 'inline';
      document.getElementById('frmPublicacaoCadastro').action = '<?=$strAcaoFormulario?>';
    }

    if (infraInArray(idVeiculoPublicacao,arrBloquearImprensaNacional)){
      document.getElementById('divImprensaOficial').style.display = 'none';
      document.getElementById('selVeiculoIO').value = null;
      document.getElementById('selSecaoIO').value = null;
      document.getElementById('txtPaginaIO').value = '';
      document.getElementById('txtDataIO').value = '';
    }else{
      document.getElementById('divImprensaOficial').style.display = 'block';
    }

    var numVeiculos = arrVeiculos.length;
    for(i=0;i<numVeiculos;i++){
      if ($.isArray(arrBotoesVeiculo[arrVeiculos[i]])){
        for(j=0;j<arrBotoesVeiculo[arrVeiculos[i]].length;j++){
          var botao = document.getElementById(arrBotoesVeiculo[arrVeiculos[i]][j]);
          if (botao != null){
            if (idVeiculoPublicacao == arrVeiculos[i]){
               botao.style.display = '';
            }else{
               botao.style.display = 'none';
            }
          }
        }
      }
    }

    if ('<?=$_GET['acao']?>' != 'publicacao_alterar_agendamento'){
      trocarDataDisponibilizacao();
    }
  }
}

function validarCadastroRI1052() {
  if (!infraSelectSelecionado('selStaMotivo')) {
    alert('Selecione um Motivo.');
    document.getElementById('selStaMotivo').focus();
    return false;
  }
  
  if (!infraSelectSelecionado('selVeiculoPublicacao')) {
    alert('Selecione um Ve?culo.');
    document.getElementById('selVeiculoPublicacao').focus();
    return false;
  }   
  
  if (infraTrim(document.getElementById('txtDisponibilizacao').value)=='') {
    alert('Informe a data de Disponibiliza??o.');
    document.getElementById('txtDisponibilizacao').focus();
    return false;
  }else{
	  if (!infraValidarData(document.getElementById('txtDisponibilizacao'))){
	    return false;
	  }  
  }

  if (!infraSelectSelecionado('selVeiculoIO') && (infraTrim(document.getElementById('txtDataIO').value)!='' || infraSelectSelecionado('selSecaoIO') || infraTrim(document.getElementById('txtPaginaIO').value)!='')) {
    alert('Informe o ve?culo da Imprensa Nacional.');
    document.getElementById('selVeiculoPublicacao').focus();
    return false;
  }	

  if (!infraSelectSelecionado('selSecaoIO') && document.getElementById('selSecaoIO').options.length > 0 && (infraSelectSelecionado('selVeiculoIO') || infraTrim(document.getElementById('txtDataIO').value)!='' || infraTrim(document.getElementById('txtPaginaIO').value)!='')) {
    alert('Informe a Se??o do ve?culo da Imprensa Nacional.');
    document.getElementById('selSecaoIO').focus();
    return false;
  }	
  
  if (infraTrim(document.getElementById('txtPaginaIO').value)=='' && (infraSelectSelecionado('selVeiculoIO') || infraTrim(document.getElementById('txtDataIO').value)!='' || infraSelectSelecionado('selSecaoIO'))) {
    alert('Informe a P?gina do ve?culo da Imprensa Nacional.');
    document.getElementById('txtPaginaIO').focus();
    return false;
  }	  

  if (infraTrim(document.getElementById('txtDataIO').value) =='' && (infraSelectSelecionado('selVeiculoIO') || infraSelectSelecionado('selSecaoIO') || infraTrim(document.getElementById('txtPaginaIO').value)!='')) {
    alert('Informe a Data do ve?culo da Imprensa Nacional.');
    document.getElementById('txtDataIO').focus();
    return false;
  }else{
	  if (!infraValidarData(document.getElementById('txtDataIO'))){
	    return false;
	  }  
  }	  
  
  return true;
}

function OnSubmitForm() {
  return validarCadastroRI1052();
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmPublicacaoCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
//PaginaSEI::getInstance()->abrirAreaDados('30em');
?>
	<div id="divDocumento" class="infraAreaDados" style="height:18em;">
	  <label id="lblDocumento" for="txtDocumento" accesskey="D" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">D</span>ocumento:</label>
	  <input type="text" id="txtDocumento" name="txtDocumento" class="infraText, infraReadOnly" readonly="readonly" value="<?=PaginaSEI::tratarHTML($strProtocoloDocumentoFormatado)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
	  
	  <label id="lblIdentificacaoDocumento" for="txtIdentificacaoDocumento" accesskey="" class="infraLabelObrigatorio">Tipo:</label>
	  <input type="text" id="txtIdentificacaoDocumento" name="txtIdentificacaoDocumento" readonly="readonly" class="infraText, infraReadOnly" value="<?=DocumentoINT::formatarIdentificacao($objDocumentoDTO)?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
	
	  <label id="lblAssinantes" for="selAssinantes" class="infraLabelObrigatorio">Assinantes:</label>
	  <select id="selAssinantes" name="selAssinantes" size="3" class="infraSelect" multiple="multiple" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
	  <?=$strItensSelAssinante?>
	  </select>    

	  <label id="lblStaMotivo" for="selStaMotivo" accesskey="M" class="infraLabelObrigatorio"><span class="infraTeclaAtalho">M</span>otivo:</label>
	  <select id="selStaMotivo" name="selStaMotivo" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?> >
		  <?=$strItensSelStaMotivo?>
	  </select>
	
	  <label id="lblVeiculoPublicacao" for="selVeiculoPublicacao" accesskey="" class="infraLabelObrigatorio">Ve?culo:</label>
	  <select id="selVeiculoPublicacao" name="selVeiculoPublicacao" onchange="trocarVeiculoPublicacao();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?> >
		  <?=$strItensSelVeiculoPublicacao?>
	  </select>

	  <label id="lblDisponibilizacao" for="txtDisponibilizacao" accesskey="" class="infraLabelObrigatorio">Disponibiliza??o:</label>
	  <input type="text" id="txtDisponibilizacao" name="txtDisponibilizacao" onkeypress="return infraMascaraData(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($objPublicacaoDTO->getDtaDisponibilizacao());?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" <?=$strDesabilitar?> />
	  <img id="imgCalDisponibilizacao" title="Selecionar Disponibiliza??o" alt="Selecionar Disponibiliza??o" src="/infra_css/imagens/calendario.gif" class="infraImg" onclick="infraCalendario('txtDisponibilizacao',this);" <?=$strOcultar?> />
	  
	</div>
	  
  <div id="divResumo" class="infraAreaDados" style="height:10em;">
		  <label id="lblResumo" for="txaResumo" accesskey="R" class="infraLabelOpcional">Resumo:</label>
		  <textarea id="txaResumo" name="txaResumo" rows="3" class="infraTextArea" onkeypress="return infraMascaraTexto(this,event);" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=PaginaSEI::tratarHTML($objPublicacaoDTO->getStrResumo());?></textarea>
	</div>
	  
  <div id="divImprensaOficial" class="infraAreaDados" style="height:10em;">
    <fieldset id="fldImprensaOficial" class="infraFieldset">
   	<legend class="infraLegend">&nbsp;Imprensa Nacional&nbsp;</legend>
   	
		  <label id="lblVeiculoIO" for="selVeiculoIO" accesskey="" class="infraLabelOpcional">Ve?culo:</label>
		  <select id="selVeiculoIO" name="selVeiculoIO" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
			  <?=$strItensSelVeiculoIO?>
		  </select>  	

		  <label id="lblSecaoIO" for="selSecaoIO" accesskey="" class="infraLabelOpcional">Se??o:</label>
		  <select id="selSecaoIO" name="selSecaoIO" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
			  <?=$strItensSelSecaoIO?>
		  </select>  	
		  
		  <label id="lblPaginaIO" for="txtPaginaIO" accesskey="" class="infraLabelOpcional">P?gina:</label>
		  <input type="text" id="txtPaginaIO" name="txtPaginaIO" class="infraText" value="<?=PaginaSEI::tratarHTML($objPublicacaoDTO->getStrPaginaIO());?>" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
		  
		  <label id="lblDataIO" for="txtDataIO" accesskey="" class="infraLabelOpcional">Data:</label>
		  <input type="text" id="txtDataIO" name="txtDataIO" onkeypress="return infraMascaraDataHora(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($objPublicacaoDTO->getDtaPublicacaoIO());?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
		  <img id="imgCalPublicacaoIO" title="Selecionar Data" alt="Selecionar Data" src="/infra_css/imagens/calendario.gif" class="infraImg" onclick="infraCalendario('txtDataIO',this);"/>
		  	  
		 </fieldset>
  </div>

  <input type="hidden" id="hdnIdPublicacao" name="hdnIdPublicacao" value="<?=$objPublicacaoDTO->getNumIdPublicacao()?>" />
  <input type="hidden" id="hdnStaEstado" name="hdnStaEstado" value="<?=$objPublicacaoDTO->getStrStaEstado()?>" />

  <?
  //PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaDebug();
  //PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>