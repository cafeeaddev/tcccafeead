<?php

class M_ModeloBaseSelect extends M_Conexao {

    public $tabela;
    public $classe;
    public $colunas;
    public $where;
    public $whereValores = array();
    public $joins = '';
    public $orderBy;
    public $groupBy;
    public $limit;
    public $iterator = false;
    public $paginador = array();

    /**
     * Montar uma condição para o SELECT. Ex.: where('nome','like','%mau%'), where('idPerfil','=','2');
     * @param string $coluna Especifica a coluna para usar na comparação
     * @param string $operador Um dos operadores válidos nas cláusula SELECT
     * @param string $comparacao O valor que se está comparando
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function where($coluna, $operador, $comparacao) {
        if ($operador == 'IN') {
            $valores = explode(',', $comparacao);
            foreach ($valores as $valor) {
                $totalValores = count($this->whereValores) + 1;
                $nomeValor = ':valor' . $totalValores;
                $this->whereValores[$nomeValor] = $valor;
                $nomesValores[] = $nomeValor;
            }
            $nomeValor = implode(",", $nomesValores);
        } elseif ($operador == 'IS NOT NULL' || $operador == 'IS NULL') {
            $nomeValor = '';
        } else {
            $totalValores = count($this->whereValores) + 1;
            $nomeValor = ':valor' . $totalValores;
            $this->whereValores[$nomeValor] = $comparacao;
        }

        $colunaExplode = explode('->', $coluna);
        if (count($colunaExplode) > 1) {
            $coluna = $colunaExplode[0]::TABELA . '.' . $colunaExplode[1];
        } else {
            $coluna = $colunaExplode[0];
        }

        if ($operador == 'IN') {
            $this->where = $coluna . ' ' . $operador . ' (' . $nomeValor . ')';
        } else {
            $this->where = $coluna . ' ' . $operador . ' ' . $nomeValor;
        }

        return $this;
    }

    /**
     * Acrescentar uma condição AND para o SELECT. Ex.: andWhere('nome','like','%mau%'), andWhere('idPerfil','=','2');
     * @param string $coluna Especifica a coluna para usar na comparação
     * @param string $operador Um dos operadores válidos nas cláusula SELECT
     * @param string $comparacao O valor que se está comparando
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function andWhere($coluna, $operador, $comparacao) {
        if ($operador == 'IN') {
            $valores = explode(',', $comparacao);
            foreach ($valores as $valor) {
                $totalValores = count($this->whereValores) + 1;
                $nomeValor = ':valor' . $totalValores;
                $this->whereValores[$nomeValor] = $valor;
                $nomesValores[] = $nomeValor;
            }
            $nomeValor = implode(",", $nomesValores);
        } elseif ($operador == 'IS NOT NULL' || $operador == 'IS NULL') {
            $nomeValor = '';
        } else {
            $totalValores = count($this->whereValores) + 1;
            $nomeValor = ':valor' . $totalValores;
            $this->whereValores[$nomeValor] = $comparacao;
        }

        $colunaExplode = explode('->', $coluna);
        if (count($colunaExplode) > 1) {
            $coluna = $colunaExplode[0]::TABELA . '.' . $colunaExplode[1];
        } else {
            $coluna = $colunaExplode[0];
        }

        if ($operador == 'IN') {
            $this->where .= ' AND ' . $coluna . ' ' . $operador . ' (' . $nomeValor . ')';
        } else {
            $this->where .= ' AND ' . $coluna . ' ' . $operador . ' ' . $nomeValor;
        }

        return $this;
    }

    /**
     * Acrescentar uma condição OR para o SELECT. Ex.: orWhere('nome','like','%mau%'), orWhere('idPerfil','=','2');
     * @param string $coluna Especifica a coluna para usar na comparação
     * @param string $operador Um dos operadores válidos nas cláusula SELECT
     * @param string $comparacao O valor que se está comparando
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function orWhere($coluna, $operador, $comparacao) {
        if ($operador == 'IN') {
            $valores = explode(',', $comparacao);
            foreach ($valores as $valor) {
                $totalValores = count($this->whereValores) + 1;
                $nomeValor = ':valor' . $totalValores;
                $this->whereValores[$nomeValor] = $valor;
                $nomesValores[] = $nomeValor;
            }
            $nomeValor = implode(",", $nomesValores);
        } elseif ($operador == 'IS NOT NULL' || $operador == 'IS NULL') {
            $nomeValor = '';
        } else {
            $totalValores = count($this->whereValores) + 1;
            $nomeValor = ':valor' . $totalValores;
            $this->whereValores[$nomeValor] = $comparacao;
        }

        $colunaExplode = explode('->', $coluna);
        if (count($colunaExplode) > 1) {
            $coluna = $colunaExplode[0]::TABELA . '.' . $colunaExplode[1];
        } else {
            $coluna = $colunaExplode[0];
        }

        if ($operador == 'IN') {
            $this->where .= ' OR ' . $coluna . ' ' . $operador . ' (' . $nomeValor . ')';
        } else {
            $this->where .= ' OR ' . $coluna . ' ' . $operador . ' ' . $nomeValor;
        }

        return $this;
    }

    private function join($classeTabela, $operadorEsquerdo, $operador, $operadorDireito) {
        $tabelaJoin = $classeTabela::TABELA;

        $innerEsquerda = explode('->', $operadorEsquerdo);
        $tabelaEsquerda = $innerEsquerda[0]::TABELA;
        $tabelaEsquerda .= '.' . $innerEsquerda[1];

        $innerDireita = explode('->', $operadorDireito);
        if (count($innerDireita) > 1) {
            $tabelaDireita = $innerDireita[0]::TABELA;
            $tabelaDireita .= '.' . $innerDireita[1];
        } else {
            $tabelaDireita = $innerDireita[0];
        }

        $join = "$tabelaJoin ON $tabelaEsquerda $operador $tabelaDireita";

        return $join;
    }

    /**
     * Exemplo: innerJoin("M_Usuario", "M_Usuario->id", "=", "M_PerfilDeAceso->idUsuario");
     * @param type $classeTabela
     * @param type $operadorEsquerdo
     * @param type $operador
     * @param type $operadorDireito
     * @return \M_ModeloBaseSelect
     */
    public function innerJoin($classeTabela, $operadorEsquerdo, $operador, $operadorDireito) {
        $join = $this->join($classeTabela, $operadorEsquerdo, $operador, $operadorDireito);

        $innerJoin = " INNER JOIN $join";

        $this->joins .= ' ' . $innerJoin;

        return $this;
    }

