<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 07/08/2009 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.27.1
*
* Vers?o no CVS: $Id$
*/

try {
  //require_once 'Infra.php';

  session_start();

  //////////////////////////////////////////////////////////////////////////////
  //InfraDebug::getInstance()->setBolLigado(false);
  //InfraDebug::getInstance()->setBolDebugInfra(true);
  //InfraDebug::getInstance()->limpar();
  //////////////////////////////////////////////////////////////////////////////

  SessaoInfra::getInstance()->validarLink();

  PaginaInfra::getInstance()->verificarSelecao('infra_configurar_selecionar');

  //SessaoInfra::getInstance()->validarPermissao($_GET['acao']);

  $arrComandos = array();

  switch($_GET['acao']){
    case 'infra_configurar':
      $strTitulo = 'Configura??es';
      
      //$arrComandos[] = '<input type="button" name="btnAplicar" value="Aplicar" onclick="this.form.submit();" class="infraButton" />';
      $arrComandos[] = '<input type="button" value="Fechar" onclick="document.getElementById(\'divInfraBarraLocalizacao\').innerHTML=\'\';document.getElementById(\'divInfraAreaTelaD\').innerHTML=\'\';" class="infraButton" />';

      if (isset($_POST['selInfraCores'])) {

        if (SessaoInfra::getInstance()->getObjInfraIBanco()){
          $objInfraDadoUsuario = new InfraDadoUsuario(SessaoInfra::getInstance());
          $objInfraDadoUsuario->setValor('INFRA_ESQUEMA_CORES', $_POST['selInfraCores']);
        }

        SessaoInfra::getInstance()->setAtributo('infra_esquema_cores', $_POST['selInfraCores']);
      }
      
      break;

    default:
      throw new InfraException("A??o '".$_GET['acao']."' n?o reconhecida.");
  }


}catch(Exception $e){
  PaginaInfra::getInstance()->processarExcecao($e);
}
PaginaInfra::getInstance()->montarDocType();
PaginaInfra::getInstance()->abrirHtml();
PaginaInfra::getInstance()->abrirHead();
PaginaInfra::getInstance()->montarMeta();
PaginaInfra::getInstance()->montarTitle(PaginaInfra::getInstance()->getStrNomeSistema());
PaginaInfra::getInstance()->montarStyle();
PaginaInfra::getInstance()->abrirStyle();
?>
#lblInfraCores {position:absolute;left:0%;top:0%;width:50%;}
#selInfraCores {position:absolute;left:0%;top:40%;width:50%;}
<?
PaginaInfra::getInstance()->fecharStyle();
PaginaInfra::getInstance()->montarJavaScript();
PaginaInfra::getInstance()->fecharHead();
PaginaInfra::getInstance()->abrirBody($strTitulo);
?>
<form id="frmInfraConfigurar" method="post">
<?
PaginaInfra::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaInfra::getInstance()->abrirAreaDados('5em');
$strEsquema = PaginaInfra::getInstance()->getStrEsquemaCores();
?>
<label id="lblInfraCores" for="selInfraCores" accesskey="E" class="infraLabelOpcional"><span class="infraTeclaAtalho">E</span>squema de Cores:</label>
<br />
<select id="selInfraCores" name="selInfraCores" onchange="infraEsquemaCoresSistema(this.value);this.form.submit();" class="infraSelect" tabindex="<?=PaginaInfra::getInstance()->getProxTabDados()?>'">
<option value="<?=InfraPaginaEsquema::$ESQUEMA_AZUL_CELESTE?>" <?=$strEsquema==InfraPaginaEsquema::$ESQUEMA_AZUL_CELESTE?' selected="selected" ':''?>>Azul Celeste</option>
<option value="<?=InfraPaginaEsquema::$ESQUEMA_CEREJA?>" <?=$strEsquema==InfraPaginaEsquema::$ESQUEMA_CEREJA?' selected="selected" ':''?>>Cereja</option>
<option value="<?=InfraPaginaEsquema::$ESQUEMA_VERDE_LIMAO?>" <?=$strEsquema==InfraPaginaEsquema::$ESQUEMA_VERDE_LIMAO?' selected="selected" ':''?>>Verde Lim?o</option>
<option value="<?=InfraPaginaEsquema::$ESQUEMA_VERMELHO?>" <?=$strEsquema==InfraPaginaEsquema::$ESQUEMA_VERMELHO?' selected="selected" ':''?>>Vermelho</option>
</select>
<?
PaginaInfra::getInstance()->fecharAreaDados();
?>
</form>
<?
PaginaInfra::getInstance()->fecharBody();
PaginaInfra::getInstance()->fecharHtml();
?>