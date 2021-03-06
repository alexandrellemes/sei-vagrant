<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 22/06/2016 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.12.0
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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $strParametros = '';
  if (isset($_GET['acesso'])){
    $strParametros .= '&acesso='.$_GET['acesso'];
  }

  switch($_GET['acao']){

    case 'procedimento_credencial_cancelar':

      $arrIdProcedimento = PaginaSEI::getInstance()->getArrStrItensSelecionados();

      try{
        $objAtividadeRN = new AtividadeRN();
        $objAtividadeRN->cancelarCredenciais(InfraArray::gerarArrInfraDTO('ProcedimentoDTO','IdProcedimento', $arrIdProcedimento));
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      }

      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao'].$strParametros.PaginaSEI::montarAncora($arrIdProcedimento)));
      die;

    case 'procedimento_acervo_sigilosos':
      $strTitulo = 'Acervo de Processos Sigilosos da Unidade';
      
      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  $arrComandos = array();

  if ($_GET['acesso']=='1') {

    $arrComandos[] = '<button type="submit" accesskey="S" id="sbmPesquisar" name="sbmPesquisar" value="Pesquisar" class="infraButton"><span class="infraTeclaAtalho">P</span>esquisar</button>';
    $arrComandos[] = '<button type="button" accesskey="L" id="btnLimpar" name="btnPesquisar" onclick="limpar();" value="Limpar" class="infraButton"><span class="infraTeclaAtalho">L</span>impar</button>';

    $objPesquisaSigilosoDTO = new PesquisaSigilosoDTO();
    $objPesquisaSigilosoDTO->setStrStaAcessoUnidade(ProtocoloRN::$TASU_TODOS);
    //$objPesquisaSigilosoDTO->setStrStaAcessoUnidade(ProtocoloRN::$TASU_NAO);
    //$objPesquisaSigilosoDTO->setStrStaAcessoUnidade(ProtocoloRN::$TASU_SIM);

    ProcedimentoINT::montarCamposPesquisaSigiloso($objPesquisaSigilosoDTO, $strCssSigilosos, $strJsSigilosos, $strJsInicializarSigilosos, $strHtmlSigilosos, true);

    PaginaSEI::getInstance()->prepararOrdenacao($objPesquisaSigilosoDTO, 'Geracao', InfraDTO::$TIPO_ORDENACAO_DESC);

    PaginaSEI::getInstance()->prepararPaginacao($objPesquisaSigilosoDTO);

    $objProtocoloRN = new ProtocoloRN();
    $arrObjProtocoloDTO = $objProtocoloRN->pesquisarAcervoSigilosos($objPesquisaSigilosoDTO);

    PaginaSEI::getInstance()->processarPaginacao($objPesquisaSigilosoDTO);

    $numRegistros = count($arrObjProtocoloDTO);

    if ($numRegistros) {


      $bolAcaoCredencialAtivar = SessaoSEI::getInstance()->verificarPermissao('procedimento_credencial_ativar');
      $bolAcaoCredencialCancelar = SessaoSEI::getInstance()->verificarPermissao('procedimento_credencial_cancelar');

      if ($bolAcaoCredencialAtivar) {
        $strLinkCredencialAtivar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_credencial_ativar&acao_origem=' . $_GET['acao'] . $strParametros);
      }

      if ($bolAcaoCredencialCancelar) {
        $strLinkCredencialCancelar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_credencial_cancelar&acao_origem=' . $_GET['acao'] . $strParametros);
      }

      $strResultado = '';

      $strSumarioTabela = 'Tabela de Processos.';
      $strCaptionTabela = 'Processos';

      $strResultado .= '<table width="99%" class="infraTable" summary="' . $strSumarioTabela . '">' . "\n";
      $strResultado .= '<caption class="infraCaption">' . PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela, $numRegistros) . '</caption>';
      $strResultado .= '<tr>';
      $strResultado .= '<th class="infraTh" width="1%">' . PaginaSEI::getInstance()->getThCheck() . '</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="20%">Processo</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objPesquisaSigilosoDTO,'Autua??o','Geracao',$arrObjProtocoloDTO).'</th>' . "\n";
      $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objPesquisaSigilosoDTO,'Tipo','NomeTipoProcedimento',$arrObjProtocoloDTO).'</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="20%">Credenciais na Unidade</th>' . "\n";
      $strResultado .= '<th class="infraTh" width="10%">A??es</th>' . "\n";
      $strResultado .= '</tr>' . "\n";
      $strCssTr = '';

      $bolBotaoAtivarCredencial = false;
      $bolBotaoCancelarCredenciais = false;
      for ($i = 0; $i < $numRegistros; $i++) {

        $arrObjAcessoDTO = $arrObjProtocoloDTO[$i]->getArrObjAcessoDTO();

        $strCssTr = ($strCssTr == '<tr class="infraTrClara">') ? '<tr class="infraTrEscura">' : '<tr class="infraTrClara">';
        $strResultado .= $strCssTr;

        $bolCredencialAtiva = false;
        $bolCredencialInativa = false;
        $bolAcessoPessoal = false;
        $strAcessos = '';
        foreach ($arrObjAcessoDTO as $objAcessoDTO) {

          if ($strAcessos != '') {
            $strAcessos .= '<br/>';
          }

          $strAcessos .= '<span class="iconeLegenda" style="color:';
          if ($objAcessoDTO->getStrStaCredencialUnidade() == ProtocoloRN::$TCU_FINALIZADA) {

            $strAcessos .= 'black;">&#9675;';

          } else if ($objAcessoDTO->getStrStaCredencialUnidade() == ProtocoloRN::$TCU_INATIVA) {

            $bolCredencialInativa = true;

            $strAcessos .= 'red;">&#9679;';

          } else if ($objAcessoDTO->getStrStaCredencialUnidade() == ProtocoloRN::$TCU_ATIVA) {

            $strAcessos .= 'green;">&#9679;';

            $bolCredencialAtiva = true;

            if ($objAcessoDTO->getNumIdUsuario() == SessaoSEI::getInstance()->getNumIdUsuario()) {
              $bolAcessoPessoal = true;
            }
          }
          $strAcessos .= '</span>';
          $strAcessos .= '<a alt="' . PaginaSEI::tratarHTML($objAcessoDTO->getStrNomeUsuario()) . '" title="' . PaginaSEI::tratarHTML($objAcessoDTO->getStrNomeUsuario()) . '" class="ancoraSigla textoLegenda">' . PaginaSEI::tratarHTML($objAcessoDTO->getStrSiglaUsuario()) . '</a>';
        }

        $bolExibeAcaoCancelar = ($bolAcaoCredencialCancelar && $bolCredencialAtiva && $bolCredencialInativa);

        $strAtributosCheck = '';
        if (!$bolAcaoCredencialAtivar && !$bolExibeAcaoCancelar) {
          $strAtributosCheck = 'disabled="disabled" style="display:none"';
        }

        $strResultado .= '<td>' . PaginaSEI::getInstance()->getTrCheck($i, $arrObjProtocoloDTO[$i]->getDblIdProtocolo(), $arrObjProtocoloDTO[$i]->getStrProtocoloFormatado(),'N','Infra',$strAtributosCheck) . '</td>' . "\n";

        if ($bolAcessoPessoal) {
          $strResultado .= '<td align="center"><a style="text-decoration:underline" href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=procedimento_trabalhar&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $arrObjProtocoloDTO[$i]->getDblIdProtocolo()) . '" target="_blank" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" alt="' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getStrNomeTipoProcedimentoProcedimento()) . '" title="' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getStrNomeTipoProcedimentoProcedimento()) . '" class="protocoloNormal">' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getStrProtocoloFormatado()) . '</a></td>' . "\n";
        }else {
          $strResultado .= '<td align="center"><span style="font-size:1.2em">' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getStrProtocoloFormatado()) . '</span></td>' . "\n";
        }

        $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getDtaGeracao()) . '</td>' . "\n";
        $strResultado .= '<td align="center">' . PaginaSEI::tratarHTML($arrObjProtocoloDTO[$i]->getStrNomeTipoProcedimentoProcedimento()) . '</td>' . "\n";
        $strResultado .= '<td align="left">' . ($strAcessos == '' ? '&nbsp;' : $strAcessos) . '</td>' . "\n";
        $strResultado .= '<td align="center">';

        if ($bolAcaoCredencialAtivar || $bolExibeAcaoCancelar) {
          $strId = $arrObjProtocoloDTO[$i]->getDblIdProtocolo();
          $strDescricao = PaginaSEI::getInstance()->formatarParametrosJavaScript($arrObjProtocoloDTO[$i]->getStrProtocoloFormatado());
        }

        if ($bolAcaoCredencialAtivar) {
          $bolBotaoAtivarCredencial = true;
          $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);acaoAtivarCredencial(\'' . $strId . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioImagensLocal() . '/sei_credencial_ativar.gif" title="Ativar Credencial na Unidade" alt="Ativar Credencial na Unidade" class="infraImg" /></a>&nbsp;';
        }

        if ($bolExibeAcaoCancelar) {
          $bolBotaoCancelarCredenciais = true;
          $strResultado .= '<a href="' . PaginaSEI::getInstance()->montarAncora($strId) . '" onclick="infraLimparFormatarTrAcessada(this.parentNode.parentNode);acaoCancelarCredenciais(\'' . $strId . '\',\'' . $strDescricao . '\');" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '"><img src="' . PaginaSEI::getInstance()->getDiretorioImagensLocal() . '/sei_credencial_cancelar.gif" title="Cancelar Credenciais Inativas na Unidade" alt="Cancelar Credenciais Inativas na Unidade" class="infraImg" /></a>&nbsp;';
        }

        $strResultado .= '</td>' . "\n";
        $strResultado .= '</tr>' . "\n";
      }
      $strResultado .= '</table>' . "\n";

      if ($bolBotaoAtivarCredencial) {
        $arrComandos[] = '<button type="button" id="btnAtivarCredencial" value="Ativar Credencial" onclick="acaoAtivarCredencialMultipla();" class="infraButton">Ativar Credencial</button>';
      }

      if ($bolBotaoCancelarCredenciais) {
        $arrComandos[] = '<button type="button" id="btnCancelarCredenciais" value="Cancelar Credenciais" onclick="acaoCancelarCredenciaisMultipla();" class="infraButton">Cancelar Credenciais Inativas</button>';
      }
      
      $strLegenda = '<label id="lblLegenda" class="infraLabelOpcional">Legenda:</label>
                     <div id="divLegenda1"><span class="iconeLegenda" style="color:green;">&#9679;</span><span class="textoLegenda">Credencial ativa</span></div>
                     <div id="divLegenda2"><span class="iconeLegenda" style="color:red;">&#9679;</span><span class="textoLegenda">Credencial inativa (sem permiss?o na unidade)</span></div>
                     <div id="divLegenda3"><span class="iconeLegenda" style="color:black;">&#9675;</span><span class="textoLegenda">Credencial finalizada (ren?ncia / cassa??o / anula??o / cancelamento)</span></div>';
    }
  }

  $strLinkAcesso = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_validar_acesso&acao_origem='.$_GET['acao'].'&acao_destino=procedimento_acervo_sigilosos');

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
<?=$strCssSigilosos;?>

