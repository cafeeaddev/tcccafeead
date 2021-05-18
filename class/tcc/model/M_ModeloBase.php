<?php

class M_ModeloBase extends M_Conexao {

    /**
     * Caso receba um id no construtor, já retorna o objeto instanciado.
     * @param int $id [opcional] O id do registro no banco de dados.
     */
    function __construct($id = '') {
        parent::__construct();
        if ($id != '') {
            $objeto = $this->buscarPorId($id);
            if ($objeto == NULL) {
                $this->preencheColunasTabela();
            } else {
                foreach ($objeto as $propriedade => $valor) {
                    $this->$propriedade = $valor;
                }
            }
        } else {
            $this->preencheColunasTabela();
        }
    }

    /**
     * Busca um objeto pelo id.
     * @param int $id O id do registro no banco de dados.
     * @return object Retorna o objeto instanciado, ou NULL caso não localize.
     */
    public static function buscarPorId($id) {
        return self::buscarPorClasseId(get_called_class(), $id);
    }

    /**
     * Busca todos os registro no Banco de Dados.
     * @return array Retorna um array de objetos localizados.
     */
    public static function buscarTodos($ordem = 'nome') {
        $classe = get_called_class();
        $tabela = $classe::TABELA;
        $sql = "SELECT * FROM $tabela ORDER BY :nome";
        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':nome' => $ordem));
        $objetos = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $objeto = self::gerarObjeto($classe, $linha);
            $objetos[] = $objeto;
        }
        return $objetos;
    }

    /**
     * Busca todos os registro no Banco de Dados.
     * @return array Retorna um array de objetos localizados.
     */
    public static function buscarTodosComIndice($ordem = 'nome', $indice = 'id') {
        $classe = get_called_class();
        $tabela = $classe::TABELA;
        $sql = "SELECT * FROM $tabela ORDER BY :nome";
        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':nome' => $ordem));
        $objetos = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $objeto = self::gerarObjeto($classe, $linha);
            $objetos[$linha[$indice]] = $objeto;
        }
        return $objetos;
    }

    /**
     * Busca um objeto da relaçao. 'Pertence A Um' significa que o id da relação está NESTE objeto. Relação do tipo MUITOS para UM.
     * @param string $classe O nome da classe do objeto que deseja instanciar.
     * @param string $coluna [opcional] O nome da propriedade NESTE objeto com o id do relacionamento (caso não seja passado, espera que a propriedade seja "id" + o nome da classe relacionada).
     * @return object Retorna o objeto relacionado
     */
    public function pertenceAUm($classe, $coluna = '') {
        if ($coluna == '') {
            $coluna = explode("_", $classe);
            $coluna = 'id' . substr($coluna[1], 0);
        }
        $id = $this->$coluna;

        if (isset($this->relacionamentos['possuiUm'][$coluna])) {
            return $this->relacionamentos['possuiUm'][$coluna];
        }

        $objeto = new $classe($id);

        $this->guardarRelacionamento($coluna, $objeto, 'possuiUm');

        return $objeto;
    }

    /**
     * Busca todos os objetos da relação. 'Possui Muitos' significa que o id da relação está nos OUTROS objetos. Relação do tipo UM para MUITOS.
     * @param string $classe O nome da classe dos objetos que deseja instanciar.
     * @param string $coluna [opcional] O nome da coluna na tabela do OUTRO objeto que se relaciona com este. Caso não receba, o padrão é "id" + o nome DESTA classe.
     * @return array Retorna um array de objetos localizados
     */
    public function possuiMuitos($classe, $coluna = '') {
        if ($coluna == '') {
            $coluna = get_called_class();
            $coluna = 'id' . substr($coluna, 2);
        }
        $array = self::buscarPorColunaEstrangeira($classe, $coluna, $this->id);
        return $array;
    }

    /**
     * Busca todos os relacionamentos através de uma tabela PIVOT
     * @param string $classeDesejada A classe dos objetos que estamos buscando
     * @param string $classeAtraves A classe que representa a tabela PIVOT
     * @return array Retornar um array de objetos desejados. Caso não encontre nada, retorna um array vazio
     */
    public function possuiMuitosAtraves($classeDesejada, $classeAtraves) {
        //busco os dados básicos (quem chama, quem busca)
        $classeAtual = get_called_class();
        $tabelaPivot = $classeAtraves::TABELA;
        $tabelaDesejada = $classeDesejada::TABELA;
        $tabelas = $classeAtraves::$MUITOSPARAMUITOS;
        $colunaDesejada = $tabelas[$classeDesejada];
        $minhaColuna = $tabelas[$classeAtual];

        //busco os ids do Relacionamento
        $sql = "SELECT $colunaDesejada as idsDesejados FROM $tabelaPivot WHERE $minhaColuna = :meuId";
        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':meuId' => $this->id));

        $ids = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $linha['idsDesejados'];
        }

        $objetos = array();
        //verifico se existe algum relacionamento e trás todos
        if (count($ids) > 0) {
            $ids = implode(',', $ids);
            $sql = "SELECT * FROM $tabelaDesejada WHERE id IN ($ids)";
            $pdo = self::getPDO();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $objeto = $classeDesejada::gerarObjeto($classeDesejada, $linha);
                $objetos[] = $objeto;
            }
        }
        return $objetos;
    }

    /**
     * Método STATIC para exclusão do registro no Banco de Dados.
     * @param int $id O id do registro no Banco de Dados.
     * @return int A quantidade de registros afetadas. Se retornar 0 é porque houve erro.
     */
    public static function excluir($id, $registrarLog = true) {
        $classe = get_called_class();
        if ($registrarLog) {
            $objetoOriginal = $classe::buscarPorId($id);
        }
        $tabela = $classe::TABELA;
        $sql = "DELETE FROM $tabela WHERE id = :id";
        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));

        $excluido = $stmt->rowCount();

        if ($excluido > 0) {
            if ($classe != M_Log && $registrarLog) {
                self::registrarLog('EXCLUIR', $objetoOriginal, null);
            }
            return $excluido;
        } else {
            return 0;
        }
    }

    /**
     * Inserir o objeto no banco de dados. Este método NÃO verifica campos em branco ou NULL, ou seja, o objeto já deve prever isso.
     * @param boolean $registrarLog Por padrão, todas as inserções registram no Log. Caso não queira registrar, deve-se passar FALSE.
     * @return int O id do novo registro. Caso ocorra erro, retorna 0.
     */
    public function inserir($registrarLog = true) {
        $classe = get_called_class();
        $tabela = $classe::TABELA;
        foreach ($this->colunasTabela as $coluna) {
            if ($coluna != 'id') {
                $campos[] = $coluna;
                $nomeCampo = ':' . $coluna;
                $camposInserir[] = $nomeCampo;
                $valoresInserir[$nomeCampo] = $this->$coluna;
            }
        }
        $campos = implode(',', $campos);
        $camposInserir = implode(',', $camposInserir);

        $sql = "INSERT INTO $tabela ($campos) VALUES ($camposInserir)";

        $pdo = self::getPDO();
//$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $stmt = $pdo->prepare($sql);
//try{
        $stmt->execute($valoresInserir);
//}catch(Exception $e){
//     echo 'Exception -> ';
//        var_dump($e->getMessage());
//}
        if ($stmt->rowCount() > 0) {
            $novoId = $pdo->lastInsertId();
            if ($classe != 'M_Log' && $registrarLog) {
                $this->id = $novoId;
                self::registrarLog('INSERIR', $this, null);
            }
            return $novoId;
        } else {
            return 0;
        }
    }

    /**
     * Atualizar o objeto no banco de dados. Este método NÃO verifica campos em branco ou NULL, ou seja, o objeto já deve prever isso.
     * @param boolean $registrarLog Por padrão, todas as inserções registram no Log. Caso não queira registrar, deve-se passar FALSE.
     * @return boolean Informa se a execução do SQL foi executada com sucesso.
     */
    public function atualizar($registrarLog = true) {
        if ($this->id == '') {
            return 0;
        }
        $classe = get_called_class();
        if ($registrarLog) {
            $objetoOriginal = $classe::buscarPorId($this->id);
        }
        $tabela = $classe::TABELA;
        foreach ($this->colunasTabela as $coluna) {
            if ($coluna != 'id') {
                $nomeCampo = ':' . $coluna;
                $camposAtualizar[] = $coluna . ' = ' . $nomeCampo;
                $valoresAtualizar[$nomeCampo] = $this->$coluna;
            }
        }
        $valoresAtualizar[':id'] = $this->id;

        $camposAtualizar = implode(', ', $camposAtualizar);

        $sql = "UPDATE $tabela SET $camposAtualizar WHERE id = :id";

        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $execucao = $stmt->execute($valoresAtualizar);

        $atualizado = $stmt->rowCount();

        if ($atualizado > 0) {
            if ($classe != 'M_Log' && $registrarLog) {
                self::registrarLog('ATUALIZAR', $objetoOriginal, $this);
            }
            return $execucao;
        } else {
            return $execucao;
        }
    }

    /**
     * Método para iniciar a montagem de um SELECT. Deve sempre terminar com '->buscar()' para executar o SELECT.
     * @param array $colunas As colunas a serem devolvidas. Pode ser passado como array ou como uma string (colunas separadas por vírgula) - Ex.: array('id','nome','endereco') ou string ('id, nome, endereço'). Caso não receba nada, utiliza *.
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public static function select($colunas = array()) {
        $classe = get_called_class();
        if (empty($colunas)) {
            $colunas[] = '*';
        }
        if (!is_array($colunas)) {
            $colunas = str_replace(', ', ',', $colunas);
            $colunas = explode(',', $colunas);
        }
        $colunasFinal = array();
        foreach ($colunas as $coluna) {
            $colunaExplode = explode('->', $coluna);
            if (count($colunaExplode) > 1) {
                $colunasFinal[] = $colunaExplode[0]::TABELA . '.' . $colunaExplode[1];
            } else {
                $colunasFinal[] = $colunaExplode[0];
            }
        }
        $select = new M_ModeloBaseSelect();
        $select->tabela = $classe::TABELA;
        $select->classe = $classe;
        $select->colunas = $colunasFinal;

        return $select;
    }

    //AQUI COMEÇAM AS FUNÇÕES NECESSÁRIAS PARA O FUNCIONAMENTO DA CLASSE

    private function preencheColunasTabela() {
        $classe = get_called_class();
        $sql = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name = :tabela";
        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':tabela' => $classe::TABELA));
        $this->colunasTabela = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->colunasTabela[] = $row['COLUMN_NAME'];
        }
    }

    //Esta função recebe o nome da classe e um id, instancia um objeto e retorno o valor
    private static function buscarPorClasseId($classe, $id) {
        $tabela = $classe::TABELA;

        $sql = "SELECT * FROM $tabela WHERE id = :id";

        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));

        if ($stmt->rowCount() > 0) {
            $linha = $stmt->fetch(PDO::FETCH_ASSOC);
            $objeto = self::gerarObjeto($classe, $linha);
        } else {
            return null;
        }

        return $objeto;
    }

    private static function buscarPorColunaEstrangeira($classe, $colunaEstrangeira, $idChaveEstrangeira) {
        $tabela = $classe::TABELA;

        $sql = "SELECT * FROM $tabela WHERE $colunaEstrangeira = :idChaveEstrangeira";

        $pdo = self::getPDO();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':idChaveEstrangeira' => $idChaveEstrangeira));

        $objetos = array();

        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $objeto = self::gerarObjeto($classe, $linha);
            $objetos[] = $objeto;
        }

        return $objetos;
    }

    private static function gerarObjeto($classe, $linha) {
        $objeto = new $classe();
        //$objeto->colunasTabela = array();
        foreach ($linha as $propriedade => $valor) {
            if (!is_int($propriedade)) {
                $objeto->$propriedade = $valor;
                //$objeto->colunasTabela[] = $propriedade;
            }
        }

        return $objeto;
    }

    /**
     * Monta um objeto
     * @param array $linha Recebe um array (geralmente um FETCH de PDO)
     * @return object Retorna o objeto montado
     */
    public static function montarObjeto($linha) {
        $classe = get_called_class();
        $objeto = new $classe();
        //$objeto->colunasTabela = array();
        foreach ($linha as $propriedade => $valor) {
            if (!is_int($propriedade) && !is_array($valor)) {
                //$objeto->$propriedade = trim($valor); //comentada em 11/11/2014
                $objeto->$propriedade = $valor;
                //$objeto->colunasTabela[] = $propriedade;
            }
        }

        return $objeto;
    }

    /**
     * Preenche o objeto atual através de um array recebido
     * @param array $linha Recebe um array (geralmente um FETCH de PDO)
     * @return object Retorna o objeto montado
     */
    public function preencherObjeto($linha) {
        foreach ($linha as $propriedade => $valor) {
            if (!is_int($propriedade) && !is_array($valor)) {
                $this->$propriedade = trim($valor);
                //$objeto->colunasTabela[] = $propriedade;
            }
        }
    }

    /**
     * recebe uma coluna e um objeto (ou array)
     * caso ainda não exista nenhum relacionamento, cria o array no objeto
     * @param type $coluna
     * @param type $objeto
     */
    protected function guardarRelacionamento($coluna, $objeto, $tipoRelacionamento) {
        if (!property_exists($this, 'relacionamentos')) {
            $this->relacionamentos = array();
        }
        $this->relacionamentos[$tipoRelacionamento][$coluna] = $objeto;
    }

    //para não poder setar qualquer coisa, eu vou verificar o que está chegando
    public function __set($nome, $valor) {
        if ($nome == "relacionamentos" || $nome == "colunasTabela") {
            $this->$nome = $valor;
        }
    }

    public function __get($nome) {
        
    }

    public function __sleep() {
        return $this->colunasTabela;
    }

    protected static function registrarLog($acao, $objeto1, $objeto2 = NULL) {
        $usuarioLogado = C_Login::getUsuarioLogado();
        if (is_array($usuarioLogado)) {
            $idUsuario = $usuarioLogado['id'];
        } else {
            $idUsuario = NULL;
        }

        $log = new M_Log();
        $log->data = time();
        $log->evento = $acao;
        $log->idUsuario = $idUsuario;
        $log->pagina = NULL;
        $log->classeObjeto = get_class($objeto1);
        $log->objeto1 = serialize($objeto1);
        if ($objeto2 != NULL)
            $log->objeto2 = serialize($objeto2);
        else
            $log->objeto2 = NULL;

        $novoId = $log->inserir();

        return $novoId;
    }

}