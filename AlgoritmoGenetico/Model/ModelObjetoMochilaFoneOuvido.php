<?php
/* 
 * Modelo de Fone de Ouvido
 *
 * @package AlgoritmoGenetico
 * @subpackage Model
 * @user felipesilvaibi
 * @since 14/03/2021
 */
class ModelObjetoMochilaFoneOuvido extends ModelObjetoMochilaBase {
    
    public function __construct() {
        $this->nome                 = 'Fone de Ouvido';
        $this->peso                 = 0.4;
        $this->valor                = 400;
        $this->quantidadeDisponivel = 5;
    }
    
}