<?php
/* 
 * Modelo de Mouse
 *
 * @package AlgoritmoGenetico
 * @subpackage Model
 * @user felipesilvaibi
 * @since 14/03/2021
 */
class ModelObjetoMochilaMouse extends ModelObjetoMochilaBase {
    
    public function __construct() {
        $this->nome                 = 'Mouse';
        $this->peso                 = 0.3;
        $this->valor                = 300;
        $this->quantidadeDisponivel = 3;
    }
    
}