<?
/**
* TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
*
* 05/11/2010 - criado por jonatas_db
*
* Vers�o do Gerador de C�digo: 1.30.0
*
* Vers�o no CVS: $Id$
*/

require_once dirname(__FILE__).'/../SEI.php';

class GrupoAcompanhamentoDTO extends InfraDTO {

  public function getStrNomeTabela() {
  	 return 'grupo_acompanhamento';
  }

  public function montar() {

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdGrupoAcompanhamento',
                                   'id_grupo_acompanhamento');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_STR,
                                   'Nome',
                                   'nome');

    $this->adicionarAtributoTabela(InfraDTO::$PREFIXO_NUM,
                                   'IdUnidade',
                                   'id_unidade');

    $this->configurarPK('IdGrupoAcompanhamento', InfraDTO::$TIPO_PK_NATIVA );
    

  }
}
?>