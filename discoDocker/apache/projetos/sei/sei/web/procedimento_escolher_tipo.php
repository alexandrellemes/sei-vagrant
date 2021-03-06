<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 31/01/2008 - criado por marcio_db
*
* Vers?o do Gerador de C?digo: 1.13.1
*
* Vers?o no CVS: $Id$
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

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('hdnFiltroTipoProcedimento'));
  
  $objProcedimentoDTO = new ProcedimentoDTO();

  $strDesabilitar = '';
  $strDesabilitarCampo = '';

  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }

	if(isset($_GET['id_procedimento_destino'])){
		$strParametros .= '&id_procedimento_destino='.$_GET['id_procedimento_destino'];
	}

  //$arrComandos = array();
  
  switch($_GET['acao']){

    case 'procedimento_escolher_tipo':
		case 'procedimento_escolher_tipo_relacionado':

		  if ($_GET['acao']=='procedimento_escolher_tipo'){
				$strTitulo = 'Iniciar Processo';
				$strAcaoDestino = 'procedimento_gerar';
			}else{
				$strTitulo = 'Iniciar Processo Relacionado';
				$strAcaoDestino = 'procedimento_gerar_relacionado';
			}

		  $objTipoProcedimentoRN = new TipoProcedimentoRN();

    	$strFiltroTipoProcedimento = PaginaSEI::getInstance()->recuperarCampo('hdnFiltroTipoProcedimento','U');

		  $arrObjTipoProcedimentoDTO = array();

    	$strImgExibir = '';

    	if ($strFiltroTipoProcedimento=='U'){

				$objTipoProcedimentoDTO = new TipoProcedimentoDTO();
				$objTipoProcedimentoDTO->setStrSinSomenteUtilizados('S');
				$arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarTiposUnidade($objTipoProcedimentoDTO);

				$strImgExibir = '<img id="imgExibirTiposProcedimento" onclick="exibirTiposProcedimento(\'T\');" src="/infra_css/imagens/mais.gif" title="Exibir todos os tipos" alt="Exibir todos os tipos" class="infraImg" />';

			}

			//se ao entrar na pagina nao retornou itens para a unidade entao consulta tudo
			if (!isset($_POST['hdnFiltroTipoProcedimento']) && count($arrObjTipoProcedimentoDTO)==0){
				$strFiltroTipoProcedimento = 'T';
			}

			if ($strFiltroTipoProcedimento == 'T'){

				$objTipoProcedimentoDTO = new TipoProcedimentoDTO();
				$objTipoProcedimentoDTO->setStrSinSomenteUtilizados('N');
				$arrObjTipoProcedimentoDTO = $objTipoProcedimentoRN->listarTiposUnidade($objTipoProcedimentoDTO);

        $strImgExibir = '<img id="imgExibirTiposProcedimento" onclick="exibirTiposProcedimento(\'U\');" src="/infra_css/imagens/menos.gif" title="Exibir apenas os tipos j? utilizados pela unidade" alt="Exibir apenas os tipos j? utilizados pela unidade" class="infraImg" />';

      }

			foreach($arrObjTipoProcedimentoDTO as $objTipoProcedimentoDTO){
				$arrOpcoes[$objTipoProcedimentoDTO->getNumIdTipoProcedimento()] = array($objTipoProcedimentoDTO->getStrNome(), $objTipoProcedimentoDTO->getStrSinOuvidoria());
			}

      $strSumarioTabela = 'Tabela de Tipos de Processo.';
      $strCaptionTabela = 'Tipos de Processo';

      $strResultado = '';
      $strResultado .= '<table id="tblTipoProcedimento" class="infraTable" style="background-color:white;" summary="'.$strSumarioTabela.'">'."\n";

	    $strResultado .= '<thead><tr style="display:none">';
      $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
	    $strResultado .= '</tr></thead><tbody>';

      $numRegistros = count($arrOpcoes);

      if ($numRegistros) {

				$objNivelAcessoPermitidoDTO = new NivelAcessoPermitidoDTO();
				$objNivelAcessoPermitidoDTO->retNumIdTipoProcedimento();
				$objNivelAcessoPermitidoDTO->setStrStaNivelAcesso(ProtocoloRN::$NA_SIGILOSO);

				$objNivelAcessoPermitidoRN = new NivelAcessoPermitidoRN();
				$arrNumTipoProcedimentoSigiloso = InfraArray::indexarArrInfraDTO($objNivelAcessoPermitidoRN->listar($objNivelAcessoPermitidoDTO), 'IdTipoProcedimento');

				foreach ($arrOpcoes as $numIdTipoProcedimento => $arrTipoProcedimento) {
					$strResultado .= '<tr class="infraTrClara" data-desc="' . strtolower(InfraString::excluirAcentos($arrTipoProcedimento[0])) . '">';

					$strStyleOpcao = '';

					if (isset($arrNumTipoProcedimentoSigiloso[$numIdTipoProcedimento])) {
						$strClassTd = 'class="tdOpcaoSigiloso"';
					} else {
						$strClassTd = '';
					}

					$strResultado .= '<td ' . $strClassTd . '>';
					$strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $numIdTipoProcedimento, $arrTipoProcedimento[0], 'N', 'Infra', 'style="display:none;"');

					$strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDestino . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_tipo_procedimento=' . $numIdTipoProcedimento . $strParametros) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" class="ancoraOpcao">' . PaginaSEI::tratarHTML($arrTipoProcedimento[0]) . ($arrTipoProcedimento[1] == 'S' ? '<sup>&nbsp;<span style="font-size:1.1em;">(Ouvidoria)</span></sup>' : '') . '</a>' . "\n";
					$strResultado .= '</td>';
					$strResultado .= '</tr>';
				}
				$strResultado .= '</table>';
			}

      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
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


td.tdOpcaoSigiloso a{
background-color:red;
color:white;
width:98.5%;
}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>
function inicializar(){
  infraEfeitoTabelas();
  seiPrepararFiltroTabela(document.getElementById('tblTipoProcedimento'),document.getElementById('txtFiltro'));
}  

function exibirTiposProcedimento(tipo){
  document.getElementById('hdnFiltroTipoProcedimento').value = tipo;
  document.getElementById('frmIniciarProcessoEscolhaTipo').submit();
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmIniciarProcessoEscolhaTipo" method="post" onsubmit="return false;" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
//PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados(null,'style="width:50%"');
?>
<br />
<br />
<label class="infraLabelObrigatorio" style="font-size:1.6em;">Escolha o Tipo do Processo:</label> <?=$strImgExibir?>
<br />
<br />
	<input type="text" id="txtFiltro" class="infraAutoCompletar" autocomplete="off" style="position:relative;width:60%;" value="<?if (isset($_POST['txtFiltro'])) echo $_POST['txtFiltro'];?>">
	<br />
<?
//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,false,'style="width:51%;"');
?>
<input type="hidden" id="hdnFiltroTipoProcedimento" name="hdnFiltroTipoProcedimento" value="" />
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>