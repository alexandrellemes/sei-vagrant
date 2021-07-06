<?
/**
 * @package infra_php
 *
 */
abstract class InfraOracle implements InfraIBanco {
    private $conexao;
    private $id;
    private $transacao;
    
		public abstract function getServidor();
		public abstract function getPorta();
		public abstract function getBanco();
		public abstract function getUsuario();
		public abstract function getSenha();
		
		public function getUsuarioOwner(){
		    return $this->getUsuario();
		}

		public function getCharset(){
		    return 'WE8ISO8859P1';
		}

		public function __construct(){
			$this->conexao = null;
			$this->id = null;
			$this->transacao = false;
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
		
		public function getValorSequencia($sequencia){
		  $rs = $this->consultarSql('SELECT '.$sequencia.'.NEXTVAL FROM DUAL');
		  return $rs[0]['nextval'];
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
    
    private function formatarSelecaoAsVarchar($tabela,$campo,$alias){
      $ret="TO_CHAR($campo) as";
      $ret = 'TO_CHAR(';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' ) AS ';
      
		  if ($alias!==null){
		    $ret .= $alias;
		  }else{
		    $ret .= $campo;
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
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoDbl($tabela,$campo,$alias){
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
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
		    throw new InfraException('Detectado caracter inv�lido.');
		  }

      $str = str_replace("'", '\'\'', $str);

			return '\''.$str.'\'';
		}
		
		public function formatarGravacaoBol($bol){
			if ( $bol===true ) {
				return 'true';
			} 
			return 'false';
		}

    public function formatarGravacaoNum($num){
      $num = trim($num);

      if ($num===''){
        return 'NULL';
      }

      if (!is_numeric($num)){
        throw new InfraException('Valor num�rico inv�lido ['.$num.'].');
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
        throw new InfraException('Valor num�rico inv�lido ['.$din.'].');
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
        throw new InfraException('Valor num�rico inv�lido ['.$dbl.'].');
      }

      return $dbl;
    }
		
		public function formatarGravacaoBin($bin){
		  if ($bin===null || $bin===''){
		    return 'NULL';
		  }
		  return '\''.($bin).'\'::bytea';
		}
		
//LEITURA		

    public function converterStr($tabela,$campo){
      $ret = 'CAST(';
      if ($tabela!==null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' as varchar)';
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
			$ret = $this->lerData($dta);
			if ($ret != null){
				return substr($ret,0,10); 
			}
			return null;
		}
		
		public function formatarLeituraDth($dth){
			return $this->lerData($dth);
		}
		
		public function formatarLeituraStr($str){
			return $str;
		}
		
		public function formatarLeituraBol($bol){
			if ( $bol === 't' ) {
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
  	  		return ($bin);
		}
		

	  //ABRE A CONEXÃO
	  public function abrirConexao() {

			try {
        if (InfraDebug::isBolProcessar()) {
          InfraDebug::getInstance()->gravarInfra('[InfraOracle->abrirConexao] '.$this->getIdBanco());
        }

        if ($this->conexao != null) {
          throw new InfraException('Tentativa de abrir nova conex�o sem fechar a anterior.');
        }

        $this->conexao = oci_connect($this->getUsuario(), $this->getSenha(), $this->getServidor(), $this->getCharset());
        $this->executarSql('ALTER SESSION SET CURRENT_SCHEMA='.$this->getUsuarioOwner());

        $this->executarSql("ALTER SESSION SET NLS_DATE_FORMAT='DD/MM/YYYY hh24:mi:ss'");
        $this->id = $this->getIdBanco();

        if ($this->conexao === FALSE) {
          throw new InfraException(oci_error($this->conexao));
        }
      }catch(Exception $e){
        if (strpos(strtolower($e->__toString()),'oci_connect')!==false){
          preg_match('/^oci_connect\(\):\s(.*)/m', $e->__toString(), $matches);
          throw new InfraException('N�o foi poss�vel abrir conex�o com a base de dados.', null, $matches[1]);
        }else{
          throw $e;
        }
      }
	  }
	
	  //FECHA A CONEXÃO
	  public function fecharConexao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->fecharConexao] ' . $this->getIdConexao());
      }


	  	//InfraDebug::getInstance()->gravarInfra('[InfraOracle->fecharConexao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentativa de fechar conex�o que n�o foi aberta.');
	  	}
	  	//InfraDebug::getInstance()->gravarInfra('[InfraOracle->fecharConexao] 20');
	  	
	    oci_close($this->conexao);

	    $this->conexao = null;
	    $this->id = null;
	  }

    public function abrirTransacao(){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->abrirTransacao] ' . $this->getIdConexao());
      }


	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando abrir transa��o em uma conex�o fechada.');
	  	}

    	$this->transacao = true;
    }
	    
	  //CONFIRMA A TRANSAÇÃO
	  public function confirmarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->confirmarTransacao] ' . $this->getIdConexao());
      }


	  	//InfraDebug::getInstance()->gravarInfra('[InfraOracle->confirmarTransacao] 10');
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando confirmar transa��o em uma conex�o fechada.');
	  	}
	    oci_commit($this->conexao);
	    $this->transacao = false;
	  }
	    
	  //CANCELA A TRANSAÇÃO
	  public function cancelarTransacao() {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->cancelarTransacao] ' . $this->getIdConexao());
      }


	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando desfazer transa��o em uma conex�o fechada.');
	  	}
	    oci_rollback($this->conexao);
	    $this->transacao = false;
	  }
    
	  //EXECUTA UMA CLA�SULA SQL
	  public function consultarSql($sql, $arrCamposBind = null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->consultarSql] ' . $sql);
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }

			
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando executar uma consulta em uma conex�o fechada.');
	  	}

	    $resultado = oci_parse($this->conexao, $sql);

      if($arrCamposBind!=null && count($arrCamposBind)>0){
        $chaves=array_keys($arrCamposBind);
        for($i=0;$i<count($chaves);$i++){
          oci_bind_by_name($resultado, $chaves[$i], $arrCamposBind[$chaves[$i]]);
        }
      }

	    oci_execute($resultado, OCI_NO_AUTO_COMMIT);

	    if ( $resultado === FALSE ) {
	    	throw new InfraException(oci_error($this->conexao),null,$sql);
	    }

	    $vetor_resultado = array();
	    $cont=0;

	    $clobs=array();

	    
		  $ncols = oci_num_fields($resultado);

		  for ($i = 1; $i <= $ncols; $i++) {
			  
		    $column_name  = oci_field_name($resultado, $i);
			  $column_type  = oci_field_type($resultado, $i);
			  
			  if($column_type=="CLOB")
				  $clobs[$column_name]=$column_type;
		  }

      while ($registro = oci_fetch_assoc($resultado)) {
      	
        $chaves = array_keys($registro);
        
        $nChaves = count($chaves);
        
        for($i=0;$i<$nChaves;$i++){

          $strChave = $chaves[$i]; 
          
          if($registro[$strChave]!=null){
            if(isset($clobs[$strChave]) && $clobs[$strChave]!=null){
              if($registro[$strChave]->size()>0){
                $registro[$strChave]=$registro[$strChave]->read($registro[$strChave]->size());
      			  }else{
                $registro[$strChave]="";
      			  }
            }
          }
      	}
        $vetor_resultado[] = array_change_key_case($registro, CASE_LOWER);
      }

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->consultarSql] ' . $numSeg . ' s');
      }


	    return $vetor_resultado;
	  }
	  
	  public function paginarSql($sql,$ini,$qtd){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->paginarSql]');
      }


      $arr = explode(' ',$sql);
      $select = '';
      for($i=0;$i<count($arr);$i++){
        if (strtoupper($arr[$i])=='FROM'){
          break;
        }
      }

      $sqlTotal = 'SELECT COUNT(*) as total';
      for(;$i<count($arr);$i++){
        if (strtoupper($arr[$i])=='ORDER'){
          break;
        }
        $sqlTotal .= ' '.$arr[$i];
      }
      $rsTotal = $this->consultarSql($sqlTotal);

      $qtd=$qtd+$ini;
      $qtdOtimizacao=$qtd+10;
      $sql="SELECT a.* FROM ( SELECT /*+ FIRST_ROWS($qtdOtimizacao)*/ b.*,rownum b_rownum FROM ( $sql ) b WHERE rownum <= $qtd) a WHERE b_rownum >= $ini";

	    $rs = $this->consultarSql($sql);
	    
	    return array('totalRegistros'=>$rsTotal[0]['total'],'registrosPagina'=>$rs);
	  }
	    
	  public function limitarSql($sql,$qtd) {

      //if (InfraDebug::isBolProcessar()) {
      //  InfraDebug::getInstance()->gravarInfra('[InfraOracle->limitarSql] ' . $sql);
      //}

	    $sql = 'SELECT * FROM ('.$sql.') WHERE rownum <= '.$qtd;
	    return $this->consultarSql($sql);
	  }

	  /**
	   * Permite a utiliza��o de parametros IN na execu��o de scripts PL/SQL
	   * @param string $sql query que ser� executada.
	   * @param string $arrCamposBind Lista de par�metros (chave => valor) de entrada que ser�o passados por bind.
	   * @return integer $numReg quantidade de registros afetados. Ap�s a execu��o do m�todo o valor de retorno pode
	   * ser acessado atrav�s da primeira posi��o do respectivo array. Caso o aparametro seja um bin�rio a chave
	   * correspondente deve obrigatoriamente ser iniciada por 'bin'.
	   */
	  public function executarSql($sql,$arrCamposBind=null) {

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSql] ' . substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }

	  	
	  	if ($this->conexao==null) {
	  		throw new InfraException('Tentando executar um comando em uma conex�o fechada.');
	  	}
	
      $resultado = oci_parse($this->conexao,$sql);

        $arrBlob = array(); // Utilizando array pra n�o perder os endere�os de mem�ria
	  	if($arrCamposBind!=null && count($arrCamposBind)>0){
	  		$chaves=array_keys($arrCamposBind);
		    for($i=0;$i<count($chaves);$i++){
		        if (substr($chaves[$i], 1, 3) == 'bin') {
		            $arrBlob[$i] = oci_new_descriptor($this->conexao, OCI_D_LOB);
		            oci_bind_by_name($resultado, $chaves[$i], $arrBlob[$i], - 1, OCI_B_BLOB);
		            $arrBlob[$i]->writeTemporary($arrCamposBind[$chaves[$i]], OCI_TEMP_BLOB);
		        } else {
		            oci_bind_by_name($resultado, $chaves[$i], $arrCamposBind[$chaves[$i]]);
		        }
		    }
	  	}

			if (!$this->transacao) {
				oci_execute($resultado, OCI_COMMIT_ON_SUCCESS);
			}else{
				oci_execute($resultado, OCI_NO_AUTO_COMMIT);
			}

	    if ( $resultado === FALSE ) {
	    	
	    	throw new InfraException(oci_error($this->conexao),null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
	    }
	    $numReg = oci_num_rows($resultado);

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSql] ' . $numReg . ' registro(s) afetado(s)');
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSql] ' . $numSeg . ' s');
      }


	    return $numReg;
	  }

  /**
   * Permite a utiliza��o de parametros IN/OUT na execu��o de scripts PL/SQL
   * @param string $sql query que ser� executada.
   * @param string $camposEntrada Lista de par�metros (chave => valor) de entrada que ser�o passados por bind.
   * @param string &$camposRetorno Lista de par�metros (chave => valor) de entrada/sa�da que podem ser passados por bind para uma procedure.
   * <pre>
   * O valor de cada item da lista de campos de retorno deve,
   * obrigatoriamente, ser um array com 3 valores.
   *   - valor do campo.
   *   - tamanho do campo
   *   - tipo do campo. Verificar os tipos permitidos em:
   *   http://php.net/manual/en/function.oci-bind-by-name.php
   * </pre>
   * @return integer $numReg quantidade de registros afetados. Ap�s a execu��o do m�todo o valor de retorno pode ser acessados atrav�s da primeira posi��o do respectivo array.
   * @example
   * <pre>
   *   $arrParametrosOUT['chave'] = [null, 20, SQLT_INT];
   *   executarSqlComRetorno($query, $arrParametrosIN, $arrParametrosOUT);
   *   $arrParametrosOUT['chave'][0];
   * </pre>
   */
  public function executarSqlComRetorno($sql, $camposEntrada = null, &$camposRetorno = null)
  {
    if (InfraDebug::isBolProcessar()) {
      InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSqlComRetorno] ' . substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
      $numSeg = InfraUtil::verificarTempoProcessamento();
    }
    if ($this->conexao == null) {
      throw new InfraException('Tentando executar um comando em uma conex�o fechada.');
    }
    $resultado = oci_parse($this->conexao, $sql);
    if ($camposEntrada != null && count($camposEntrada) > 0) {
      $chaves = array_keys($camposEntrada);
      for ($i = 0; $i < count($chaves); $i ++) {
        oci_bind_by_name($resultado, $chaves[$i], $camposEntrada[$chaves[$i]]);
      }
    }
    if ($camposRetorno != null && count($camposRetorno) > 0) {
      $chaves = array_keys($camposRetorno);
      for ($i = 0; $i < count($chaves); $i ++) {
        $tamanho = $camposRetorno[$chaves[$i]][1]; // o valor da chave indica o tamanho
        $tipo = $camposRetorno[$chaves[$i]][2]; // a chave do retorno indica o tipo
        oci_bind_by_name($resultado, $chaves[$i], $camposRetorno[$chaves[$i]][0], $tamanho, $tipo[0]);
      }
    }
    if (! $this->transacao) {
      oci_execute($resultado, OCI_COMMIT_ON_SUCCESS);
    } else {
      oci_execute($resultado, OCI_NO_AUTO_COMMIT);
    }
    if ($resultado === FALSE) {

      throw new InfraException(oci_error($this->conexao), null, substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
    }
    $numReg = oci_num_rows($resultado);
    if (InfraDebug::isBolProcessar()) {
      $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
      InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSql] ' . $numReg . ' registro(s) afetado(s)');
      InfraDebug::getInstance()->gravarInfra('[InfraOracle->executarSql] ' . $numSeg . ' s');
    }
    return $numReg;
  }

	function lerData($Oracle_date){
			return $Oracle_date;
		}

		public function gravarData($brasil_date){
			return 'TO_DATE(\''.$brasil_date.'\',\'dd/mm/yyyy hh24:mi:ss\')';
		
		}
		
		public function criarSequencialNativa($strSequencia, $numInicial){

      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraOracle->criarSequencialNativa]');
      }

      $this->executarSql('CREATE SEQUENCE '.$strSequencia.' START WITH '.$numInicial.' INCREMENT BY 1 NOCACHE NOCYCLE');
		}
}

?>
