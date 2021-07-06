<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 03/12/2009 - criado por mga
*
* Vers�o do Gerador de C�digo: 1.29.1
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class ContextoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'contexto';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdContexto',
                                   'id_contexto');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdOrgao',
                                   'id_orgao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Nome',
                                   'nome');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'BaseDnLdap',
                                   'base_dn_ldap');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinAtivo',
                                   'sin_ativo');

    $this->configurarPK('IdContexto', InfraDTO::$TIPO_PK_INFORMADO );
    

    $this->configurarExclusaoLogica('SinAtivo', 'N');

  }
}
?>