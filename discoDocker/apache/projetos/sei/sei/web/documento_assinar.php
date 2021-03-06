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
  InfraDebug::getInstance()->setBolLigado(false);
  InfraDebug::getInstance()->setBolDebugInfra(true);
  InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  SessaoSEI::getInstance()->setArrParametrosRepasseLink(array('arvore', 'id_procedimento', 'id_documento', 'id_bloco', 'sta_estado', 'unidade_atual', 'unidade_outra', 'nao_assinados'));

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
	
  //PaginaSEI::getInstance()->salvarCamposPost(array('selCargoFuncao'));
  
  PaginaSEI::getInstance()->setTipoPagina(InfraPagina::$TIPO_PAGINA_SIMPLES);

  $bolAssinaturaOK = false;
  $bolPermiteAssinaturaLogin=false;
  $bolPermiteAssinaturaCertificado=false;
  $bolAutenticacao = false;
  $strCodigoAssinatura = '';
  $strLinkVerificacaoAssinatura = '';

  switch($_GET['acao']){
    
    case 'documento_assinar':

      $objInfraParametro=new InfraParametro(BancoSEI::getInstance());
      $tipoAssinatura=$objInfraParametro->getValor('SEI_TIPO_ASSINATURA_INTERNA');

      $strTitulo = 'Assinatura de Documento';            
      if ($_GET['acao_origem']=='bloco_assinatura_listar'){

        $arrIdDocumentos = array();
        $arrIdBlocos = PaginaSEI::getInstance()->getArrStrItensSelecionados();

        $objRelBlocoProtocoloRN = new RelBlocoProtocoloRN();

        foreach($arrIdBlocos as $numIdBloco){
          $objRelBlocoProtocoloDTO = new RelBlocoProtocoloDTO();
          $objRelBlocoProtocoloDTO->setNumIdBloco($numIdBloco);
          $objRelBlocoProtocoloDTO->setOrdNumSequencia(InfraDTO::$TIPO_ORDENACAO_ASC);

          $arrIdDocumentos = array_merge($arrIdDocumentos, InfraArray::converterArrInfraDTO($objRelBlocoProtocoloRN->listarProtocolosBloco($objRelBlocoProtocoloDTO),'IdProtocolo'));
        }

        $arrIdDocumentos = array_unique($arrIdDocumentos);

        $strLinkRetorno = SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::montarAncora($arrIdBlocos));

      }else if ($_GET['acao_origem']=='rel_bloco_protocolo_listar'){
        
        $arrIdDocumentos = array();
        $arrIdDocumentoBloco = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        
        foreach($arrIdDocumentoBloco as $idDocumentoBloco){
          $arrTemp = explode('-',$idDocumentoBloco);
          $arrIdDocumentos[] = $arrTemp[0];
        }

        $strLinkRetorno = SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].PaginaSEI::montarAncora($arrIdDocumentos));

      }else if ($_GET['acao_origem']=='arvore_visualizar' || $_GET['acao_origem']=='bloco_navegar' || $_GET['acao_origem']=='editor_montar'){

        $arrIdDocumentos = array($_GET['id_documento']);

      }else{

        if (!isset($_POST['hdnIdDocumentos'])){
          throw new InfraException('Nenhum documento informado.');
        }

        if ($_GET['hash_documentos'] != md5($_POST['hdnIdDocumentos'])){
          throw new InfraException('Conjunto de documentos inv?lido.');
        }

        $arrIdDocumentos = explode(',',$_POST['hdnIdDocumentos']);

        $strLinkRetorno = $_POST['hdnLinkRetorno'];

      }

      $numRegistros = count($arrIdDocumentos);

      if ($numRegistros==1){
        $objDocumentoDTO = new DocumentoDTO();
        $objDocumentoDTO->retStrStaDocumento();
        $objDocumentoDTO->retNumIdTipoConferencia();
        $objDocumentoDTO->setDblIdDocumento($arrIdDocumentos[0]);

        $objDocumentoRN = new DocumentoRN();
        $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

        if ($objDocumentoDTO!=null && $objDocumentoDTO->getStrStaDocumento()==DocumentoRN::$TD_EXTERNO){
          $strTitulo = 'Autentica??o de Documento';
          $tipoAssinatura=$objInfraParametro->getValor('SEI_TIPO_AUTENTICACAO_INTERNA');
          $bolAutenticacao = true;
        }
      }

      switch ($tipoAssinatura){
        case 1:
          $bolPermiteAssinaturaCertificado=true;
          $bolPermiteAssinaturaLogin=true;
          break;
        case 2:
          $bolPermiteAssinaturaLogin=true;
          break;
        case 3:
          $bolPermiteAssinaturaCertificado=true;
      }

      $objAssinaturaDTO = new AssinaturaDTO();
      $objAssinaturaDTO->setStrStaFormaAutenticacao($_POST['hdnFormaAutenticacao']);
      
      if (!isset($_POST['hdnFlagAssinatura'])){
        $objAssinaturaDTO->setNumIdOrgaoUsuario(SessaoSEI::getInstance()->getNumIdOrgaoUsuario());
      }else{
        $objAssinaturaDTO->setNumIdOrgaoUsuario($_POST['selOrgao']);
      }

      if (!isset($_POST['hdnFlagAssinatura'])){
        $objAssinaturaDTO->setNumIdContextoUsuario(SessaoSEI::getInstance()->getNumIdContextoUsuario());
      }else{
        $objAssinaturaDTO->setNumIdContextoUsuario($_POST['selContexto']);
      }
      
      $objAssinaturaDTO->setNumIdUsuario($_POST['hdnIdUsuario']);
      $objAssinaturaDTO->setStrSenhaUsuario($_POST['pwdSenha']);
      
      //$objAssinaturaDTO->setStrCargoFuncao(PaginaSEI::getInstance()->recuperarCampo('selCargoFuncao'));
      
      $objInfraDadoUsuario = new InfraDadoUsuario(SessaoSEI::getInstance());

      $strChaveDadoUsuarioAssinatura = 'ASSINATURA_CARGO_FUNCAO_'.SessaoSEI::getInstance()->getNumIdUnidadeAtual();

      if (!isset($_POST['selCargoFuncao'])){
        $objAssinaturaDTO->setStrCargoFuncao($objInfraDadoUsuario->getValor($strChaveDadoUsuarioAssinatura));
      }else{
        $objAssinaturaDTO->setStrCargoFuncao($_POST['selCargoFuncao']);

        if ($objAssinaturaDTO->getNumIdUsuario()==SessaoSEI::getInstance()->getNumIdUsuario()) {
          $objInfraDadoUsuario->setValor($strChaveDadoUsuarioAssinatura, $_POST['selCargoFuncao']);
        }
      }

      if ($_POST['hdnFormaAutenticacao'] != null){

        if($_POST['hdnFormaAutenticacao']==AssinaturaRN::$TA_CERTIFICADO_DIGITAL && !$bolPermiteAssinaturaCertificado){
          throw new InfraException('Assinatura por Certificado Digital n?o permitida.');
        } else if($_POST['hdnFormaAutenticacao']==AssinaturaRN::$TA_SENHA && !$bolPermiteAssinaturaLogin){
          throw new InfraException('Assinatura por login n?o permitida.');
        }
        $objAssinaturaDTO->setArrObjDocumentoDTO(InfraArray::gerarArrInfraDTO('DocumentoDTO','IdDocumento',$arrIdDocumentos));

        try{

          $objDocumentoRN = new DocumentoRN();
          $arrObjAssinaturaDTO = $objDocumentoRN->assinar($objAssinaturaDTO);

          if($_POST['hdnFormaAutenticacao']==AssinaturaRN::$TA_CERTIFICADO_DIGITAL) {
            $strCodigoAssinatura = base64_encode(ConfiguracaoSEI::getInstance()->getValor('SEI', 'URL').'/controlador_ws.php?servico=assinador|'.$arrObjAssinaturaDTO[0]->getStrAgrupador());
            $strLinkVerificacaoAssinatura = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=assinatura_verificar_confirmacao&agrupador=' . $arrObjAssinaturaDTO[0]->getStrAgrupador());
          }

          $bolAssinaturaOK = true;

        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e, true);
        }
      }
      
      break;
      
    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  $arrComandos = array();
  

  if ($numRegistros) {
    if ($bolPermiteAssinaturaCertificado && $objAssinaturaDTO->getStrStaFormaAutenticacao() == AssinaturaRN::$TA_CERTIFICADO_DIGITAL){
      $arrComandos[] = '<button type="button" accesskey="A" onclick="assinarCertificadoDigital();" id="btnAssinar" name="btnAssinar" value="Assinar" class="infraButton" style="visibility:hidden">&nbsp;<span class="infraTeclaAtalho">A</span>ssinar&nbsp;</button>';
    }else if ($bolPermiteAssinaturaLogin ) {
      $arrComandos[] = '<button type="button" accesskey="A" onclick="assinarSenha();" id="btnAssinar" name="btnAssinar" value="Assinar" class="infraButton">&nbsp;<span class="infraTeclaAtalho">A</span>ssinar&nbsp;</button>';
    }
  }

  if (!isset($_POST['hdnIdUsuario'])){
    $strIdUsuario = SessaoSEI::getInstance()->getNumIdUsuario();
    $strNomeUsuario = SessaoSEI::getInstance()->getStrNomeUsuario();
  }else{
    $strIdUsuario = $_POST['hdnIdUsuario'];
    $strNomeUsuario = $_POST['txtUsuario'];
  }

  $strDisplayContexto = '';
  $objContextoDTO = new ContextoDTO();
  $objContextoDTO->setNumIdOrgao($objAssinaturaDTO->getNumIdOrgaoUsuario());

  $objContextoRN = new ContextoRN();
  if ($objContextoRN->contar($objContextoDTO) == 0){
    $strDisplayContexto = 'display:none;';
  }

  $strDisplayIdentificacao = '';
  $strDisplayAutenticacao = '';
  if ($bolAssinaturaOK){
    if ($objAssinaturaDTO->getStrStaFormaAutenticacao() == AssinaturaRN::$TA_CERTIFICADO_DIGITAL){
      $strDisplayIdentificacao = 'display:none';
    }
    $strDisplayAutenticacao = 'display:none;';
  }

  $strDisplayCodigo = '';
  if ($strCodigoAssinatura==''){
    $strDisplayCodigo = 'display:none';
  }

  $strLinkAjaxUsuarios = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_assinatura_auto_completar');
  $strItensSelOrgaos = OrgaoINT::montarSelectSiglaRI1358('null','&nbsp;',$objAssinaturaDTO->getNumIdOrgaoUsuario());
  $strLinkAjaxContexto = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=contexto_carregar_nome');
  $strItensSelContextos = ContextoINT::montarSelectNome('null','&nbsp;',$objAssinaturaDTO->getNumIdContextoUsuario(),$objAssinaturaDTO->getNumIdOrgaoUsuario());
  $strItensSelCargoFuncao = AssinanteINT::montarSelectCargoFuncaoUnidadeUsuarioRI1344('null','&nbsp;', $objAssinaturaDTO->getStrCargoFuncao(), $strIdUsuario);
  $strLinkAjaxCargoFuncao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=assinante_carregar_cargo_funcao');

  $strIdDocumentos = implode(',',$arrIdDocumentos);
  $strHashDocumentos = md5($strIdDocumentos);

  $strDisplayDadosAssinante = '';
  if ($bolAssinaturaOK && $objAssinaturaDTO->getStrStaFormaAutenticacao() == AssinaturaRN::$TA_CERTIFICADO_DIGITAL){
    $strDisplayDadosAssinante = 'display:none';
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

.infraButton{
  font-size: 1.2em;
  height:2.2em !important;
}

#divIdentificacao {<?=$strDisplayIdentificacao?>}

#lblOrgao {position:absolute;left:0%;top:0%;}
#selOrgao {position:absolute;left:0%;top:40%;width:40%;}

#divContexto {<?=$strDisplayContexto?>}
#lblContexto {position:absolute;left:0%;top:0%;}
#selContexto {position:absolute;left:0%;top:40%;width:40%;}

#divUsuario {}
#lblUsuario {position:absolute;left:0%;top:0%;}
#txtUsuario {position:absolute;left:0%;top:40%;width:60%;}

#divAutenticacao {<?=$strDisplayAutenticacao?>}
#pwdSenha {width:15%;}

#lblCargoFuncao {position:absolute;left:0%;top:0%;}
#selCargoFuncao {position:absolute;left:0%;top:40%;width:99%;}

#lblOu {<?=((PaginaSEI::getInstance()->isBolIpad() || PaginaSEI::getInstance()->isBolAndroid())?'visibility:hidden;':'')?>}
#lblCertificadoDigital {<?=((PaginaSEI::getInstance()->isBolIpad() || PaginaSEI::getInstance()->isBolAndroid())?'visibility:hidden;':'')?>}

#divCodigo {<?=$strDisplayCodigo?>}
#lblCodigo {font-size:1.4em}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->adicionarJavaScript('js/clipboard/clipboard.min.js');
PaginaSEI::getInstance()->abrirJavaScript();
?>

//<script>

var objAjaxContexto = null;
var objAutoCompletarUsuario = null;
var objAjaxCargoFuncao = null;
var bolAssinandoSenha = false;
var timer = null;

function inicializar(){

  <?if ($numRegistros==0){?>
    alert('Nenhum documento informado.');
    return;
  <?}?>

  <?if ($bolDocumentoNaoEncontrado){?>
    alert('Documento n?o encontrado.');
    return;
  <?}?>


  //se realizou assinatura
  <?if ($bolAssinaturaOK){ ?>

    <?if ($objAssinaturaDTO->getStrStaFormaAutenticacao() == AssinaturaRN::$TA_CERTIFICADO_DIGITAL) {?>

        var clipboard = new Clipboard('.clipboard', {
          text: function (trigger) {
            return '<?=$strCodigoAssinatura?>';
          }
        });

        clipboard.on('success', function (e) {

          verificarConfirmacaoAssinatura();

          var btnCopiarCodigo = document.getElementById('btnCopiarCodigo');

          if (btnCopiarCodigo != null) {

            p = infraObterPosicao(btnCopiarCodigo)

            var div = document.getElementById('divMsgClipboard');
            var criou = false;

            if (div==null) {
              var div = document.createElement("div");
              div.id = 'divMsgClipboard';
              criou = true;
            }
            div.className = 'msgGeral msgSucesso';
            div.innerHTML = 'Dados disponibilizados';
            div.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
            div.style.textAlign = 'center';


            div.style.top = (p.y + 30) + 'px';
            div.style.left = p.x + 'px';
            div.style.width = '180px';

            if (criou) {
              document.body.appendChild(div);
            }

            $("#divMsgClipboard").fadeIn(300).delay(1500).fadeOut(400);
          }

          e.clearSelection();
        });

        clipboard.on('error', function (e) {
          alert('N?o foi poss?vel copiar os dados de assinatura para a ?rea de Transfer?ncia.');
        });

    <?}else{?>
       finalizar();
    <?}?>

    return;

  <?}else{?>
  
    if (document.getElementById('selCargoFuncao').options.length==2){
      document.getElementById('selCargoFuncao').options[1].selected = true;
    }

    objAjaxContexto = new infraAjaxMontarSelect('selContexto','<?=$strLinkAjaxContexto?>');
    objAjaxContexto.mostrarAviso = false;
    objAjaxContexto.prepararExecucao = function(){
      return 'id_orgao=' + document.getElementById('selOrgao').value;
    }
    objAjaxContexto.processarResultado = function(numItens){
      if (numItens){
        document.getElementById('divContexto').style.display = 'block';
      }else{
        document.getElementById('divContexto').style.display = 'none';
      }
    }

    objAjaxCargoFuncao = new infraAjaxMontarSelect('selCargoFuncao','<?=$strLinkAjaxCargoFuncao?>');
    //objAjaxCargoFuncao.mostrarAviso = true;
    //objAjaxCargoFuncao.tempoAviso = 2000;
    objAjaxCargoFuncao.prepararExecucao = function(){

      if (document.getElementById('hdnIdUsuario').value==''){
        return false;
      }

      return 'id_usuario=' + document.getElementById('hdnIdUsuario').value;
    }

    objAutoCompletarUsuario = new infraAjaxAutoCompletar('hdnIdUsuario','txtUsuario','<?=$strLinkAjaxUsuarios?>');
    //objAutoCompletarUsuario.maiusculas = true;
    //objAutoCompletarUsuario.mostrarAviso = true;
    //objAutoCompletarUsuario.tempoAviso = 1000;
    //objAutoCompletarUsuario.tamanhoMinimo = 3;
    objAutoCompletarUsuario.limparCampo = true;
    //objAutoCompletarUsuario.bolExecucaoAutomatica = false;

    objAutoCompletarUsuario.prepararExecucao = function(){

      if (!infraSelectSelecionado(document.getElementById('selOrgao'))){
        alert('Selecione um ?rg?o.');
        document.getElementById('selOrgao').focus();
        return false;
      }

      return 'id_orgao=' + document.getElementById('selOrgao').value + '&palavras_pesquisa='+document.getElementById('txtUsuario').value + '&inativos=0';
    };

    objAutoCompletarUsuario.processarResultado = function(id,descricao,complemento){
      if (id!=''){
        document.getElementById('hdnIdUsuario').value = id;
        document.getElementById('txtUsuario').value = descricao;
        objAjaxCargoFuncao.executar();
        window.status='Finalizado.';
      }
    }

    //infraSelecionarCampo(document.getElementById('txtUsuario'));

    <? if($bolPermiteAssinaturaLogin) { ?>
    document.getElementById('pwdSenha').focus();
    <?}?>

  <?}?>
}

function OnSubmitForm() {

  if (!infraSelectSelecionado(document.getElementById('selOrgao'))){
    alert('Selecione um ?rg?o.');
    document.getElementById('selOrgao').focus();
    return false;
  }

  if (document.getElementById('selContexto').options.length > 0 &&  !infraSelectSelecionado(document.getElementById('selContexto'))){
    alert('Selecione um Contexto.');
    document.getElementById('selContexto').focus();
    return false;
  }
  
  if (infraTrim(document.getElementById('hdnIdUsuario').value)==''){
    alert('Informe um Assinante.');
    document.getElementById('txtUsuario').focus();
    return false;
  }

  if (!infraSelectSelecionado(document.getElementById('selCargoFuncao'))){
    alert('Selecione um Cargo/Fun??o.');
    document.getElementById('selCargoFuncao').focus();
    return false;
  }
  
  if ('<?=$numRegistros?>'=='0'){
    alert('Nenhum documento informado para assinatura.');
    return false;
  }

  return true;
}

function trocarOrgaoUsuario(){
  objAutoCompletarUsuario.limpar();
  objAjaxContexto.executar();
  objAjaxCargoFuncao.executar();
}

<? if($bolPermiteAssinaturaLogin) { ?>
  function assinarSenha(){
    if (infraTrim(document.getElementById('pwdSenha').value)==''){
      alert('Senha n?o informada.');
      document.getElementById('pwdSenha').focus();
    }else{
      document.getElementById('hdnFormaAutenticacao').value = '<?=AssinaturaRN::$TA_SENHA?>';
      if (OnSubmitForm()){
        infraExibirAviso(false);
        document.getElementById('frmAssinaturas').submit();
        return true;
      }
    }
    return false;
  }

  function tratarSenha(ev){
    if (!bolAssinandoSenha && infraGetCodigoTecla(ev)==13){
      bolAssinandoSenha = true;
      if (!assinarSenha()){
        bolAssinandoSenha = false;
      }
    }
  }
<? } ?>

<? if($bolPermiteAssinaturaCertificado) { ?>
  function assinarCertificadoDigital(){
    document.getElementById('hdnFormaAutenticacao').value = '<?=AssinaturaRN::$TA_CERTIFICADO_DIGITAL?>';
    if (OnSubmitForm()) {
      infraExibirAviso(false);
      document.getElementById('frmAssinaturas').submit();
    }
  }

function abrirAjudaAssinaturaDigital(){
  infraAbrirJanela('<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=assinatura_digital_ajuda&acao_origem='.$_GET['acao'])?>','janelaInstrucoesAssinatura',800,600,'location=0,status=1,resizable=1,scrollbars=1',false);
}

<? } ?>

function finalizar(){

  //se realizou assinatura
  <?if ($bolAssinaturaOK){ ?>

     window.opener.infraFecharJanelaModal();
  
     <? if ($_GET['arvore'] == '1'){ ?>
     
       //atualiza ?rvore para mostrar caneta de assinatura
       window.opener.parent.document.getElementById('ifrArvore').src = '<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_visualizar&acao_origem='.$_GET['acao'].'&montar_visualizacao=1')?>';
       
     <?}else if($_GET['acao_retorno']=='bloco_navegar'){?>
        window.opener.processarDocumento(window.opener.posAtual);
        window.opener.objAjaxAssinaturas.executar();
     <?}else if($_GET['acao_retorno']=='editor_montar'){?>
        window.opener.atualizarArvore(true);
     <?} else {?>
        window.opener.location = '<?=$strLinkRetorno?>';
     <?}?>

     self.setTimeout('window.close()',500);

  <?}?>
}

