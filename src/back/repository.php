<?php
require 'models.php';
class DataBase {
    
    /**
     * Подключение к базе данных
     */
    public $db;
    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname=nomokoiw_admin;charset=UTF8','nomokoiw_admin','KESRdV2f');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    }

    /**
     * Загрузка файла на хост
     * 
     * Позволяет загрузить файл на хост и сохранить путь к нему в указанную таблицу, строку и колонку
     * 
     * @param string $l логин пользователя
     * @param string $p пароль пользователя
     * @param string $pid id строки в которую необходимо вставить адрес файла
     * @param blob $files файл для вставки из глобального массива $_FILES
     * @param string $t таблица, в которую вставить адрес файла
     * @param string $column столбец для вставки адреса файла
     */
    public function uploadFile($l, $p, $pid, $files, $t, $column){
        if($this->checkAdmin($l, $p)){
            $img=$this->getImage($pid, $t, $column);
            if($img){
                $this->removeFile($img);
            }
            $url = "http://client.nomokoiw.beget.tech/admin/";
            $n = basename($t."_".$pid."_".$files['Data']['name']);
            //$tid=ucfirst($t)."Id";
            $tid="Id";
            $t .="s";
            $d = "Files/$n";
            if(file_exists("Files")){
                
                if(move_uploaded_file($files['Data']['tmp_name'], $d)){
                    $s = $this->db->prepare("UPDATE $t SET $column=? WHERE $tid=?");
                    $s->execute(array($url.$d, $pid));
                    return($url.$d);
                }else{
                    return($_FILES['Data']['tmp_name']);
                }
            }else{
                mkdir("Files");
                if(move_uploaded_file($files['Data']['tmp_name'], $d)){
                    $s = $this->db->prepare("UPDATE $t SET $column=? WHERE $tid=?");
                    $s->execute(array($url.$d, $pid));
                    return($url.$d);
                }else{
                    return($_FILES['Data']['tmp_name']);
                }
            }
            
            return false;
        }else{
            return null;
        }
    }

    /**
     * Получение адреса изображения или файла
     * 
     * @param number $id id строки
     * @param string $t таблица для поиска адреса изображения
     * @param string $column столбец для поиска изображения
     * @return string адрес файла
     */
    public function getImage($id, $t, $column){
        // $tid=ucfirst($t)."Id";
        $tid="Id";
        $t .="s";
        $s = $this->db->prepare("SELECT $column FROM $t WHERE $tid=?");
        $s->execute(array($id));
        return $s->fetch()[$column];
    }

    /**
     * Генерация запроса вставки
     * 
     * @param mixed $ins объект для вставки, столбцы объекта должны соответсвовать столбцам таблицы
     * @param string $t таблица для вставки
     * @return array массив, первым элементом которого является строка запроса, вторым - массив вставляемых значений
     */
    private function genInsertQuery($ins, $t){
        $res = array('INSERT INTO '.$t.' (',array());
        $q = '';
        for ($i = 0; $i < count(array_keys($ins)); $i++) {
            $res[0] = $res[0].array_keys($ins)[$i].',';
            $res[1][]=$ins[array_keys($ins)[$i]];
            $q=$q.'?,';
            
        }
        $res[0]=rtrim($res[0],',');
        $res[0]=$res[0].') VALUES ('.rtrim($q,',').');';
        
        return $res;
        
    }

    /**
     * Генерация запроса обновления
     * 
     * @param array $keys стобцы таблицы, которые надо обновить
     * @param array $values значения, которые надо вставить в указанные стобцы
     * @param string $t таблица для вставки
     * @return array массив, первым элементом которого является строка запроса, вторым - массив вставляемых значений
     */
    private function genUpdateQuery($keys, $values, $t, $id){
        $res = array('UPDATE '.$t.' SET ',array());
        $q = '';
        for ($i = 0; $i < count($keys); $i++) {
            if($values[$i]!='now()'){
                $res[0] = $res[0].$keys[$i].'=?, ';
                $res[1][]=$values[$i];
            }
            else{
                $res[0] = $res[0].$keys[$i].'=now(), ';
            }
            
            
        }
        $res[0]=rtrim($res[0],', ');
        $res[0]=$res[0].' WHERE Id = '.$id;
        
        return $res;
        
    }
    
    /**
     * Удаление файла с хоста
     * 
     * @param string $filelink путь к файлу
     */
    private function removeFile($filelink){
        $path = explode('admin/',$filelink);
        if($path[1]){
            unlink($path[1]);
        }
        
    }
    
    // ------------------------       Запросы на получение данных из базы       ------------------------

    public function getSolids($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM solids");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Solid');
        return $sth->fetchAll();
    }

    public function getPeriodicals($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM periodicals");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Periodical');
        return $sth->fetchAll();
    }

