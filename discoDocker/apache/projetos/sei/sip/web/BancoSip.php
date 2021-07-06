<?
/*
 * TRIBUNAL REGIONAL FEDERAL DA 4ª REGIÃO
 * 
 * 12/11/2007 - criado por MGA
 *
 */

require_once dirname(__FILE__).'/Sip.php';

if (!ConfiguracaoSip::getInstance()->isSetValor('BancoSip','Tipo')){
  die('Tipo do banco de dados do SIP não configurado.');
}

switch(ConfiguracaoSip::getInstance()->getValor('BancoSip','Tipo')){
  case 'MySql':
    class BancoSip extends InfraMySqli {
      private static $instance = null;
      private static $bolScript = false;

      public static function getInstance() {
        if (self::$instance == null) {
          self::$instance = new BancoSip();
        }
        return self::$instance;
      }

      public function setBolScript($bolScript){
        self::$bolScript = $bolScript;
      }

      public function getServidor() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
      }

      public function getPorta() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
      }

      public function getBanco() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
      }

      public function getUsuario(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'UsuarioScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Usuario');
        }
      }

      public function getSenha(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'SenhaScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Senha');
        }
      }

      public function isBolManterConexaoAberta(){
        return true;
      }

      public function isBolForcarPesquisaCaseInsensitive(){
        return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
      }

      public function isBolConsultaRetornoAssociativo(){
        return true;
      }
    }
    break;

  case 'SqlServer':
    class BancoSip extends InfraSqlServer {
      private static $instance = null;
      private static $bolScript = false;

      public static function getInstance() {
        if (self::$instance == null) {
          self::$instance = new BancoSip();
        }
        return self::$instance;
      }

      public function setBolScript($bolScript){
        self::$bolScript = $bolScript;
      }

      public function getServidor() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
      }

      public function getPorta() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
      }

      public function getBanco() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
      }

      public function getUsuario(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'UsuarioScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Usuario');
        }
      }

      public function getSenha(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'SenhaScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Senha');
        }
      }

      public function isBolManterConexaoAberta(){
        return true;
      }

      public function isBolForcarPesquisaCaseInsensitive(){
        return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
      }

      public function isBolConsultaRetornoAssociativo(){
        return true;
      }
    }
    break;

  case 'Oracle':
    class BancoSip extends InfraOracle {
      private static $instance = null;
      private static $bolScript = false;

      public static function getInstance() {
        if (self::$instance == null) {
          self::$instance = new BancoSip();
        }
        return self::$instance;
      }

      public function setBolScript($bolScript){
        self::$bolScript = $bolScript;
      }

      public function getServidor() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Servidor');
      }

      public function getPorta() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Porta');
      }

      public function getBanco() {
        return ConfiguracaoSip::getInstance()->getValor('BancoSip','Banco');
      }

      public function getUsuario(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'UsuarioScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Usuario');
        }
      }

      public function getSenha(){
        if (self::$bolScript) {
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'SenhaScript');
        }else{
          return ConfiguracaoSip::getInstance()->getValor('BancoSip', 'Senha');
        }
      }

      public function isBolManterConexaoAberta(){
        return true;
      }

      public function isBolForcarPesquisaCaseInsensitive(){
        return !ConfiguracaoSip::getInstance()->getValor('BancoSip', 'PesquisaCaseInsensitive', false, false);
      }
    }
    break;

  default:
    die('Configuração do tipo de banco de dados do SIP inválida.');
}
?>