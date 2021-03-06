<?
  /**
  * @package infra_php
  * ADICIONADO PDO EM 24/05/2018 - POR cle@trf4.jus.br
  */
  
  abstract class InfraSqlServer implements InfraIBanco {
    private $conexao;
    private $id;
    private $transacao;
    private $numTipoInstalacao = null; //1 - FreeTDS, 2 - Microsoft SqlSrv, 3 - PDO
    
		public abstract function getServidor();
		public abstract function getPorta();
		public abstract function getBanco();
		public abstract function getUsuario();
		public abstract function getSenha();
		
		public function __construct() {
			$this->conexao = null;
			$this->id = null;
			$this->transacao = false;
			
			if (function_exists('mssql_connect')) {
			  $this->numTipoInstalacao = 1;
			} elseif (function_exists('sqlsrv_connect')) {
			  $this->numTipoInstalacao = 2;
			} elseif (class_exists('PDO')) {
			  $this->numTipoInstalacao = 3;
			} else{
			  throw new InfraException('Nenhuma extens?o detectada para acesso ao SQL Server.');
			}
		}
		public function __destruct() {
		  if ($this->getIdConexao() != null) {
		    try {
		      $this->fecharConexao(); 
		    } catch(Exception $e){}
		  }
		}
				
		public function getIdBanco() {
			return __CLASS__.'-'.$this->getServidor().'-'.$this->getPorta().'-'.$this->getBanco().'-'.$this->getUsuario();
		}

		public function getIdConexao() {
		  return $this->id;
		}
		
		public function isBolProcessandoTransacao() {
		  return $this->transacao;
		}
		
		public function isBolValidarISO88591() {
		  return false;
		}
				
		public function getValorSequencia($sequencia) {
		  $rs = $this->consultarSql('INSERT INTO '.$sequencia.' OUTPUT CAST(INSERTED.id as VARCHAR) as \'id\' VALUES (null);');
		  return $rs[0]['id'];
		}
		
		public function isBolForcarPesquisaCaseInsensitive() {
			return true;
		}

		public function isBolManterConexaoAberta() {
			return false;
		}

		public function isBolConsultaRetornoAssociativo() {
			return false;
		}

  	//SELE??O
    private function formatarSelecaoGenerico($tabela,$campo,$alias) {
      $ret = '';
      if ($tabela !== null) {
        $ret .= $tabela.'.';
      }
      
      $ret .= $campo;
      
      if ($alias != null) {
        $ret .= ' AS '.$alias;
      }
      return $ret;
    }
    
    private function formatarSelecaoAsVarchar($tabela,$campo,$alias) {
      $ret = 'CAST(';
      if ($tabela !== null) {
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' as varchar)';
      
		  if ($alias !== null) {
		    $ret .= ' AS '.$alias;
		  } else {
		    $ret .= ' AS '.$campo;
		  }
		  
      return $ret;
    }

		public function formatarSelecaoDta($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoDth($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoStr($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoBol($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoNum($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}

		public function formatarSelecaoDin($tabela,$campo,$alias) {
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
		}
		
		public function formatarSelecaoDbl($tabela,$campo,$alias) {
		  return $this->formatarSelecaoAsVarchar($tabela,$campo,$alias);
		}
		
    public function formatarSelecaoBin($tabela,$campo,$alias) {
		  return $this->formatarSelecaoGenerico($tabela,$campo,$alias);
		}
		
    //GRAVA??O
		public function formatarGravacaoDta($dta) {
			return $this->gravarData(substr($dta,0,10));
		}
		
		public function formatarGravacaoDth($dth) {
      return $this->gravarData($dth);
    }
		
		public function formatarGravacaoStr($str) {
		  if ($str === null || $str === ''){
		    return 'NULL';
			}

			if ($this->isBolValidarISO88591() && InfraUtil::filtrarISO88591($str) != $str) {
			  throw new InfraException('Detectado caracter inv?lido.');
			}
				
			$str = str_replace("\'",'\'',$str);
			$str = str_replace("'",'\'\'',$str);
				
			return '\''.$str.'\'';
		}
		
		public function formatarGravacaoBol($bol) {
			if ($bol === true) {
				return 1;
			} 
			return 0;
		}

		public function formatarGravacaoNum($num) {
			$num = trim($num);

			if ($num === '') {
				return 'NULL';
			}

			if (!is_numeric($num)) {
				throw new InfraException('Valor num?rico inv?lido ['.$num.'].');
			}

			return $num;
		}

		public function formatarGravacaoDin($din) {
			$din = trim($din);

			if ($din === '') {
				return 'NULL';
			}

			$din = InfraUtil::prepararDin($din);

			if (!is_numeric($din)) {
				throw new InfraException('Valor num?rico inv?lido ['.$din.'].');
			}

			return $din;
		}

		public function formatarGravacaoDbl($dbl) {
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

		public function formatarGravacaoBin($bin) {
		  if ($bin === null || $bin === '') {
		    return 'NULL';
		  }
		  return '0x'.bin2hex($bin);
		}
		
    //LEITURA		
    public function converterStr($tabela,$campo) {
      $ret = 'CAST(';
      if ($tabela !== null){
        $ret .= $tabela.'.';
      }
      $ret .= $campo.' as varchar)';
		  
      return $ret;
    }

    public function formatarPesquisaStr($strTabela,$strCampo,$strValor,$strOperador,$bolCaseInsensitive){

      if ($bolCaseInsensitive){
        return 'upper('.$strCampo.') '.$strOperador.' \''.str_replace("'",'\'\'',str_replace("\'",'\'',InfraString::transformarCaixaAlta($strValor))).'\' ';
      }else{
        return $strCampo.' '.$strOperador.' \''.str_replace("'",'\'\'',str_replace("\'",'\'',$strValor)).'\' ';
      }
    }
		
		public function formatarLeituraDta($dta) {
 			$dta = $this->lerData($dta);
 			if ($dta !== null) {
 			  $dta = substr($dta,0,10);
 			}
			return $dta;
		}
		
		public function formatarLeituraDth($dth) {
			 return $this->lerData($dth);
		}
		
		public function formatarLeituraStr($str) {
			return $str;
		}
		
		public function formatarLeituraBol($bol) {
			if ($bol == 1) {
				return true;
			} else {
			  return false;
			}	
		}
		
		public function formatarLeituraNum($num) {
			return $num;
		}

		public function formatarLeituraDin($din) {
		  return InfraUtil::formatarDin($din);
		}
		
		public function formatarLeituraDbl($dbl) {
 		  return InfraUtil::formatarDbl($dbl);
		}

  	public function formatarLeituraBin($bin) {
  	  return $bin;
		}
		
	  public function abrirConexao() {
	    try {
        if (InfraDebug::isBolProcessar()) {
          InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->abrirConexao] ' . $this->getIdBanco());
        }
  
  		  if ($this->conexao != null) {
  		  	throw new InfraException('Tentativa de abrir nova conex?o sem fechar a anterior.');
  		  }
  
  		  if ($this->numTipoInstalacao == 1) {
    			$this->conexao = mssql_connect($this->getServidor().':'.$this->getPorta(), $this->getUsuario(), $this->getSenha());
    			$this->id = $this->getIdBanco();
    			mssql_select_db($this->getBanco(), $this->conexao);
  		  } elseif ($this->numTipoInstalacao == 2) {
  		    $connectionInfo = array("Database"=>$this->getBanco(), "UID"=>$this->getUsuario(), "PWD"=>$this->getSenha(), 'MultipleActiveResultSets' => false);
  		    $this->conexao = sqlsrv_connect($this->getServidor().','.$this->getPorta(), $connectionInfo);
  		    $this->id = $this->getIdBanco();
  		    sqlsrv_query( $this->conexao, "USE {$this->getBanco()};", array());  		    
  		  } elseif ($this->numTipoInstalacao == 3) {
  		    $this->conexao = new PDO('dblib:host='.$this->getServidor().':'.$this->getPorta().';dbname='.$this->getBanco(), $this->getUsuario(), $this->getSenha());
  		    $this->id = $this->getIdBanco();
  		  }
	    } catch(Exception $e) {
	      if (strpos(strtolower($e->__toString()),'unable to connect to server') !== false) {
	        throw new InfraException('N?o foi poss?vel abrir conex?o com a base de dados.');
	      } elseif (strpos(strtolower($e->__toString()),'not locate entry in sysdatabases for database') !== false) {
	        throw new InfraException('Base de dados n?o encontrada no servidor.');
	      } else {
	        throw $e;
	      }
	    }
    }
		
	  public function fecharConexao() {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->fecharConexao] ' . $this->getIdConexao());
      }
	  	
	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentativa de fechar conex?o que n?o foi aberta.');
	  	}

	  	if ($this->numTipoInstalacao == 1) {
        mssql_close($this->conexao);
	  	} elseif ($this->numTipoInstalacao == 2) {
        sqlsrv_close($this->conexao);
	  	}
	    
			$this->conexao = null;
			$this->id = null;
	  }

    public function abrirTransacao() {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->abrirTransacao] ' . $this->getIdConexao());
      }

	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentando abrir transa??o em uma conex?o fechada.');
	  	}

	  	if ($this->numTipoInstalacao == 3) {
	  	  $this->conexao->beginTransaction();
	  	} else {
      	$this->executarSql('BEGIN TRANSACTION');
	  	}
    	
    	$this->transacao = true;
    }

	  public function confirmarTransacao() {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->confirmarTransacao] ' . $this->getIdConexao());
      }

	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentando confirmar transa??o em uma conex?o fechada.');
	  	}
			
	  	if ($this->numTipoInstalacao == 3) {
	  	  $this->conexao->commit();
	  	} else {
    	  $this->executarSql('COMMIT TRANSACTION');
	  	}
    	
    	$this->transacao = false;
	  }

	  public function cancelarTransacao() {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->cancelarTransacao] ' . $this->getIdConexao());
      }

	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentando desfazer transa??o em uma conex?o fechada.');
	  	}
			
	  	if ($this->numTipoInstalacao == 3) {
	  	  $this->conexao->rollBack();
	  	} else {
      	$this->executarSql('ROLLBACK TRANSACTION');
	  	}
    	
    	$this->transacao = false;
	  }

	  public function consultarSql($sql, $arrCamposBind = null) {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->consultarSql] ' . $sql);
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }
			
	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentando executar uma consulta em uma conex?o fechada.');
	  	}

	  	if ($this->getIdBanco() !== $this->getIdConexao()){
	  	  throw new InfraException('Tentando executar comando em um banco de dados diferente do utilizado pela conex?o atual.');
	  	}
	  	
	  	$vetor_resultado = array();
	  	
	  	if ($this->numTipoInstalacao == 1) {
	  	  mssql_select_db($this->getBanco(), $this->conexao);
	  	  $resultado = mssql_query('SET TEXTSIZE 2147483647;'.$sql, $this->conexao);
	  	  
	  	  if ($resultado === false) {
	  	    throw new InfraException(mssql_get_last_message(),null,$sql);
	  	  }	  	  

				$tipo_vetor = MSSQL_BOTH;
				if ($this->isBolConsultaRetornoAssociativo()) {
					$tipo_vetor = MSSQL_ASSOC;
				}

				while ($registro = mssql_fetch_array($resultado, $tipo_vetor)) {
					$vetor_resultado[] = $registro;
				}
	  	} elseif ($this->numTipoInstalacao == 2) {
	  	  sqlsrv_query( $this->conexao, "USE {$this->getBanco()};", array());
	  	  $resultado = sqlsrv_query($this->conexao, 'SET TEXTSIZE 2147483647;'.$sql);
	  	  
	  	  if ($resultado === FALSE) {
	  	    throw new InfraException(implode(sqlsrv_errors()),null,$sql);
	  	  }

				$tipo_vetor = SQLSRV_FETCH_BOTH;
				if ($this->isBolConsultaRetornoAssociativo()){
					$tipo_vetor = SQLSRV_FETCH_ASSOC;
				}

				while ($registro = sqlsrv_fetch_array($resultado, $tipo_vetor)) {
					$vetor_resultado[] = $registro;
				}
      } elseif ($this->numTipoInstalacao == 3) {
        $hndQuery = $this->conexao->prepare($sql);
        $hndQuery->execute();
        while ($registro = $hndQuery->fetch()) {
          $vetor_resultado[] = $registro;
        }
	  	}

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->consultarSql] ' . $numSeg . ' s');
      }

      return $vetor_resultado;      
	  }

 	  public function paginarSql($sql,$ini,$qtd) {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->paginarSql]');
      }
	    
      $arr = explode(' ',$sql);
      $select = '';
      
      for ($i=0; $i<count($arr); $i++) {
        if (strtoupper($arr[$i]) == 'FROM'){
          break;
        }
        
        $select .= ' '.$arr[$i];
      }

      $from = '';
      for(; $i<count($arr); $i++) {
        if (strtoupper($arr[$i]) == 'ORDER') {
          break;
        }
        $from .= ' '.$arr[$i];
      }

      if (trim($from) == '') {
        throw new InfraException('Cl?usula FROM n?o encontrada.');        
      }
      
      $order = '';
      for(; $i<count($arr); $i++) {
        $order .= ' '.$arr[$i];
      }

      if (trim($order) == '') {
        throw new InfraException('Para utilizar a pagina??o com este banco de dados ? necess?rio que a consulta utilize pelo menos um campo para ordena??o.');
      }
      
      $sql = '';
      $sql .= ' SELECT * FROM (';
      
      if (strpos(strtoupper($select),'DISTINCT') === false) {
        $sql .= $select;
        $sql .= ',InfraRowCount = COUNT(*) OVER(),ROW_NUMBER() OVER ('.$order.') as InfraRowNumber ';
        $sql .= $from;
      } else {
        /*SE TIVER DISTINCT TEM QUE MONTAR DE OUTRA MANEIRA, ADICIONANDO OUTRO N?VEL DE CONSULTA:
        SELECT TOP 100 * FROM (
        SELECT *
        ,ROW_NUMBER() OVER (order by id_pessoa) as InfraRowNumber [order by sem o nome da tabela nos campos]
        ,InfraRowCount = COUNT(*) OVER()
        FROM  (
        [sql original sem o order by]
        ) as InfraTabelaDistinct
        ) AS InfraTabela 
        WHERE InfraRowNumber > 10*/

        $arrSelect = explode(' ',str_replace(',',' ',str_replace('CAST(','',str_replace(' as varchar)','',$select))));
      	$arrOrder = explode(' ',$order);
      	$order = '';
      	
      	for ($i=0; $i<count($arrOrder); $i++) {
      		$order .= ' ';
      		for ($j=0; $j<count($arrSelect); $j++) {
      			if ($arrSelect[$j]==$arrOrder[$i] && isset($arrSelect[$j+1]) && strtoupper($arrSelect[$j+1])=='AS' && isset($arrSelect[$j+2])) {
      				$order .= $arrSelect[$j+2];
      				break;
      			}
      		}
      		 
      		if ($j == count($arrSelect) && strpos($arrOrder[$i],'.') !== false) {
      			//SE O CAMPO NAO TINHA ALIAS E POSSUI ".", DEVE RETIRAR O NOME DA TABELA PRINCIPAL
      			$order .= substr($arrOrder[$i],strpos($arrOrder[$i],'.')+1);
      		} elseif ($j == count($arrSelect)) {
      			//SE O CAMPO NAO TINHA ALIAS E NAO POSSUI ".", APENAS COPIA ORDER
      			$order .= $arrOrder[$i];
      		}
      	}
        
        $sql .= ' SELECT *';
        $sql .= ',InfraRowCount = COUNT(*) OVER(),ROW_NUMBER() OVER ('.$order.') as InfraRowNumber ';
        $sql .= ' FROM (';
        $sql .= $select;
        $sql .= $from;
        $sql .= ') AS InfraTabelaDistinct';
      }
      $sql .= ') AS InfraTabela WHERE InfraRowNumber BETWEEN '.($ini+1) .' AND ' .($ini+$qtd).' ORDER BY InfraRowNumber';

      $rs = $this->consultarSql($sql);

      return array('totalRegistros'=>$rs[0]['InfraRowCount'],'registrosPagina'=>$rs);
	  }
	  	  	  	  
	  public function limitarSql($sql,$qtd) {
      /*if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->limitarSql]');
      }*/
	   
	    $sqlUpper = strtoupper(trim($sql));
	     
	    if (substr($sqlUpper,0,7) != 'SELECT ') {
	      throw new InfraException('In?cio da consulta n?o localizado.');
	    }

	    if (($pos=strpos($sqlUpper,' DISTINCT ')) !== false) {
	    	$sql = substr($sql,0,$pos+10).'TOP '.$qtd.' '.substr($sql,$pos+10);
	    } else {
	      $sql = substr($sql,0,7).'TOP '.$qtd.' '.substr($sql,7);	
	    }
	    
	    return $this->consultarSql($sql);
	  }
	  	  
	  public function executarSql($sql, $arrCamposBind=null) {
      if (InfraDebug::isBolProcessar()) {
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->executar] ' . substr($sql, 0, INFRA_TAM_MAX_LOG_SQL));
        $numSeg = InfraUtil::verificarTempoProcessamento();
      }

	  	if ($this->conexao == null) {
	  		throw new InfraException('Tentando executar um comando em uma conex?o fechada.');
	  	}

	  	if ($this->getIdBanco()!==$this->getIdConexao()) {
	  	  throw new InfraException('Tentando executar comando em um banco de dados diferente do utilizado pela conex?o atual.');
	  	}

	  	if ($this->numTipoInstalacao == 1) {
  	  	mssql_select_db($this->getBanco(), $this->conexao);
  	    $resultado = mssql_query($sql, $this->conexao);
  	    
  	    if ($resultado === false) {
  	    	throw new InfraException(mssql_get_last_message(),null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
  	    }
  	    
  	    $numReg = mssql_rows_affected($this->conexao);
	  	} elseif ($this->numTipoInstalacao == 2) {
	  	  sqlsrv_query( $this->conexao, "USE {$this->getBanco()};", array());
	  	  $resultado = sqlsrv_query($this->conexao, $sql);
	  	   
	  	  if ($resultado === false) {
	  	    $errors = sqlsrv_errors();
	  	    $message = (isset($errors[0]['message'])?$errors[0]['message']:'sqlsrv_errors');
	  	    throw new InfraException($message,null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
	  	  }
	  	  
	  	  $numReg = sqlsrv_rows_affected($resultado);	  	  
	  	} elseif ($this->numTipoInstalacao == 3) {
	  	  $numReg = $this->conexao->exec($sql);
	  	  
	  	  if ($numReg === false) {
          $arrErro = $this->conexao->errorInfo();
          //'00000'->SUCCESS, '01000'->SUCCESS WITH WARNING
          if (($arrErro[0] !== '00000') && ($arrErro[0] !== '01000')) {
            throw new InfraException($arrErro[2],null,substr($sql,0,INFRA_TAM_MAX_LOG_SQL));
          }
        }	  	
	  	}

      if (InfraDebug::isBolProcessar()) {
        $numSeg = InfraUtil::verificarTempoProcessamento($numSeg);
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->executar] ' . $numReg . ' registro(s) afetado(s)');
        InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->executar] ' . $numSeg . ' s');
      }

	    return $numReg;
	  }
	  	  
		public function lerData($sqlServerDate) {
			//InfraDebug::getInstance()->gravarInfra($sqlServerDate);
			/* php.ini 
      ; Specify how datetime and datetim4 columns are returned
      ; On => Returns data converted to SQL server settings
      ; Off => Returns values as YYYY-MM-DD hh:mm:ss
      ;mssql.datetimeconvert = On*/ 
			
		  if ($this->numTipoInstalacao == 2) {
		    $sqlServerDate = $sqlServerDate->format('Y-m-d H:i:s');
		  }
		  
			if ($sqlServerDate === null) {
			  return null;
			}
			
			if (strlen($sqlServerDate) != 19) {
				throw new InfraException('Tamanho de data inv?lido.',null,$sqlServerDate);
			}
			
			return substr($sqlServerDate,8,2).'/'.substr($sqlServerDate,5,2).'/'.substr($sqlServerDate,0,4).substr($sqlServerDate,10);
		}
	
		public function gravarData($brasilDate) {
			 
      if(trim($brasilDate) === '') {
      	return 'NULL';
      }
      			
			if (strlen($brasilDate) == 10) {
			  $brasilDate .= ' 00:00:00';
			}

			//31/12/2005 15:23:50 -> 2005-12-31 15:23:50
			return '\''.substr($brasilDate,6,4).'-'.substr($brasilDate,3,2).'-'.substr($brasilDate,0,2).substr($brasilDate,10).'\'';
		}

    public function formatarPesquisaFTS($strPalavras) {      
     $arrDados = InfraString::agruparItens($strPalavras);
   
     for ($i=0; $i<count($arrDados); $i++) {
       if (strpos($arrDados[$i]," ") !== false || strpos($arrDados[$i],",") !== false || (strpos($arrDados[$i],"*") !== false && strpos($arrDados[$i],"\"") === false)) {
         $arrDados[$i] = "\"".$arrDados[$i]."\"";
       }
        
       if ($arrDados[$i] == "e") {
         $arrDados[$i] = "and";
       } elseif($arrDados[$i] == "ou") {
         $arrDados[$i] = "or";
       } elseif($arrDados[$i] == "nao") {
         $arrDados[$i] = "and not";
       } elseif($arrDados[$i] == "prox") {
         $arrDados[$i] = "near";
       }
     }
   
     $strPesquisaFormatada = "";
     for($i=0; $i<count($arrDados); $i++) {
       //ADICIONA OPERADOR AND COMO PADR?O SE N?O INFORMADO
       if ($i > 0) {
         if (!in_array($arrDados[$i-1],array('and','or','and not','near','(')) && !in_array($arrDados[$i],array('and','or','and not','near',')'))) {
           $strPesquisaFormatada .= " and";
         }
       }
       $strPesquisaFormatada .= " ".$arrDados[$i];
     }
     $strPesquisaFormatada = trim(InfraString::substituirIterativo('and and not', 'and not', $strPesquisaFormatada));
   
     return $strPesquisaFormatada;
   }
   
   public function criarSequencialNativa($strSequencia, $numInicial) {
     if (InfraDebug::isBolProcessar()) {
       InfraDebug::getInstance()->gravarInfra('[InfraSqlServer->criarSequencialNativa]');
     }

     $this->executarSql('create table '.$strSequencia.' (id int identity('.$numInicial.',1), campo char(1) null)');
   }

  }
?>