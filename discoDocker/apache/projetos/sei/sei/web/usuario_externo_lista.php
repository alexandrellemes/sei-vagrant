<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 14/04/2008 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.14.0
*
* Vers�o no CVS: $Id$
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

  PaginaSEI::getInstance()->prepararSelecao('usuario_externo_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);
  
  PaginaSEI::getInstance()->salvarCamposPost(array('selOrgao','txtSiglaUsuario','txtNomeUsuario', 'txtCpfUsuario'));

  switch($_GET['acao']){
    case 'usuario_externo_excluir':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjUsuarioDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
          $arrObjUsuarioDTO[] = $objUsuarioDTO;
        }
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioRN->excluirRN0491($arrObjUsuarioDTO);
        PaginaSEI::getInstance()->setStrMensagem('Opera��o realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;


    case 'usuario_externo_desativar':
      try{
        $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
        $arrObjUsuarioDTO = array();
        for ($i=0;$i<count($arrStrIds);$i++){
          $objUsuarioDTO = new UsuarioDTO();
          $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
          $arrObjUsuarioDTO[] = $objUsuarioDTO;
        }
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioRN->desativarRN0695($arrObjUsuarioDTO);
        PaginaSEI::getInstance()->setStrMensagem('Opera��o realizada com sucesso.');
      }catch(Exception $e){
        PaginaSEI::getInstance()->processarExcecao($e);
      } 
      header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
      die;

    case 'usuario_externo_reativar':
      $strTitulo = 'Reativar Usu�rios Externos';
      if ($_GET['acao_confirmada']=='sim'){
        try{
          $arrStrIds = PaginaSEI::getInstance()->getArrStrItensSelecionados();
          $arrObjUsuarioDTO = array();
          for ($i=0;$i<count($arrStrIds);$i++){
            $objUsuarioDTO = new UsuarioDTO();
            $objUsuarioDTO->setNumIdUsuario($arrStrIds[$i]);
            $arrObjUsuarioDTO[] = $objUsuarioDTO;
          }
          $objUsuarioRN = new UsuarioRN();
          $objUsuarioRN->reativarRN0696($arrObjUsuarioDTO);
          PaginaSEI::getInstance()->setStrMensagem('Opera��o realizada com sucesso.');
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        } 
        header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao_origem'].'&acao_origem='.$_GET['acao']));
        die;
      } 
      break;

    case 'usuario_externo_selecionar':
      $strTitulo = PaginaSEI::getInstance()->getTituloSelecao('Selecionar Usuario','Selecionar Usuarios');
      break;

    case 'usuario_externo_listar':
      $strTitulo = 'Usu�rios Externos';
      break;

    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }

  $arrComandos = array();
  
  $arrComandos[] = '<input type="submit" id="btnPesquisar" value="Pesquisar" class="infraButton" />';  
  
  if ($_GET['acao'] == 'usuario_externo_selecionar'){
    $arrComandos[] = '<button type="button" accesskey="T" id="btnTransportarSelecao" value="Transportar" onclick="infraTransportarSelecao();" class="infraButton"><span class="infraTeclaAtalho">T</span>ransportar</button>';
  }
  $objInfraParametro = new InfraParametro(BancoSEI::getInstance());

  $objUsuarioDTO = new UsuarioDTO();
  $objUsuarioDTO->retNumIdUsuario();
  $objUsuarioDTO->retStrSigla();
  $objUsuarioDTO->retStrNome();
  $objUsuarioDTO->retStrStaTipo();

  $strSiglaPesquisa = trim(PaginaSEI::getInstance()->recuperarCampo('txtSiglaUsuario'));
  if ($strSiglaPesquisa!==''){
    $objUsuarioDTO->setStrSigla($strSiglaPesquisa);
  }

  $strCpfPesquisa = trim(PaginaSEI::getInstance()->recuperarCampo('txtCpfUsuario'));
  if ($strCpfPesquisa!==''){
    $objUsuarioDTO->setDblCpfContato(InfraUtil::retirarFormatacao($strCpfPesquisa));
  }

  $strNomePesquisa = PaginaSEI::getInstance()->recuperarCampo('txtNomeUsuario');
  if ($strNomePesquisa!==''){
    $objUsuarioDTO->setStrNome($strNomePesquisa);
  }
  
  if ($_GET['acao'] == 'usuario_externo_reativar'){
    //Lista somente inativos
    $objUsuarioDTO->setBolExclusaoLogica(false);
    $objUsuarioDTO->setStrSinAtivo('N');
  }
  
  
  $objUsuarioDTO->adicionarCriterio(array('StaTipo', 'StaTipo'),
  		array(InfraDTO::$OPER_IGUAL, InfraDTO::$OPER_IGUAL),
  		array(UsuarioRN::$TU_EXTERNO, UsuarioRN::$TU_EXTERNO_PENDENTE),
  		array(InfraDTO::$OPER_LOGICO_OR));
  
  
  PaginaSEI::getInstance()->prepararOrdenacao($objUsuarioDTO, 'Sigla', InfraDTO::$TIPO_ORDENACAO_ASC);
  
  PaginaSEI::getInstance()->prepararPaginacao($objUsuarioDTO);

  $objUsuarioRN = new UsuarioRN();
  $arrObjUsuarioDTO = $objUsuarioRN->pesquisar($objUsuarioDTO);

  PaginaSEI::getInstance()->processarPaginacao($objUsuarioDTO);
  
  $numRegistros = count($arrObjUsuarioDTO);

  if ($numRegistros > 0){

    $bolCheck = false;

    if ($_GET['acao']=='usuario_externo_selecionar'){
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_alterar');
      $bolAcaoImprimir = false;
      $bolAcaoExcluir = false;
      $bolAcaoDesativar = false;
      $bolCheck = true;
    }else if ($_GET['acao']=='usuario_externo_reativar'){
      $bolAcaoReativar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_reativar');
      $bolAcaoConsultar = false;
      $bolAcaoAlterar = false;
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_excluir');
      $bolAcaoDesativar = false;
    }else{
      $bolAcaoReativar = false;
      $bolAcaoConsultar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_consultar');
      $bolAcaoAlterar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_alterar');
      $bolAcaoImprimir = true;
      $bolAcaoExcluir = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_excluir');
      $bolAcaoDesativar = SessaoSEI::getInstance()->verificarPermissao('usuario_externo_desativar');
    }

    if ($bolAcaoDesativar){
      $bolCheck = true;
      $strLinkDesativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_externo_desativar&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoReativar){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="R" id="btnReativar" value="Reativar" onclick="acaoReativacaoMultipla();" class="infraButton"><span class="infraTeclaAtalho">R</span>eativar</button>';
      $strLinkReativar = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_externo_reativar&acao_origem='.$_GET['acao'].'&acao_confirmada=sim');
    }
    

    if ($bolAcaoExcluir){
      $bolCheck = true;
      $strLinkExcluir = SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_externo_excluir&acao_origem='.$_GET['acao']);
    }

    if ($bolAcaoImprimir){
      $bolCheck = true;
      $arrComandos[] = '<button type="button" accesskey="I" id="btnImprimir" value="Imprimir" onclick="infraImprimirTabela();" class="infraButton"><span class="infraTeclaAtalho">I</span>mprimir</button>';

    }

    $strResultado = '';

    if ($_GET['acao']!='usuario_externo_reativar'){
      $strSumarioTabela = 'Tabela de Usu�rios Externos.';
      $strCaptionTabela = 'Usu�rios Externos';
    }else{
      $strSumarioTabela = 'Tabela de Usu�rios Externos Inativos.';
      $strCaptionTabela = 'Usu�rios Externos Inativos';
    }

    $strResultado .= '<table width="99%" class="infraTable" summary="'.$strSumarioTabela.'">'."\n";
    $strResultado .= '<caption class="infraCaption">'.PaginaSEI::getInstance()->gerarCaptionTabela($strCaptionTabela,$numRegistros).'</caption>';
    $strResultado .= '<tr>';
    if ($bolCheck) {
      $strResultado .= '<th class="infraTh" width="1%">'.PaginaSEI::getInstance()->getThCheck().'</th>'."\n";
    }
    
    //$strResultado .= '<th class="infraTh" width="10%">ID SIP</th>'."\n";    
    $strResultado .= '<th class="infraTh" width="30%">'.PaginaSEI::getInstance()->getThOrdenacao($objUsuarioDTO,'E-mail','Sigla',$arrObjUsuarioDTO,true).'</th>'."\n";
    $strResultado .= '<th class="infraTh">'.PaginaSEI::getInstance()->getThOrdenacao($objUsuarioDTO,'Nome','Nome',$arrObjUsuarioDTO,true).'</th>'."\n";    
    $strResultado .= '<th class="infraTh" width="10%">'.PaginaSEI::getInstance()->getThOrdenacao($objUsuarioDTO,'Pendente','StaTipo',$arrObjUsuarioDTO,true).'</th>'."\n";    
    
    $strResultado .= '<th class="infraTh" width="15%">A��es</th>'."\n";
    $strResultado .= '</tr>'."\n";
    $strCssTr='';
    for($i = 0;$i < $numRegistros; $i++){

      $strCssTr = ($strCssTr=='<tr class="infraTrClara">')?'<tr class="infraTrEscura">':'<tr class="infraTrClara">';
      $strResultado .= $strCssTr;

      if ($bolCheck){
        $strResultado .= '<td valign="top">'.PaginaSEI::getInstance()->getTrCheck($i,$arrObjUsuarioDTO[$i]->getNumIdUsuario(),$arrObjUsuarioDTO[$i]->getStrSigla()).'</td>';
      }
      //$strResultado .= '<td align="center">'.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjUsuarioDTO[$i]->getStrSigla()).'</td>';
      $strResultado .= '<td>'.PaginaSEI::tratarHTML($arrObjUsuarioDTO[$i]->getStrNome()).'</td>';
      $pendente=($arrObjUsuarioDTO[$i]->getStrStaTipo()==UsuarioRN::$TU_EXTERNO_PENDENTE)?'S':'';
      $strResultado .= '<td align="center">'.$pendente.'</td>';
      $strResultado .= '<td align="center">';
      
      $strResultado .= PaginaSEI::getInstance()->getAcaoTransportarItem($i,$arrObjUsuarioDTO[$i]->getNumIdUsuario());
      
      
      if ($bolAcaoConsultar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_externo_consultar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_usuario='.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'&sigla='.$arrObjUsuarioDTO[$i]->getStrSigla().'&nome='.$arrObjUsuarioDTO[$i]->getStrNome()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/consultar.gif" title="Consultar Usu�rio Externo" alt="Consultar Usu�rio Externo" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoAlterar){
        $strResultado .= '<a href="'.SessaoSEI::getInstance()->assinarLink('controlador.php?acao=usuario_externo_alterar&acao_origem='.$_GET['acao'].'&acao_retorno='.$_GET['acao'].'&id_usuario='.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'&sigla='.$arrObjUsuarioDTO[$i]->getStrSigla().'&nome='.$arrObjUsuarioDTO[$i]->getStrNome()).'" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/alterar.gif" title="Alterar Usu�rio Externo" alt="Alterar Usu�rio Externo" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoDesativar){
        $strResultado .= '<a href="#ID-'.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'"  onclick="acaoDesativar(\''.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'\',\''.$arrObjUsuarioDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/desativar.gif" title="Desativar Usu�rio Externo" alt="Desativar Usu�rio Externo" class="infraImg" /></a>&nbsp;';
      }

      if ($bolAcaoReativar){
        $strResultado .= '<a href="#ID-'.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'"  onclick="acaoReativar(\''.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'\',\''.$arrObjUsuarioDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/reativar.gif" title="Reativar Usu�rio Externo" alt="Reativar Usu�rio Externo" class="infraImg" /></a>&nbsp;';
      }


      if ($bolAcaoExcluir){
        $strResultado .= '<a href="#ID-'.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'"  onclick="acaoExcluir(\''.$arrObjUsuarioDTO[$i]->getNumIdUsuario().'\',\''.$arrObjUsuarioDTO[$i]->getStrSigla().'\');" tabindex="'.PaginaSEI::getInstance()->getProxTabTabela().'"><img src="imagens/excluir.gif" title="Excluir Usu�rio Externo" alt="Excluir Usu�rio Externo" class="infraImg" /></a>&nbsp;';
      }

      $strResultado .= '</td></tr>'."\n";
    }
    $strResultado .= '</table>';
  }
  if ($_GET['acao'] == 'usuario_externo_selecionar'){
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

#lblSiglaUsuario {position:absolute;left:0%;top:0%;width:20%;}
#txtSiglaUsuario {position:absolute;left:0%;top:40%;width:20%;}

#lblNomeUsuario {position:absolute;left:22%;top:0%;width:40%;}
#txtNomeUsuario {position:absolute;left:22%;top:40%;width:40%;}

#lblCpfUsuario {position:absolute;left:64%;top:0%;width:15%;}
#txtCpfUsuario {position:absolute;left:64%;top:40%;width:15%;}

<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='usuario_externo_selecionar'){
    infraReceberSelecao();
    document.getElementById('btnFecharSelecao').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  
  infraEfeitoTabelas();
}

<? if ($bolAcaoDesativar){ ?>
function acaoDesativar(id,desc){
  if (confirm("Confirma desativa��o do usu�rio externo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoDesativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum usu�rio externo selecionado.');
    return;
  }
  if (confirm("Confirma desativa��o dos usu�rios externos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkDesativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoReativar){ ?>
function acaoReativar(id,desc){
  if (confirm("Confirma reativa��o do usu�rio externo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoReativacaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum usu�rio externo selecionado.');
    return;
  }
  if (confirm("Confirma reativa��o dos usu�rios selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkReativar?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<? if ($bolAcaoExcluir){ ?>
function acaoExcluir(id,desc){
  if (confirm("Confirma exclus�o do usu�rio externo \""+desc+"\"?")){
    document.getElementById('hdnInfraItemId').value=id;
    document.getElementById('frmUsuarioLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}

function acaoExclusaoMultipla(){
  if (document.getElementById('hdnInfraItensSelecionados').value==''){
    alert('Nenhum usu�rio externo selecionado.');
    return;
  }
  if (confirm("Confirma exclus�o dos usu�rios externos selecionados?")){
    document.getElementById('hdnInfraItemId').value='';
    document.getElementById('frmUsuarioLista').action='<?=$strLinkExcluir?>';
    document.getElementById('frmUsuarioLista').submit();
  }
}
<? } ?>

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUsuarioLista" method="post" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'])?>">
  <?
  //PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
  PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
  PaginaSEI::getInstance()->abrirAreaDados('5em');
  ?>
  <label id="lblSiglaUsuario" for="txtSiglaUsuario" accesskey="E" class="infraLabelOpcional"><span class="infraTeclaAtalho">E</span>-mail:</label>
  <input type="text" id="txtSiglaUsuario" name="txtSiglaUsuario" class="infraText" value="<?=PaginaSEI::tratarHTML($strSiglaPesquisa)?>" maxlength="100" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
  <label id="lblNomeUsuario" for="txtNomeUsuario" accesskey="N" class="infraLabelOpcional"><span class="infraTeclaAtalho">N</span>ome:</label>
  <input type="text" id="txtNomeUsuario" name="txtNomeUsuario" class="infraText" value="<?=PaginaSEI::tratarHTML($strNomePesquisa)?>" maxlength="50" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

  <label id="lblCpfUsuario" for="txtCpfUsuario" accesskey="C" class="infraLabelOpcional"><span class="infraTeclaAtalho">C</span>PF:</label>
  <input type="text" id="txtCpfUsuario" name="txtCpfUsuario" onkeypress="return infraMascaraCpf(this, event)" class="infraText" value="<?=PaginaSEI::tratarHTML($strCpfPesquisa);?>" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />

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