<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 14/04/2008 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.14.0
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

  PaginaSEI::getInstance()->prepararSelecao('unidade_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao','txtSiglaUnidade','txtDescricaoUnidade'));
  
  switch($_GET['acao']){
    case 'unidade_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjUnidadeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->setNumIdUnidade($arrStrIds[$i]);
          $arrObjUnidadeDTO[] = $objUnidadeDTO;
        }
        $objUnidadeRN = new UnidadeRN();
        $objUnidadeRN->excluirRN0126($arrObjUnidadeDTO);
        PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'unidade_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjUnidadeDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUnidadeDTO = new UnidadeDTO();
          $objUnidadeDTO->setNumIdUnidade($arrStrIds[$i]);
          $arrObjUnidadeDTO[] = $objUnidadeDTO;
        }
        $objUnidadeRN = new UnidadeRN();
        $objUnidadeRN->desativarRN0484($arrObjUnidadeDTO);
        PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'unidade_reativar':
      $strTitulo = 'Reativar Unidades';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjUnidadeDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setNumIdUnidade($arrStrIds[$i]);
            $arrObjUnidadeDTO[] = $objUnidadeDTO;
          }
          $objUnidadeRN = new UnidadeRN();
          $objUnidadeRN->reativarRN0485($arrObjUnidadeDTO);
          PaginaSEI::getInstance()->setStrMensagem('Opera??o realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

    case 'unidade_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Unidade','Selecionar Unidades');
      break;
      
    case 'unidade_listar':
      $strTitulo = 'Unidades';
      break;
      
      
    case 'gerar_estatisticas_unidade':
    	$strTitulo = 'Estat?sticas Unidade';
    	//exit;
    	break;
      

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';  
  
  if ($_GET['acao'] == 'unidade_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }

  $objUnidadeDTO = new UnidadeDTO();
  $objUnidadeDTO->retNumIdUnidade();
  $objUnidadeDTO->retStrSigla();
  $objUnidadeDTO->retStrDescricao();
  $objUnidadeDTO->retStrSiglaOrgao();
  $objUnidadeDTO->retStrDescricaoOrgao();


  $numNumAno = PaginaSEI::getInstance()->recuperarCampo('selAno');
  
  $numIdOrgao = PaginaSEI::getInstance()->recuperarCampo('selOrgao');
  if ($numIdOrgao!==''){
    $objUnidadeDTO->setNumIdOrgao($numIdOrgao);
  }

  $strSiglaPesquisa = trim(PaginaSEI::getInstance()->recuperarCampo('txtSiglaUnidade'));
  if ($strSiglaPesquisa!==''){
    $objUnidadeDTO->setStrSigla($strSiglaPesquisa);
  }
  
  $strDescricaoPesquisa = PaginaSEI::getInstance()->recuperarCampo('txtDescricaoUnidade');
  if ($strDescricaoPesquisa!==''){
    $objUnidadeDTO->setStrDescricao($strDescricaoPesquisa);
  }
  
  if ($_GET['acao'] == 'unidade_reativar'){
    //Lista somente inativos
    $objUnidadeDTO->setBolExclusaoLogica(false);
    $objUnidadeDTO->setStrSinAtivo('N');
  }

  PaginaSEI::getInstance()->prepararOrdenacao($objUnidadeDTO, 'Sigla', InfraDTO::$TIPO_ORDENACAO_ASC);

  PaginaSEI::getInstance()->prepararPaginacao($objUnidadeDTO);

  $objUnidadeRN = new UnidadeRN();
  $arrObjUnidadeDTO = $objUnidadeRN->pesquisar($objUnidadeDTO);

  PaginaSEI::getInstance()->processarPaginacao($objUnidadeDTO);
  
  $numRegistros = count($arrObjUnidadeDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='unidade_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('unidade_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('unidade_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
    }else if ($_GET['acao']=='unidade_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('unidade_reativar');
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('unidade_excluir');
      $bolAcaoDesativar = false;
    }else if ($_GET['acao']=='gerar_estatisticas_unidade'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('unidade_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('unidade_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('unidade_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('unidade_desativar');
    }

    if ($bolAcaoDesativar){
      $bolCheck = true;
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    if ($_GET['acao']!='unidade_reativar'){
      $strSumarioTabela = 'Tabela de Unidades.';
      $strCaptionTabela = 'Unidades';
    }else{
      $strSumarioTabela = 'Tabela de Unidades Inativas.';
      $strCaptionTabela = 'Unidades Inativas';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO,'ID','IdUnidade',$arrObjUnidadeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO,'Sigla','Sigla',$arrObjUnidadeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO,'Desri??o','Descricao',$arrObjUnidadeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objUnidadeDTO,'?rg?o','SiglaOrgao',$arrObjUnidadeDTO).'</th>'."\n";
    $strResultado .= '<th class="infraTh" width="15%">A??es</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      //echo $arrObjUnidadeDTO[$i]->__toString();die;
      
      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td>'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjUnidadeDTO[$i]->getNumIdUnidade(),UnidadeINT::formatarSiglaDescricao($arrObjUnidadeDTO[$i]->getStrSigla(),$arrObjUnidadeDTO[$i]->getStrDescricao())).'</td>';
      }
      
      $strResultado .= '<td align="center">'.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjUnidadeDTO[$i]->getStrSigla()).'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjUnidadeDTO[$i]->getStrDescricao()).'</td>';
      $strResultado .= '<td align="center"><a alt="'.PaginaSEI::tratarHTML($arrObjUnidadeDTO[$i]->getStrDescricaoOrgao()).'" title="'.PaginaSEI::tratarHTML($arrObjUnidadeDTO[$i]->getStrDescricaoOrgao()).'" class="ancoraSigla">'.PaginaSEI::tratarHTML($arrObjUnidadeDTO[$i]->getStrSiglaOrgao()).'</a></td>';
      $strResultado .= '<td align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjUnidadeDTO[$i]->getNumIdUnidade());
      
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_unidade='.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'&sigla='.$arrObjUnidadeDTO[$i]->getStrSigla()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/consultar.gif" title="Consultar Unidade" alt="Consultar Unidade" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=unidade_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_unidade='.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'&sigla='.$arrObjUnidadeDTO[$i]->getStrSigla()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Unidade" alt="Alterar Unidade" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'"  onclick="acaoDesativar(\''.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'\',\''.$arrObjUnidadeDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Unidade" alt="Desativar Unidade" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'"  onclick="acaoReativar(\''.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'\',\''.$arrObjUnidadeDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Unidade" alt="Reativar Unidade" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'"  onclick="acaoExcluir(\''.$arrObjUnidadeDTO[$i]->getNumIdUnidade().'\',\''.$arrObjUnidadeDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Unidade" alt="Excluir Unidade" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'unidade_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFecharSelecao" value="Fechar" onclick="window.close();" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }else{
    $arrComandos[] = '<button type="button" accesskey="F" id="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'\'" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';
  }
  
  $strItensSelOrgao = OrgaoINT::montarSelectSiglaRI1358('','Todos',$numIdOrgao);
  
  
  
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
#lblOrgao {position:absolute;left:0%;top:0%;width:20%;}
#selOrgao {position:absolute;left:0%;top:40%;width:20%;}

#lblSiglaUnidade {position:absolute;left:25%;top:0%;width:15%;}
#txtSiglaUnidade {position:absolute;left:25%;top:40%;width:15%;}

#lblDescricaoUnidade {position:absolute;left:45%;top:0%;width:50%;}
#txtDescricaoUnidade {position:absolute;left:45%;top:40%;width:50%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='unidade_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativa??o da Unidade \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUnidadeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma Unidade selecionada.');
    return;
  }
  if (confirm("Confirma desativa??o das Unidades selecionadas?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUnidadeLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativa??o da Unidade \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUnidadeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma Unidade selecionada.');
    return;
  }
  if (confirm("Confirma reativa??o das Unidades selecionadas?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUnidadeLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclus?o da Unidade \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUnidadeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhuma Unidade selecionada.');
    return;
  }
  if (confirm("Confirma exclus?o das Unidades selecionadas?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUnidadeLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUnidadeLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUnidadeLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblOrgao" for="selOrgao" accesskey="o" class="infraLabelOpcional">?rg?<span class="infraTeclaAtalho">o</span>:</label>
  <select id="selOrgao" name="selOrgao" onchange="this.form.submit();" class="infraSelect" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" >
  <?=$strItensSelOrgao?>
  </select>

  <label id="lblSiglaUnidade" for="txtSiglaUnidade" class="infraLabelOpcional">Sigla:</label>
  <input type="text" id="txtSiglaUnidade" name="txtSiglaUnidade" class="infraText" value="<?=PaginaSEI::tratarHTML($strSiglaPesquisa)?>" maxlength="15" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <label id="lblDescricaoUnidade" for="txtDescricaoUnidade" class="infraLabelOpcional">Descri??o:</label>
  <input type="text" id="txtDescricaoUnidade" name="txtDescricaoUnidade" class="infraText" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" value="<?=PaginaSEI::tratarHTML($strDescricaoPesquisa)?>" />
  
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  PaginaSEI::getInstance()->montarAreaTabela($strResultado,$numRegistros);
  PaginaSEI::getInstance()->montarAreaDebug();
  PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>