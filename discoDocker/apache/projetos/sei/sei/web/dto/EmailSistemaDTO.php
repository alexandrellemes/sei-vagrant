<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
*
* 19/11/2012 - criado por mga
*
* Vers?o do Gerador de C?digo: 1.33.0
*
* Vers?o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class EmailSistemaDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'email_sistema';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdEmailSistema',
                                   'id_email_sistema');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                    'IdEmailSistemaModulo',
                                    'id_email_sistema_modulo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Descricao',
                                   'descricao');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'De',
                                   'de');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Para',
                                   'para');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Assunto',
                                   'assunto');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Conteudo',
                                   'conteudo');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'SinAtivo',
                                   'sin_ativo');
        
    $this->configurarPK('IdEmailSistema',InfraDTO::$TIPO_PK_INFORMADO);
    
    $this->configurarExclusaoLogica('SinAtivo', 'N');

  }
}
?>