    public function getCrochets($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM crochets");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Crochet');
        return $sth->fetchAll();
    }

    public function getMethods($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM methods");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Method');
        return $sth->fetchAll();
    }

    public function getAuthors($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM authors");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Author');
        return $sth->fetchAll();
    }
    
    public function getGrowings($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM growings");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Growing');
        return $sth->fetchAll();
    }

    public function getExperiments($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM experiments");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Experiment');
        return $sth->fetchAll();
    }

    public function getInventory($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM inventory");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Inventory');
        return $sth->fetchAll();
    }

    public function getCatalog($l, $p){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $sth = $this->db->query("SELECT * FROM catalog_of_solids");
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Catalog');
        return $sth->fetchAll();
    }

    private function getSolidPeriodicals($id){
        $sth = $this->db->prepare("SELECT * FROM periodicals_catalog pc JOIN periodicals p ON pc.id_period=p.id_periodicals WHERE id_solid=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Periodical');
        return $sth->fetchAll();
    }

    private function getCrochet($id){
        $sth = $this->db->prepare("SELECT * FROM crochets WHERE id_crochet=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Crochet');
        return $sth->fetch();
    }

    private function getMethod($id){
        $sth = $this->db->prepare("SELECT * FROM methods WHERE id_crochet=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Method');
        return $sth->fetch();
    }

    private function getSolid($id){
        $sth = $this->db->prepare("SELECT * FROM solids WHERE id_solid=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Solid');
        return $sth->fetch();
    }

    private function getPeriodicalAuthors($id){
        $sth = $this->db->prepare("SELECT * FROM periodic_author pa JOIN authors a ON pa.id_author=a.id_authors WHERE id_Periodic=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Author');
        return $sth->fetchAll();
    }

    private function getExperimentInventory($id){
        $sth = $this->db->prepare("SELECT * FROM exp_inv ei JOIN invetory i ON ei.id_inv=i.id_invetory WHERE id_exp=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Inventory');
        return $sth->fetchAll();
    }

    private function getPeriodicalSolids($id){
        $sth = $this->db->prepare("SELECT * FROM periodicals_catalog pc JOIN solids s ON pc.id_solid =s.id_solids WHERE id_period=?");
        $sth->execute(array($id));
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Solid');
        return $sth->fetchAll();
    }

    // ------------------------       Запросы на добавление данных в базу      ------------------------
    

    public function addSolid($l, $p, $solid){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($solid,"solids");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addCrochet($l, $p, $crochet){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($crochet,"crochets");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }
    
    public function addInventory($l, $p, $inv){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($inv,"invetory");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addAuthor($l, $p, $auth){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($auth,"authors");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addMethod($l, $p, $meth){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($meth,"methods");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addPeriodical($l, $p, $per){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($per,"periodicals");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    private function addPeriodicalSolid($l, $p, $solid){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($solid,"periodicals_catalog");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    private function addPeriodicalAuthor($l, $p, $auth){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($auth,"periodic_author");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addGrowing($l, $p, $gr){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($gr,"growings");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addSolidCatalog($l, $p, $solid){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($solid,"catalog_of_solids");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    public function addExperiment($l, $p, $ex){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($ex,"experiments");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }

    private function addExperimentInventory($l, $p, $inv){
        if(!$this->checkAdmin($l, $p)){
            return;
        }
        $res = $this->genInsertQuery($inv,"exp_inv");
        $s = $this->db->prepare($res[0]);
        if($res[1][0]!=null){
            $s->execute($res[1]);
        }
        return $this->db->lastInsertId();
    }
    
    // ------------------------       Запросы на изменение данных в базе       ------------------------
    
    public function updateNews($l, $p, $new){
        if($this->checkAdmin($l, $p)){
            $id = $new['Id'];
            unset($new['Id']);
            $a = $this->genUpdateQuery(array_keys($new), array_values($new), "news", $id);
            $s = $this->db->prepare($a[0]);
            $s->execute($a[1]);
            return $a;
        }else{
            return false;
        }
    }

    /**
     * Вход админа
     * 
     * Проверка корректности логина и пароля
     * @param string $l логин админа
     * @param string $p пароль админа
     * @return boolean true - данные корректны
     */
    public function enterAdmin($l, $p){
        
        return $this->checkAdmin($l, $p);
    }

    /**
     * Вход админа
     * 
     * Проверка корректности логина и пароля
     * @param string $l логин админа
     * @param string $p пароль админа
     * @return boolean true - данные корректны
     */
    private function checkAdmin($l, $p){
        
        $access = file("user.php"); 
        $login = trim($access[1]); 
        $passw = trim($access[2]); 
        if($l==$login && $p==$passw){
            return true;
        }else{
            return false;
        }
    }


    
}
?>