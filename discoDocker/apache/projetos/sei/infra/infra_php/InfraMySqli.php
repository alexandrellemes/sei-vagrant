<?
/**
 * @package infra_php
 *
 */
abstract class InfraMySqli extends InfraMySql {
    private $conexao;
    private $id;
    private $transacao;
    private $arrOpcoes;

    private static $MYSQL_INFO_RECORDS = 0;
    private static $MYSQL_INFO_DUPLICATES = 1;
    private static $MYSQL_INFO_WARNINGS = 2;
    private static $MYSQL_INFO_DELETED = 3;
    private static $MYSQL_INFO_SKIPPED = 4;
    private static $MYSQL_INFO_ROWS_MATCHED = 5;
    private static $MYSQL_INFO_CHANGED = 6;
     
    public function __construct(){
        $this->conexao = null;
        $this->id = null;
        $this->transacao = false;
        $this->arrOpcoes = array();
    }

    public function __destruct(){
      if ($this->getIdConexao()!=null){
        try{
          $this->fecharConexao();
        }catch(Exception $e){}
      }
    }
     
    public function getIdBanco(){
        return __CLASS__.'-'.$this->getServidor().'-'.$this->getPorta().'-'.$this->getBanco().'-'.$this->getUsuario();
    }

    public function getIdConexao(){
        return $this->id;
    }

    public function setArrOpcoes($arrOpcoes){
      $this->arrOpcoes = $arrOpcoes;
    }

    public function getArrOpcoes(){
      return $this->arrOpcoes;
    }
     
    public function getValorSequencia($sequencia){
        $this->executarSql('INSERT INTO '.$sequencia.' (campo) VALUES (\'0\')');
        return $this->conexao->insert_id;
    }
     
    public function isBolProcessandoTransacao(){
        return $this->transacao;
    }

    public function isBolForcarPesquisaCaseInsensitive(){
        return true;
    }

    public function isBolManterConexaoAberta(){
        return false;
    }

    public function isBolValidarISO88591(){
      return false;
    }

    public function isBolConsultaRetornoAssociativo(){
      return false;
    }

  //SELECAO

    private function formatarSelecaoGenerico($tabela,$campo,$alias){
        $ret = '';
        if ($tabela!==null){
            $ret .= $tabela.'.';
        }
         
        $ret .= $campo;
         
        if ($alias!=null) {
            $ret .= ' AS '.$alias;
        }
        return $ret;
    }
     
    private function formatarSelecaoAsChar($tabela,$campo,$alias){
         
        $ret = 'CAST(';
        if ($tabela!==null){
            $ret .= $tabela.'.';
        }
        $ret .= $campo.' AS CHAR)';
         
        if ($alias!==null){
            $ret .= ' AS '.$alias;
        }else{
            $ret .= ' AS '.$campo;
        }
         
        return $ret;
    }