    /**
     * Exemplo: innerJoin("M_Usuario", "M_Usuario->id", "=", "M_PerfilDeAceso->idUsuario");
     * @param type $classeTabela
     * @param type $operadorEsquerdo
     * @param type $operador
     * @param type $operadorDireito
     * @return \M_ModeloBaseSelect
     */
    public function leftJoin($classeTabela, $operadorEsquerdo, $operador, $operadorDireito) {
        $join = $this->join($classeTabela, $operadorEsquerdo, $operador, $operadorDireito);

        $innerJoin = " LEFT JOIN $join";

        $this->joins .= ' ' . $innerJoin;

        return $this;
    }

    /**
     * Exemplo: innerJoin("M_Usuario", "M_Usuario->id", "=", "M_PerfilDeAceso->idUsuario");
     * @param type $classeTabela
     * @param type $operadorEsquerdo
     * @param type $operador
     * @param type $operadorDireito
     * @return \M_ModeloBaseSelect
     */
    public function rightJoin($classeTabela, $operadorEsquerdo, $operador, $operadorDireito) {
        $join = $this->join($classeTabela, $operadorEsquerdo, $operador, $operadorDireito);

        $innerJoin = " RIGHT JOIN $join";

        $this->joins .= ' ' . $innerJoin;

        return $this;
    }

    /**
     * Exemplo: innerJoin("M_Usuario", "M_Usuario->id", "=", "M_PerfilDeAceso->idUsuario");
     * @param type $classeTabela
     * @param type $operadorEsquerdo
     * @param type $operador
     * @param type $operadorDireito
     * @return \M_ModeloBaseSelect
     */
    public function fullOuterJoin($classeTabela, $operadorEsquerdo, $operador, $operadorDireito) {
        $join = $this->join($classeTabela, $operadorEsquerdo, $operador, $operadorDireito);

        $innerJoin = " FULL OUTER JOIN $join";

        $this->joins .= ' ' . $innerJoin;

        return $this;
    }