#lblLegenda {position:absolute;left:0%;top:0%;width:18%;}
#divLegenda1 {position:absolute;left:19%;top:0%;width:60%;}
#divLegenda2 {position:absolute;left:19%;top:30%;width:60%;}
#divLegenda3 {position:absolute;left:19%;top:60%;width:60%;}

.iconeLegenda {
margin:0;
border:0;
padding:0 .1em 0 0;
display:inline-table;
font-size:20px;
}

.textoLegenda{
font-size:1.2em;
line-height:16px;
vertical-align:text-bottom;
}


<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
<?=$strJsSigilosos;?>

function inicializar(){

  if ('<?=$_GET['acesso']?>'!='1'){
  infraAbrirJanela('<?=$strLinkAcesso?>','janelaAcessoAcervo',500,350,'location=0,status=1,resizable=1,scrollbars=1');
  return;
  }

<?=$strJsInicializarSigilosos;?>

  infraEfeitoTabelas();
}

function onSubmitForm(){
  return true;
}

<?if ($bolAcaoCredencialAtivar) {?>

function acaoAtivarCredencial(id){

  infraAbrirJanela('<?=$strLinkCredencialAtivar?>','janelaAtivarCredencial',700,250,'location=0,status=1,resizable=1,scrollbars=1');
  
  document.getElementById('hdnInfraItemId').value=id;

  var actionAnterior = document.getElementById('frmProcedimentoAcervoSigilosos').action;
  document.getElementById('frmProcedimentoAcervoSigilosos').target='janelaAtivarCredencial';
  document.getElementById('frmProcedimentoAcervoSigilosos').action='<?=$strLinkCredencialAtivar?>';
  document.getElementById('frmProcedimentoAcervoSigilosos').submit();
  document.getElementById('frmProcedimentoAcervoSigilosos').action=actionAnterior;
  document.getElementById('frmProcedimentoAcervoSigilosos').target='_self';
}

function acaoAtivarCredencialMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum processo selecionado.');
    return;
  }
  
  infraAbrirJanela('<?=$strLinkCredencialAtivar?>','janelaAtivarCredencial',700,250,'location=0,status=1,resizable=1,scrollbars=1');
  
  document.getElementById('hdnInfraItemId').value='';

  var actionAnterior = document.getElementById('frmProcedimentoAcervoSigilosos').action;
  document.getElementById('frmProcedimentoAcervoSigilosos').target='janelaAtivarCredencial';
  document.getElementById('frmProcedimentoAcervoSigilosos').action='<?=$strLinkCredencialAtivar?>';
  document.getElementById('frmProcedimentoAcervoSigilosos').submit();
  document.getElementById('frmProcedimentoAcervoSigilosos').action=actionAnterior;
  document.getElementById('frmProcedimentoAcervoSigilosos').target='_self';
}
<?}?>

<? if ($bolAcaoCredencialCancelar){ ?>
function acaoCancelarCredenciais(id, desc){
  if (confirm("Confirma cancelamento das credenciais inativas do processo \""+desc+"\" nesta unidade?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmProcedimentoAcervoSigilosos').action='<?=$strLinkCredencialCancelar?>';
    document.getElementById('frmProcedimentoAcervoSigilosos').submit();
  }
}

function acaoCancelarCredenciaisMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum processo selecionado.');
    return;
  }
  if (confirm("Confirma cancelamento das credenciais inativas dos processos selecionados nesta unidade?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmProcedimentoAcervoSigilosos').action='<?=$strLinkCredencialCancelar?>';
    document.getElementById('frmProcedimentoAcervoSigilosos').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmProcedimentoAcervoSigilosos" onsubmit="return onSubmitForm();" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados();
  echo $strHtmlSigilosos;
  PaginaSEI::getInstance()->fecharAreaDados();
  if ($strLegenda!='') {
    PaginaSEI::getInstance()->abrirAreaDados('8em');
    echo $strLegenda;
    PaginaSEI::getInstance()->fecharAreaDados();
  }
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);

  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>