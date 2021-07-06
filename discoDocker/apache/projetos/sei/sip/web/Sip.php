<?
require_once 'Infra.php';

define('SIP_VERSAO','2.1.0');

define('DIR_SIP_CONFIG', dirname(__FILE__).'/../config');
define('DIR_SIP_TEMP', dirname(__FILE__).'/../temp');
define('DIR_SIP_BIN',dirname(__FILE__).'/../bin');

ini_set('session.gc_maxlifetime','28800');
ini_set('memory_limit','256M');

require_once DIR_SIP_CONFIG.'/ConfiguracaoSip.php';

//ini_set('session.cookie_secure', ConfiguracaoSip::getInstance()->getValor('SessaoSip', 'https'));

infraAdicionarPath(dirname(__FILE__));

$SIP_MODULOS = array();

if (ConfiguracaoSip::getInstance()->isSetValor('Sip','Modulos')) {

  foreach(ConfiguracaoSip::getInstance()->getValor('Sip','Modulos') as $strModulo => $strPathModulo){

    infraAdicionarPath(dirname(__FILE__).'/modulos/'.$strPathModulo);

    if (!file_exists(dirname(__FILE__).'/modulos/'.$strPathModulo . '/' . $strModulo .'.php')) {
      die('Classe de Integra��o do m�dulo "'.$strModulo.'" n�o encontrada.');
    }

    $reflectionClass = new ReflectionClass($strModulo);
    $SIP_MODULOS[$strModulo] = $reflectionClass->newInstance();
  }

  foreach($SIP_MODULOS as $strModulo => $objModulo){

    if (trim($objModulo->getNome())==''){
      die('Nome do m�dulo "'.$strModulo.'" n�o informado.');
    }

    if (trim($objModulo->getVersao())==''){
      die('Vers�o do m�dulo "'.$strModulo.'" n�o informada.');
    }

    if (trim($objModulo->getInstituicao())==''){
      die('Institui��o do m�dulo "'.$strModulo.'" n�o informada.');
    }

    $objModulo->inicializar(SIP_VERSAO);
  }

}
?>