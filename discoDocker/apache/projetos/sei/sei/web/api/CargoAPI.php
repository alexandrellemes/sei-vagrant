<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4? REGI?O
 *
 * 11/08/2016 - criado por mga
 *
 */

class CargoAPI{
  private $IdCargo;
  private $ExpressaoCargo;
  private $ExpressaoTratamento;
  private $ExpressaoVocativo;

  /**
   * @return mixed
   */
  public function getIdCargo()
  {
    return $this->IdCargo;
  }

  /**
   * @param mixed $IdCargo
   */
  public function setIdCargo($IdCargo)
  {
    $this->IdCargo = $IdCargo;
  }

  /**
   * @return mixed
   */
  public function getExpressaoCargo()
  {
    return $this->ExpressaoCargo;
  }

  /**
   * @param mixed $ExpressaoCargo
   */
  public function setExpressaoCargo($ExpressaoCargo)
  {
    $this->ExpressaoCargo = $ExpressaoCargo;
  }

  /**
   * @return mixed
   */
  public function getExpressaoTratamento()
  {
    return $this->ExpressaoTratamento;
  }

  /**
   * @param mixed $ExpressaoTratamento
   */
  public function setExpressaoTratamento($ExpressaoTratamento)
  {
    $this->ExpressaoTratamento = $ExpressaoTratamento;
  }

  /**
   * @return mixed
   */
  public function getExpressaoVocativo()
  {
    return $this->ExpressaoVocativo;
  }

  /**
   * @param mixed $ExpressaoVocativo
   */
  public function setExpressaoVocativo($ExpressaoVocativo)
  {
    $this->ExpressaoVocativo = $ExpressaoVocativo;
  }
}