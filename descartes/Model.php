<?php
    namespace descartes;

    /**
     * Cette classe sert de mère à tous les modèles, elle permet de gérer l'ensemble des fonction necessaires aux requetes en base de données
     * @param $pdo : Une instance de \PDO
     */
    class Model
    {
        //Les variables internes au Model
        var $pdo;

        //Les constantes des différents types de retours possibles
        const NO = 0; //Pas de retour
        const FETCH = 1; //Retour de type fetch
        const FETCHALL = 2; //Retour de type fetchall
        const ROWCOUNT = 3; //Retour de type rowCount()

        /**
         * Model constructor
         * @param \PDO $pdo : \PDO connect to use
         */
        public function __construct(\PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        /**
         * Cette fonction permet créer une connexion à une base SQL via \PDO
         * @param string $host : L'host à contacter
         * @param string $dbname : Le nom de la base à contacter
         * @param string $user : L'utilisateur à utiliser
         * @param string $password : Le mot de passe à employer
         * @return mixed : Un objet \PDO ou false en cas d'erreur
         */
        public static function _connect ($host, $dbname, $user, $password, ?string $charset = 'utf8mb4', ?array $options = null)
        {
            $options = $options ?? [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
            
            // On se connecte à MySQL
            $pdo = new \PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';charset=' . $charset , $user, $password, $options);

            if ($pdo === false)
            {
                throw new DescartesExceptionDatabaseConnection('Cannot connect to database ' . $dbname . '.');
            }
                
            return $pdo;
        }

        /**
         * Run a query and return result
         * @param string $query : Query to run
         * @param array $data : Data to pass to query
         * @param const $return_type : Type of return, by default all results, see Model constants
         * @param const $fetch_mode : Format of result from db, by default array, FETCH_ASSOC
         * @param boolean $debug : If we must return debug info instead of data, by default false
         * @return mixed : Result of query, depend of $return_type | null | array | object | int
         */
        protected function _run_query (string $query, array $data = array(), int $return_type = self::FETCHALL, int $fetch_mode = \PDO::FETCH_ASSOC, bool $debug = false)
        {
            try
            {
                //Must convert bool to 1 or 0 because of some strange inconsistent behavior between PHP versions
                foreach ($data as $key => $value)
                {
                    if (is_bool($value))
                    {
                        $data[$key] = (int) $value;
                    }
                }

                $query = $this->pdo->prepare($query);
                $query->setFetchMode($return_type);
                $query->execute($data);

                if ($debug)
                {
                    return $query->errorInfo();
                }

                switch ($return_type)
                {
                    case self::NO :
                        $return = NULL;
                        break; 

                    case self::FETCH :
                        $return = $query->fetch();
                        break; 

                    case self::FETCHALL :
                        $return = $query->fetchAll();
                        break; 
                    
                    case self::ROWCOUNT : 
                        $return = $query->rowCount();
                        break;
                
                    default :
                        $return = $query->fetchAll();
                }

                return $return;
            }
            catch (\PDOException $e)
            {
                $error = $query->errorInfo();

                //Get query string and params
                ob_start();
                $query->debugDumpParams();
                $debug_string = ob_get_clean();

                throw new \descartes\exceptions\DescartesExceptionSqlError(
                    'SQL Error : ' . "\n" . 
                    'SQLSTATE : ' . $error[0] . "\n" .
                    'Driver Error Code : ' . $error[1] . "\n" .
                    'Driver Error Message : ' . $error[2] . "\n" .
                    'SQL QUERY DEBUG :' . "\n" .
                    '-----------------' . "\n" .
                    $debug_string . "\n" .
                    '-----------------' . "\n"
                );
            }
        }
        
        /**
         * Return last inserted id
         * return int : Last inserted id
         */
        protected function _last_id() : int
        {
            return $this->pdo->lastInsertId();
        }

        /*
            Fonctions d'execution des requetes ou de génération
        */

        
        /**
         * Generate IN query params and values
         * @param array $values : Values to generate in array from
         * @return array : Array ['QUERY' => string 'IN(...)', 'PARAMS' => [parameters to pass to execute]]
        */
        protected function _generate_in_from_array ($values)
        {
            $return = array(
                'QUERY' => '',
                'PARAMS' => array(),
            );
            
            $flags = array();

            $values = count($values) ? $values : array();
            
            foreach ($values as $key => $value)
            {
                $key = preg_replace('#[^a-zA-Z0-9_]#', '', $key);
                $return['PARAMS']['in_value_' . $key] = $value;
                $flags[] = ':in_value_' . $key;
            }        
                
            $return['QUERY'] .= ' IN(' . implode(', ', $flags) . ')';
            return $return;
        }


        /**
         * Evaluate a condition to generate query string and params array for
         * @param string $fieldname : fieldname possibly preceed by '<, >, <=, >=, ! or ='
         * @param $value : value of field
         * @return array : array with QUERY and PARAMS
         */
        protected function _evaluate_condition (string $fieldname, $value) : array
        {
            $first_char = mb_substr($fieldname, 0, 1);
            $second_char = mb_substr($fieldname, 1, 1);

            switch(true)
            {
                //Important de traiter <= & >= avant < & >
                case ('<=' == $first_char . $second_char) :
                    $true_fieldname = mb_substr($fieldname, 2);
                    $operator = '<=';
                    break;

                case ('>=' == $first_char . $second_char) :
                    $true_fieldname = mb_substr($fieldname, 2);
                    $operator = '>=';
                    break;

                case ('!=' == $first_char . $second_char) :
                    $true_fieldname = mb_substr($fieldname, 2);
                    $operator = '!=';
                    break;

                case ('!' == $first_char) :
                    $true_fieldname = mb_substr($fieldname, 1);
                    $operator = '!=';
                    break;

                case ('<' == $first_char) :
                    $true_fieldname = mb_substr($fieldname, 1);
                    $operator = '<';
                    break;

                case ('>' == $first_char) :
                    $true_fieldname = mb_substr($fieldname, 1);
                    $operator = '>';
                    break;

                case ('%' == $first_char) :
                    $true_fieldname = mb_substr($fieldname, 1);
                    $operator = 'LIKE';
                    break;

                case ('=' == $first_char) :
                    $true_fieldname = mb_substr($fieldname, 1);
                    $operator = '=';
                    break;

                default :
                    $true_fieldname = $fieldname;
                    $operator = '=';
            }

            //Protect against injection in fieldname
            $true_fieldname = preg_replace('#[^a-zA-Z0-9_]#', '', $true_fieldname);

            // Add a uid to fieldname so we can combine multiple rules on same field
            $uid = uniqid();

            $query = '`' . $true_fieldname . '` ' . $operator . ' :where_' . $true_fieldname . '_' . $uid;
            $param = ['where_' . $true_fieldname . '_' . $uid => $value];

            return ['QUERY' => $query, 'PARAM' => $param];
        }


        /**
         * Get from table, posssibly with some conditions
         * @param string $table : table name
         * @param array $conditions : Where conditions to use, format 'fieldname' => 'value', fieldname can be preceed by operator '<, >, <=, >=, ! or = (by default)' to adapt comparaison operator
         * @param ?string $order_by : name of column to order result by, null by default
         * @param string $desc : L'ordre de tri (asc ou desc). Si non défini, ordre par défaut (ASC)
         * @param string $limit : Le nombre maximum de résultats à récupérer (par défaut pas le limite)
         * @param string $offset : Le nombre de résultats à ignorer (par défaut pas de résultats ignorés)
         * @return mixed : False en cas d'erreur, sinon les lignes retournées
         */
        protected function _select (string $table, array $conditions = [], ?string $order_by = null, bool $desc = false, ?int $limit = null, ?int $offset = null)
        {
            try 
            {
                $wheres = array();
                $params = array();
                foreach ($conditions as $label => $value)
                {
                    $condition = $this->_evaluate_condition($label, $value);
                    $wheres[] = $condition['QUERY'];
                    $params = array_merge($params, $condition['PARAM']);
                }

                $query = "SELECT * FROM `" . $table . "` WHERE 1 " . (count($wheres) ? 'AND ' : '') . implode(' AND ', $wheres);

                if ($order_by !== null)
                {
                    $query .= ' ORDER BY ' . $order_by;
                    
                    if ($desc) 
                    {
                        $query .= ' DESC';
                    }
                }

                if ($limit !== null)
                {
                    $query .= ' LIMIT :limit';
                    if ($offset !== null)
                    {
                        $query .= ' OFFSET :offset';
                    }
                }


                $query = $this->pdo->prepare($query);

                if ($limit !== null)
                {
                    $query->bindParam(':limit', $limit, \PDO::PARAM_INT);
                    
                    if ($offset !== null)
                    {
                        $query->bindParam(':offset', $offset, \PDO::PARAM_INT);
                    }
                }

                foreach ($params as $label => &$param)
                {
                    $query->bindParam(':' . $label, $param);
                }

                $query->setFetchMode(\PDO::FETCH_ASSOC);
                $query->execute();

                return $query->fetchAll();
            }
            catch (\PDOException $e)
            {
                $error = $query->errorInfo();

                //Get query string and params
                ob_start();
                $query->debugDumpParams();
                $debug_string = ob_get_clean();

                throw new \descartes\exceptions\DescartesExceptionSqlError(
                    'SQL Error : ' . "\n" . 
                    'SQLSTATE : ' . $error[0] . "\n" .
                    'Driver Error Code : ' . $error[1] . "\n" .
                    'Driver Error Message : ' . $error[2] . "\n" .
                    'SQL QUERY DEBUG :' . "\n" .
                    '-----------------' . "\n" .
                    $debug_string . "\n" .
                    '-----------------' . "\n"
                );
            }
        }


        /**
         * Get one line from table, posssibly with some conditions
         * see get
         */
        protected function _select_one (string $table, array $conditions = [], ?string $order_by = null, bool $desc = false, ?int $limit = null, ?int $offset = null)
        {
            $result = $this->_select($table, $conditions, $order_by, $desc, $limit, $offset);

            if (empty($result[0]))
            {
                return $result;
            }

            return $result[0];
        }
        
        /**
         * Count line from table, posssibly with some conditions
         * @param array $conditions : conditions of query Les conditions pour la mise à jour sous la forme "label" => "valeur". Un operateur '<, >, <=, >=, !' peux précder le label pour modifier l'opérateur par défaut (=)
         */
        protected function _count (string $table, array $conditions = []) : int
        {
            try
            {
                $wheres = array();
                $params = array();
                foreach ($conditions as $label => $value)
                {
                    $condition = $this->_evaluate_condition($label, $value);
                    $wheres[] = $condition['QUERY'];
                    $params = array_merge($params, $condition['PARAM']);
                }

                $query = "SELECT COUNT(*) as `count` FROM `" . $table . "` WHERE 1 " . (count($wheres) ? 'AND ' : '') . implode(' AND ', $wheres);
                $query = $this->pdo->prepare($query);

                foreach ($params as $label => &$param)
                {
                    $query->bindParam(':' . $label, $param);
                }

                $query->setFetchMode(\PDO::FETCH_ASSOC);
                $query->execute();

                return $query->fetch()['count'];
            }
            catch (\PDOException $e)
            {
                $error = $query->errorInfo();

                //Get query string and params
                ob_start();
                $query->debugDumpParams();
                $debug_string = ob_get_clean();

                throw new \descartes\exceptions\DescartesExceptionSqlError(
                    'SQL Error : ' . "\n" . 
                    'SQLSTATE : ' . $error[0] . "\n" .
                    'Driver Error Code : ' . $error[1] . "\n" .
                    'Driver Error Message : ' . $error[2] . "\n" .
                    'SQL QUERY DEBUG :' . "\n" .
                    '-----------------' . "\n" .
                    $debug_string . "\n" .
                    '-----------------' . "\n"
                );
            }
        }


        /**
         * Update data from table with some conditions
         * @param string $table : table name
         * @param array $data : new data to set
         * @param array $conditions : conditions of update, Les conditions pour la mise à jour sous la forme "label" => "valeur". Un operateur '<, >, <=, >=, !' peux précder le label pour modifier l'opérateur par défaut (=)
         * @param array $conditions : conditions to use, format 'fieldname' => 'value', fieldname can be preceed by operator '<, >, <=, >=, ! or = (by default)' to adapt comparaison operator
         * @return mixed : Number of line modified
         */
        protected function _update (string $table, array $data, array $conditions = array()) : int
        {
            $params = array();
            $sets = array();

            
            foreach ($data as $label => $value)
            {
                $label = preg_replace('#[^a-zA-Z0-9_]#', '', $label);
                $params['set_' . $label] = $value;
                $sets[] = '`' . $label . '` = :set_' . $label . ' ';
            }


            $wheres = array();
            foreach ($conditions as $label => $value)
            {
                $condition = $this->_evaluate_condition($label, $value);
                $wheres[] = $condition['QUERY'];
                $params = array_merge($params, $condition['PARAM']);
            }


            $query = "UPDATE `" . $table . "` SET " . implode(', ', $sets) . " WHERE 1 " . (count($wheres) ? " AND " : "") . implode(' AND ', $wheres);
            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Delete from table according to certain conditions
         * @param string $table : Table name
         * @param array $conditions : conditions to use, format 'fieldname' => 'value', fieldname can be preceed by operator '<, >, <=, >=, ! or = (by default)' to adapt comparaison operator
         * @return mixed : Number of line deleted
         */
        protected function _delete (string $table, array $conditions = []) : int
        {
            //On gère les conditions
            $wheres = array();
            $params = array();
            foreach ($conditions as $label => $value)
            {
                $condition = $this->_evaluate_condition($label, $value);
                $wheres[] = $condition['QUERY'];
                $params = array_merge($params, $condition['PARAM']);
            }

            $query = "DELETE FROM `" . $table . "` WHERE 1 " . (count($wheres) ? " AND " : "") . implode(' AND ', $wheres);
            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert new line into table
         * @param string $table : table name
         * @param array $data : new data
         * @return mixed : null on error, number of line inserted else
         */
        protected function _insert (string $table, array $data) : ?int
        {
            $params = array();
            $field_names = array();

            foreach ($data as $field_name => $value)
            {
                //Protect against injection in fieldname
                $field_name = preg_replace('#[^a-zA-Z0-9_]#', '', $field_name);
                $params[$field_name] = $value;
                $field_names[] = $field_name;
            }

            $query = "INSERT INTO `" . $table . "` (`" . implode('`, `', $field_names) . "`) VALUES(:" . implode(', :', $field_names) . ")";

            //On retourne le nombre de lignes insérées
            return $this->_run_query($query, $params, self::ROWCOUNT);
        }

    } 