<?if ($bolAssinaturaOK && $objAssinaturaDTO->getStrStaFormaAutenticacao() == AssinaturaRN::$TA_CERTIFICADO_DIGITAL){ ?>

function verificarConfirmacaoAssinatura(){
  if (timer != null){
    timer = 1;
  }else {
    timer = 1;
    var intervalId = setInterval(function () {
      $.ajax({
        url: '<?=$strLinkVerificacaoAssinatura?>',
        dataType: 'xml',
        method: 'GET',
        success: function (xml) {
          var strConfirmacao;
          $(xml).find('complemento').each(function (index, el) {
            strConfirmacao = $(el).find('complemento').first().context.textContent;
          });
          if (strConfirmacao=='S' || timer > 300) {
            clearInterval(intervalId);
            finalizar();
          }
          timer += 3;
        }
      });
    }, 3000);
  }
}

window.onbeforeunload = function(evt){
    finalizar();
};
<?}?>


//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>

<form id="frmAssinaturas" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&acao_retorno='.PaginaSEI::getInstance()->getAcaoRetorno().'&hash_documentos='.$strHashDocumentos)?>">
  
	<?
	//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
	PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
	//PaginaSEI::getInstance()->montarAreaValidacao();
	if ($numRegistros > 0){
  ?>

    <div id="divIdentificacao">
      <div id="divOrgao" class="infraAreaDados" style="height:4.5em;">
        <label id="lblOrgao" for="selOrgao" accesskey="r" class="infraLabelObrigatorio">?<span class="infraTeclaAtalho">r</span>g?o do Assinante:</label>
        <select id="selOrgao" name="selOrgao" onchange="trocarOrgaoUsuario();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <?=$strItensSelOrgaos?>
        </select>
      </div>

      <div id="divContexto" class="infraAreaDados" style="height:4.5em;">
        <label id="lblContexto" for="selContexto" accesskey=""  class="infraLabelObrigatorio">Contexto do Assinante:</label>
        <select id="selContexto" name="selContexto" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <?=$strItensSelContextos?>
        </select>
      </div>

      <div id="divUsuario" class="infraAreaDados" style="height:4.5em;">
        <label id="lblUsuario" for="txtUsuario" accesskey="e" class="infraLabelObrigatorio">Assinant<span class="infraTeclaAtalho">e</span>:</label>
        <input type="text" id="txtUsuario" name="txtUsuario" class="infraText" value="<?=$strNomeUsuario?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
        <input type="hidden" id="hdnIdUsuario" name="hdnIdUsuario" value="<?=$strIdUsuario?>" />
      </div>

      <div id="divCargoFuncao" class="infraAreaDados" style="height:4.5em;">
        <label id="lblCargoFuncao" for="selCargoFuncao" accesskey="F" class="infraLabelObrigatorio">Cargo / <span class="infraTeclaAtalho">F</span>un??o:</label>
        <select id="selCargoFuncao" name="selCargoFuncao" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">
        <?=$strItensSelCargoFuncao?>
        </select>
      </div>
      <br />
      <div id="divAutenticacao" class="infraAreaDados" style="height:2.5em;">
        <? if($bolPermiteAssinaturaLogin) { ?>
          <label id="lblSenha" for="pwdSenha" accesskey="S" class="infraLabelRadio infraLabelObrigatorio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><span class="infraTeclaAtalho">S</span>enha</label>&nbsp;&nbsp;
          <input type="password" id="pwdSenha" name="pwdSenha" autocomplete="off" class="infraText" onkeypress="return tratarSenha(event);" value="" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />&nbsp;&nbsp;&nbsp;&nbsp;
        <? }
           if($bolPermiteAssinaturaLogin && $bolPermiteAssinaturaCertificado) { ?>
          <label id="lblOu" class="infraLabelOpcional" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>">ou</label>&nbsp;&nbsp;&nbsp;
        <? }
           if($bolPermiteAssinaturaCertificado) { ?>
          <label id="lblCertificadoDigital" onclick="assinarCertificadoDigital();" accesskey="" for="optCertificadoDigital" class="infraLabelRadio infraLabelObrigatorio" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"><?=((!$bolPermiteAssinaturaLogin)?(!$bolAutenticacao?'Assinar com ':'Autenticar com '):'')?>Certificado Digital</label>&nbsp;
        <? } ?>
      </div>
    </div>

    <div id="divCodigo" class="infraAreaDados">
      <label id="lblCodigo" class="infraLabelOpcional">Para prosseguir disponibilize os dados de assinatura e execute o programa <span style="font-weight:bold">Assinador de Documentos com Certificado Digital do SEI</span>.</label>
      <br>
      <br>
      <button type="button" id="btnCopiarCodigo" name="btnCopiarCodigo" value="Copiar" class="infraButton clipboard">Disponibilizar dados para o assinador</button>
      &nbsp;
      &nbsp;
      <button type="button" id="btnAjuda" name="btnAjuda" onclick="abrirAjudaAssinaturaDigital()" value="Ajuda" class="infraButton">Ajuda</button>
    </div>

    <?
	}
	  //PaginaSEI::getInstance()->fecharAreaDados();
	PaginaSEI::getInstance()->montarAreaDebug();
	//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
  <input type="hidden" id="hdnFormaAutenticacao" name="hdnFormaAutenticacao" value="" />
  <input type="hidden" id="hdnLinkRetorno" name="hdnLinkRetorno" value="<?=$strLinkRetorno?>" />
  <input type="hidden" id="hdnFlagAssinatura" name="hdnFlagAssinatura" value="1" />
  <input type="hidden" id="hdnIdDocumentos" name="hdnIdDocumentos" value="<?=$strIdDocumentos?>" />
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>