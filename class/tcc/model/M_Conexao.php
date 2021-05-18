<?php

/*
Criado por Cafeead
*/
class M_Conexao {

    public $conexao;

    /**
     * Método que retorna uma conexão com a Base de dados do moodle
     * @return mysql_connect $conexao
     */
    function __construct() {
        //$config = dirname(__FILE__).'/../../../../config.php';
        

    }

    public function PDO() {
        return $this->conexao;
    }



    //função criada para testes do ModeloBase
    public static function getPDO($classeChamadora='') {
        if(!$classeChamadora){
            $classeChamadora = get_called_class();
        }
        //$classe = new ReflectionClass($classeChamadora);
                  
       
    }

}