    public function formatarSelecaoDta($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoDth($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoStr($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoBol($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoNum($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }

    public function formatarSelecaoDin($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoDbl($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
        //return $this->formatarSelecaoAsChar($tabela,$campo,$alias);
    }
     
    public function formatarSelecaoBin($tabela,$campo,$alias){
        return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
    }
     
    //GRAVACAO
    public function formatarGravacaoDta($dta){
        return $this->gravarData(substr($dta,0,10));
    }
     
    public function formatarGravacaoDth($dth){
        return $this->gravarData($dth);
    }
     
    public function formatarGravacaoStr($str){
        if ($str===null || $str===''){
            return 'NULL';
        }

        if ($this->isBolValidarISO88591() && InfraUtil::filtrarISO88591($str) != $str){
          throw new InfraException('Detectado caracter inv?lido.');
        }

      return '\''.str_replace("\\","\\\\",str_replace('\'','\'\'',$str)).'\'';
    }
     
    public function formatarGravacaoBol($bol){
        if ( $bol===true ) {
            return 1;
        }
        return 0;
    }
     
    public function formatarGravacaoNum($num){
      $num = trim($num);
    
      if ($num===''){
        return 'NULL';
      }
      	
      if (!is_numeric($num)){
        throw new InfraException('Valor num?rico inv?lido ['.$num.'].');
      }
      	
      return $num;
    }
    
    public function formatarGravacaoDin($din){
      $din = trim($din);
    
      if ($din===''){
        return 'NULL';
      }
      	
      $din = InfraUtil::prepararDin($din);
      	
      if (!is_numeric($din)){
        throw new InfraException('Valor num?rico inv?lido ['.$din.'].');
      }
      	
      return $din;
    }
    
    public function formatarGravacaoDbl($dbl){
      $dbl = trim($dbl);
    
      if ($dbl===''){
        return 'NULL';
      }
      	
      $dbl = InfraUtil::prepararDbl($dbl);
      	
      if (!is_numeric($dbl)){
        throw new InfraException('Valor num?rico inv?lido ['.$dbl.'].');
      }
      	
      return $dbl;
    }
    
    
    public function formatarGravacaoBin($bin){
        if ($bin===null || $bin===''){
            return 'NULL';
        }
        return '0x'.bin2hex($bin);
    }

    //LEITURA
    public function converterStr($tabela,$campo){
         
        $ret = 'CAST(';
        if ($tabela!==null){
            $ret .= $tabela.'.';
        }
        $ret .= $campo.' AS CHAR)';
         
        return $ret;
    }

    public function formatarPesquisaStr($strTabela,$strCampo,$strValor,$strOperador,$bolCaseInsensitive){
        if ($bolCaseInsensitive){
            return 'upper('.$strCampo.') '.$strOperador.' \''.str_replace('\'','\'\'',InfraString::transformarCaixaAlta($strValor)).'\' ';
        }else{
            return $strCampo.' '.$strOperador.' \''.str_replace('\'','\'\'',$strValor).'\' ';
        }
    }
     
     
    public function formatarLeituraDta($dta){
        $dta = $this->lerData($dta);
        if ($dta!==null){
            $dta = substr($dta,0,10);
        }
        return $dta;
    }
     
    public function formatarLeituraDth($dth){
        return $this->lerData($dth);
    }
     
    public function formatarLeituraStr($str){
        return $str;
    }
     
    public function formatarLeituraBol($bol){
        if ( $bol == 1 ) {
            return true;
        } else {
            return false;
        }
    }
     
    public function formatarLeituraNum($num){
        return $num;
    }

    public function formatarLeituraDin($din){
        return InfraUtil::formatarDin($din);
    }
     
    public function formatarLeituraDbl($dbl){
        return InfraUtil::formatarDbl($dbl);
    }
     
    public function formatarLeituraBin($bin){
        return $bin;
    }

    public function abrirConexao() {
        try{

          if (InfraDebug::isBolProcessar()) {
            InfraDebug::getInstance()->gravarInfra('[InfraMySqli->abrirConexao] ' . $this->getIdBanco());
          }

            //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->abrirConexao] 10');
             
            if ( $this->conexao!=null) {
                throw new InfraException('Tentativa de abrir nova conex?o sem fechar a anterior.');
            }
             
            //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->abrirConexao] 20');
            $this->conexao = new ConectorMySqli($this);

            if (!$this->conexao){
                throw new InfraException('N?o foi poss?vel abrir conex?o com o banco de dados.');
            }
             
             
            $this->id = $this->getIdBanco();
             
            //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->abrirConexao] 30');
             
        }catch(Exception $e){
            if (strpos(strtolower($e->__toString()),'mysql_connect')!==false){
                throw new InfraException('N?o foi poss?vel abrir conex?o com a base de dados.');
            }else{
                throw $e;
            }
        }
    }
     
    public function fecharConexao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->fecharConexao] ' . $this->getIdConexao());
      }


        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->fecharConexao] 10');
         
        if ($this->conexao==null) {
            throw new InfraException('Tentativa de fechar conex?o que n?o foi aberta.');
        }
         
        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->fecharConexao] 20');
         
        $this->conexao->close();
         
        $this->conexao = null;
        $this->id = null;
         
    }

