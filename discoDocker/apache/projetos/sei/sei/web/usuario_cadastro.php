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
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('usuario_selecionar');

  SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objUsuarioDTO = new UsuarioDTO();

  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){

    case 'usuario_alterar':
      $strTitulo = 'Alterar Usu?rio';
      $arrComandos[] = '<button type="submit" accesskey="S" name="sbmAlterarUsuario" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
      $strDesabilitar = 'disabled="disabled"';

      if (isset($_GET['id_usuario'])){
        $objUsuarioDTO->setNumIdUsuario($_GET['id_usuario']);
        $objUsuarioDTO->retTodos();
        $objUsuarioRN = new UsuarioRN();
        $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
        if ($objUsuarioDTO==null){
          throw new InfraException("Registro n?o encontrado.");
        }
      } else {
        $objUsuarioDTO->setNumIdUsuario($_GET['id_usuario_alteracao']);
        $objUsuarioDTO->setNumIdOrgao($_GET['id_orgao']);
        $objUsuarioDTO->setStrIdOrigem($_GET['id_origem']);
        $objUsuarioDTO->setNumIdContato($_GET['id_contato']);
        $objUsuarioDTO->setStrSigla($_POST['txtSiglaContatoAssociado']);
        $objUsuarioDTO->setStrNome($_POST['txtNomeContatoAssociado']);
        $objUsuarioDTO->setStrSinAcessibilidade(PaginaSEI::getInstance()->getCheckbox($_POST['chkSinAcessibilidade']));
        $objUsuarioDTO->setStrSinAtivo('S');
      }

      $arrComandos[] = '<button type="button" accesskey="C" id="btnCancelar" name="btnCancelar" value="Cancelar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'#ID-'.$objUsuarioDTO->getNumIdUsuario().'\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

      if (isset($_POST['sbmAlterarUsuario'])) {
        try{
          $objUsuarioRN = new UsuarioRN();
          $objUsuarioRN->alterarRN0488($objUsuarioDTO);
          PaginaSEI::getInstance()->setStrMensagem('Usu?rio "'.$objUsuarioDTO->getStrSigla().'" alterado com sucesso.');
          header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].'#ID-'.$objUsuarioDTO->getNumIdUsuario()));
          die;
        }catch(Exception $e){
          PaginaSEI::getInstance()->processarExcecao($e);
        }
      }
      break;

    case 'usuario_consultar':
      $strTitulo = "Consultar Usu?rio";
      $arrComandos[] = '<button type="button" accesskey="F" name="btnFechar" value="Fechar" onclick="location.href=\''.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao']).'#ID-'.$_GET['id_usuario'].'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

      $objUsuarioDTO->retTodos();
      $objUsuarioDTO->setNumIdUsuario($_GET['id_usuario']);

      $objUsuarioRN = new UsuarioRN();
      $objUsuarioDTO = $objUsuarioRN->consultarRN0489($objUsuarioDTO);
      if ($objUsuarioDTO===null){
        throw new InfraException("Registro n?o encontrado.");
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
#divSinAcessibilidade {position:absolute;left:0%;top:0%;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
?>

function inicializar(){
  if ('<?=$_GET['acao']?>'=='usuario_consultar'){
    infraDesabilitarCamposAreaDados();
  }else{
    document.getElementById('btnCancelar').focus();
  }
}

function OnSubmitForm() {
  return validarFormRI0699();
}

function validarFormRI0699() {
  return true;
}

<?
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmUsuarioCadastro" method="post" onsubmit="return OnSubmitForm();" action="<?=SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao'].'&acao_origem='.$_GET['acao'].'&id_usuario_alteracao='.$objUsuarioDTO->getNumIdUsuario().'&id_orgao='.$objUsuarioDTO->getNumIdOrgao().'&id_contato='.$objUsuarioDTO->getNumIdContato().'&id_origem='.$objUsuarioDTO->getStrIdOrigem())?>">
<?
//PaginaSEI::getInstance()->montarBarraLocalizacao($strTitulo);
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
//PaginaSEI::getInstance()->montarAreaValidacao();
ContatoINT::montarContatoAssociado(true, $objUsuarioDTO->getNumIdUsuario(), false,  null, true, $objUsuarioDTO->getStrIdOrigem(), $objUsuarioDTO->getNumIdContato(), $objUsuarioDTO->getStrSigla(), $objUsuarioDTO->getStrNome(),true,'frmUsuarioCadastro');
PaginaSEI::getInstance()->abrirAreaDados('5em');
?>

  <div id="divSinAcessibilidade" class="infraDivCheckbox">
    <input type="checkbox" id="chkSinAcessibilidade" name="chkSinAcessibilidade" class="infraCheckbox" <?=PaginaSEI::getInstance()->setCheckbox($objUsuarioDTO->getStrSinAcessibilidade())?>  tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>"/>
    <label id="lblSinAcessibilidade" for="chkSinAcessibilidade" accesskey="" class="infraLabelCheckbox">Ativar recursos de acessibilidade</label>
    &nbsp;&nbsp;
    <a href="javascript:void(0);" id="ancAjuda" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" <?=PaginaSEI::montarTitleTooltip('? necess?rio que o usu?rio realize novo login no sistema para que esta altera??o tenha efeito.')?>><img src="<?=PaginaSEI::getInstance()->getDiretorioImagensGlobal()?>/ajuda.gif" class="infraImg"/></a>
  </div>

<?
PaginaSEI::getInstance()->fecharAreaDados();
//PaginaSEI::getInstance()->montarAreaDebug();
//PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos);
?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>