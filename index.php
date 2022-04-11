<?php
require('src/controller/controller_algoritmo_genetico_mochila.php');
/* 
 * Index para a execução do algorítmo
 * 
 * @user felipesilvaibi
 * @since 14/03/2021
 */
$oClass = new ControllerAlgoritmoGeneticoMochila();
$oClass->processaDados();