    public function abrirTransacao(){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->abrirTransacao] ' . $this->getIdConexao());
      }

         
        if ($this->conexao==null) {
            throw new InfraException('Tentando abrir transa??o em uma conex?o fechada.');
        }
         
        $this->conexao->autocommit(false);
         
        $this->transacao = true;
    }

    public function confirmarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->confirmarTransacao] ' . $this->getIdConexao());
      }


        if ($this->conexao==null) {
            throw new InfraException('Tentando confirmar transa??o em uma conex?o fechada.');
        }
         
        $this->conexao->commit();

        $this->conexao->autocommit(true);

        $this->transacao = false;
    }

    public function cancelarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->cancelarTransacao] ' . $this->getIdConexao());
      }


        if ($this->conexao==null) {
            throw new InfraException('Tentando desfazer transa??o em uma conex?o fechada.');
        }
         
        $this->conexao->rollback();

        $this->conexao->autocommit(true);

        $this->transacao = false;
    }

    public function consultarSql($sql, $arrCamposBind = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] ' . $sql);
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }


        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] 10 : '.$sql);
        if ($this->conexao==null) {
            throw new InfraException('Tentando executar uma consulta em uma conex?o fechada.');
        }

        if ($this->getIdBanco()!==$this->getIdConexao()){
            throw new InfraException('Tentando executar comando em um banco de dados diferente do utilizado pela conex?o atual.');
        }
         
        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] 20');
        $resultado = $this->conexao->query($sql);
         
        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] 30');
        if ( $resultado === FALSE ) {
            throw new InfraException($this->conexao->error,null,$sql);
        }
        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] 40');
        $vetor_resultado = array();

        $tipo_vetor = MYSQLI_BOTH;
        if ($this->isBolConsultaRetornoAssociativo()){
          $tipo_vetor = MYSQLI_ASSOC;
        }

        while ($registro = mysqli_fetch_array($resultado, $tipo_vetor)) {
          $vetor_resultado[] = $registro;
        }

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->consultarSql] ' . $numSeg . ' s');
      }

         
        return $vetor_resultado;
    }

    public function paginarSql( $sql , $ini , $qtd ){

      $posSelect = strpos($sql, 'SELECT') + 6;
      $sql = substr($sql, 0, $posSelect) . ' SQL_CALC_FOUND_ROWS' . substr($sql, $posSelect);
      $sql .= ' LIMIT ' . $ini . ',' . $qtd ;

      $sqlTotal = 'SELECT FOUND_ROWS() as total' ;

      $rs = $this ->consultarSql( $sql ) ;
      $rsTotal = $this ->consultarSql( $sqlTotal ) ;

      return array ( 'totalRegistros' => $rsTotal [ 0 ][ 'total' ] , 'registrosPagina' => $rs ) ;
    }

