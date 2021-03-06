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
  
  PaginaSEI::getInstance()->salvarCamposPost(array('hdnFiltroSerie'));
  
  $strParametros = '';
  if(isset($_GET['arvore'])){
    PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
    $strParametros .= '&arvore='.$_GET['arvore'];
  }

  if (isset($_GET['id_procedimento'])){
    $strParametros .= '&id_procedimento='.$_GET['id_procedimento'];
  }
  
  $objProcedimentoDTO = new ProcedimentoDTO();

  $strDesabilitar = '';
  $strDesabilitarCampo = '';

  $arrComandos = array();
  
  switch($_GET['acao']){
    
    case 'documento_escolher_tipo':
      
      $strTitulo = 'Gerar Documento';

      $objSerieRN = new SerieRN();

      $strFiltroSerie = PaginaSEI::getInstance()->recuperarCampo('hdnFiltroSerie','U');
      
      $strImgExibir = '';
      
      $arrObjSerieDTO = array();
      
      if (SessaoSEI::getInstance()->verificarPermissao('documento_gerar')){
        
        if ($strFiltroSerie=='U'){

          $objSerieDTO = new SerieDTO();
          $objSerieDTO->setStrSinSomenteUtilizados('S');
          $arrObjSerieDTO = $objSerieRN->listarTiposUnidade($objSerieDTO);

          $strImgExibir = '<img id="imgExibirSeries" onclick="exibirSeries(\'T\');" src="/infra_css/imagens/mais.gif" title="Exibir todos os tipos" alt="Exibir todos os tipos" class="infraImg" />';
        }

        if (!isset($_POST['hdnFiltroSerie']) && count($arrObjSerieDTO)==0){
          $strFiltroSerie = 'T';
        }

        if ($strFiltroSerie=='T'){

          $objSerieDTO = new SerieDTO();
          $objSerieDTO->setStrSinSomenteUtilizados('N');
          $arrObjSerieDTO = $objSerieRN->listarTiposUnidade($objSerieDTO);

          $strImgExibir = '<img id="imgExibirSeries" onclick="exibirSeries(\'U\');" src="/infra_css/imagens/menos.gif" title="Exibir apenas os tipos j? utilizados pela unidade" alt="Exibir apenas os tipos j? utilizados pela unidade" class="infraImg" />';
        }
      }

      foreach($arrObjSerieDTO as $objSerieDTO){
        $arrOpcoes[] = array($objSerieDTO->getNumIdSerie(), $objSerieDTO->getStrNome(), $objSerieDTO->getStrStaAplicabilidade());
      }

      $strSumarioTabela = 'Tabela de Tipos de Documento.';
      
      $strResultado = '';
      $strResultado .= '<table id="tblSeries" class="infraTable" style="background-color:white;" summary="'.$strSumarioTabela.'">'."\n";

	    $strResultado .= '<thead><tr style="display:none">';
      $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
	    $strResultado .= '</tr></thead><tbody>';
      
      if (SessaoSEI::getInstance()->verificarPermissao('documento_receber')){
        //coloca com espa?o em branco na frente para aparecer como primeiro da lista
        $arrOpcoes[] = array(-1,' Externo', SerieRN::$TA_EXTERNO);
      }
      
      InfraArray::ordenarArray($arrOpcoes,1,InfraArray::$TIPO_ORDENACAO_ASC);
      
      $numRegistros = count($arrOpcoes);

      if ($numRegistros) {

        $arrObjSerieNaoLiberados = InfraArray::indexarArrInfraDTO($objSerieRN->listarNaoLiberadosNaUnidade(),'IdSerie');

        for ($i = 0; $i < $numRegistros; $i++) {

          if (!isset($arrObjSerieNaoLiberados[$arrOpcoes[$i][0]])) {

            $strResultado .= '<tr class="infraTrClara" data-desc="' . strtolower(InfraString::excluirAcentos($arrOpcoes[$i][1])) . '">';
            $strResultado .= '<td>';

            $strResultado .= PaginaSEI::getInstance()->getTrCheck($i, $arrOpcoes[$i][0], $arrOpcoes[$i][1], 'N', 'Infra', 'style="display:none;"');

            if ($arrOpcoes[$i][2] == SerieRN::$TA_EXTERNO) {
              $strAcaoDestino = 'documento_receber';
            } else if ($arrOpcoes[$i][2] == SerieRN::$TA_FORMULARIO) {
              $strAcaoDestino = 'formulario_gerar';
            } else {
              $strAcaoDestino = 'documento_gerar';
            }

            $strResultado .= '<a href="' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $strAcaoDestino . '&acao_origem=' . $_GET['acao'] . '&acao_retorno=' . $_GET['acao'] . '&id_procedimento=' . $_GET['id_procedimento'] . '&id_serie=' . $arrOpcoes[$i][0] . $strParametros) . '" tabindex="' . PaginaSEI::getInstance()->getProxTabTabela() . '" class="ancoraOpcao">' . PaginaSEI::tratarHTML($arrOpcoes[$i][1]) . ($arrOpcoes[$i][2] == SerieRN::$TA_FORMULARIO ? '<sup>&nbsp;<span style="font-size:1.1em;">(Formul?rio)</span></sup>' : '') . '</a>' . "\n";
            $strResultado .= '</td>';
            $strResultado .= '</tr>';
          }
        }
      }

      $strResultado .= '</tbody></table>';
		 
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

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>
//<script>
function inicializar(){
  infraEfeitoTabelas();
  seiPrepararFiltroTabela(document.getElementById('tblSeries'),document.getElementById('txtFiltro'));
}  

function exibirSeries(tipo){
  document.getElementById('hdnFiltroSerie').value = tipo;
  document.getElementById('frmDocumentoEscolherTipo').submit();
}

//</script>
<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmDocumentoEscolherTipo" method="post" onsubmit="return false;" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].$strParametros)?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
//PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
PaginaSEI::getInstance()->abrirAreaDados(null,'style="width:50%;"');
?>
<label id="lblExibirSeries" class="infraLabelObrigatorio" style="font-size:1.6em;">Escolha o Tipo do Documento: </label> <?=$strImgExibir?>
<br />
<br />
  <input type="text" id="txtFiltro" class="infraAutoCompletar" autocomplete="off"  style="position:relative;width:60%;" value="<?if (isset($_POST['txtFiltro'])) echo $_POST['txtFiltro'];?>">
  <br />

<?
PaginaSEI::getInstance()->fecharAreaDados();
PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros,false,'style="width:51%;"');
?>
<input type="hidden" id="hdnFiltroSerie" name="hdnFiltroSerie" value="" />
</form>
<?
PaginaSEI::getInstance()->montarAreaDebug();
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>