    /**
     * Acrescenta a cláusula ORDDER BY no SELECT
     * @param array $ordem Deve receber um array com coluna e ordem - array('nome'=>'ASC','turma'=>'DESC') ou uma string separada por vírgulas ('nome ASC, turma DESC')
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function orderBy($ordem = array()) {
        $ordemArray = array();
        if (!is_array($ordem)) {
            $ordemS = str_replace(', ', ',', $ordem);
            $ordemExplode = explode(',', $ordemS);
            foreach ($ordemExplode as $explode) {
                $explodeArray = explode(' ', $explode);
                $ordemArray[$explodeArray[0]] = (isset($explodeArray[1])) ? $explodeArray[1] : "ASC";
            }
        } else {
            $ordemArray = $ordem;
        }

        foreach ($ordemArray as $coluna => $ordemV) {
            $colunaExplode = explode('->', $coluna);
            if (count($colunaExplode) > 1) {
                $coluna = $colunaExplode[0]::TABELA . '.' . $colunaExplode[1];
            } else {
                $coluna = $colunaExplode[0];
            }
            $ordemVerificada[] = $coluna . ' ' . strtoupper($ordemV);
        }

        if (!empty($ordemVerificada)) {
            $this->orderBy = implode(', ', $ordemVerificada);
        }

        return $this;
    }

    /**
     * Acrescenta a cláusula GROUP BY no SELECT.
     * @param array $grupo Deve receber um array com a coluna - array('nome','turma') ou uma string separada por vírgulas ('nome, turma')
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function groupBy($grupos = array()) {
        if (!is_array($grupos)) {
            $grupoCorrigido = str_replace(', ', ',', $grupos);
            $grupos = explode(',', $grupoCorrigido);
        }

        $grupoFinal = array();
        foreach ($grupos as $grupo) {
            $grupoExplode = explode('->', $grupo);
            if (count($grupoExplode) > 1) {
                $grupoFinal[] = $grupoExplode[0]::TABELA . '.' . $grupoExplode[1];
            } else {
                $grupoFinal[] = $grupoExplode[0];
            }
        }

        $this->groupBy = implode(',', $grupoFinal);


        return $this;
    }

    /**
     * Acrescenta a cláusula LIMIT no SELECT. Lembre que no SELECT o primeiro elemento é 0.
     * @param array $limit Deve receber um array com os limites - array(0,10) ou uma string separada por vírgulas ('0, 10')
     * @return object Objeto do tipo M_ModeloBaseSelect
     */
    public function limit($limite = array()) {
        if (!is_array($limite)) {
            $this->limit = $limite;
        } else {
            $this->limit = implode(',', $limite);
        }

        return $this;
    }

    public function exibirSelect($preenchido = false, $final = true) {
        $classe = $this->classe;
        $valores = $this->whereValores;

        $sql = $this->montarSelect();
        if ($final) {
            $pdo = self::getPDO($classe);
            $stmt = $pdo->prepare($sql);

            $query = $stmt->queryString;
        } else {
            $query = $sql;
        }

        if ($preenchido) {
            foreach ($valores as $indice => $valor) {
                $query = str_replace($indice, $valor, $query);
            }
            return $query;
        } else {
            return array("sql" => $query, 'valores' => $valores);
        }
    }