/*
  public function paginarSql($sql,$ini,$qtd){
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->paginarSql]');

        $arr = explode(' ',$sql);
        $select = '';

        $bolDistinct = false;
        foreach( $arr as  $pl){
            if ( strtoupper($pl) == 'DISTINCT' )  {
                $bolDistinct = true;
                break;
            }
        }
        // Se houver DISTINCT, armazena os nomes de campos para construir comando diferenciado.
        if ($bolDistinct) {
            // Pega nomes de todos os campos, desde o "SELECT DISTINCT" at? o "FROM".
            $numTamanhoComando = strlen($sql);
            $numPosInicial = strpos($sql, 'DISTINCT') + 9;
            $numExtensao = $numTamanhoComando - $numPosInicial - ($numTamanhoComando - (strpos($sql, 'FROM') - 1));
            $strAliasCampos = substr($sql, $numPosInicial, $numExtensao);
            $arrAliasCampos = explode(',',$strAliasCampos);
            $strCampos = '';

            for ($c = 0; $c < count($arrAliasCampos); $c++) {
                //nem todos os campos possuem alias (somente maiores que 30 caracteres)
                $posAlias = strpos($arrAliasCampos[$c], 'AS');
                if ($posAlias!==false) {
                    $strCampos .= 'IFNULL(' . substr($arrAliasCampos[$c], 0, (strlen($arrAliasCampos[$c]) - (strlen($arrAliasCampos[$c]) - ($posAlias - 1)))) . ', 0), ';
                }else{
                    $strCampos .= 'IFNULL(' . $arrAliasCampos[$c] . ', 0), ';
                }
            }

            $strCampos = substr($strCampos, 0, -2); // Retira v?rgula e espa?o ap?s o ?ltimo.
        }
         
        for($i=0;$i<count($arr);$i++){
            if (strtoupper($arr[$i])=='FROM'){
                break;
            }
        }

        if ($bolDistinct == true) {
            $sqlTotal = 'SELECT COUNT(DISTINCT ' . $strCampos . ') as total';
        } else {
            $sqlTotal = 'SELECT COUNT(*) as total';
        }
         
        for(;$i<count($arr);$i++){
            if (strtoupper($arr[$i])=='ORDER'){
                break;
            }
            $sqlTotal .= ' '.$arr[$i];
        }
         
        $rsTotal = $this->consultarSql($sqlTotal);

        $sql .= ' LIMIT '.$ini.','.$qtd;
         
        $rs = $this->consultarSql($sql);

        return array('totalRegistros'=>$rsTotal[0]['total'],'registrosPagina'=>$rs);
         
    }
*/
     
    public function limitarSql($sql,$qtd) {

      //if (InfraDebug::isBolProcessar()) {
      //  InfraDebug::getInstance()->gravarInfra('[InfraMySqli->limitarSql] ' . $sql);
      //}

        $sql .= ' LIMIT 0,'.$qtd;
        return $this->consultarSql($sql);
    }
     
    public function executarSql($sql, $arrCamposBind = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] ' . substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }


        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] 10 : '.$sql);
        if ($this->conexao==null) {
            throw new InfraException('Tentando executar um comando em uma conex?o fechada.');
        }

        if ($this->getIdBanco()!==$this->getIdConexao()){
            throw new InfraException('Tentando executar comando em um banco de dados diferente do utilizado pela conex?o atual.');
        }

        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] 20');
        $resultado = $this->conexao->query($sql);
        //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] 30');
         
        if ( $resultado === FALSE ) {
            //InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] 35');
            throw new InfraException($this->conexao->error,null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
        }
         
        $affectedRows = $this->conexao->affected_rows;
        if($affectedRows == 0) {
            $arrInfo = $this->getMysqlInfo($this->conexao);
            $affectedRows = $arrInfo[self::$MYSQL_INFO_ROWS_MATCHED];
        }

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] ' . $affectedRows . ' registro(s) afetado(s)');
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->executar] ' . $numSeg . ' s');
      }

         
        //return $arrInfo[self::$MYSQL_INFO_ROWS_MATCHED];
         
        return $affectedRows;
    }
     
    public function lerData($mySqlDate)
    {
        if ($mySqlDate===null){
            return null;
        }
         
        //2007-01-01 12:12:12
        //2015-09-22 08:50:00.000000
        $tam = strlen($mySqlDate);
         
        if ($tam!=10 && $tam!=19 && $tam!=26){
            throw new InfraException('Tamanho de data inv?lido.',null,$mySqlDate);
        }
         
        $ret = substr($mySqlDate,8,2).'/'.substr($mySqlDate,5,2).'/'.substr($mySqlDate,0,4);
         
        if ($tam==19){
          $ret .= substr($mySqlDate,10);
        }else if ($tam==26){
          $ret .= substr($mySqlDate,10,9);
        }
         
        return $ret;
    }
     
    public function gravarData($brasilDate)
    {

        if(trim($brasilDate)===''){
            return 'NULL';
        }

        $ret = '\''.substr($brasilDate,6,4).'-'.substr($brasilDate,3,2).'-'.substr($brasilDate,0,2);
         
        if (strlen($brasilDate)==19){
            $ret .= substr($brasilDate,10);
        }

        $ret .= '\'';
         
         
        return $ret;
    }

    function getMysqlInfo($linkid = null){
         
        $linkid? $strInfo = $linkid->info : $strInfo = $this->conexao->info;
         
        //InfraDebug::getInstance()->gravar($strInfo);
         
        $return = array();
        preg_match("/Records: ([0-9]*)/", $strInfo, $records);
        preg_match("/Duplicates: ([0-9]*)/", $strInfo, $dupes);
        preg_match("/Warnings: ([0-9]*)/", $strInfo, $warnings);
        preg_match("/Deleted: ([0-9]*)/", $strInfo, $deleted);
        preg_match("/Skipped: ([0-9]*)/", $strInfo, $skipped);
        preg_match("/Rows matched: ([0-9]*)/", $strInfo, $rows_matched);
        preg_match("/Changed: ([0-9]*)/", $strInfo, $changed);

        if (isset($records[1])) {
          $return[self::$MYSQL_INFO_RECORDS] = $records[1];
        }else{
          $return[self::$MYSQL_INFO_RECORDS] = 0;
        }

        if (isset($dupes[1])) {
          $return[self::$MYSQL_INFO_DUPLICATES] = $dupes[1];
        }else{
          $return[self::$MYSQL_INFO_DUPLICATES] = 0;
        }

        if (isset($warnings[1])){
          $return[self::$MYSQL_INFO_WARNINGS] = $warnings[1];
        }else{
          $return[self::$MYSQL_INFO_WARNINGS] = 0;
        }

        if (isset($deleted[1])) {
          $return[self::$MYSQL_INFO_DELETED] = $deleted[1];
        }else{
          $return[self::$MYSQL_INFO_DELETED] = 0;
        }

        if (isset($skipped[1])) {
          $return[self::$MYSQL_INFO_SKIPPED] = $skipped[1];
        }else{
          $return[self::$MYSQL_INFO_SKIPPED] = 0;
        }

        if (isset($rows_matched[1])) {
          $return[self::$MYSQL_INFO_ROWS_MATCHED] = $rows_matched[1];
        }else{
          $return[self::$MYSQL_INFO_ROWS_MATCHED] = 0;
        }

        if (isset($changed[1])) {
          $return[self::$MYSQL_INFO_CHANGED] = $changed[1];
        }else{
          $return[self::$MYSQL_INFO_CHANGED] = 0;
        }
         
        return $return;
    }

    public function criarSequencialNativa($strSequencia, $numInicial){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->criarSequencialNativa]');
      }

      $this->executarSql('create table '.$strSequencia.' (id int not null primary key AUTO_INCREMENT, campo char(1) null)');
      $this->executarSql('alter table '.$strSequencia.' AUTO_INCREMENT = '.$numInicial);
    }

    public function ping() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraMySqli->ping] ' . $this->getIdBanco());
      }

      if ($this->conexao == null) {
        throw new InfraException('Tentativa de ping em uma conex?o fechada.');
      }
      return $this->conexao->ping();
    }


    public function realEscapeString($str){
      return $this->conexao->real_escape_string($str);
    }

}

class ConectorMySqli extends mysqli {

    public function __construct(InfraMySqli $objInfraMySqli) {
        parent::init();

        /*
		Exemplos de passagem de par?metros de conex?o
		if (!parent::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
            die('Setting MYSQLI_INIT_COMMAND failed');
        }

        if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }*/

        foreach($objInfraMySqli->getArrOpcoes() as $varOpcao => $varValor){
          if (!parent::options($varOpcao, $varValor)) {
            throw new InfraException('Erro configurando op??o do banco de dados ['.$varOpcao.'].');
          }
        }

        try{
          if (!parent::real_connect($objInfraMySqli->getServidor(),  $objInfraMySqli->getUsuario(),  $objInfraMySqli->getSenha(), $objInfraMySqli->getBanco(), $objInfraMySqli->getPorta())) {
              throw new InfraException('N?o foi poss?vel abrir conex?o com o banco de dados.');
          }
        }catch(Exception $e){
          throw new InfraException('Falha ao abrir conex?o com o banco de dados.');
        }
    }
}
?>