    /**
     * Retornar o resultado do SELECT. Caso esteja buscando todas as colunas (*), retorna um array de objeto.
     * Caso esteja buscando colunas específicas, retornar um array com um ARRAY ASSOCIATIVO das colunas=>valores.
     * @return array Retornar o resultado do SELECT
     */
    public function buscar($formato = '') {
        $classe = $this->classe;
        $valores = $this->whereValores;

        $sql = $this->montarSelect();
        $pdo = self::getPDO($this->classe);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        $objetos = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->colunas[0] != '*') {
                $objetos[] = ($formato == 'objeto') ? (object) $linha : $linha;
            } else {
                $objeto = new $classe();
                $objetos[] = $objeto->montarObjeto($linha);
            }
        }

        if ($this->iterator) {
            $retorno = new M_ModeloBaseIterator($objetos);
            $retorno->paginador = $this->paginador;
            return $retorno;
        } else {
            return $objetos;
        }
    }

    /**
     * Retornar o resultado do SELECT. Caso esteja buscando todas as colunas (*), retorna um array de objeto.
     * Caso esteja buscando colunas específicas, retornar um array com um ARRAY ASSOCIATIVO das colunas=>valores.
     * O indice do array será o id da linha
     * @return array Retornar o resultado do SELECT
     */
    public function buscarComIndice($indice = 'id', $formato = '') {
        $classe = $this->classe;
        $valores = $this->whereValores;

        $sql = $this->montarSelect();
        $pdo = self::getPDO($this->classe);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        $objetos = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->colunas[0] != '*') {
                $objetos[$linha[$indice]] = ($formato == 'objeto') ? (object) $linha : $linha;
                ;
            } else {
                $objeto = new $classe();
                $objetos[$linha[$indice]] = $objeto->montarObjeto($linha);
            }
        }

        if ($this->iterator) {
            $retorno = new M_ModeloBaseIterator($objetos);
            $retorno->paginador = $this->paginador;
            return $retorno;
        } else {
            return $objetos;
        }
    }

    /**
     * Retornar o resultado do SELECT. Caso esteja buscando todas as colunas (*), retorna um array de objeto.
     * Caso esteja buscando colunas específicas, retornar um array com um ARRAY ASSOCIATIVO das colunas=>valores.
     * @return array Retornar o resultado do SELECT
     */
    public function buscarPrimeiro($formato = '') {
        $classe = $this->classe;
        $valores = $this->whereValores;

        $sql = $this->montarSelect();
        $pdo = self::getPDO($this->classe);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        $objetos = array();
        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->colunas[0] != '*') {
                $objetos[] = ($formato == 'objeto') ? (object) $linha : $linha;
            } else {
                $objeto = new $classe();
                $objetos[] = $objeto->montarObjeto($linha);
            }
        }
        if (isset($objetos[0])) {
            return $objetos[0];
        } else {
            return null;
        }
    }

    /**
     * Retornar o resultado do SELECT. Caso esteja buscando todas as colunas (*), retorna um array de objeto.
     * Caso esteja buscando colunas específicas, retornar um array com um ARRAY ASSOCIATIVO das colunas=>valores.
     * @return array Retornar o resultado do SELECT
     */
    public function buscarUltimo($formato = '') {
        $classe = $this->classe;
        $valores = $this->whereValores;

        $sql = $this->montarSelect();
        $pdo = self::getPDO($this->classe);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        $objeto = null;

        while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($this->colunas[0] != '*') {
                $objeto = ($formato == 'objeto') ? (object) $linha : $linha;
            } else {
                $objeto = new $classe();
                $objeto = $objeto->montarObjeto($linha);
            }
        }
        return $objeto;
    }

    /**
     * 
     * @return array Retornar o resultado do SELECT
     */
    public function paginar($totalPorPagina = 20) {
        $contar = clone $this;
        $contar->colunas = array("count(*) as total");
        $retorno = $contar->buscarPrimeiro();
        $totalRegistros = $retorno['total'];

        $paginaAtual = capturaGet('_pag', 1);
        $multiplicador = $paginaAtual - 1;

        $this->iterator = true;
        $this->paginador['paginaAtual'] = $paginaAtual;
        $this->paginador['paginaTotais'] = ceil($totalRegistros / $totalPorPagina);
        $this->paginador['totalRegistro'] = $totalRegistros;
        $this->paginador['link'] = $this->full_path();

        $limite = array($multiplicador * $totalPorPagina, $totalPorPagina);

        return $this->limit($limite);
    }

    /**
     * Função internar para gerar o SELECT
     * @return string Uma declaração de SQL montado de acordo com os parâmetros
     */
    protected function montarSelect() {
        $colunas = implode(',', $this->colunas);
        $tabela = $this->tabela;
        $join = ($this->joins != '') ? trim($this->joins) : '';
        $where = ($this->where != '') ? $this->where : '1';
        $grupo = ($this->groupBy != '') ? ' GROUP BY ' . $this->groupBy : '';
        $ordem = ($this->orderBy != '') ? ' ORDER BY ' . $this->orderBy : '';
        $limite = ($this->limit != '') ? ' LIMIT ' . $this->limit : '';

        $sql = 'SELECT ' . $colunas . ' FROM ' . $tabela . ' ' . $join . ' WHERE ' . $where . $grupo . $ordem . $limite;

        return $sql;
    }

    private function full_path() {
        $s = &$_SERVER;
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        $uri = $protocol . '://' . $host . $s['REQUEST_URI'];
        $segments = explode('&', $uri);
        $url = $segments[0];
        foreach ($segments as $segment) {
            $teste = explode("=", $segment);
            if ($teste[0] != "_pag" && $teste[0] != "_pagTotalRegistros" && $segment != $segments[0]) {
                $url .= "&" . $segment;
            }
        }
        return $url;
    }

}
